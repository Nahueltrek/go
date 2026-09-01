<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  event: Object,
})

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('es-CL', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <div class="relative h-56 w-full bg-sky-100 overflow-hidden">
      <img v-if="event.cover_image" :src="event.cover_image" class="h-full w-full object-cover" />
      <svg class="absolute bottom-0 left-0 w-full h-6 z-10" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </div>

    <div class="px-4 py-5">
      <p class="text-xs uppercase tracking-wide text-sky-400">{{ event.category }}</p>
      <h1 class="text-xl font-semibold text-sky-900 mt-0.5 animate-fade-in-up">{{ event.title }}</h1>

      <Link
        v-if="event.organization"
        :href="`/operadores/${event.organization.slug}`"
        class="text-sm text-sky-500 underline"
      >{{ event.organization.name }}</Link>

      <div class="grid grid-cols-2 gap-3 mt-5">
        <div class="rounded-xl bg-sky-50 p-3 col-span-2">
          <p class="text-xs text-sky-400">Fecha</p>
          <p class="text-sm font-medium text-sky-900">{{ formatDate(event.starts_at) }}</p>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Cupos</p>
          <p class="text-sm font-medium text-sky-900">{{ event.capacity || 'Sin límite' }}</p>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Precio</p>
          <p class="text-sm font-medium text-sky-900">
            {{ event.price ? `$${event.price.toLocaleString('es-CL')}` : 'Gratis' }}
          </p>
        </div>
      </div>

      <p v-if="event.description" class="text-sm text-sky-600 mt-5 leading-relaxed">
        {{ event.description }}
      </p>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-sky-100 px-4 py-3">
      <a
        v-if="event.organization?.whatsapp"
        :href="`https://wa.me/${event.organization.whatsapp}`"
        class="block text-center text-sm font-medium rounded-full bg-sky-900 text-white py-3"
      >Reservar por WhatsApp</a>
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
