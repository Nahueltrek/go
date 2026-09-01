<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MapView from '@/Components/MapView.vue';

const props = defineProps({
  route: Object,
});

const difficultyLabel = {
  facil: 'Fácil',
  media: 'Media',
  dificil: 'Difícil',
};

// MapView distingue negocio (turquesa) de atractivo (óxido). Los puntos de
// ruta son polimórficos (business | attraction | activity); 'activity' no
// tiene representación propia en el mapa todavía — no aplica a los datos
// actuales de esta ruta, pero queda anotado por si se agrega en el futuro.
const mapBusinesses = computed(() =>
  props.route.points.filter((p) => p.type === 'business' && p.item?.latitude).map((p) => p.item)
);
const mapAttractions = computed(() =>
  props.route.points.filter((p) => p.type === 'attraction' && p.item?.latitude).map((p) => p.item)
);
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-3xl mx-auto">
      <Link href="/" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Ruta 360
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-3xl mx-auto pb-16">
      <span class="font-mono text-[11px] uppercase tracking-wide text-scrub">Ruta de trekking</span>
      <h1 class="font-display font-medium text-3xl md:text-4xl mt-2 leading-tight text-balance">
        {{ route.name }}
      </h1>

      <div class="flex flex-wrap gap-x-6 gap-y-1 mt-5 font-mono text-sm text-river-dark">
        <span v-if="route.distance_km">{{ route.distance_km }} km</span>
        <span v-if="route.duration_minutes">{{ Math.round(route.duration_minutes / 60) }} h aprox.</span>
        <span v-if="route.difficulty">{{ difficultyLabel[route.difficulty] ?? route.difficulty }}</span>
      </div>

      <p v-if="route.description" class="text-base leading-relaxed text-ink/75 mt-6 max-w-xl">
        {{ route.description }}
      </p>

      <MapView
        v-if="mapBusinesses.length || mapAttractions.length"
        :businesses="mapBusinesses"
        :attractions="mapAttractions"
        class="mt-8"
      />

      <!-- Itinerario -->
      <section v-if="route.points.length" class="mt-10 border-t border-ink/10 pt-6">
        <h2 class="font-display text-lg mb-4">Itinerario</h2>
        <ol class="space-y-4">
          <li v-for="p in route.points" :key="p.position" class="flex gap-4">
            <span class="font-mono text-sm text-river-dark shrink-0 w-6">{{ p.position }}</span>
            <div>
              <p class="font-medium text-sm">{{ p.item?.name ?? 'Punto sin datos' }}</p>
              <p v-if="p.note" class="text-sm text-ink/60 mt-0.5">{{ p.note }}</p>
            </div>
          </li>
        </ol>
      </section>
    </main>
  </div>
</template>
