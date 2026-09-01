<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import MapView from '@/Components/MapView.vue'

const props = defineProps({
  organization: Object,
  experiences: Object,
  projects: Object,
})

const locationLabel = computed(() => {
  const commune = props.organization.commune
  if (!commune) return null
  return commune.region ? `${commune.name}, ${commune.region}` : commune.name
})

const mapBusinesses = computed(() => {
  const loc = props.organization.location
  if (!loc) return []
  return [{
    name: props.organization.name,
    category: props.organization.categories?.[0] ?? '',
    latitude: loc.lat,
    longitude: loc.lng,
  }]
})
</script>

<template>
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <!-- Hero -->
    <section
      class="relative overflow-hidden animate-fade-in-up"
      :class="!organization.cover_image && 'bg-gradient-to-br from-sky-800 to-sky-950'"
      :style="organization.cover_image
        ? { backgroundImage: `url(${organization.cover_image})`, backgroundSize: 'cover', backgroundPosition: 'center' }
        : {}"
    >
      <div v-if="organization.cover_image" class="absolute inset-0 bg-gradient-to-t from-sky-950/90 via-sky-950/40 to-sky-950/10"></div>
      <div class="relative px-4 pt-10 pb-6">
        <div class="flex items-center gap-3">
          <div class="h-16 w-16 shrink-0 rounded-2xl bg-white/10 backdrop-blur overflow-hidden ring-1 ring-white/20">
            <img v-if="organization.logo_url" :src="organization.logo_url" class="h-full w-full object-cover" />
          </div>
          <div class="min-w-0">
            <p class="text-xs uppercase tracking-wide text-sky-200">{{ organization.type }}</p>
            <h1 class="text-xl font-bold text-white truncate">{{ organization.name }}</h1>
            <p v-if="locationLabel" class="text-xs text-sky-200">{{ locationLabel }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Datos destacados -->
    <section class="px-4 pt-4 animate-fade-in-up" style="animation-delay: .05s">
      <div class="flex gap-2 flex-wrap">
        <span
          v-for="cat in organization.categories"
          :key="cat"
          class="text-xs rounded-full border border-sky-200 px-2.5 py-1 text-sky-600"
        >{{ cat }}</span>
      </div>

      <p v-if="organization.description" class="text-sm text-sky-600 mt-4 leading-relaxed">
        {{ organization.description }}
      </p>

      <div class="flex gap-2 mt-4 flex-wrap">
        <a v-if="organization.whatsapp" :href="`https://wa.me/${organization.whatsapp}`"
           class="text-sm font-medium rounded-full bg-sky-900 text-white px-4 py-2">WhatsApp</a>
        <a v-if="organization.instagram" :href="organization.instagram"
           class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Instagram</a>
        <a v-if="organization.website" :href="organization.website"
           class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Sitio web</a>
      </div>
    </section>

    <!-- Mapa -->
    <section v-if="organization.location" class="px-4 pt-6 animate-fade-in-up" style="animation-delay: .1s">
      <h2 class="text-sm font-semibold text-sky-900 mb-3">Ubicación</h2>
      <MapView :businesses="mapBusinesses" :attractions="[]" />
    </section>

    <!-- Experiencias -->
    <section v-if="experiences.data?.length" class="px-4 py-6 space-y-3 animate-fade-in-up" style="animation-delay: .15s">
      <h2 class="text-sm font-semibold text-sky-900">Experiencias</h2>
      <Link
        v-for="exp in experiences.data"
        :key="exp.id"
        :href="`/experiencias/${exp.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="exp.images?.[0]" :src="exp.images[0]" class="h-full w-full object-cover" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-sky-900 truncate">{{ exp.name }}</p>
          <p class="text-xs text-sky-500">{{ exp.activity_type }} · {{ exp.difficulty }}</p>
        </div>
      </Link>
    </section>

    <!-- Proyectos -->
    <section v-if="projects.data?.length" class="px-4 py-6 space-y-3 animate-fade-in-up" style="animation-delay: .2s">
      <h2 class="text-sm font-semibold text-sky-900">Proyectos</h2>
      <Link
        v-for="p in projects.data"
        :key="p.id"
        :href="`/proyectos/${p.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="p.images?.[0]" :src="p.images[0]" class="h-full w-full object-cover" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-sky-900 truncate">{{ p.name }}</p>
        </div>
      </Link>
    </section>
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
