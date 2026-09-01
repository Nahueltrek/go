<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  articles: Object,
});

const statusLabel = {
  draft: 'Borrador',
  published: 'Publicado',
  archived: 'Archivado',
};
</script>

<template>
  <AdminLayout>
    <div class="flex items-baseline justify-between mb-6">
      <h1 class="font-display text-2xl">Artículos</h1>
      <Link href="/admin/articles/nuevo" class="font-mono text-xs px-3.5 py-2 rounded-full bg-river text-paper hover:bg-river-dark">
        + Nuevo artículo
      </Link>
    </div>

    <div v-if="articles.data.length" class="space-y-3">
      <Link
        v-for="a in articles.data"
        :key="a.id"
        :href="`/admin/articles/${a.id}/editar`"
        class="bg-paper rounded-xl p-5 border border-ink/10 flex items-center justify-between block hover:border-river/40 transition-colors"
      >
        <div>
          <p class="font-medium text-sm">{{ a.title }}</p>
          <p class="font-mono text-xs text-ink/45 mt-1">
            {{ a.author ?? 'Sin autor' }} · {{ a.published_at ?? 'sin publicar' }}
          </p>
        </div>
        <span
          class="font-mono text-[11px] uppercase tracking-wide px-2.5 py-1 rounded-full"
          :class="a.status === 'published' ? 'bg-river/10 text-river-dark' : 'bg-ink/5 text-ink/50'"
        >
          {{ statusLabel[a.status] ?? a.status }}
        </span>
      </Link>
    </div>
    <p v-else class="font-mono text-sm text-ink/45">Todavía no hay artículos.</p>
  </AdminLayout>
</template>
