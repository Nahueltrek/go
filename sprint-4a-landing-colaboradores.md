# SPRINT 4a — LANDING DE COLABORADORES (imagen de portada + mapa embebido)
## Prompt para Claude Code (carpeta local C:\gochile-code)

Contexto: ya existe el formulario de edición en `admin/organizaciones` (Sprint
3a, funcionando en producción). Esta tarea mejora la página PÚBLICA de cada
colaborador (`/operadores/{slug}`) con imagen de portada, más datos
destacados, animaciones, y un mapa embebido mostrando su ubicación.

Recordatorio de infraestructura (importante, ya nos costó horas hoy):
después de compilar (`npm run build`), los archivos van a
`C:\gochile-code\public\build`. Al subir al servidor hay que copiar esa
carpeta a **dos** lugares: `~/domains/go.0km.app_new/public/build/` (la que
lee Laravel de verdad) Y `~/domains/go.0km.app/public_html/build/` (la
pública). Si solo se actualiza una, el sitio sirve una mezcla vieja/nueva sin
dar ningún error visible. Después de subir, hay que purgar el "Cache
Manager" (LiteSpeed) desde hPanel — si no, el navegador sigue viendo la
versión anterior aunque el servidor ya tenga la nueva.

### Paso 0 — Investigar antes de escribir código

1. Leer `resources/js/Pages/Public/Operador.vue` completo (la landing
   pública actual de un colaborador).
2. Leer `app/Http/Controllers/OrganizationController.php` (el `show()`
   público, no el de Admin) para ver qué datos ya le pasa a la vista.
3. Revisar `resources/js/Components/MapView.vue` — ya existe un componente
   de mapa (usado en `/mapa`), ver si se puede reutilizar en modo "un solo
   punto, sin filtros" para esta landing, en vez de crear un mapa nuevo
   desde cero.
4. Revisar la migración de `organizations` — confirmar que no exista ya un
   campo de portada antes de agregar uno nuevo.

### Paso 1 — Backend: campo de imagen de portada

Crear una migración nueva:
```php
Schema::table('organizations', function (Blueprint $table) {
    $table->string('cover_image')->nullable()->after('logo_url');
});
```

Agregar `cover_image` a:
- `$fillable` en `app/Models/Organization.php`
- La validación de `update()` en `Admin/OrganizationController.php`
  (`'cover_image' => ['nullable', 'url', 'max:255']`)
- `OrganizationResource.php` (agregar al array devuelto)
- El formulario `Admin/OrganizacionesEdit.vue` (un input de texto para la
  URL, junto a donde ya está `logo_url` — no hay sistema de subida de
  archivos todavía, así que por ahora es solo una URL pegada a mano, igual
  que `logo_url` y `website`)

### Paso 2 — Frontend: rediseño de la landing pública

En `resources/js/Pages/Public/Operador.vue`:

1. **Hero con imagen de portada**: si `organization.cover_image` existe,
   mostrarla como fondo grande arriba de la página (con overlay oscuro
   gradiente para que el nombre se lea encima, estilo típico de landing).
   Si no existe `cover_image`, usar un fondo de color sólido con el mismo
   degradé sky-800→sky-950 que ya se usa en el resto del admin, para no
   dejar un hueco roto.
2. **Datos destacados**: tipo de colaborador, comuna/región, categorías (ya
   vienen en `OrganizationResource`), y los enlaces de contacto (instagram,
   website, whatsapp) como botones/chips visibles, no solo texto plano.
3. **Mapa embebido**: si `organization.location` (lat/lng) existe, mostrar
   un mapa chico (usando el componente del Paso 0) centrado en ese punto
   con un único marcador — sin capas ni filtros, solo referencia visual. Si
   no hay ubicación cargada todavía, no mostrar el mapa (nada de mapa vacío
   o con error).
4. **Animaciones**: reutilizar el patrón `fade-in-up` que ya se usa en
   `Organizaciones.vue`/`OrganizacionesEdit.vue` para las secciones de la
   página (aparecen escalonadas al cargar), en vez de inventar una
   animación nueva.
5. Mantener la paleta de colores ya establecida (sky-900/sky-50, degradé
   sky-800→sky-950) — esta landing debe sentirse parte del mismo sitio, no
   un diseño aparte.

### Paso 3 — Compilar y entregar

1. `npm run build`
2. Confirmar que no hay errores de compilación.
3. Listar los archivos modificados/creados para que Nahuel los suba al
   servidor (los PHP/migración por scp, y la carpeta `build/` completa a
   los dos destinos mencionados arriba).

### Paso 4 — Verificación (la hace Nahuel en el servidor, no vos)

- Correr `php artisan migrate` en el servidor para aplicar la columna nueva.
- Cargar una URL de imagen de portada de prueba en Green Sport desde
  `admin/organizaciones` → Editar.
- Visitar `/operadores/green-sport` y confirmar que se ve la portada, los
  datos y el mapa (Green Sport ya tiene coordenadas cargadas del Sprint 3a).
- Confirmar que un colaborador SIN cover_image ni ubicación (por ejemplo uno
  de los que todavía no editamos) no rompe la página — debe verse bien con
  los valores por defecto.
