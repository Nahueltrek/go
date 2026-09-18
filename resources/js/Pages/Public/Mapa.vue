<script setup>
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  layers: Array,
})

const mapContainer = ref(null)
const activeLayers = ref(new Set(props.layers.map((l) => l.key)))
const allFeatures = ref([])
const userLocation = ref(null)
const selectedFeature = ref(null)
const locating = ref(false)
const eclipseZoneVisible = ref(true)
let map
let geolocate
let eclipseZoneLabelMarkers = []

/**
 * Franja referencial de mayor duración del Eclipse Solar Anular del
 * 6-feb-2027 — NO es el trazado científico exacto de la franja de
 * anularidad (eso requiere el KML/GeoJSON oficial de NASA/observatorios,
 * que este entorno no puede descargar). Es un rectángulo recto orientado
 * según la línea real entre Quellón y Palena (las localidades públicamente
 * citadas con mayor duración, junto a Futaleufú y Chaitén, todas dentro de
 * esta franja) — no una elipse arbitraria — pensado para dar notoriedad
 * visual a la dirección real por la que pasa la sombra hasta que se cargue
 * el trazado oficial.
 */
const ECLIPSE_TOWNS = {
  quellon: { lat: -43.121, lng: -73.608 },
  palena: { lat: -43.618, lng: -71.804 },
}
const ECLIPSE_ZONE_CENTER = { lat: -43.21, lng: -72.5 }
const ECLIPSE_MAX_DURATION_POINT = { lat: -43.1833, lng: -71.8667, label: 'Futaleufú — 7 min 29 s (mayor duración registrada en Chile)' }

function eclipseBandPolygon() {
  const centerLat = ECLIPSE_ZONE_CENTER.lat
  const centerLng = ECLIPSE_ZONE_CENTER.lng
  const latKmPerDeg = 110.574
  const lngKmPerDeg = 111.320 * Math.cos(centerLat * Math.PI / 180)

  // Dirección real de la franja: línea Quellón → Palena, en km locales.
  const dLatKm = (ECLIPSE_TOWNS.palena.lat - ECLIPSE_TOWNS.quellon.lat) * latKmPerDeg
  const dLngKm = (ECLIPSE_TOWNS.palena.lng - ECLIPSE_TOWNS.quellon.lng) * lngKmPerDeg
  const len = Math.hypot(dLatKm, dLngKm)
  const dir = { lng: dLngKm / len, lat: dLatKm / len }
  const perp = { lng: -dir.lat, lat: dir.lng } // rotar 90°

  const halfLength = 170 // km — más larga que la distancia real entre pueblos, para sugerir que la franja sigue más allá del mapa visible
  const halfWidth = 32   // km

  const corner = (alongSign, acrossSign) => {
    const eastKm = alongSign * halfLength * dir.lng + acrossSign * halfWidth * perp.lng
    const northKm = alongSign * halfLength * dir.lat + acrossSign * halfWidth * perp.lat
    return [centerLng + eastKm / lngKmPerDeg, centerLat + northKm / latKmPerDeg]
  }

  const ring = [corner(-1, -1), corner(-1, 1), corner(1, 1), corner(1, -1), corner(-1, -1)]
  return ring
}

const layerMeta = computed(() => Object.fromEntries(props.layers.map((l) => [l.key, l])))

// Haversine — suficiente para "cerca tuyo" a escala de decenas de km, no
// necesitamos precisión geodésica para esto.
function distanceKm(lat1, lng1, lat2, lng2) {
  const R = 6371
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLng = (lng2 - lng1) * Math.PI / 180
  const a = Math.sin(dLat / 2) ** 2
    + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}

// Se recalcula porque activeLayers se reasigna (ver toggleLayer) — un Set
// mutado in-place no dispara reactividad de Vue por sí solo.
const nearbyFeatures = computed(() => {
  if (!userLocation.value) return []
  const { lat, lng } = userLocation.value
  return allFeatures.value
    .filter((f) => activeLayers.value.has(f.properties.layer))
    .map((f) => ({
      ...f,
      distanceKm: distanceKm(lat, lng, f.geometry.coordinates[1], f.geometry.coordinates[0]),
    }))
    .sort((a, b) => a.distanceKm - b.distanceKm)
    .slice(0, 12)
})

