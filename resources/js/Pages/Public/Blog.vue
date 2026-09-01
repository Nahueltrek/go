<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  articles: Array,
});
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-5xl mx-auto flex items-baseline justify-between">
      <Link href="/" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Ruta 360
      </Link>
      <span class="font-mono text-[11px] uppercase tracking-wide text-ink/50">Blog</span>
    </header>

    <main class="px-6 md:px-10 max-w-5xl mx-auto pb-16">
      <h1 class="font-display font-medium text-3xl md:text-4xl mt-4 mb-10">
        Notas de Cajón del Maipo
      </h1>

      <div v-if="articles.length" class="grid gap-5 sm:grid-cols-2">
        <Link
          v-for="a in articles"
          :key="a.slug"
          :href="`/blog/${a.slug}`"
          class="bg-paper rounded-xl p-6 border border-ink/10 block hover:border-river/40 transition-colors"
        >
          <h2 class="font-display text-xl leading-snug">{{ a.title }}</h2>
          <p v-if="a.excerpt" class="text-sm text-ink/65 mt-2.5 leading-relaxed">{{ a.excerpt }}</p>
          <div class="flex items-center gap-3 mt-4 font-mono text-[11px] text-ink/45">
            <span v-if="a.author">{{ a.author }}</span>
            <span v-if="a.published_at">{{ a.published_at }}</span>
          </div>
        </Link>
      </div>
      <p v-else class="font-mono text-sm text-ink/45">Todavía no hay artículos publicados.</p>
    </main>
  </div>
</template>
