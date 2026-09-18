<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const nav = [
  { label: 'Dashboard', href: '/admin', exact: true },
  { label: 'Organizaciones', href: '/admin/organizaciones' },
  { label: 'Prospectos', href: '/admin/prospectos' },
  { label: 'Experiencias', href: '/admin/experiencias' },
  { label: 'Proyectos', href: '/admin/proyectos' },
  { label: 'Moderación', href: '/admin/moderacion' },
  { label: 'Colaboradores', href: '/admin/colaboradores' },
  { label: 'Reclamos', href: '/admin/claims' },
  { label: 'Reseñas', href: '/admin/reviews' },
  { label: 'Bitácora', href: '/admin/bitacora' },
  { label: 'Artículos', href: '/admin/articles' },
]

function isActive(item) {
  const path = page.url.split('?')[0]
  return item.exact ? path === item.href : path.startsWith(item.href)
}
</script>

<template>
  <div class="min-h-screen bg-sky-50">
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile · Admin</span>
    </header>

    <!-- Nav mobile: tabs horizontales scrolleables -->
    <nav class="md:hidden bg-white border-b border-sky-100 px-4 py-2 flex gap-2 overflow-x-auto no-scrollbar">
      <Link
        v-for="item in nav"
        :key="item.href"
        :href="item.href"
        class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
        :class="isActive(item) ? 'bg-sky-900 text-white' : 'bg-white border border-sky-200 text-sky-600'"
      >{{ item.label }}</Link>
    </nav>

    <div class="md:flex">
      <!-- Nav desktop: sidebar lateral -->
      <aside class="hidden md:block md:w-56 md:shrink-0 border-r border-sky-100 bg-white px-3 py-4">
        <nav class="space-y-1">
          <Link
            v-for="item in nav"
            :key="item.href"
            :href="item.href"
            class="block rounded-xl px-3 py-2 text-sm font-medium transition-colors"
            :class="isActive(item) ? 'bg-sky-900 text-white' : 'text-sky-700 hover:bg-sky-100'"
          >{{ item.label }}</Link>
        </nav>
      </aside>

      <main class="flex-1 min-w-0">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
