<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  mode: String, // 'home' | 'category'
  activeCategory: String,
  categories: Array,
  // mode === 'home'
  featured: Object,
  secondary: Object,
  personas: Object,
  educacion: Object,
  proyectos: Object,
  // mode === 'category'
  posts: Object,
})

// `featured` es un BlogPostResource individual (no colección), así que llega
// envuelto en { data: {...} } — el wrapping default de los API Resources de
// Laravel. `secondary`/`personas`/`educacion`/`proyectos`/`posts` son
// colecciones y ya se consumían bien como `x.data` en el template.
const featuredPost = computed(() => props.featured?.data ?? null)

const categoryCopy = {
  rutas: 'Caminos que vale la pena recorrer.',
  personas: 'Historias de quienes mueven el territorio.',
  territorio: 'Lugares que cuentan mucho más que un paisaje.',
  educacion: 'Aprende antes de salir.',
  conservacion: 'Proyectos que protegen y transforman.',
  experiencias: 'Chile también se vive.',
}

const learnTopics = [
  'Seguridad en montaña',
  'No Dejar Rastro',
  'Preparación de rutas',
  'Orientación',
  'Equipamiento',
  'Primeros auxilios',
]

function filterBy(key) {
  router.get('/bitacora', key === props.activeCategory ? {} : { category: key }, { preserveState: true })
}
</script>

