<?php

namespace App\Models\Concerns;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * Las columnas `geography(subtype: ...)` de MariaDB no tienen un cast nativo
 * en Eloquent — el driver las devuelve como WKB binario en una lectura
 * normal, y no aceptan un valor PHP plano en un insert/update normal. Este
 * trait centraliza el patrón que se usa en todo el proyecto para leerlas y
 * escribirlas. Ver docs/GEO_MARIADB.md para el detalle de las queries.
 *
 * ESCRITURA: nunca asignar lat/lng directo a la columna. Usar:
 *   $business->location = Business::pointExpression($lat, $lng);
 *   $business->save();
 *
 * LECTURA: la columna cruda no es usable en PHP. Para traer lat/lng legibles,
 * encadenar el scope en la query:
 *   Business::withCoordinates()->find($id);
 * Eso agrega los atributos planos `latitude`/`longitude` al resultado.
 */
trait HasGeoLocation
{
    /**
     * Arma el literal SQL de un punto en WKT. $lat/$lng en grados decimales
     * (WGS84). Recordar: WKT usa orden (X Y) = (lng lat). Se devuelve como
     * string plano (no como Expression) para poder reutilizarlo dentro de
     * otras cadenas SQL sin depender de cómo cada versión de Laravel
     * castea un Expression a string.
     */
    protected static function pointWkt(float $lat, float $lng): string
    {
        return sprintf("ST_GeomFromText('POINT(%F %F)', 4326)", $lng, $lat);
    }

    /**
     * Expresión lista para asignar directo a un atributo del modelo:
     *   $business->location = Business::pointExpression($lat, $lng);
     *   $business->save();
     */
    public static function pointExpression(float $lat, float $lng): Expression
    {
        return DB::raw(self::pointWkt($lat, $lng));
    }

    /**
     * Agrega columnas `latitude`/`longitude` legibles a la query, calculadas
     * a partir de la columna espacial indicada (por defecto `location`).
     */
    public function scopeWithCoordinates($query, string $column = 'location')
    {
        return $query->addSelect([
            '*',
            DB::raw("ST_Y(`{$column}`) as latitude"),
            DB::raw("ST_X(`{$column}`) as longitude"),
        ]);
    }

    /**
     * Filtra por distancia (en metros) a un punto dado. Requiere que la
     * columna tenga índice spatial (ver Fase 1 README para qué columnas lo
     * tienen). No usa el índice para el filtro de radio en sí — MariaDB no
     * soporta ST_DWithin — así que en tablas grandes conviene acotar antes
     * por otra columna (destino, categoría) para no barrer todo.
     */
    public function scopeNearby($query, float $lat, float $lng, float $radiusMeters, string $column = 'location')
    {
        $point = self::pointWkt($lat, $lng);

        return $query
            ->addSelect(['*', DB::raw("ST_Distance_Sphere(`{$column}`, {$point}) as distance_m")])
            ->whereRaw("ST_Distance_Sphere(`{$column}`, {$point}) <= ?", [$radiusMeters])
            ->orderBy('distance_m');
    }
}
