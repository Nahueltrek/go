<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import EntityHero from '@/Components/Public/EntityHero.vue'
import LocationMap from '@/Components/Public/LocationMap.vue'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  attraction: Object,
})

const locationLabel = computed(() => {
  const parts = [props.attraction.commune, props.attraction.destination].filter(Boolean)
  return parts.length ? [...new Set(parts)].join(' · ') : null
})

const seoDescription = computed(() =>
  props.attraction.description || `Conocé ${props.attraction.name}, parte de la red de atractivos de GO Chile.`
)
</script>

<template>
  <SeoHead
    :title="`${attraction.name} — GO Chile`"
    :description="seoDescription"
    :image="attraction.cover_image"
  />
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <EntityHero
      :title="attraction.name"
      :subtitle="attraction.category"
      :meta="locationLabel"
      :cover-image="attraction.cover_image"
    />

    <section class="px-4 pt-4 animate-fade-in-up" style="animation-delay: .05s">
      <p v-if="attraction.description" class="text-sm text-sky-600 leading-relaxed">
        {{ attraction.description }}
      </p>

      <a v-if="attraction.source_url" :href="attraction.source_url" target="_blank" rel="noopener"
         class="inline-block text-sm font-medium rounded-full bg-sky-900 text-white px-4 py-2 mt-4">
        Cómo llegar (Google Maps)
      </a>
    </section>

    <LocationMap
      :lat="attraction.latitude"
      :lng="attraction.longitude"
      :name="attraction.name"
      :category="attraction.category ?? ''"
    />
  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out both;
}
</style>
