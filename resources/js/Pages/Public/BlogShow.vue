<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  article: Object,
});
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-2xl mx-auto">
      <Link href="/blog" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Blog
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-2xl mx-auto pb-16">
      <span v-if="article.category" class="font-mono text-[11px] uppercase tracking-wide text-scrub">
        {{ article.category }}
      </span>
      <h1 class="font-display font-medium text-3xl md:text-4xl mt-2 leading-tight text-balance">
        {{ article.title }}
      </h1>

      <div class="flex items-center gap-3 mt-5 font-mono text-xs text-ink/50">
        <span v-if="article.author">{{ article.author }}</span>
        <span v-if="article.published_at">{{ article.published_at }}</span>
      </div>

      <!-- Cuerpo en texto plano: se preserva el salto de línea tal cual lo
           escribió el autor, sin parser de markdown (fuera de alcance de
           esta pasada). -->
      <div class="prose-body text-base leading-relaxed text-ink/80 mt-8 whitespace-pre-line">
        {{ article.body }}
      </div>

      <div v-if="article.tags.length" class="flex flex-wrap gap-2 mt-10 pt-6 border-t border-ink/10">
        <span
          v-for="tag in article.tags"
          :key="tag"
          class="font-mono text-[11px] uppercase tracking-wide px-2.5 py-1 rounded-full bg-scrub/10 text-scrub"
        >
          {{ tag }}
        </span>
      </div>
    </main>
  </div>
</template>
