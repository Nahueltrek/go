<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  post: Object,
  related: Object,
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

    <div class="relative h-56 w-full bg-sky-100 overflow-hidden">
      <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover" />
      <svg class="absolute bottom-0 left-0 w-full h-6 z-10" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </div>

    <div class="px-4 py-5">
      <p class="text-xs uppercase tracking-wide text-sky-400">{{ post.category }}</p>
      <h1 class="text-xl font-semibold text-sky-900 mt-0.5 animate-fade-in-up">{{ post.title }}</h1>
      <p class="text-xs text-sky-400 mt-1">{{ post.published_at }}</p>

      <Link
        v-if="post.related_organization"
        :href="`/operadores/${post.related_organization.slug}`"
        class="text-sm text-sky-500 underline block mt-2"
      >{{ post.related_organization.name }}</Link>

      <div class="prose prose-sm max-w-none mt-6 text-sky-700 leading-relaxed whitespace-pre-line">
        {{ post.content }}
      </div>
    </div>

    <section v-if="related?.data?.length" class="px-4 py-6 border-t border-sky-100 space-y-3">
      <h2 class="text-sm font-semibold text-sky-900">Te puede interesar</h2>
      <Link
        v-for="r in related.data"
        :key="r.id"
        :href="`/bitacora/${r.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-14 w-14 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="r.cover_image_url" :src="r.cover_image_url" class="h-full w-full object-cover" />
        </div>
        <p class="text-sm font-medium text-sky-900 self-center">{{ r.title }}</p>
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
