<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  stats: Object,
})

const cards = [
  { key: 'colaboraciones_pendientes', label: 'Postulaciones pendientes', href: '/admin/colaboradores', highlight: true },
  { key: 'organizaciones_pendientes', label: 'Operadores pendientes', href: '/admin/organizaciones?status=pending', highlight: true },
  { key: 'organizaciones_aprobadas', label: 'Operadores activos', href: '/admin/organizaciones' },
  { key: 'experiencias_publicadas', label: 'Experiencias publicadas', href: '/admin/experiencias' },
  { key: 'proyectos_publicados', label: 'Proyectos publicados' },
  { key: 'eventos_proximos', label: 'Eventos próximos', href: '/agenda' },
  { key: 'articulos_publicados', label: 'Artículos en Bitácora', href: '/bitacora' },
]
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Panel GO Chile</h1>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">Gestión de la red y el contenido</p>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <component
        v-for="(c, i) in cards"
        :key="c.key"
        :is="c.href ? Link : 'div'"
        :href="c.href"
        class="rounded-2xl bg-white border p-4 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 animate-fade-in-up"
        :class="c.highlight && stats[c.key] > 0 ? 'border-amber-300' : 'border-sky-100'"
        :style="{ animationDelay: (0.05 * i) + 's' }"
      >
        <p class="text-2xl font-semibold text-sky-900">{{ stats[c.key] }}</p>
        <p class="text-xs text-sky-500 mt-1">{{ c.label }}</p>
      </component>
    </div>
  </div>
  </AdminLayout>
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
