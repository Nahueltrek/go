<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'

defineProps({
  activities: Array,
  featuredExperiences: Object,
  upcomingEvents: Object,
  latestPosts: Object,
  networkOrganizations: Object,
})

const q = ref('')

function goExplorar(activitySlug = null) {
  router.get('/explorar', {
    q: q.value || undefined,
    activity: activitySlug || undefined,
  })
}
</script>

<template>
  <div class="min-h-screen bg-white overflow-x-hidden">
    <!-- Hero -->
    <section class="relative h-[92vh] min-h-[560px] text-white overflow-hidden">
      <div class="absolute inset-0 z-0">
        <img
          src="/images/comunidad/02-torres-del-paine.jpg"
          class="h-full w-full object-cover scale-105 animate-hero-zoom"
          alt=""
        />
        <div class="absolute inset-0 bg-gradient-to-b from-sky-950/80 via-sky-900/55 to-sky-950/90"></div>
      </div>

      <div class="relative z-10 flex h-full flex-col px-5 pt-6">
        <img src="/images/logo.png" alt="GO Chile" class="h-11 w-11 rounded-full ring-2 ring-white/40 animate-fade-in" />

        <div class="flex-1 flex flex-col justify-end pb-16">
          <p class="text-xs font-semibold tracking-[0.25em] text-sky-300 uppercase animate-fade-in-up" style="animation-delay: .05s">
            Chile · Outdoor · Naturaleza
          </p>
          <h1 class="text-[2.6rem] leading-[1.05] font-extrabold mt-3 animate-fade-in-up" style="animation-delay: .15s">
            Descubre<br />Chile <span class="text-sky-300">outdoor</span>
          </h1>
          <p class="text-sm text-sky-100/90 mt-4 leading-relaxed max-w-xs animate-fade-in-up" style="animation-delay: .25s">
            Rutas, experiencias, operadores y proyectos que conectan personas con la naturaleza.
          </p>

          <form @submit.prevent="goExplorar()" class="relative mt-6 animate-fade-in-up" style="animation-delay: .35s">
            <input
              v-model="q"
              type="search"
              placeholder="¿Qué querés hacer? Trekking, rafting…"
              class="w-full rounded-full bg-white/95 backdrop-blur py-4 pl-12 pr-4 text-sm text-sky-950
                     placeholder:text-sky-950/40 shadow-xl shadow-sky-950/30 focus:outline-none focus:ring-2 focus:ring-sky-400"
            />
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sky-950/50">🔎</span>
          </form>
        </div>
      </div>

      <!-- Silueta de montaña -->
      <svg class="absolute bottom-0 left-0 w-full h-16 z-10" viewBox="0 0 400 60" preserveAspectRatio="none">
        <path d="M0,60 L0,35 L45,12 L80,32 L130,4 L175,30 L220,10 L265,34 L310,15 L360,32 L400,20 L400,60 Z" fill="white" />
      </svg>
    </section>

    <!-- Explorar por actividad -->
    <section class="px-5 pt-8 pb-6 -mt-1">
      <h2 class="text-base font-bold text-sky-950 mb-4">Explora por actividad</h2>
      <div class="grid grid-cols-4 gap-3">
        <button
          v-for="(a, i) in activities.slice(0, 8)"
          :key="a.id"
          @click="goExplorar(a.slug)"
          class="group flex flex-col items-center gap-2 rounded-2xl bg-sky-50 py-4 transition-all
                 duration-300 hover:bg-sky-100 hover:-translate-y-1 active:scale-95 animate-fade-in-up"
          :style="{ animationDelay: (0.05 * i) + 's' }"
        >
          <span class="text-2xl transition-transform duration-300 group-hover:scale-125">{{ a.icon || '📍' }}</span>
          <span class="text-[11px] font-medium text-sky-900 text-center leading-tight">{{ a.name }}</span>
        </button>
      </div>
    </section>

    <!-- Mapa CTA -->
    <section class="px-5 pb-6">
      <Link
        href="/mapa"
        class="group relative block rounded-3xl overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950 p-5
               transition-transform duration-300 hover:scale-[1.02] shadow-lg shadow-sky-900/20"
      >
        <div class="relative z-10 flex items-center justify-between">
          <div>
            <p class="text-sm font-bold text-white flex items-center gap-1.5">
              <span class="text-lg">🗺️</span> Mapa GO Chile
            </p>
            <p class="text-xs text-sky-200 mt-1">Todo el ecosistema outdoor, en un mapa</p>
          </div>
          <span class="text-white text-xl transition-transform duration-300 group-hover:translate-x-1">→</span>
        </div>
        <div class="absolute -right-6 -bottom-6 h-24 w-24 rounded-full bg-sky-400/20 blur-xl"></div>
      </Link>
    </section>

    <!-- Comunidad -->
    <section class="px-5 pb-8">
      <h2 class="text-base font-bold text-sky-950 mb-4">Nuestra comunidad</h2>
      <div class="grid grid-cols-3 gap-2">
        <div
          v-for="(img, i) in [
            '01-grupo-en-parque-irquinco',
            '03-trekking-nocturno',
            '05-equipo-de-rescate-gope-en-terreno',
            '08-grupo-con-bandera-outdoor-chile-en-ester',
            '11-voluntarios-plantando-un-arbol-nativo',
            '14-grupo-grande-con-banderas-de-trekking',
          ]"
          :key="img"
          class="group aspect-square rounded-2xl overflow-hidden animate-fade-in-up"
          :style="{ animationDelay: (0.05 * i) + 's' }"
        >
          <img
            :src="`/images/comunidad/${img}.jpg`"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            alt=""
          />
        </div>
      </div>
    </section>

    <!-- Experiencias destacadas -->
    <section v-if="featuredExperiences.data?.length" class="px-5 py-8 border-t border-sky-100 bg-sky-50/40">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-sky-950">Experiencias destacadas</h2>
        <Link href="/explorar" class="text-xs font-medium text-sky-600">Ver todas</Link>
      </div>
      <div class="flex gap-3 overflow-x-auto no-scrollbar -mx-5 px-5">
        <Link
          v-for="exp in featuredExperiences.data"
          :key="exp.id"
          :href="`/experiencias/${exp.slug}`"
          class="group shrink-0 w-44 rounded-2xl bg-white overflow-hidden shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
        >
          <div class="h-28 w-full bg-sky-100 overflow-hidden">
            <img v-if="exp.images?.[0]" :src="exp.images[0]" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
          </div>
          <div class="p-3">
            <p class="text-xs font-semibold text-sky-950 truncate">{{ exp.name }}</p>
            <p class="text-[11px] text-sky-500 truncate mt-0.5">{{ exp.organization?.name }}</p>
          </div>
        </Link>
      </div>
    </section>

    <!-- Agenda próxima -->
    <section v-if="upcomingEvents.data?.length" class="px-5 py-8 border-t border-sky-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-sky-950">📅 Próximas actividades</h2>
        <Link href="/agenda" class="text-xs font-medium text-sky-600">Ver agenda</Link>
      </div>
      <div class="space-y-2">
        <Link
          v-for="e in upcomingEvents.data"
          :key="e.id"
          :href="`/agenda/${e.slug}`"
          class="flex items-center gap-3 rounded-2xl border border-sky-100 p-3 transition-colors duration-300 hover:bg-sky-50"
        >
          <div class="text-sm font-medium text-sky-950 truncate flex-1">{{ e.title }}</div>
          <div class="text-[11px] font-medium text-sky-500 capitalize bg-sky-50 rounded-full px-2 py-1">{{ e.category }}</div>
        </Link>
      </div>
    </section>

    <!-- Red de colaboradores -->
    <section v-if="networkOrganizations.data?.length" class="px-5 py-8 border-t border-sky-100 bg-sky-50/40">
      <h2 class="text-base font-bold text-sky-950 mb-4">Nuestra red</h2>
      <div class="flex gap-4 overflow-x-auto no-scrollbar -mx-5 px-5">
        <Link
          v-for="org in networkOrganizations.data"
          :key="org.id"
          :href="`/operadores/${org.slug}`"
          class="group shrink-0 flex flex-col items-center gap-2 w-16"
        >
          <div class="h-16 w-16 rounded-full bg-white ring-2 ring-sky-100 overflow-hidden transition-transform duration-300 group-hover:scale-110 group-hover:ring-sky-300">
            <img v-if="org.logo_url" :src="org.logo_url" class="h-full w-full object-cover" />
          </div>
          <p class="text-[10px] font-medium text-sky-900 text-center truncate w-full">{{ org.name }}</p>
        </Link>
      </div>
    </section>

    <!-- Bitácora -->
    <section v-if="latestPosts.data?.length" class="px-5 py-8 border-t border-sky-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-sky-950">Bitácora GO</h2>
        <Link href="/bitacora" class="text-xs font-medium text-sky-600">Ver todo</Link>
      </div>
      <div class="space-y-3">
        <Link
          v-for="post in latestPosts.data"
          :key="post.id"
          :href="`/bitacora/${post.slug}`"
          class="group flex gap-3 rounded-2xl p-2 -mx-2 transition-colors duration-300 hover:bg-sky-50"
        >
          <div class="h-14 w-14 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
            <img v-if="post.cover_image_url" :src="post.cover_image_url" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110" />
          </div>
          <div class="min-w-0 flex flex-col justify-center">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-500">{{ post.category }}</p>
            <p class="text-sm font-medium text-sky-950 truncate">{{ post.title }}</p>
          </div>
        </Link>
      </div>
    </section>

    <!-- CTA colaboradores -->
    <section class="px-5 py-10 border-t border-sky-100">
      <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-sky-800 via-sky-900 to-sky-950 p-7 text-center">
        <div class="absolute -left-8 -top-8 h-32 w-32 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-sky-300/10 blur-2xl"></div>
        <p class="relative z-10 text-base font-bold text-white">🤝 ¿Sos guía, operador o tenés un proyecto territorial?</p>
        <Link
          href="/colaboradores"
          class="relative z-10 inline-block mt-4 text-sm font-semibold rounded-full bg-white text-sky-900 px-6 py-3
                 transition-transform duration-300 hover:scale-105 shadow-lg"
        >
          Quiero ser parte de GO Chile
        </Link>
      </div>
    </section>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes hero-zoom {
  from { transform: scale(1.12); }
  to { transform: scale(1.0); }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out both;
}
.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out both;
}
.animate-hero-zoom {
  animation: hero-zoom 8s ease-out forwards;
}
</style>
