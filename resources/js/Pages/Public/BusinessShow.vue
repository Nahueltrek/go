<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import MapView from '@/Components/MapView.vue';

const props = defineProps({
  business: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const claimForm = useForm({ evidence: '' });

function submitClaim() {
  claimForm.post(`/emprendimientos/${props.business.slug}/reclamar`, {
    preserveScroll: true,
  });
}

const mapPoint = computed(() => (props.business.latitude ? [{ ...props.business }] : []));

const averageRating = computed(() => {
  if (!props.business.reviews.length) return null;
  const sum = props.business.reviews.reduce((acc, r) => acc + r.rating, 0);
  return (sum / props.business.reviews.length).toFixed(1);
});

const verificationLabel = {
  unverified: 'Sin verificar',
  pending: 'Verificación pendiente',
  verified: 'Verificado',
};
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-3xl mx-auto">
      <Link href="/" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Ruta 360
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-3xl mx-auto pb-16">
      <span class="font-mono text-[11px] uppercase tracking-wide text-scrub">
        {{ business.category ?? 'Sin categoría' }}
      </span>
      <h1 class="font-display font-medium text-3xl md:text-4xl mt-2 leading-tight text-balance">
        {{ business.name }}
      </h1>

      <div class="flex flex-wrap gap-x-4 gap-y-1 mt-4 font-mono text-xs text-ink/55">
        <span v-if="business.commune">{{ business.commune }}</span>
        <span v-if="averageRating">★ {{ averageRating }} ({{ business.reviews.length }} reseña{{ business.reviews.length === 1 ? '' : 's' }})</span>
        <span>{{ verificationLabel[business.verification_status] }}</span>
      </div>

      <p v-if="business.description" class="text-base leading-relaxed text-ink/75 mt-6 max-w-xl">
        {{ business.description }}
      </p>

      <p v-if="business.address" class="font-mono text-sm text-ink/55 mt-4">
        {{ business.address }}
      </p>

      <MapView
        v-if="mapPoint.length"
        :businesses="mapPoint"
        :attractions="[]"
        class="mt-8"
      />

      <!-- Contacto -->
      <section v-if="business.contacts.length" class="mt-10 border-t border-ink/10 pt-6">
        <h2 class="font-display text-lg mb-3">Contacto</h2>
        <ul class="space-y-1.5 font-mono text-sm text-ink/70">
          <li v-if="business.contacts[0].phone">Tel: {{ business.contacts[0].phone }}</li>
          <li v-if="business.contacts[0].whatsapp">WhatsApp: {{ business.contacts[0].whatsapp }}</li>
          <li v-if="business.contacts[0].email">{{ business.contacts[0].email }}</li>
          <li v-if="business.contacts[0].website">
            <a :href="business.contacts[0].website" target="_blank" rel="noopener" class="text-river-dark hover:underline">
              {{ business.contacts[0].website }}
            </a>
          </li>
        </ul>
      </section>

      <!-- Servicios -->
      <section v-if="business.services.length" class="mt-10 border-t border-ink/10 pt-6">
        <h2 class="font-display text-lg mb-3">Servicios</h2>
        <ul class="space-y-3">
          <li v-for="(s, i) in business.services" :key="i">
            <p class="font-medium text-sm">{{ s.name }}</p>
            <p v-if="s.description" class="text-sm text-ink/60 mt-0.5">{{ s.description }}</p>
          </li>
        </ul>
      </section>

      <!-- Reseñas -->
      <section class="mt-10 border-t border-ink/10 pt-6">
        <h2 class="font-display text-lg mb-3">Reseñas</h2>
        <div v-if="business.reviews.length" class="space-y-4">
          <article v-for="(r, i) in business.reviews" :key="i" class="bg-paper rounded-lg p-4 border border-ink/10">
            <span class="font-mono text-sm">★ {{ r.rating }}/5</span>
            <p v-if="r.comment" class="text-sm text-ink/70 mt-1.5">{{ r.comment }}</p>
          </article>
        </div>
        <p v-else class="font-mono text-sm text-ink/45">
          Todavía no hay reseñas aprobadas para este negocio.
        </p>
      </section>

      <!-- Reclamo de ficha -->
      <section v-if="business.claim_status === 'unclaimed'" class="mt-10 border-t border-ink/10 pt-6">
        <h2 class="font-display text-lg mb-2">¿Sos el dueño de este negocio?</h2>
        <template v-if="user">
          <p class="text-sm text-ink/60 mb-3">
            Reclamá esta ficha para poder editar su descripción, dirección y contacto.
          </p>
          <div v-if="claimForm.recentlySuccessful" class="bg-river/10 border border-river/30 rounded-lg px-4 py-2.5 text-sm text-river-dark mb-3">
            Solicitud enviada. Un administrador la va a revisar.
          </div>
          <form v-else @submit.prevent="submitClaim" class="space-y-3">
            <textarea
              v-model="claimForm.evidence"
              placeholder="Contanos brevemente cómo podemos confirmar que sos el dueño (opcional)"
              rows="2"
              class="w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river"
            />
            <button
              type="submit"
              :disabled="claimForm.processing"
              class="bg-river text-paper rounded-lg px-4 py-2 text-sm font-medium hover:bg-river-dark transition-colors disabled:opacity-50"
            >
              Reclamar este negocio
            </button>
          </form>
        </template>
        <p v-else class="text-sm text-ink/60">
          <Link href="/login" class="text-river-dark hover:underline">Iniciá sesión</Link>
          para reclamar esta ficha.
        </p>
      </section>
      <p v-else-if="business.claim_status === 'pending'" class="mt-10 border-t border-ink/10 pt-6 font-mono text-xs text-ink/45">
        Esta ficha tiene un reclamo en revisión.
      </p>
    </main>
  </div>
</template>
