<script setup>
import { Link } from '@inertiajs/vue3'
import SeoHead from '@/Components/SeoHead.vue'

defineProps({
  events: Object,
})

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('es-CL', { day: 'numeric', month: 'short' })
}
</script>

<template>
  <SeoHead
    title="Agenda — GO Chile"
    description="Próximas salidas, talleres y actividades outdoor en Chile."
  />
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <header class="relative px-4 pt-7 pb-8 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Agenda GO Chile</h1>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">Próximas salidas, talleres y actividades</p>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </header>

    <div class="px-4 py-4 space-y-3">
      <Link
        v-for="(e, i) in events.data"
        :key="e.id"
        :href="`/agenda/${e.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 animate-fade-in-up"
        :style="{ animationDelay: (0.05 * i) + 's' }"
      >
        <div class="flex flex-col items-center justify-center h-14 w-14 shrink-0 rounded-xl bg-sky-900 text-white">
          <span class="text-xs font-medium">{{ formatDate(e.starts_at) }}</span>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-sky-900 truncate">{{ e.title }}</p>
          <p class="text-xs text-sky-500 truncate">{{ e.organization?.name }}</p>
          <p class="text-xs text-sky-400 mt-0.5 capitalize">{{ e.category }}</p>
        </div>
      </Link>

      <p v-if="!events.data?.length" class="text-sm text-sky-400 text-center py-12">
        No hay eventos próximos por ahora.
      </p>
    </div>
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