function openFeature(feature) {
  selectedFeature.value = {
    ...feature.properties,
    coordinates: feature.geometry.coordinates,
    distanceKm: userLocation.value
      ? distanceKm(userLocation.value.lat, userLocation.value.lng, feature.geometry.coordinates[1], feature.geometry.coordinates[0])
      : (feature.distanceKm ?? null),
  }
  map.flyTo({ center: feature.geometry.coordinates, zoom: Math.max(map.getZoom(), 13), duration: 600 })
}

function closeFeature() {
  selectedFeature.value = null
}

async function loadGeojson() {
  const res = await fetch('/mapa/geojson')
  const data = await res.json()
  allFeatures.value = data.features ?? []

  if (!userLocation.value && data.features?.length) {
    const bounds = new maplibregl.LngLatBounds()
    data.features.forEach((f) => bounds.extend(f.geometry.coordinates))
    map.fitBounds(bounds, { padding: 80, maxZoom: 10, duration: 800 })
  }

  if (map.getSource('gochile-points')) {
    map.getSource('gochile-points').setData(data)
  } else {
    map.addSource('gochile-points', {
      type: 'geojson',
      data,
      cluster: true,
      clusterMaxZoom: 10,
      clusterRadius: 50,
    })

    map.addLayer({
      id: 'clusters',
      type: 'circle',
      source: 'gochile-points',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': '#1c1c1c',
        'circle-radius': ['step', ['to-number', ['coalesce', ['get', 'point_count'], 0]], 16, 10, 20, 30, 26],
        'circle-opacity': 0.85,
      },
    })

    map.addLayer({
      id: 'cluster-count',
      type: 'symbol',
      source: 'gochile-points',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': ['to-string', ['coalesce', ['get', 'point_count_abbreviated'], '']],
        'text-size': 12,
      },
      paint: { 'text-color': '#ffffff' },
    })

    map.addLayer({
      id: 'unclustered-point',
      type: 'circle',
      source: 'gochile-points',
      filter: ['!', ['has', 'point_count']],
      paint: {
        'circle-color': [
          'match', ['get', 'layer'],
          'experiencias', '#1c8c82',
          'operadores', '#b4562a',
          'negocios', '#7b5ea3',
          'proyectos', '#4a7c3f',
          'atractivos', '#c2410c',
          'destinos', '#0d9488',
          'eventos', '#c9962c',
          '#1c1c1c',
        ],
        'circle-radius': 7,
        'circle-stroke-width': 2,
        'circle-stroke-color': '#ffffff',
      },
    })

    map.on('click', 'unclustered-point', (e) => {
      openFeature(e.features[0])
    })

    map.on('click', 'clusters', (e) => {
      const features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] })
      const clusterId = features[0].properties.cluster_id
      map.getSource('gochile-points').getClusterExpansionZoom(clusterId, (err, zoom) => {
        if (err) return
        map.easeTo({ center: features[0].geometry.coordinates, zoom })
      })
    })

    map.on('mouseenter', 'unclustered-point', () => (map.getCanvas().style.cursor = 'pointer'))
    map.on('mouseleave', 'unclustered-point', () => (map.getCanvas().style.cursor = ''))
  }
}

function htmlPill(text, { color = '#92400e', bold = true } = {}) {
  const el = document.createElement('div')
  el.textContent = text
  el.style.cssText = `max-width:190px;padding:4px 10px;border-radius:9999px;`
    + `background:rgba(255,255,255,.92);color:${color};font-size:11px;font-weight:${bold ? 600 : 500};`
    + `text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.15);pointer-events:none;`
    + `white-space:normal;line-height:1.3;`
  return el
}

