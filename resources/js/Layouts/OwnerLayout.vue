<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const nav = [
  { label: 'Mi organización', href: '/mi-organizacion', exact: true },
  { label: 'Experiencias', href: '/mi-organizacion/experiencias' },
  { label: 'Proyectos', href: '/mi-organizacion/proyectos' },
]

function isActive(item) {
  const path = page.url.split('?')[0]
  return item.exact ? path === item.href : path.startsWith(item.href)
}
</script>

<template>
  <div class="min-h-screen bg-sky-50">
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center justify-between gap-2">
      <Link href="/" class="flex items-center gap-2 shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
        <span class="text-xs font-bold text-sky-950">GO Chile · Mi organización</span>
      </Link>
      <Link href="/" class="text-xs font-medium text-sky-600 shrink-0">← Volver al sitio</Link>
    </header>

    <nav class="bg-white border-b border-sky-100 px-4 py-2 flex gap-2 overflow-x-auto no-scrollbar">
      <Link
        v-for="item in nav"
        :key="item.href"
        :href="item.href"
        class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
        :class="isActive(item) ? 'bg-sky-900 text-white' : 'bg-white border border-sky-200 text-sky-600'"
      >{{ item.label }}</Link>
    </nav>

    <main>
      <slot />
    </main>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