<template>
  <SeoHead
    :title="activeCategory ? `${categories.find(c => c.key === activeCategory)?.label ?? ''} — Bitácora GO` : 'Bitácora GO — Historias que nacen en el territorio'"
    description="Personas, rutas, lugares y experiencias que nos ayudan a descubrir Chile de otra manera."
  />

  <PublicLayout>
    <!-- ============ HERO ============ -->
    <section class="relative px-4 pt-10 pb-10 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <p class="text-xs font-semibold tracking-widest text-sky-300 animate-fade-in-up">CHILE · OUTDOOR · NATURALEZA</p>
      <h1 class="text-3xl font-bold text-white mt-2 animate-fade-in-up" style="animation-delay: .05s">BITÁCORA GO</h1>
      <p class="text-sm font-medium text-sky-200 mt-1.5 animate-fade-in-up" style="animation-delay: .1s">
        Historias que nacen en el territorio.
      </p>
      <p class="text-sm text-sky-300 mt-2 max-w-md animate-fade-in-up" style="animation-delay: .15s">
        Personas, rutas, lugares y experiencias que nos ayudan a descubrir Chile de otra manera.
      </p>
      <a href="#explora" class="inline-block mt-5 rounded-full bg-white text-sky-950 text-xs font-semibold px-4 py-2.5 animate-fade-in-up" style="animation-delay: .2s">
        Explorar historias
      </a>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </section>

    <!-- ============ MODE: CATEGORÍA (grilla filtrada) ============ -->
    <template v-if="mode === 'category'">
      <div class="px-4 pt-6">
        <Link href="/bitacora" class="text-xs text-sky-500 underline">← Volver a la Bitácora</Link>
        <h2 class="text-lg font-bold text-sky-950 mt-2">
          {{ categories.find(c => c.key === activeCategory)?.label }}
        </h2>
      </div>

      <div class="px-4 py-4 flex gap-2 overflow-x-auto no-scrollbar">
        <button
          v-for="cat in categories"
          :key="cat.key"
          @click="filterBy(cat.key)"
          class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
          :class="activeCategory === cat.key ? 'bg-sky-900 text-white' : 'bg-white border border-sky-200 text-sky-600'"
        >{{ cat.label }}</button>
      </div>

      <div class="px-4 pb-12 space-y-4">
        <Link
          v-for="(post, i) in posts.data" :key="post.id"
          :href="`/bitacora/${post.slug}`"
          class="group block rounded-2xl border border-sky-100 overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 animate-fade-in-up"
          :style="{ animationDelay: (0.05 * i) + 's' }"
        >
          <div class="h-40 w-full bg-sky-100 overflow-hidden">
            <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
          </div>
          <div class="p-3">
            <p class="text-xs uppercase tracking-wide text-sky-400">{{ post.category_label }}</p>
            <p class="text-sm font-semibold text-sky-900 mt-0.5">{{ post.title }}</p>
            <p v-if="post.excerpt" class="text-xs text-sky-500 mt-1 line-clamp-2">{{ post.excerpt }}</p>
          </div>
        </Link>

        <div v-if="!posts.data?.length" class="text-center py-12">
          <p class="text-sm font-medium text-sky-900">Estamos construyendo esta bitácora desde el territorio.</p>
          <p class="text-xs text-sky-400 mt-1">Pronto vas a encontrar historias de esta categoría acá.</p>
        </div>
      </div>
    </template>

    <!-- ============ MODE: PORTADA EDITORIAL ============ -->
    <template v-else>
      <!-- Historias destacadas -->
      <section id="explora" class="px-4 pt-8">
        <h2 class="text-base font-bold text-sky-950">Historias destacadas</h2>

        <template v-if="featuredPost">
          <Link :href="`/bitacora/${featuredPost.slug}`" class="group block mt-4 rounded-2xl overflow-hidden border border-sky-100 animate-fade-in-up">
            <div class="h-52 w-full bg-sky-100 overflow-hidden">
              <img v-if="featuredPost.cover_image_url" :src="featuredPost.cover_image_url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
            </div>
            <div class="p-4">
              <p class="text-xs uppercase tracking-wide text-sky-400">{{ featuredPost.category_label }}</p>
              <p class="text-lg font-semibold text-sky-950 mt-1 leading-snug">{{ featuredPost.title }}</p>
              <p v-if="featuredPost.excerpt" class="text-sm text-sky-500 mt-1.5 line-clamp-2">{{ featuredPost.excerpt }}</p>
            </div>
          </Link>

          <div v-if="secondary?.data?.length" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
            <Link
              v-for="(post, i) in secondary.data" :key="post.id"
              :href="`/bitacora/${post.slug}`"
              class="group block rounded-2xl overflow-hidden border border-sky-100 animate-fade-in-up"
              :style="{ animationDelay: (0.05 * (i + 1)) + 's' }"
            >
              <div class="h-28 w-full bg-sky-100 overflow-hidden">
                <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
              </div>
              <div class="p-3">
                <p class="text-[11px] uppercase tracking-wide text-sky-400">{{ post.category_label }}</p>
                <p class="text-sm font-semibold text-sky-900 mt-0.5 line-clamp-2">{{ post.title }}</p>
              </div>
            </Link>
          </div>
        </template>

        <div v-else class="mt-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-8 text-center">
          <p class="text-sm font-medium text-sky-900">Estamos construyendo esta bitácora desde el territorio.</p>
          <p class="text-xs text-sky-400 mt-1">Muy pronto vas a encontrar acá las primeras historias.</p>
        </div>
      </section>

      <!-- Explora la Bitácora -->
      <section class="px-4 pt-10">
        <h2 class="text-base font-bold text-sky-950">Explora la Bitácora</h2>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <button
            v-for="cat in categories" :key="cat.key"
            @click="filterBy(cat.key)"
            class="text-left rounded-2xl border border-sky-100 bg-white p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
          >
            <p class="text-sm font-semibold text-sky-900">{{ cat.label }}</p>
            <p class="text-xs text-sky-500 mt-1 leading-snug">{{ categoryCopy[cat.key] }}</p>
          </button>
        </div>
      </section>

      <!-- Chile, territorio por territorio -->
      <section class="px-4 pt-10">
        <h2 class="text-base font-bold text-sky-950">Chile, territorio por territorio</h2>
        <p class="text-sm text-sky-500 mt-1.5 max-w-md">
          Una mirada a los lugares, personas, historias y experiencias que construyen cada territorio.
        </p>
        <div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50/60 p-8 text-center">
          <p class="text-sm font-medium text-sky-900">Estamos construyendo esta mirada territorio por territorio.</p>
          <p class="text-xs text-sky-400 mt-1">A medida que crezca la Bitácora, vas a poder explorar Chile por región desde acá.</p>
        </div>
      </section>

      <!-- Personas que mueven el territorio -->
      <section v-if="personas?.data?.length" class="px-4 pt-10">
        <h2 class="text-base font-bold text-sky-950">Personas que mueven el territorio</h2>
        <p class="text-sm text-sky-500 mt-1.5 max-w-md">
          Detrás de cada ruta hay personas que conocen, protegen y viven estos lugares.
        </p>
        <div class="space-y-3 mt-4">
          <Link
            v-for="post in personas.data" :key="post.id"
            :href="`/bitacora/${post.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 hover:shadow-md transition-shadow"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-sky-900 line-clamp-2">{{ post.title }}</p>
              <p v-if="post.excerpt" class="text-xs text-sky-500 mt-1 line-clamp-2">{{ post.excerpt }}</p>
            </div>
          </Link>
        </div>
        <button @click="filterBy('personas')" class="mt-4 text-xs font-semibold text-sky-700 underline">
          Conocer historias
        </button>
      </section>

      <!-- Aprende antes de salir -->
      <section class="px-4 pt-10">
        <h2 class="text-base font-bold text-sky-950">Aprende antes de salir</h2>

        <div v-if="educacion?.data?.length" class="space-y-3 mt-4">
          <Link
            v-for="post in educacion.data" :key="post.id"
            :href="`/bitacora/${post.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 hover:shadow-md transition-shadow"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-sky-900 line-clamp-2">{{ post.title }}</p>
              <p v-if="post.excerpt" class="text-xs text-sky-500 mt-1 line-clamp-2">{{ post.excerpt }}</p>
            </div>
          </Link>
        </div>
        <div v-else class="flex flex-wrap gap-2 mt-4">
          <span
            v-for="topic in learnTopics" :key="topic"
            class="text-xs rounded-full border border-sky-200 bg-white px-3 py-1.5 text-sky-600"
          >{{ topic }}</span>
        </div>
      </section>

      <!-- Proyectos que transforman -->
      <section v-if="proyectos?.data?.length" class="px-4 pt-10">
        <h2 class="text-base font-bold text-sky-950">Proyectos que transforman</h2>
        <p class="text-sm text-sky-500 mt-1.5 max-w-md">
          Conoce iniciativas que trabajan por la conservación, educación ambiental, biodiversidad y desarrollo sostenible de sus territorios.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
          <Link
            v-for="p in proyectos.data" :key="p.id"
            :href="`/proyectos/${p.slug}`"
            class="group block rounded-2xl overflow-hidden border border-sky-100"
          >
            <div class="h-28 w-full bg-sky-100 overflow-hidden">
              <img v-if="p.images?.[0]" :src="p.images[0]" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
            </div>
            <div class="p-3">
              <p class="text-sm font-semibold text-sky-900 line-clamp-2">{{ p.name }}</p>
              <p v-if="p.organization" class="text-xs text-sky-500 mt-1">{{ p.organization.name }}</p>
            </div>
          </Link>
        </div>
      </section>

      <!-- CTA final -->
      <section class="px-4 pt-10 pb-14">
        <div class="rounded-2xl bg-gradient-to-br from-sky-800 to-sky-950 p-6 text-center">
          <h2 class="text-lg font-bold text-white">¿Tienes una historia del territorio?</h2>
          <p class="text-sm text-sky-200 mt-1.5">Queremos conocerla.</p>
          <p class="text-xs text-sky-300 mt-2 max-w-sm mx-auto">
            Si tienes una ruta, experiencia, proyecto, fotografía, historia o iniciativa que quieras compartir con la comunidad GO Chile, conversemos.
          </p>
          <Link href="/colaboradores" class="inline-block mt-4 rounded-full bg-white text-sky-950 text-xs font-semibold px-4 py-2.5">
            Quiero compartir una historia
          </Link>
        </div>
      </section>
    </template>
  </PublicLayout>
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
