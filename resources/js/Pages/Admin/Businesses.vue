<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  businesses: Object,
});
</script>

<template>
  <AdminLayout>
    <h1 class="font-display text-2xl mb-6">Negocios</h1>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="font-mono text-[11px] uppercase tracking-wide text-ink/45 text-left border-b border-ink/10">
            <th class="py-2 pr-4">Nombre</th>
            <th class="py-2 pr-4">Categoría</th>
            <th class="py-2 pr-4">Comuna</th>
            <th class="py-2 pr-4">SERNATUR</th>
            <th class="py-2 pr-4">Verificación</th>
            <th class="py-2 pr-4">Reclamo</th>
            <th class="py-2 pr-4">Dueño</th>
            <th class="py-2 pr-4">Origen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in businesses.data" :key="b.id" class="border-b border-ink/5">
            <td class="py-2.5 pr-4 font-medium">{{ b.name }}</td>
            <td class="py-2.5 pr-4 text-ink/60">{{ b.category }}</td>
            <td class="py-2.5 pr-4 text-ink/60">{{ b.commune }}</td>
            <td class="py-2.5 pr-4 font-mono text-xs text-ink/50">{{ b.sernatur_status }}</td>
            <td class="py-2.5 pr-4 font-mono text-xs text-ink/50">{{ b.verification_status }}</td>
            <td class="py-2.5 pr-4 font-mono text-xs text-ink/50">{{ b.claim_status }}</td>
            <td class="py-2.5 pr-4 text-ink/60">{{ b.owner ?? '—' }}</td>
            <td class="py-2.5 pr-4 font-mono text-xs text-ink/50">{{ b.source }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="!businesses.data.length" class="font-mono text-sm text-ink/45 mt-4">No hay negocios cargados.</p>
  </AdminLayout>
</template>
