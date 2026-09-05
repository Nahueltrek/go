<script setup>
import { router, Link } from '@inertiajs/vue3'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  posts: Object,
  activeCategory: String,
  categories: Array,
})

function filterBy(key) {
  router.get('/bitacora', key === props.activeCategory ? {} : { category: key }, { preserveState: true })
}
</script>

<template>
  <SeoHead
    title="Bitácora — GO Chile"
    description="Historias, rutas y contenido de la comunidad outdoor de GO Chile."
  />
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <header class="relative px-4 pt-7 pb-8 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Bitácora GO</h1>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">Contenido generado desde el territorio</p>

      <div class="flex gap-2 mt-4 overflow-x-auto no-scrollbar animate-fade-in-up" style="animation-delay: .2s">
        <button
          v-for="cat in categories"
          :key="cat.key"
          @click="filterBy(cat.key)"
          class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
          :class="activeCategory === cat.key ? 'bg-white text-sky-950' : 'bg-white/15 text-white'"
        >{{ cat.label }}</button>
      </div>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </header>

    <div class="px-4 py-4 space-y-4">
      <Link
        v-for="(post, i) in posts.data"
        :key="post.id"
        :href="`/bitacora/${post.slug}`"
        class="group block rounded-2xl border border-sky-100 overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 animate-fade-in-up"
        :style="{ animationDelay: (0.05 * i) + 's' }"
      >
        <div class="h-40 w-full bg-sky-100 overflow-hidden">
          <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
        </div>
        <div class="p-3">
          <p class="text-xs uppercase tracking-wide text-sky-400">{{ post.category }}</p>
          <p class="text-sm font-semibold text-sky-900 mt-0.5">{{ post.title }}</p>
          <p v-if="post.excerpt" class="text-xs text-sky-500 mt-1 line-clamp-2">{{ post.excerpt }}</p>
        </div>
      </Link>

      <p v-if="!posts.data?.length" class="text-sm text-sky-400 text-center py-12">
        Todavía no hay artículos publicados{{ activeCategory ? ' en esta categoría' : '' }}.
      </p>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out both;
}
</style>
