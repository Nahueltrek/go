<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  posts: Object,
  categories: Object,
})

const page = usePage()

const statusLabel = {
  draft: 'Borrador',
  published: 'Publicado',
  unpublished: 'Despublicado',
}

function destroy() {
  // No hay borrado de historias en este sprint (no se pidió); se deja
  // el botón fuera hasta que exista un flujo editorial que lo requiera.
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Bitácora</h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div v-if="page.props.flash?.status" class="mt-3 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.status }}
    </div>

    <div class="flex items-center justify-end mt-4 mb-4">
      <Link href="/admin/bitacora/nueva"
            class="shrink-0 text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">+ Nueva historia</Link>
    </div>

    <div class="space-y-3">
      <Link
        v-for="(p, i) in posts.data" :key="p.id"
        :href="`/admin/bitacora/${p.id}/editar`"
        class="block rounded-2xl bg-white border border-sky-100 p-4 transition-all duration-300 hover:shadow-md animate-fade-in-up"
        :style="{ animationDelay: (0.05 * i) + 's' }"
      >
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ p.title }}</p>
            <p class="text-xs text-sky-500">{{ categories[p.category] ?? p.category }} · {{ p.author ?? 'Sin autor' }} · {{ p.published_at ?? 'sin publicar' }}</p>
          </div>
          <span
            class="shrink-0 text-[11px] font-medium rounded-full px-2.5 py-1"
            :class="{
              'bg-green-50 text-green-700': p.status === 'published',
              'bg-amber-50 text-amber-700': p.status === 'draft',
              'bg-sky-50 text-sky-500': p.status === 'unpublished',
            }"
          >{{ statusLabel[p.status] ?? p.status }}</span>
        </div>
      </Link>

      <p v-if="!posts.data?.length" class="text-sm text-sky-400 text-center py-12">
        Todavía no hay historias en la Bitácora.
      </p>
    </div>
  </div>
  </AdminLayout>
</template>

<style scoped>
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out both;
}
</style>
