<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import SeoHead from '@/Components/SeoHead.vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({
  activities: Array,
  featuredExperiences: Object,
  upcomingEvents: Object,
  latestPosts: Object,
  networkOrganizations: Object,
  featuredProjects: Object,
})

const categoryLabel = {
  conservacion: 'Conservación',
  geologia: 'Geología',
  biodiversidad: 'Biodiversidad',
  comunidad: 'Comunidad',
}

const q = ref('')

function goExplorar(query = null, activitySlug = null) {
  router.get('/explorar', {
    q: query ?? (q.value || undefined),
    activity: activitySlug || undefined,
  })
}

// "Lugares" no tiene todavía un tipo de búsqueda propio en SearchController
// (ver docs/SPRINT_1_ARQUITECTURA_GO_CHILE.md §4) — apunta al mapa, que es
// el destino real donde hoy se puede explorar territorio/lugares.
const exploreCategories = [
  { label: 'Rutas', icon: '🥾', href: '/explorar?type=ruta' },
  { label: 'Experiencias', icon: '⛺', href: '/explorar?type=experiencia' },
  { label: 'Lugares', icon: '🏔️', href: '/mapa' },
  { label: 'Organizaciones', icon: '🤝', href: '/explorar?type=operador' },
  { label: 'Proyectos', icon: '🌱', href: '/explorar?type=proyecto' },
  { label: 'Agenda', icon: '📅', href: '/agenda' },
]
</script>