function addEclipseZoneLayer() {
  map.addSource('eclipse-zone', {
    type: 'geojson',
    data: {
      type: 'Feature',
      properties: {},
      geometry: { type: 'Polygon', coordinates: [eclipseBandPolygon()] },
    },
  })

  map.addLayer({
    id: 'eclipse-zone-fill',
    type: 'fill',
    source: 'eclipse-zone',
    paint: { 'fill-color': '#f59e0b', 'fill-opacity': 0.12 },
  })

  map.addLayer({
    id: 'eclipse-zone-outline',
    type: 'line',
    source: 'eclipse-zone',
    paint: { 'line-color': '#d97706', 'line-width': 2, 'line-dasharray': [3, 2] },
  })

  // Etiquetas como Markers HTML (no symbol layer de MapLibre) — el estilo
  // base (openfreemap/positron) no sirve los glyphs de emoji/rangos unicode
  // altos (404 en /fonts/.../55296-... etc.), así que un 'text-field' con
  // 🌑 rompía el render de toda la capa. Un div posicionado no depende de eso.
  eclipseZoneLabelMarkers = [
    new maplibregl.Marker({ element: htmlPill('🌑 Franja de mayor duración — Eclipse 2027 (referencial, no es el trazado oficial)'), anchor: 'center' })
      .setLngLat([ECLIPSE_ZONE_CENTER.lng, ECLIPSE_ZONE_CENTER.lat])
      .addTo(map),
    new maplibregl.Marker({ element: htmlPill(ECLIPSE_MAX_DURATION_POINT.label), anchor: 'top' })
      .setLngLat([ECLIPSE_MAX_DURATION_POINT.lng, ECLIPSE_MAX_DURATION_POINT.lat])
      .addTo(map),
  ]
}

function toggleEclipseZone() {
  eclipseZoneVisible.value = !eclipseZoneVisible.value
  const visibility = eclipseZoneVisible.value ? 'visible' : 'none'
  for (const id of ['eclipse-zone-fill', 'eclipse-zone-outline']) {
    if (map.getLayer(id)) map.setLayoutProperty(id, 'visibility', visibility)
  }
  const display = eclipseZoneVisible.value ? '' : 'none'
  eclipseZoneLabelMarkers.forEach((m) => m.getElement().style.setProperty('display', display))

  if (eclipseZoneVisible.value) {
    const bounds = new maplibregl.LngLatBounds()
    eclipseBandPolygon().forEach((coord) => bounds.extend(coord))
    map.fitBounds(bounds, { padding: 60, duration: 1000 })
  }
}

function toggleLayer(key) {
  const next = new Set(activeLayers.value)
  if (next.has(key)) next.delete(key)
  else next.add(key)
  activeLayers.value = next
  applyLayerFilter()
}

function applyLayerFilter() {
  const keys = [...activeLayers.value]
  const filter = ['!', ['has', 'point_count']]
  const layerFilter = keys.length
    ? ['all', filter, ['in', ['get', 'layer'], ['literal', keys]]]
    : filter
  if (map.getLayer('unclustered-point')) {
    map.setFilter('unclustered-point', layerFilter)
  }
}

function locateMe() {
  locating.value = true
  geolocate?.trigger()
}

onMounted(async () => {
  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://tiles.openfreemap.org/styles/positron',
    center: [-71.0, -35.5], // centro aproximado de Chile continental
    zoom: 4.2,
  })

  map.addControl(new maplibregl.NavigationControl(), 'top-right')

  geolocate = new maplibregl.GeolocateControl({ trackUserLocation: false, showUserLocation: true })
  map.addControl(geolocate, 'top-right')

  geolocate.on('geolocate', (e) => {
    userLocation.value = { lat: e.coords.latitude, lng: e.coords.longitude }
    locating.value = false
    map.flyTo({ center: [e.coords.longitude, e.coords.latitude], zoom: 12, duration: 1000 })
  })
  geolocate.on('error', () => { locating.value = false })

  map.on('load', () => {
    addEclipseZoneLayer()
    loadGeojson()
    // Pide ubicación automáticamente al abrir el mapa — si el usuario
    // rechaza el permiso, el mapa se queda con la vista de todo Chile
    // (el fitBounds de loadGeojson ya corrió) y "locateMe" queda como
    // botón manual para reintentar.
    locateMe()
  })
})

onBeforeUnmount(() => map?.remove())
</script>

