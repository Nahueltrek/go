<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'

const props = defineProps({
  layers: Array,
})

const mapContainer = ref(null)
const activeLayers = ref(new Set(props.layers.map((l) => l.key)))
let map

function escapeHtml(str) {
  const div = document.createElement('div')
  div.textContent = str ?? ''
  return div.innerHTML
}

async function loadGeojson() {
  const res = await fetch('/mapa/geojson')
  const data = await res.json()

  if (data.features?.length) {
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
          'eventos', '#c9962c',
          '#1c1c1c',
        ],
        'circle-radius': 7,
        'circle-stroke-width': 2,
        'circle-stroke-color': '#ffffff',
      },
    })

    map.on('click', 'unclustered-point', (e) => {
      const props = e.features[0].properties
      new maplibregl.Popup({ offset: 14 })
        .setLngLat(e.features[0].geometry.coordinates)
        .setHTML(
          `<strong>${escapeHtml(props.name)}</strong><br>` +
          `<span style="font-size:12px;color:#666">${escapeHtml(props.subtitle ?? '')}</span><br>` +
          `<a href="${props.url}" style="font-size:12px">Ver ficha →</a>`
        )
        .addTo(map)
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
  if (activeLayers.value.has(key)) {
    activeLayers.value.delete(key)
  } else {
    activeLayers.value.add(key)
  }
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

onMounted(async () => {
  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://tiles.openfreemap.org/styles/positron',
    center: [-71.0, -35.5], // centro aproximado de Chile continental
    zoom: 4.2,
  })

  map.addControl(new maplibregl.NavigationControl(), 'top-right')
  map.addControl(new maplibregl.GeolocateControl({ trackUserLocation: false }), 'top-right')

  map.on('load', loadGeojson)
})

onBeforeUnmount(() => map?.remove())
</script>

<template>
  <div class="relative h-screen w-full">
    <div ref="mapContainer" style="position:absolute;top:0;right:0;bottom:0;left:0;"></div>

    <!-- Chips de capas — mobile first, flotante abajo -->
    <div class="absolute top-4 left-4 right-4 z-10 flex items-center justify-between">
      <Link href="/" class="flex items-center gap-2 bg-white/95 backdrop-blur rounded-full pl-1.5 pr-3 py-1.5 shadow-lg">
        <img src="/images/logo.png" alt="GO Chile" class="h-8 w-8 rounded-full" />
        <span class="text-xs font-semibold text-sky-950">GO Chile</span>
      </Link>
      <Link href="/explorar" class="bg-white/95 backdrop-blur rounded-full px-3 py-2 shadow-lg text-sky-950">
        <span class="text-sm">🔎</span>
      </Link>
    </div>

    <div class="absolute bottom-4 left-0 right-0 px-4 z-10">
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