<template>
  <SeoHead
    title="GO Chile — Grupo Outdoor Chile"
    description="Descubrí rutas, experiencias, operadores y proyectos de naturaleza y turismo outdoor en todo Chile."
  />
  <PublicLayout>
  <div class="overflow-x-hidden">
    <!-- Hero -->
    <section class="relative h-[52vh] min-h-[400px] md:h-[62vh] text-white overflow-hidden">
      <div class="absolute inset-0 z-0">
        <img
          src="/images/comunidad/02-torres-del-paine.jpg"
          class="h-full w-full object-cover scale-105 animate-hero-zoom"
          alt=""
        />
        <div class="absolute inset-0 bg-gradient-to-b from-sky-950/75 via-sky-900/50 to-sky-950/90"></div>
      </div>

      <div class="relative z-10 flex h-full flex-col justify-end px-5 pb-8">
        <p class="text-xs font-semibold tracking-[0.25em] text-sky-300 uppercase animate-fade-in-up" style="animation-delay: .05s">
          Chile · Outdoor · Naturaleza
        </p>
        <h1 class="text-[2rem] md:text-[2.4rem] leading-[1.08] font-extrabold mt-3 animate-fade-in-up" style="animation-delay: .15s">
          Descubre Chile desde sus <span class="text-sky-300">territorios</span>
        </h1>
        <p class="text-sm text-sky-100/90 mt-3 leading-relaxed max-w-sm animate-fade-in-up" style="animation-delay: .25s">
          Rutas, experiencias, organizaciones y proyectos que conectan personas con la naturaleza y las comunidades de Chile.
        </p>

        <div class="mt-5 animate-fade-in-up" style="animation-delay: .35s">
          <form @submit.prevent="goExplorar()" class="relative">
            <label for="home-search" class="sr-only">Buscar en GO Chile</label>
            <input
              id="home-search"
              v-model="q"
              type="search"
              placeholder="¿Qué quieres descubrir? Rutas, experiencias, proyectos…"
              class="w-full rounded-full bg-white/95 backdrop-blur py-4 pl-12 pr-4 text-sm text-sky-950
                     placeholder:text-sky-950/40 shadow-xl shadow-sky-950/30 focus:outline-none focus:ring-2 focus:ring-sky-400"
            />
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sky-950/50" aria-hidden="true">🔎</span>
          </form>

          <div class="flex gap-2 mt-4">
            <Link href="/explorar"
                  class="flex-1 text-center text-xs font-semibold rounded-full bg-white text-sky-900 px-4 py-3 shadow-lg hover:scale-[1.02] transition-transform">
              Explorar Chile
            </Link>
            <Link href="/colaboradores"
                  class="flex-1 text-center text-xs font-semibold rounded-full border border-white/40 text-white px-4 py-3 hover:bg-white/10 transition-colors">
              Quiero ser parte
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Explora Chile: las 6 puertas principales — máxima jerarquía de esta zona -->
    <section class="px-5 pt-8 pb-6">
      <h2 class="text-xl font-bold text-sky-950 mb-4">Explora Chile</h2>
      <div class="grid grid-cols-3 gap-3">
        <Link
          v-for="(cat, i) in exploreCategories" :key="cat.label" :href="cat.href"
          class="group flex flex-col items-center gap-2 rounded-2xl bg-sky-50 py-6 transition-all
                 duration-300 hover:bg-sky-100 hover:-translate-y-1 animate-fade-in-up"
          :style="{ animationDelay: (0.05 * i) + 's' }"
        >
          <span class="text-3xl transition-transform duration-300 group-hover:scale-125" aria-hidden="true">{{ cat.icon }}</span>
          <span class="text-xs font-semibold text-sky-900 text-center leading-tight">{{ cat.label }}</span>
        </Link>
      </div>
    </section>

    <!-- Actividad específica — secundario a propósito, fila angosta que no compite con "Explora Chile" -->
    <section v-if="activities.length" class="pb-8">
      <h2 class="px-5 text-[11px] font-semibold text-sky-500 uppercase tracking-wide mb-2">O buscá por actividad</h2>
      <div class="flex gap-2 overflow-x-auto no-scrollbar px-5">
        <button
          v-for="a in activities.slice(0, 10)"
          :key="a.id"
          @click="goExplorar(undefined, a.slug)"
          class="shrink-0 flex items-center gap-1.5 rounded-full bg-sky-50 text-sky-700 text-xs font-medium px-3 py-2 hover:bg-sky-100 transition-colors"
        >
          <span aria-hidden="true">{{ a.icon || '📍' }}</span>{{ a.name }}
        </button>
      </div>
    </section>

    <!-- Mapa: dimensión territorial nacional -->
    <section class="px-5 pb-8">
      <Link
        href="/mapa"
        class="group relative block rounded-3xl overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950 p-6
               transition-transform duration-300 hover:scale-[1.01] shadow-lg shadow-sky-900/20"
      >
        <div class="relative z-10">
          <p class="text-lg font-bold text-white">Chile, territorio por territorio.</p>
          <p class="text-xs text-sky-200 mt-2 max-w-xs leading-relaxed">
            Explora lugares, experiencias, rutas y proyectos a lo largo del país.
          </p>
          <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-white mt-4">
            Explorar mapa
            <span class="transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
          </span>
        </div>
        <div class="absolute -right-8 -bottom-8 h-32 w-32 rounded-full bg-sky-400/20 blur-xl" aria-hidden="true"></div>
      </Link>
    </section>

    <!-- Experiencias -->
    <section v-if="featuredExperiences.data?.length" class="px-5 py-8 border-t border-sky-100 bg-sky-50/40">
      <div class="flex items-center justify-between mb-1">
        <h2 class="text-lg font-bold text-sky-950">Vive Chile</h2>
        <Link href="/explorar?type=experiencia" class="text-xs font-medium text-sky-600">Ver experiencias</Link>
      </div>
      <p class="text-xs text-sky-500 mb-4">Experiencias para descubrir el territorio de otra manera.</p>
      <div class="flex gap-3 overflow-x-auto no-scrollbar -mx-5 px-5">
        <Link
          v-for="exp in featuredExperiences.data"
          :key="exp.id"
          :href="`/experiencias/${exp.slug}`"
          class="group shrink-0 w-44 rounded-2xl bg-white overflow-hidden shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
        >
          <div class="h-28 w-full bg-sky-100 overflow-hidden">
            <img v-if="exp.images?.[0]" :src="exp.images[0]" :alt="exp.name" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
          </div>
          <div class="p-3">
            <p class="text-xs font-semibold text-sky-950 truncate">{{ exp.name }}</p>
            <p class="text-[11px] text-sky-500 truncate mt-0.5">{{ exp.organization?.name }}</p>
          </div>
        </Link>
      </div>
    </section>

    <!-- Proyectos — identidad visual distinta de Experiencias a propósito:
         card grande con imagen de fondo y overlay, no la card chica de
         imagen-arriba-texto-abajo que usan las Experiencias. -->
    <section v-if="featuredProjects.data?.length" class="px-5 py-8 border-t border-sky-100">
      <div class="flex items-center justify-between mb-1">
        <h2 class="text-lg font-bold text-sky-950">Proyectos que transforman</h2>
        <Link href="/explorar?type=proyecto" class="text-xs font-medium text-sky-600">Conocer proyectos</Link>
      </div>
      <p class="text-xs text-sky-500 mb-4">Conoce iniciativas que trabajan por la naturaleza, las comunidades y los territorios.</p>
      <div class="flex gap-3 overflow-x-auto no-scrollbar -mx-5 px-5">
        <Link
          v-for="project in featuredProjects.data"
          :key="project.id"
          :href="`/proyectos/${project.slug}`"
          class="group relative shrink-0 w-64 h-40 rounded-2xl overflow-hidden shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 bg-sky-900"
        >
          <img v-if="project.images?.[0]" :src="project.images[0]" :alt="project.name"
               class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
          <div class="absolute inset-0 bg-gradient-to-t from-sky-950/90 via-sky-950/30 to-transparent"></div>
          <div class="relative z-10 h-full flex flex-col justify-end p-4">
            <span class="text-[10px] font-semibold uppercase tracking-wide text-sky-300 mb-1">
              {{ categoryLabel[project.category] ?? project.category }}
            </span>
            <p class="text-sm font-semibold text-white truncate">{{ project.name }}</p>
            <p v-if="project.organization?.name" class="text-[11px] text-sky-200 truncate mt-0.5">{{ project.organization.name }}</p>
          </div>
        </Link>
      </div>
    </section>

    <!-- Organizaciones -->
    <section v-if="networkOrganizations.data?.length" class="px-5 py-8 border-t border-sky-100">
      <div class="flex items-center justify-between mb-1">
        <h2 class="text-lg font-bold text-sky-950">Personas que hacen territorio</h2>
        <Link href="/explorar?type=operador" class="text-xs font-medium text-sky-600">Conocé las organizaciones</Link>
      </div>
      <p class="text-xs text-sky-500 mb-4">Detrás de cada experiencia hay una persona u organización local.</p>
      <div class="flex gap-4 overflow-x-auto no-scrollbar -mx-5 px-5">
        <Link
          v-for="org in networkOrganizations.data"
          :key="org.id"
          :href="`/operadores/${org.slug}`"
          class="group shrink-0 flex flex-col items-center gap-2 w-20"
        >
          <div class="h-16 w-16 rounded-full bg-white ring-2 ring-sky-100 overflow-hidden transition-transform duration-300 group-hover:scale-110 group-hover:ring-sky-300">
            <img v-if="org.logo_url" :src="org.logo_url" :alt="org.name" class="h-full w-full object-cover" />
          </div>
          <p class="text-[10px] font-medium text-sky-900 text-center truncate w-full">{{ org.name }}</p>
          <p class="text-[9px] text-sky-400 text-center truncate w-full capitalize">{{ org.type }}</p>
        </Link>
      </div>
    </section>

    <!-- Bitácora — peso visual reducido a propósito respecto de la Home anterior -->
    <section v-if="latestPosts.data?.length" class="px-5 py-6 border-t border-sky-100 bg-sky-50/40">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-bold text-sky-950">Historias del territorio</h2>
        <Link href="/bitacora" class="text-xs font-medium text-sky-600">Ver Bitácora</Link>
      </div>
      <div class="space-y-2">
        <Link
          v-for="post in latestPosts.data"
          :key="post.id"
          :href="`/bitacora/${post.slug}`"
          class="group flex gap-3 rounded-xl p-2 -mx-2 transition-colors duration-300 hover:bg-white"
        >
          <div class="h-11 w-11 shrink-0 rounded-lg bg-sky-100 overflow-hidden">
            <img v-if="post.cover_image_url" :src="post.cover_image_url" :alt="post.title" class="h-full w-full object-cover" />
          </div>
          <div class="min-w-0 flex flex-col justify-center">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-500">{{ post.category }}</p>
            <p class="text-xs font-medium text-sky-950 truncate">{{ post.title }}</p>
          </div>
        </Link>
      </div>
    </section>

    <!-- Agenda -->
    <section v-if="upcomingEvents.data?.length" class="px-5 py-8 border-t border-sky-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-sky-950">Lo que está pasando</h2>
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

    <!-- Comunidad -->
    <section class="px-5 py-10 border-t border-sky-100">
      <h2 class="text-lg font-bold text-sky-950 mb-1">Esto lo construimos entre todos</h2>
      <p class="text-xs text-sky-500 mb-5 max-w-sm leading-relaxed">
        GO Chile conecta personas, organizaciones y proyectos que comparten una forma de explorar, cuidar y vivir nuestros territorios.
      </p>

      <div class="grid grid-cols-3 gap-2 mb-6">
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
            alt="Comunidad GO Chile en terreno"
          />
        </div>
      </div>

      <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-sky-800 via-sky-900 to-sky-950 p-7 text-center">
        <div class="absolute -left-8 -top-8 h-32 w-32 rounded-full bg-sky-400/10 blur-2xl" aria-hidden="true"></div>
        <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-sky-300/10 blur-2xl" aria-hidden="true"></div>
        <p class="relative z-10 text-base font-bold text-white">🤝 ¿Sos guía, operador o tenés un proyecto territorial?</p>
        <Link
          href="/colaboradores"
          class="relative z-10 inline-block mt-4 text-sm font-semibold rounded-full bg-white text-sky-900 px-6 py-3
                 transition-transform duration-300 hover:scale-105 shadow-lg"
        >
          Quiero ser parte
        </Link>
      </div>
    </section>
  </div>
  </PublicLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes hero-zoom {
  from { transform: scale(1.12); }
  to { transform: scale(1.0); }
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out both;
}
.animate-hero-zoom {
  animation: hero-zoom 8s ease-out forwards;
}
</style>
