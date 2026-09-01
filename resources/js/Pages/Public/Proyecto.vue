<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  project: Object,
})

const categoryLabels = {
  conservacion: 'Conservación',
  geologia: 'Geología',
  biodiversidad: 'Biodiversidad',
  comunidad: 'Comunidad',
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
      <img v-if="project.images?.[0]" :src="project.images[0]" class="h-full w-full object-cover" />
      <svg class="absolute bottom-0 left-0 w-full h-6 z-10" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </div>

    <div class="px-4 py-5">
      <p class="text-xs uppercase tracking-wide text-sky-400">
        {{ categoryLabels[project.category] || project.category }}
      </p>
      <h1 class="text-xl font-semibold text-sky-900 mt-0.5 animate-fade-in-up">{{ project.name }}</h1>

      <Link
        v-if="project.organization"
        :href="`/operadores/${project.organization.slug}`"
        class="text-sm text-sky-500 underline"
      >{{ project.organization.name }}</Link>

      <p v-if="project.description" class="text-sm text-sky-600 mt-5 leading-relaxed">
        {{ project.description }}
      </p>

      <section v-if="project.how_to_collaborate" class="mt-6 rounded-2xl bg-sky-50 p-4">
        <h2 class="text-sm font-semibold text-sky-900 mb-2">🤝 Cómo colaborar</h2>
        <p class="text-sm text-sky-600 leading-relaxed">{{ project.how_to_collaborate }}</p>
      </section>
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
