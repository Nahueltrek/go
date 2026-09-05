<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  requests: Object,
  activeStatus: String,
})

const page = usePage()
const statuses = [
  { key: 'pending', label: 'Pendientes' },
  { key: 'approved', label: 'Aprobadas' },
  { key: 'rejected', label: 'Rechazadas' },
  { key: 'all', label: 'Todas' },
]

function filterBy(status) {
  router.get('/admin/colaboradores', { status }, { preserveState: true })
}

function approve(id) {
  router.post(`/admin/colaboradores/${id}/approve`, {}, { preserveScroll: true })
}

function reject(id) {
  router.post(`/admin/colaboradores/${id}/reject`, {}, { preserveScroll: true })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Postulaciones de colaboradores</h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div v-if="page.props.flash?.success" class="mt-3 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>

    <div class="flex gap-2 mt-4 mb-4 overflow-x-auto no-scrollbar">
      <button
        v-for="s in statuses"
        :key="s.key"
        @click="filterBy(s.key)"
        class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
        :class="activeStatus === s.key ? 'bg-sky-900 text-white' : 'bg-white border border-sky-200 text-sky-600'"
      >{{ s.label }}</button>
    </div>

    <div class="space-y-3">
      <div v-for="(req, i) in requests.data" :key="req.id"
           class="rounded-2xl bg-white border border-sky-100 p-4 transition-all duration-300 hover:shadow-md animate-fade-in-up"
           :style="{ animationDelay: (0.05 * i) + 's' }">
        <div class="flex justify-between items-start">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900">{{ req.name }}</p>
            <p class="text-xs text-sky-500 capitalize">{{ req.type }} · {{ req.region?.name || 'Sin región' }}</p>
          </div>
          <span class="text-xs shrink-0 rounded-full px-2 py-0.5"
                :class="{
                  'bg-amber-50 text-amber-700': req.status === 'pending',
                  'bg-green-50 text-green-700': req.status === 'approved',
                  'bg-red-50 text-red-700': req.status === 'rejected',
                }">{{ req.status }}</span>
        </div>

        <p v-if="req.description" class="text-xs text-sky-600 mt-2 line-clamp-2">{{ req.description }}</p>

        <div class="flex gap-3 mt-2 text-xs">
          <a v-if="req.instagram" :href="req.instagram" class="text-sky-500 underline">Instagram</a>
          <a v-if="req.website" :href="req.website" class="text-sky-500 underline">Sitio web</a>
          <a v-if="req.whatsapp" :href="`https://wa.me/${req.whatsapp}`" class="text-sky-500 underline">WhatsApp</a>
        </div>

        <div v-if="req.status === 'pending'" class="flex gap-2 mt-3">
          <button @click="approve(req.id)"
                  class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Aprobar</button>
          <button @click="reject(req.id)"
                  class="flex-1 text-xs font-medium rounded-full border border-sky-200 py-2">Rechazar</button>
        </div>
      </div>

      <p v-if="!requests.data?.length" class="text-sm text-sky-400 text-center py-12">
        No hay postulaciones en este estado.
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
