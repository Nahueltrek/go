<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

defineProps({
  experiences: Array,
})

const page = usePage()

const statusLabel = {
  draft: 'Borrador',
  pending_review: 'En revisión',
  published: 'Publicada',
  rejected: 'Rechazada',
  unpublished: 'Despublicada',
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-bold text-sky-950">Experiencias</h1>
      <Link href="/mi-organizacion/experiencias/nueva"
            class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">+ Crear experiencia</Link>
    </div>

    <div v-if="page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>
    <div v-if="page.props.flash?.error" class="mb-4 rounded-xl bg-red-50 text-red-800 text-sm p-3">
      {{ page.props.flash.error }}
    </div>

    <div v-if="experiences.length" class="space-y-3">
      <div v-for="exp in experiences" :key="exp.id" class="rounded-2xl bg-white border border-sky-100 p-4">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ exp.name }}</p>
            <p class="text-xs text-sky-500">{{ exp.activity_type ?? 'Sin tipo' }}</p>
          </div>
          <span class="shrink-0 text-[11px] font-medium rounded-full px-2.5 py-1"
                :class="{
                  'bg-green-50 text-green-700': exp.status === 'published',
                  'bg-amber-50 text-amber-700': exp.status === 'draft',
                  'bg-blue-50 text-blue-700': exp.status === 'pending_review',
                  'bg-red-50 text-red-700': exp.status === 'rejected',
                  'bg-sky-50 text-sky-500': exp.status === 'unpublished',
                }">{{ statusLabel[exp.status] ?? exp.status }}</span>
        </div>

        <p v-if="exp.status === 'pending_review'" class="text-xs text-sky-400 mt-2">
          GO Chile está revisando esta experiencia — no se puede editar hasta que la publiquen o rechacen.
        </p>

        <div class="flex gap-2 mt-3">
          <Link :href="`/mi-organizacion/experiencias/${exp.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">
            {{ exp.status === 'pending_review' ? 'Ver' : 'Editar' }}
          </Link>
          <Link v-if="['draft', 'rejected'].includes(exp.status)"
                :href="`/mi-organizacion/experiencias/${exp.id}/enviar-revision`" method="post" as="button"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">
            {{ exp.status === 'rejected' ? 'Volver a enviar' : 'Enviar a revisión' }}
          </Link>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <p class="text-sm text-sky-400 mb-4">No tenés experiencias todavía.</p>
      <Link href="/mi-organizacion/experiencias/nueva"
            class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">Crear mi primera experiencia</Link>
    </div>
  </div>
  </OwnerLayout>
</template>
