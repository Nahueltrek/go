<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Footer from '@/Components/Footer.vue'

const page = usePage()
const menuOpen = ref(false)

// Experiencias/Rutas/Proyectos/Organizaciones no tienen ruta propia de
// listado hoy (solo detalle: /experiencias/{slug}, etc.) — se reusa
// /explorar con el filtro de tipo que SearchController ya resuelve, en
// vez de crear rutas/controladores nuevos (esta fase es visual, no de
// backend).
const nav = [
  { label: 'Explorar', href: '/explorar' },
  { label: 'Mapa', href: '/mapa' },
  { label: 'Experiencias', href: '/explorar?type=experiencia' },
  { label: 'Rutas', href: '/explorar?type=ruta' },
  { label: 'Proyectos', href: '/explorar?type=proyecto' },
  { label: 'Agenda', href: '/agenda' },
  { label: 'Bitácora', href: '/bitacora' },
]

function closeMenu() {
  menuOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col">
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-sky-100">
      <div class="px-4 py-2.5 flex items-center justify-between gap-2">
        <Link href="/" class="flex items-center gap-2 shrink-0" @click="closeMenu">
          <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
          <span class="text-xs font-bold text-sky-950">GO Chile</span>
        </Link>

        <nav class="hidden md:flex items-center gap-5" aria-label="Navegación principal">
          <Link v-for="item in nav" :key="item.href" :href="item.href"
                class="text-xs font-medium text-sky-700 hover:text-sky-900 transition-colors">{{ item.label }}</Link>
        </nav>

        <div class="hidden md:flex items-center gap-3 shrink-0">
          <Link v-if="page.props.auth?.user" href="/mi-organizacion" class="text-xs font-medium text-sky-700 hover:text-sky-900">Mi GO</Link>
          <Link href="/colaboradores" class="text-xs font-semibold rounded-full bg-sky-900 text-white px-4 py-2 hover:bg-sky-800 transition-colors">Quiero ser parte</Link>
        </div>

        <button
          type="button"
          class="md:hidden shrink-0 h-9 w-9 flex items-center justify-center text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-300 rounded-lg"
          :aria-expanded="menuOpen"
          aria-label="Abrir menú de navegación"
          @click="menuOpen = !menuOpen"
        >
          <svg v-if="!menuOpen" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <nav v-if="menuOpen" class="md:hidden px-4 pb-3 space-y-1 border-t border-sky-100" aria-label="Navegación móvil">
        <Link
          v-for="item in nav" :key="item.href" :href="item.href" @click="closeMenu"
          class="block rounded-xl px-3 py-2.5 text-sm font-medium text-sky-800 hover:bg-sky-50"
        >{{ item.label }}</Link>
        <div class="pt-2 flex gap-2">
          <Link v-if="page.props.auth?.user" href="/mi-organizacion" @click="closeMenu"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2.5">Mi GO</Link>
          <Link href="/colaboradores" @click="closeMenu"
                class="flex-1 text-center text-xs font-semibold rounded-full bg-sky-900 text-white py-2.5">Quiero ser parte</Link>
        </div>
      </nav>
    </header>

    <main class="flex-1">
      <slot />
    </main>

    <Footer />
  </div>
</template>
