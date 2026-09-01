# Geoespacial en MariaDB (en vez de PostGIS)

El plan maestro original pedía PostGIS como requisito obligatorio para el
componente geográfico. El hosting real (compartido, sin PostgreSQL) no lo
permite. Este documento es el reemplazo funcional: qué se puede hacer con los
tipos espaciales nativos de MariaDB y cómo se traduce cada consulta que pedía
el plan original.

## Qué cambia

- Tipo de columna: `POINT`/`POLYGON`/`LINESTRING` con `SRID 4326` (en vez de
  `geography(...)` de PostGIS).
- Índice: `SPATIAL INDEX` (R-tree), en vez de `GIST`. **Restricción real de
  MariaDB: toda columna con índice spatial debe ser `NOT NULL`** — por eso
  algunas columnas geográficas (`destinations.boundary`, `communes.boundary`,
  `routes.path`) quedaron sin índice: son legítimamente opcionales al crear el
  registro. Las que sí son obligatorias al crear (`businesses.location`,
  `business_locations.point`, `attractions.location`) se hicieron `NOT NULL`
  precisamente para poder indexarlas.
- Sin `ST_DWithin` nativo — se resuelve con `ST_Distance_Sphere` (disponible
  en MariaDB 10.9+; confirmar versión exacta en el hosting) o con la fórmula
  de Haversine calculada en SQL si no está disponible.

## Equivalencias de consultas (las que pedía el documento maestro, sección 9)

**"Buscar cerca de un punto" / "dentro de un radio":**
```sql
SELECT *, ST_Distance_Sphere(location, POINT(:lng, :lat)) AS distance_m
FROM businesses
WHERE ST_Distance_Sphere(location, POINT(:lng, :lat)) <= :radius_m
ORDER BY distance_m
```
Nota: `POINT(lng, lat)` — MariaDB espera longitud primero, no como el orden
"lat, lng" que se usa en muchas APIs de mapas; hay que ser consistente en todo
el código.

**"Buscar dentro de un radio" (variante con índice, más rápido en tablas
grandes):** primero filtrar por un bounding box con el índice `SPATIAL`, y
recién ahí aplicar `ST_Distance_Sphere` exacto sobre ese subconjunto — patrón
estándar para compensar que el índice R-tree no entiende "radio" directamente:
```sql
SELECT *, ST_Distance_Sphere(location, POINT(:lng, :lat)) AS distance_m
FROM businesses
WHERE MBRContains(
    ST_Buffer(POINT(:lng, :lat), :radius_deg), -- aproximación en grados, no metros
    location
)
HAVING distance_m <= :radius_m
ORDER BY distance_m
```

**"Buscar por comuna":** dado que `communes.boundary` no tiene índice spatial
(es nullable), esta consulta no puede optimizarse con `ST_Contains` + índice
como en PostGIS. Alternativa práctica para el piloto: filtrar directamente por
`businesses.commune_id` (ya es una FK indexada normal) en vez de por
contención geométrica. Es menos flexible que PostGIS pero es exacto y rápido
para el volumen de datos de un solo destino piloto.

**"Buscar negocios cercanos a una ruta":** igual que "por comuna", sin índice
sobre `routes.path`. Para el piloto, la alternativa es precalcular en
`route_points` qué negocios están asociados a cada ruta (ya existe esa tabla)
en vez de calcular distancia a la traza en cada consulta.

**"Calcular distancias":** `ST_Distance_Sphere(punto_a, punto_b)` devuelve
metros directamente — más simple que el equivalente en PostGIS.

**"Filtrar por categorías":** sin cambios, es un filtro relacional normal
(`business_category_id`), no geoespacial.

## Qué se pierde de verdad frente a PostGIS

- Sin operadores topológicos completos (`ST_Intersects`, `ST_Within` con alto
  rendimiento sobre polígonos complejos).
- Sin soporte real para geometrías 3D o proyecciones múltiples.
- El patrón de "bounding box + filtro exacto" hay que implementarlo a mano en
  cada query en vez de que el motor lo resuelva con un solo operador.

Si el proyecto escala a "Ruta 360 Chile" con múltiples destinos y volumen
alto, migrar a un Postgres/PostGIS gestionado (ej. un VPS separado solo para
la base de datos, o un proveedor administrado) sigue siendo la recomendación
de largo plazo — este documento es la solución para operar en el hosting
compartido actual, no un reemplazo permanente ideal.
