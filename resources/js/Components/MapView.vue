<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const props = defineProps({
  businesses: Array,
  attractions: Array,
});

const mapContainer = ref(null);
let map;

// Colores del sistema de diseño — no se pueden leer variables CSS de Tailwind
// acá, se repiten como hex literal a propósito (evita depender del orden de
// carga del CSS para algo crítico como los pines del mapa).
const RIVER = '#1c8c82';
const ROCK = '#b4562a';

onMounted(() => {
  const points = [
    ...props.businesses.filter((b) => b.latitude && b.longitude),
    ...props.attractions.filter((a) => a.latitude && a.longitude),
  ];

  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://tiles.openfreemap.org/styles/positron',
    center: points.length ? [points[0].longitude, points[0].latitude] : [-70.35, -33.75],
    zoom: 10,
  });

  map.addControl(new maplibregl.NavigationControl(), 'top-right');

  if (!points.length) return;

  const bounds = new maplibregl.LngLatBounds();

  props.businesses
    .filter((b) => b.latitude && b.longitude)
    .forEach((b) => {
      const marker = new maplibregl.Marker({ color: RIVER })
        .setLngLat([b.longitude, b.latitude])
        .setPopup(
          new maplibregl.Popup({ offset: 24 }).setHTML(
            `<strong>${escapeHtml(b.name)}</strong><br><span style="font-size:12px;color:#6b7b4f">${escapeHtml(b.category ?? '')}</span>`
          )
        )
        .addTo(map);
      bounds.extend([b.longitude, b.latitude]);
    });

  props.attractions
    .filter((a) => a.latitude && a.longitude)
    .forEach((a) => {
      const marker = new maplibregl.Marker({ color: ROCK })
        .setLngLat([a.longitude, a.latitude])
        .setPopup(
          new maplibregl.Popup({ offset: 24 }).setHTML(
            `<strong>${escapeHtml(a.name)}</strong><br><span style="font-size:12px;color:#b4562a">${escapeHtml(a.category ?? '')}</span>`
          )
        )
        .addTo(map);
      bounds.extend([a.longitude, a.latitude]);
    });

  map.fitBounds(bounds, { padding: 48, maxZoom: 13 });
});

onBeforeUnmount(() => {
  map?.remove();
});

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
</script>

<template>
  <div ref="mapContainer" class="w-full h-80 md:h-[28rem] rounded-xl border border-ink/10 overflow-hidden" />
</template>
