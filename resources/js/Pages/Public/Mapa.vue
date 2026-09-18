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
let map
let geolocate

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
