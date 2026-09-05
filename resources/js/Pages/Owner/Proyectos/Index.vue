<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

defineProps({
  projects: Array,
})

const page = usePage()

const statusLabel = {
  draft: 'Borrador',
  pending_review: 'En revisión',
  published: 'Publicado',
  rejected: 'Rechazado',
  unpublished: 'Despublicado',
}

const categoryLabel = {
  conservacion: 'Conservación',
  geologia: 'Geología',
  biodiversidad: 'Biodiversidad',
  comunidad: 'Comunidad',
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-bold text-sky-950">Proyectos</h1>
      <Link href="/mi-organizacion/proyectos/nuevo"
            class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">+ Crear proyecto</Link>
    </div>

    <div v-if="page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>
    <div v-if="page.props.flash?.error" class="mb-4 rounded-xl bg-red-50 text-red-800 text-sm p-3">
      {{ page.props.flash.error }}
    </div>

    <div v-if="projects.length" class="space-y-3">
      <div v-for="p in projects" :key="p.id" class="rounded-2xl bg-white border border-sky-100 p-4">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ p.name }}</p>
            <p class="text-xs text-sky-500">{{ categoryLabel[p.category] ?? p.category }}</p>
          </div>
          <span class="shrink-0 text-[11px] font-medium rounded-full px-2.5 py-1"
                :class="{
                  'bg-green-50 text-green-700': p.status === 'published',
                  'bg-amber-50 text-amber-700': p.status === 'draft',
                  'bg-blue-50 text-blue-700': p.status === 'pending_review',
                  'bg-red-50 text-red-700': p.status === 'rejected',
                  'bg-sky-50 text-sky-500': p.status === 'unpublished',
                }">{{ statusLabel[p.status] ?? p.status }}</span>
        </div>

        <p v-if="p.status === 'pending_review'" class="text-xs text-sky-400 mt-2">
          GO Chile está revisando este proyecto — no se puede editar hasta que lo publiquen o rechacen.
        </p>

        <div class="flex gap-2 mt-3">
          <Link :href="`/mi-organizacion/proyectos/${p.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">
            {{ p.status === 'pending_review' ? 'Ver' : 'Editar' }}
          </Link>
          <Link v-if="['draft', 'rejected'].includes(p.status)"
                :href="`/mi-organizacion/proyectos/${p.id}/enviar-revision`" method="post" as="button"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">
            {{ p.status === 'rejected' ? 'Volver a enviar' : 'Enviar a revisión' }}
          </Link>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <p class="text-sm text-sky-400 mb-4">No tenés proyectos todavía.</p>
      <Link href="/mi-organizacion/proyectos/nuevo"
            class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">Crear mi primer proyecto</Link>
    </div>
  </div>
  </OwnerLayout>
</template>
