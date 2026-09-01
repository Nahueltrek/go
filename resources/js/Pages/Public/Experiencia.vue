<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  experience: Object,
  reviews: Array,
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

    <!-- Galería -->
    <div class="relative h-56 w-full bg-sky-100 overflow-hidden">
      <img v-if="experience.images?.[0]" :src="experience.images[0]" class="h-full w-full object-cover" />
      <svg class="absolute bottom-0 left-0 w-full h-6 z-10" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </div>

    <div class="px-4 py-5">
      <p class="text-xs uppercase tracking-wide text-sky-400">{{ experience.activity_type }}</p>
      <h1 class="text-xl font-semibold text-sky-900 mt-0.5 animate-fade-in-up">{{ experience.name }}</h1>

      <Link
        v-if="experience.organization"
        :href="`/operadores/${experience.organization.slug}`"
        class="text-sm text-sky-500 underline"
      >{{ experience.organization.name }}</Link>

      <!-- Datos clave -->
      <div class="grid grid-cols-2 gap-3 mt-5">
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Dificultad</p>
          <p class="text-sm font-medium text-sky-900 capitalize">{{ experience.difficulty || '—' }}</p>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Duración</p>
          <p class="text-sm font-medium text-sky-900">
            {{ experience.duration_minutes ? `${experience.duration_minutes} min` : '—' }}
          </p>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Capacidad</p>
          <p class="text-sm font-medium text-sky-900">{{ experience.capacity || '—' }}</p>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <p class="text-xs text-sky-400">Precio</p>
          <p class="text-sm font-medium text-sky-900">
            {{ experience.price ? `$${experience.price.toLocaleString('es-CL')}` : 'Consultar' }}
          </p>
        </div>
      </div>

      <p v-if="experience.description" class="text-sm text-sky-600 mt-5 leading-relaxed">
        {{ experience.description }}
      </p>

      <!-- Reseñas -->
      <section v-if="reviews?.length" class="mt-8 space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">Reseñas</h2>
        <div v-for="(r, i) in reviews" :key="i" class="rounded-xl border border-sky-100 p-3">
          <p class="text-sm text-sky-900">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</p>
          <p v-if="r.comment" class="text-sm text-sky-600 mt-1">{{ r.comment }}</p>
          <p class="text-xs text-sky-400 mt-1">{{ r.user_name }}</p>
        </div>
      </section>
    </div>

    <!-- CTA fijo -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-sky-100 px-4 py-3">
      <a
        v-if="experience.organization?.whatsapp"
        :href="`https://wa.me/${experience.organization.whatsapp}`"
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
