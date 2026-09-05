<script setup>
import { computed } from 'vue'
import MapView from '@/Components/MapView.vue'

const props = defineProps({
  lat: { type: Number, default: null },
  lng: { type: Number, default: null },
  name: { type: String, default: '' },
  category: { type: String, default: '' },
})

const businesses = computed(() => [{
  name: props.name,
  category: props.category,
  latitude: props.lat,
  longitude: props.lng,
}])
</script>

<template>
  <section v-if="lat && lng" class="px-4 pt-6 animate-fade-in-up" style="animation-delay: .1s">
    <h2 class="text-sm font-semibold text-sky-900 mb-3">Ubicación</h2>
    <MapView :businesses="businesses" :attractions="[]" />
  </section>
</template>
