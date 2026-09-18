<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->geography('location', subtype: 'point', srid: 4326)->nullable()->after('boundary');
        });

        // Traslada los datos ya cargados por SQL directo en latitude/longitude
        // hacia la columna espacial `location`, siguiendo el mismo formato WKT
        // que usa HasGeoLocation::pointWkt() (orden lng/lat, SRID 4326).
        // latitude/longitude quedan intactas como respaldo — no se eliminan aquí.
        DB::statement("
            UPDATE destinations
            SET location = ST_GeomFromText(CONCAT('POINT(', longitude, ' ', latitude, ')'), 4326)
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
