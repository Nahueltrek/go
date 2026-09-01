<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MapView from '@/Components/MapView.vue';

const props = defineProps({
  attraction: Object,
});

const mapPoint = computed(() => (props.attraction.latitude ? [{ ...props.attraction }] : []));
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-3xl mx-auto">
      <Link href="/" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Ruta 360
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-3xl mx-auto pb-16">
      <span class="font-mono text-[11px] uppercase tracking-wide text-rock">
        {{ attraction.category ?? 'Atractivo' }}
      </span>
      <h1 class="font-display font-medium text-3xl md:text-4xl mt-2 leading-tight text-balance">
        {{ attraction.name }}
      </h1>

      <p v-if="attraction.commune" class="font-mono text-xs text-ink/55 mt-4">
        {{ attraction.commune }}
      </p>

      <p v-if="attraction.description" class="text-base leading-relaxed text-ink/75 mt-6 max-w-xl">
        {{ attraction.description }}
      </p>

      <MapView
        v-if="mapPoint.length"
        :businesses="[]"
        :attractions="mapPoint"
        class="mt-8"
      />
    </main>
  </div>
</template>
