<script setup>
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  claims: Object,
  status: String,
});

function approve(id) {
  router.post(`/admin/claims/${id}/approve`, {}, { preserveScroll: true });
}

function reject(id) {
  router.post(`/admin/claims/${id}/reject`, {}, { preserveScroll: true });
}

function filterBy(newStatus) {
  router.get('/admin/claims', { status: newStatus }, { preserveScroll: true });
}
</script>

<template>
  <AdminLayout>
    <div class="flex items-baseline justify-between mb-6">
      <h1 class="font-display text-2xl">Reclamos de ficha</h1>
      <div class="flex gap-2 font-mono text-xs uppercase">
        <button
          v-for="s in ['pending', 'approved', 'rejected', 'all']"
          :key="s"
          @click="filterBy(s)"
          class="px-3 py-1.5 rounded-full border"
          :class="status === s ? 'bg-river text-paper border-river' : 'border-ink/15 text-ink/60'"
        >
          {{ s }}
        </button>
      </div>
    </div>

    <div v-if="claims.data.length" class="space-y-3">
      <article v-for="c in claims.data" :key="c.id" class="bg-paper rounded-xl p-5 border border-ink/10">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="font-medium text-sm">{{ c.business }}</p>
            <p class="font-mono text-xs text-ink/45 mt-1">{{ c.user }} · {{ c.user_email }} · {{ c.created_at }}</p>
            <p v-if="c.evidence" class="text-sm text-ink/70 mt-2">{{ c.evidence }}</p>
          </div>
          <div v-if="c.status === 'pending'" class="flex gap-2 shrink-0">
            <button @click="approve(c.id)" class="font-mono text-xs px-3 py-1.5 rounded-full bg-river text-paper hover:bg-river-dark">
              Aprobar
            </button>
            <button @click="reject(c.id)" class="font-mono text-xs px-3 py-1.5 rounded-full border border-rock/40 text-rock hover:bg-rock/5">
              Rechazar
            </button>
          </div>
          <span v-else class="font-mono text-[11px] uppercase text-ink/40 shrink-0">{{ c.status }}</span>
        </div>
      </article>
    </div>
    <p v-else class="font-mono text-sm text-ink/45">No hay reclamos en este filtro.</p>
  </AdminLayout>
</template>