<template>
  <SeoHead
    title="Mapa — GO Chile"
    description="Explorá en el mapa experiencias, operadores, negocios, proyectos, atractivos y eventos outdoor cerca tuyo."
  />
  <div class="relative h-screen w-full">
    <div ref="mapContainer" style="position:absolute;top:0;right:0;bottom:0;left:0;"></div>

    <div class="absolute top-4 left-4 right-4 z-10 flex items-center justify-between">
      <Link href="/" class="flex items-center gap-2 bg-white/95 backdrop-blur rounded-full pl-1.5 pr-3 py-1.5 shadow-lg">
        <img src="/images/logo.png" alt="GO Chile" class="h-8 w-8 rounded-full" />
        <span class="text-xs font-semibold text-sky-950">GO Chile</span>
      </Link>
      <div class="flex items-center gap-2">
        <button @click="locateMe"
                class="bg-white/95 backdrop-blur rounded-full px-3 py-2 shadow-lg text-sky-950 disabled:opacity-50"
                :disabled="locating">
          <span class="text-sm">{{ locating ? '…' : '📍' }}</span>
        </button>
        <Link href="/explorar" class="bg-white/95 backdrop-blur rounded-full px-3 py-2 shadow-lg text-sky-950">
          <span class="text-sm">🔎</span>
        </Link>
      </div>
    </div>

    <!-- Cerca tuyo — sugerencias ordenadas por distancia real, solo si ya tenemos ubicación -->
    <div v-if="nearbyFeatures.length" class="absolute top-16 left-0 right-0 z-10 px-4">
      <p class="text-[11px] font-semibold text-white mb-1.5 drop-shadow">Cerca tuyo</p>
      <div class="flex gap-2 overflow-x-auto no-scrollbar">
        <button
          v-for="f in nearbyFeatures"
          :key="f.properties.name + f.geometry.coordinates.join(',')"
          @click="openFeature(f)"
          class="shrink-0 text-left bg-white/95 backdrop-blur rounded-2xl px-3 py-2 shadow-lg w-40"
        >
          <div class="flex items-center gap-1">
            <span class="text-xs">{{ layerMeta[f.properties.layer]?.icon }}</span>
            <span class="text-xs font-medium text-sky-900 truncate">{{ f.properties.name }}</span>
          </div>
          <p class="text-[11px] text-sky-500 mt-0.5">{{ f.distanceKm.toFixed(1) }} km</p>
        </button>
      </div>
    </div>

    <!-- Ficha flotante de la selección -->
    <transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-y-full"
      enter-to-class="translate-y-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-y-0"
      leave-to-class="translate-y-full"
    >
      <div v-if="selectedFeature" class="absolute bottom-0 left-0 right-0 z-20 px-4 pb-4">
        <div class="bg-white rounded-2xl shadow-2xl p-4">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="text-sm">{{ layerMeta[selectedFeature.layer]?.icon }}</span>
                <span class="text-[11px] font-medium uppercase tracking-wide"
                      :style="{ color: layerMeta[selectedFeature.layer]?.color }">
                  {{ layerMeta[selectedFeature.layer]?.label }}
                </span>
              </div>
              <h2 class="text-base font-bold text-sky-950 truncate mt-0.5">{{ selectedFeature.name }}</h2>
              <p v-if="selectedFeature.subtitle" class="text-xs text-sky-500 mt-0.5">{{ selectedFeature.subtitle }}</p>
              <p v-if="selectedFeature.distanceKm !== null" class="text-xs text-sky-400 mt-1">
                📍 {{ selectedFeature.distanceKm.toFixed(1) }} km de tu ubicación
              </p>
            </div>
            <button @click="closeFeature" class="shrink-0 text-sky-400 text-lg leading-none px-1">✕</button>
          </div>
          <a v-if="selectedFeature.url" :href="selectedFeature.url"
             class="block text-center text-xs font-medium rounded-full bg-sky-900 text-white py-2.5 mt-3">
            Ver ficha completa →
          </a>
        </div>
      </div>
    </transition>

    <div v-if="!selectedFeature" class="absolute bottom-4 left-0 right-0 px-4 z-10">
      <div class="flex gap-2 overflow-x-auto no-scrollbar bg-white/95 backdrop-blur rounded-full px-2 py-2 shadow-lg mx-auto max-w-fit">
        <button
          @click="toggleEclipseZone"
          class="shrink-0 flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-opacity"
          style="background-color:#f59e0b1a; color:#92400e"
          :class="eclipseZoneVisible ? 'opacity-100' : 'opacity-35'"
        >
          <span>🌑</span>Eclipse 2027
        </button>
        <button
          v-for="l in layers"
          :key="l.key"
          @click="toggleLayer(l.key)"
          class="shrink-0 flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-opacity"
          :style="{ backgroundColor: l.color + '1a', color: l.color }"
          :class="activeLayers.has(l.key) ? 'opacity-100' : 'opacity-35'"
        >
          <span>{{ l.icon }}</span>{{ l.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
