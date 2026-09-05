<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  results: Object,
  filters: Object,
  facets: Object,
})

const q = ref(props.filters.q || '')
const activeType = ref(props.filters.type || null)
const activeActivity = ref(props.filters.activity || null)
const activeRegion = ref(props.filters.region_id || null)

const types = [
  { key: 'experiencia', label: 'Experiencias', icon: '🥾' },
  { key: 'operador', label: 'Operadores', icon: '🤝' },
  { key: 'negocio', label: 'Negocios', icon: '🏪' },
  { key: 'ruta', label: 'Rutas', icon: '🗺️' },
  { key: 'evento', label: 'Agenda', icon: '📅' },
  { key: 'proyecto', label: 'Proyectos', icon: '🌱' },
]

function submitSearch() {
  router.get('/explorar', {
    q: q.value || undefined,
    type: activeType.value || undefined,
    activity: activeActivity.value || undefined,
    region_id: activeRegion.value || undefined,
  }, { preserveState: true, replace: true })
}

function toggleType(key) {
  activeType.value = activeType.value === key ? null : key
  submitSearch()
}

function toggleActivity(slug) {
  activeActivity.value = activeActivity.value === slug ? null : slug
  submitSearch()
}

const totalResults = computed(() => {
  return (props.results.experiencias?.data?.length || 0)
    + (props.results.operadores?.data?.length || 0)
    + (props.results.proyectos?.data?.length || 0)
    + (props.results.negocios?.data?.length || 0)
    + (props.results.rutas?.data?.length || 0)
    + (props.results.eventos?.data?.length || 0)
})
</script>

<template>
  <SeoHead
    title="Explorar — GO Chile"
    description="Buscá experiencias, operadores, negocios, rutas, eventos y proyectos outdoor en Chile por región, categoría o actividad."
  />
  <div class="min-h-screen bg-white">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <!-- Header de búsqueda: sticky, mobile-first -->
    <header class="sticky top-0 z-20 relative overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950 px-4 pt-5 pb-4">
      <h1 class="text-xl font-bold text-white mb-3 animate-fade-in-up">
        Explorar Chile outdoor
      </h1>

      <form @submit.prevent="submitSearch" class="relative animate-fade-in-up" style="animation-delay: .1s">
        <input
          v-model="q"
          type="search"
          placeholder="Trekking, cicloturismo, wellness…"
          class="w-full rounded-full bg-white/95 backdrop-blur py-3 pl-11 pr-4 text-sm text-sky-950
                 placeholder:text-sky-950/40 focus:outline-none focus:ring-2 focus:ring-sky-300"
        />
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sky-950/50">🔎</span>
      </form>

      <!-- Filtro de tipo -->
      <div class="flex gap-2 mt-3 overflow-x-auto no-scrollbar animate-fade-in-up" style="animation-delay: .15s">
        <button
          v-for="t in types"
          :key="t.key"
          @click="toggleType(t.key)"
          class="shrink-0 flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors"
          :class="activeType === t.key
            ? 'bg-white text-sky-950'
            : 'bg-white/15 text-white'"
        >
          <span>{{ t.icon }}</span>{{ t.label }}
        </button>
      </div>

      <!-- Filtro de actividad -->
      <div class="flex gap-2 mt-2 overflow-x-auto no-scrollbar animate-fade-in-up" style="animation-delay: .2s">
        <button
          v-for="activity in facets.activities"
          :key="activity.slug"
          @click="toggleActivity(activity.slug)"
          class="shrink-0 rounded-full px-3 py-1 text-xs font-medium border transition-colors"
          :class="activeActivity === activity.slug
            ? 'border-white text-white bg-white/10'
            : 'border-white/25 text-sky-200'"
        >
          {{ activity.name }}
        </button>
      </div>

      <svg class="absolute bottom-0 left-0 w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
        <path d="M0,16 L0,10 L50,3 L100,12 L150,1 L200,10 L250,4 L300,12 L350,6 L400,10 L400,16 Z" fill="white" />
      </svg>
    </header>

    <!-- Resultados -->
    <main class="px-4 py-4 space-y-8">
      <p class="text-xs text-sky-400">{{ totalResults }} resultados</p>

      <!-- Experiencias -->
      <section v-if="results.experiencias?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">🥾 Experiencias</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="exp in results.experiencias.data"
            :key="'exp-' + exp.id"
            :href="`/experiencias/${exp.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="exp.images?.[0]" :src="exp.images[0]" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ exp.name }}</p>
              <p class="text-xs text-sky-500 truncate">
                {{ exp.organization?.name }} · {{ exp.destination?.name }}
              </p>
              <p class="text-xs text-sky-400 mt-0.5">
                {{ exp.difficulty }} · {{ exp.duration_minutes ? `${exp.duration_minutes} min` : '' }}
              </p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Operadores -->
      <section v-if="results.operadores?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">🤝 Operadores</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="org in results.operadores.data"
            :key="'org-' + org.id"
            :href="`/operadores/${org.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="org.logo_url" :src="org.logo_url" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ org.name }}</p>
              <p class="text-xs text-sky-500 truncate">{{ org.commune?.name }}</p>
              <p class="text-xs text-sky-400 truncate">{{ (org.categories || []).join(' · ') }}</p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Negocios -->
      <section v-if="results.negocios?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">🏪 Negocios</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="b in results.negocios.data"
            :key="'biz-' + b.id"
            :href="`/negocios/${b.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="b.images?.[0]" :src="b.images[0]" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ b.name }}</p>
              <p class="text-xs text-sky-500 truncate">{{ b.category }}</p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Rutas -->
      <section v-if="results.rutas?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">🗺️ Rutas</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="r in results.rutas.data"
            :key="'route-' + r.id"
            :href="`/rutas/${r.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ r.name }}</p>
              <p class="text-xs text-sky-500 truncate">{{ r.destination?.name }}</p>
              <p class="text-xs text-sky-400 mt-0.5">
                {{ r.difficulty }} · {{ r.distance_km ? `${r.distance_km} km` : '' }}
              </p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Agenda / Eventos -->
      <section v-if="results.eventos?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">📅 Agenda</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="e in results.eventos.data"
            :key="'event-' + e.id"
            :href="`/agenda/${e.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ e.title }}</p>
              <p class="text-xs text-sky-500 truncate">{{ e.organization?.name }}</p>
              <p class="text-xs text-sky-400 mt-0.5">{{ e.category }}</p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Proyectos -->
      <section v-if="results.proyectos?.data?.length" class="space-y-3">
        <h2 class="text-sm font-semibold text-sky-900">🌱 Proyectos</h2>
        <div class="grid grid-cols-1 gap-3">
          <Link
            v-for="p in results.proyectos.data"
            :key="'proj-' + p.id"
            :href="`/proyectos/${p.slug}`"
            class="flex gap-3 rounded-2xl border border-sky-100 p-3 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 active:bg-sky-50 animate-fade-in-up"
          >
            <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="p.images?.[0]" :src="p.images[0]" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ p.name }}</p>
              <p class="text-xs text-sky-500 truncate">{{ p.organization?.name }}</p>
            </div>
          </Link>
        </div>
      </section>

      <p v-if="totalResults === 0" class="text-sm text-sky-400 text-center py-12">
        No encontramos resultados. Probá con otra búsqueda o quitá filtros.
      </p>
    </main>
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
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
