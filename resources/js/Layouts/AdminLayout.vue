<script setup>
import { Link } from '@inertiajs/vue3';

const nav = [
  { label: 'Dashboard', href: '/admin' },
  { label: 'Reseñas', href: '/admin/reviews' },
  { label: 'Reclamos', href: '/admin/claims' },
  { label: 'Negocios', href: '/admin/businesses' },
  { label: 'Artículos', href: '/admin/articles' },
];
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="border-b border-ink/10">
      <div class="max-w-5xl mx-auto px-6 md:px-10 py-4 flex items-center justify-between">
        <div class="flex items-center gap-8">
          <span class="font-display text-lg">Ruta 360</span>
          <nav class="flex gap-5 font-mono text-xs uppercase tracking-wide">
            <Link
              v-for="item in nav"
              :key="item.href"
              :href="item.href"
              class="text-ink/60 hover:text-ink transition-colors"
            >
              {{ item.label }}
            </Link>
          </nav>
        </div>
        <Link href="/logout" method="post" as="button" class="font-mono text-xs uppercase tracking-wide text-ink/50 hover:text-rock transition-colors">
          Cerrar sesión
        </Link>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 md:px-10 py-8">
      <slot />
    </main>
  </div>
</template>
