# SPRINT 8 — CAPA DE ATRACTIVOS EN EL MAPA
## Prompt para Claude Code (carpeta local C:\gochile-code)

Contexto: `MapController.php` arma el GeoJSON de `/mapa/geojson` combinando
capas de `experiencias`, `operadores`, `proyectos`, `negocios`, `eventos` —
pero nunca incluyó una capa de `attractions`, pese a que el modelo
`Attraction` ya existe, usa `HasGeoLocation` (mismo patrón que el resto), y
hoy tiene 2 registros reales con coordenadas (Monumento Natural El Morado,
Embalse El Yeso). Por eso no aparecen en el mapa aunque tengan ubicación
cargada.

`destinations` (111 registros, Cajón del Maipo + 110 áreas CONAF) NO se
agrega en este sprint — esa tabla no tiene columna de ubicación puntual
(`location`), solo un `boundary` (polígono) opcional que está vacío en
todos los registros. Mostrarlos requeriría coordenadas que todavía no
tenemos.

### Paso 0 — Investigar antes de escribir código

1. Leer `app/Models/Attraction.php` completo (confirmar que usa
   `HasGeoLocation`, y qué campos tiene: `category`, `cover_image`, etc.).
2. Leer `MapController.php` completo, específicamente cómo arma cada
   capa existente (`experiencias`, `operadores`, etc.) para replicar
   exactamente el mismo patrón con `Attraction`.
3. Confirmar si existe una ruta pública `/atractivos/{slug}` funcionando
   (la auditoría inicial la mencionaba) para poder armar el link de cada
   punto del mapa correctamente.
4. Revisar `resources/js/Pages/Public/Mapa.vue` para ver cómo se define
   la lista de capas visibles/filtrables en el frontend, y agregar
   "Atractivos" ahí también si hace falta (no solo en el backend).

### Paso 1 — Agregar la capa en el backend

En `MapController.php`, agregar un bloque igual a los existentes:
```php
if (! $layer || $layer === 'atractivos') {
    $features = $features->merge(
        Attraction::withCoordinates()->whereNotNull('location')->get()
            ->filter(fn ($a) => $a->latitude !== null)
            ->map(fn ($a) => $this->feature(
                $a->latitude, $a->longitude, 'atractivos', $a->name,
                "/atractivos/{$a->slug}", $a->category
            ))
    );
}
```
(ajustar nombres de campo exactos según lo que confirme el Paso 0 — por
ejemplo si `category` no existe con ese nombre)

Agregar también la entrada correspondiente en `layerDefinitions()`:
```php
['key' => 'atractivos', 'label' => 'Atractivos', 'color' => '#c2410c', 'icon' => '📍'],
```
(elegir color/ícono que no choque visualmente con las capas existentes)

### Paso 2 — Frontend

Si `Mapa.vue` tiene una lista de capas hardcodeada (no generada dinámicamente
desde `layerDefinitions()`), agregar "Atractivos" ahí también, siguiendo el
mismo patrón visual que las demás.

### Paso 3 — Compilar y entregar

1. `npm run build`
2. Confirmar que no hay errores.
3. Listar archivos modificados con rutas completas.

### Paso 4 — Verificación (la hace Nahuel en el servidor)

- Visitar `/mapa` y confirmar que aparecen dos puntos nuevos (El Morado,
  Embalse El Yeso) en el Cajón del Maipo, con la capa "Atractivos" activada.
- Confirmar que las demás capas (operadores, experiencias) siguen
  funcionando igual que antes.
- Hacer clic en uno de los puntos de atractivo y confirmar que lleva a
  `/atractivos/{slug}` correctamente.
