<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  post: Object,
  related: Object,
  categoryLabel: String,
})

// BlogPostResource individual (no colección) llega envuelto en { data: {...} }
// — el wrapping default de los API Resources de Laravel — a diferencia de
// `related`, que ya se consumía bien como `related.data` más abajo. Este
// alias resuelve el envoltorio una sola vez para el resto del componente.
const postData = computed(() => props.post?.data ?? {})

const canonicalPath = computed(() => `/bitacora/${postData.value.slug}`)

const jsonLd = computed(() => {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''

  const articleSchema = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: postData.value.title,
    description: postData.value.excerpt || undefined,
    image: postData.value.cover_image_url || undefined,
    datePublished: postData.value.published_at || undefined,
    author: postData.value.author ? { '@type': 'Person', name: postData.value.author.name } : undefined,
  }

  const breadcrumbSchema = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      { '@type': 'ListItem', position: 1, name: 'GO Chile', item: origin + '/' },
      { '@type': 'ListItem', position: 2, name: 'Bitácora GO', item: origin + '/bitacora' },
      { '@type': 'ListItem', position: 3, name: postData.value.title, item: origin + canonicalPath.value },
    ],
  }

  return [articleSchema, breadcrumbSchema]
})

// "Descubre más de este territorio": solo se muestran las conexiones que
// realmente existen para este artículo — no se inventan relaciones.
const discoverCards = computed(() => {
  const cards = []
  if (postData.value.related_route) {
    cards.push({ label: 'Ruta', name: postData.value.related_route.name, href: `/rutas/${postData.value.related_route.slug}` })
  }
  if (postData.value.related_experience) {
    cards.push({ label: 'Experiencia', name: postData.value.related_experience.name, href: `/experiencias/${postData.value.related_experience.slug}` })
  }
  if (postData.value.related_organization) {
    cards.push({ label: 'Organización', name: postData.value.related_organization.name, href: `/operadores/${postData.value.related_organization.slug}` })
  }
  if (postData.value.related_project) {
    cards.push({ label: 'Proyecto', name: postData.value.related_project.name, href: `/proyectos/${postData.value.related_project.slug}` })
  }
  return cards
})
</script>

<template>
  <SeoHead
    :title="`${postData.title} — Bitácora GO`"
    :description="postData.excerpt"
    :image="postData.cover_image_url"
    type="article"
    :json-ld="jsonLd"
  />

  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/bitacora" class="shrink-0 text-sky-700">
        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
          <path d="M12 4l-6 6 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </Link>
      <span class="text-xs font-bold text-sky-950">Bitácora GO</span>
    </header>

    <div class="relative h-56 w-full bg-sky-100 overflow-hidden">
      <img v-if="postData.cover_image_url" :src="postData.cover_image_url" class="h-full w-full object-cover" />
      <svg class="absolute bottom-0 left-0 w-full h-6 z-10" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </div>

    <div class="px-4 py-5">
      <p class="text-xs uppercase tracking-wide text-sky-400">{{ categoryLabel }}</p>
      <h1 class="text-xl font-semibold text-sky-900 mt-0.5 leading-snug animate-fade-in-up">{{ postData.title }}</h1>
      <p v-if="postData.excerpt" class="text-sm text-sky-600 mt-2 leading-relaxed">{{ postData.excerpt }}</p>

      <div class="flex items-center gap-2 mt-3 text-xs text-sky-400">
        <span v-if="postData.author">{{ postData.author.name }}</span>
        <span v-if="postData.author && postData.published_at">·</span>
        <span v-if="postData.published_at">{{ postData.published_at }}</span>
      </div>

      <div class="prose prose-sm max-w-none mt-6 text-sky-700 leading-relaxed whitespace-pre-line">
        {{ postData.content }}
      </div>

      <!-- Este artículo está relacionado con -->
      <div v-if="discoverCards.length" class="flex flex-wrap gap-2 mt-6 pt-5 border-t border-sky-100">
        <span class="w-full text-xs font-semibold text-sky-900 mb-1">Este artículo está relacionado con</span>
        <Link
          v-for="card in discoverCards" :key="card.label"
          :href="card.href"
          class="text-xs rounded-full border border-sky-200 px-3 py-1.5 text-sky-600 hover:bg-sky-50"
        >{{ card.label }}: {{ card.name }}</Link>
        <span
          v-if="postData.related_destination"
          class="text-xs rounded-full bg-sky-50 px-3 py-1.5 text-sky-500"
        >Territorio: {{ postData.related_destination.name }}</span>
      </div>
    </div>

    <!-- Descubre más de este territorio -->
    <section v-if="discoverCards.length" class="px-4 py-6 border-t border-sky-100">
      <h2 class="text-sm font-semibold text-sky-900">Descubre más de este territorio</h2>
      <div class="grid grid-cols-2 gap-3 mt-3">
        <Link
          v-for="card in discoverCards" :key="card.href"
          :href="card.href"
          class="rounded-2xl border border-sky-100 p-3 hover:shadow-md transition-shadow"
        >
          <p class="text-[11px] uppercase tracking-wide text-sky-400">{{ card.label }}</p>
          <p class="text-sm font-medium text-sky-900 mt-0.5 line-clamp-2">{{ card.name }}</p>
        </Link>
      </div>
    </section>

    <!-- Artículos relacionados -->
    <section v-if="related?.data?.length" class="px-4 py-6 border-t border-sky-100 space-y-3">
      <h2 class="text-sm font-semibold text-sky-900">Te puede interesar</h2>
      <Link
        v-for="r in related.data" :key="r.id"
        :href="`/bitacora/${r.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-14 w-14 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="r.cover_image_url" :src="r.cover_image_url" class="h-full w-full object-cover" />
        </div>
        <p class="text-sm font-medium text-sky-900 self-center">{{ r.title }}</p>
      </Link>
    </section>

    <!-- CTA final -->
    <section class="px-4 pt-4">
      <div class="rounded-2xl bg-gradient-to-br from-sky-800 to-sky-950 p-6 text-center">
        <h2 class="text-base font-bold text-white">¿Tienes una historia del territorio?</h2>
        <p class="text-xs text-sky-200 mt-1.5">Queremos conocerla.</p>
        <Link href="/colaboradores" class="inline-block mt-4 rounded-full bg-white text-sky-950 text-xs font-semibold px-4 py-2.5">
          Quiero compartir una historia
        </Link>
      </div>
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
