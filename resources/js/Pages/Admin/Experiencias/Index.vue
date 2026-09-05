<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  experiences: Object,
  activeStatus: String,
})

const page = usePage()
const statuses = [
  { key: 'all', label: 'Todas' },
  { key: 'pending_review', label: 'En revisión' },
  { key: 'published', label: 'Publicadas' },
  { key: 'draft', label: 'Borradores' },
  { key: 'rejected', label: 'Rechazadas' },
  { key: 'unpublished', label: 'Despublicadas' },
]

function filterBy(status) {
  router.get('/admin/experiencias', { status }, { preserveState: true })
}

function destroy(exp) {
  if (!confirm(`¿Eliminar "${exp.name}"? Esta acción no se puede deshacer.`)) return
  router.delete(`/admin/experiencias/${exp.id}`, { preserveScroll: true })
}

function approve(exp) {
  router.post(`/admin/experiencias/${exp.id}/approve`, {}, { preserveScroll: true })
}

function reject(exp) {
  router.post(`/admin/experiencias/${exp.id}/reject`, {}, { preserveScroll: true })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Experiencias</h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div v-if="page.props.flash?.success" class="mt-3 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>

    <div class="flex items-center justify-between mt-4 mb-4 gap-2">
      <div class="flex gap-2 overflow-x-auto no-scrollbar">
        <button
          v-for="s in statuses"
          :key="s.key"
          @click="filterBy(s.key)"
          class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
          :class="activeStatus === s.key ? 'bg-sky-900 text-white' : 'bg-white border border-sky-200 text-sky-600'"
        >{{ s.label }}</button>
      </div>
      <Link href="/admin/experiencias/nueva"
            class="shrink-0 text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">+ Nueva</Link>
    </div>

    <div class="space-y-3">
      <div v-for="(exp, i) in experiences.data" :key="exp.id"
           class="rounded-2xl bg-white border border-sky-100 p-4 transition-all duration-300 hover:shadow-md animate-fade-in-up"
           :style="{ animationDelay: (0.05 * i) + 's' }">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ exp.name }}</p>
            <p class="text-xs text-sky-500">{{ exp.organization ?? 'Sin operador' }} · {{ exp.activity_type ?? 'Sin tipo' }}</p>
            <p v-if="exp.price" class="text-xs text-sky-400 mt-0.5">${{ Number(exp.price).toLocaleString('es-CL') }} CLP</p>
          </div>
          <span
            class="shrink-0 text-[11px] font-medium rounded-full px-2.5 py-1 capitalize"
            :class="{
              'bg-green-50 text-green-700': exp.status === 'published',
              'bg-amber-50 text-amber-700': exp.status === 'draft',
              'bg-blue-50 text-blue-700': exp.status === 'pending_review',
              'bg-red-50 text-red-700': exp.status === 'rejected',
              'bg-sky-50 text-sky-500': exp.status === 'unpublished',
            }"
          >{{ exp.status.replace('_', ' ') }}</span>
        </div>

        <div class="flex gap-2 mt-3" v-if="exp.status === 'pending_review'">
          <button @click="approve(exp)"
                  class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Publicar</button>
          <button @click="reject(exp)"
                  class="flex-1 text-xs font-medium rounded-full border border-red-200 text-red-600 py-2">Rechazar</button>
        </div>
        <div class="flex gap-2 mt-3">
          <Link :href="`/admin/experiencias/${exp.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Editar</Link>
          <button @click="destroy(exp)"
                  class="flex-1 text-xs font-medium rounded-full border border-red-200 text-red-600 py-2">Eliminar</button>
        </div>
      </div>

      <p v-if="!experiences.data?.length" class="text-sm text-sky-400 text-center py-12">
        No hay experiencias en este estado.
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
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
