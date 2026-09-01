<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  businesses: Array,
});
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-3xl mx-auto flex items-center justify-between">
      <Link href="/" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Ruta 360
      </Link>
      <Link href="/logout" method="post" as="button" class="font-mono text-xs uppercase tracking-wide text-ink/50 hover:text-rock">
        Cerrar sesión
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-3xl mx-auto pb-16">
      <h1 class="font-display text-2xl mb-6">Mis negocios</h1>

      <div v-if="businesses.length" class="space-y-3">
        <div v-for="b in businesses" :key="b.slug" class="bg-paper rounded-xl p-5 border border-ink/10 flex items-center justify-between">
          <div>
            <p class="font-medium text-sm">{{ b.name }}</p>
            <p class="font-mono text-xs text-ink/45 mt-1">{{ b.claim_status }} · {{ b.verification_status }}</p>
          </div>
          <Link
            v-if="b.claim_status === 'claimed'"
            :href="`/dashboard/negocios/${b.slug}/editar`"
            class="font-mono text-xs px-3 py-1.5 rounded-full bg-river text-paper hover:bg-river-dark"
          >
            Editar
          </Link>
        </div>
      </div>
      <div v-else class="font-mono text-sm text-ink/50">
        <p>Todavía no tenés ningún negocio reclamado.</p>
        <p class="mt-2">
          Buscá tu negocio en
          <Link href="/" class="text-river-dark hover:underline">el listado</Link>
          y tocá "Reclamar este negocio" en su ficha.
        </p>
      </div>
    </main>
  </div>
</template>
