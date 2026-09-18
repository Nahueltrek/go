<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  organizations: Object,
  activeStatus: String,
})

const page = usePage()
const statuses = [
  { key: 'approved', label: 'Activos' },
  { key: 'pending', label: 'Pendientes' },
  { key: 'rejected', label: 'Suspendidos' },
  { key: 'all', label: 'Todos' },
]

function filterBy(status) {
  router.get('/admin/organizaciones', { status }, { preserveState: true })
}

function approve(id) {
  router.post(`/admin/organizaciones/${id}/approve`, {}, { preserveScroll: true })
}

function suspend(id) {
  router.post(`/admin/organizaciones/${id}/suspend`, {}, { preserveScroll: true })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Operadores</h1>
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
      <div v-for="(org, i) in organizations.data" :key="org.id"
           class="rounded-2xl bg-white border border-sky-100 p-4 transition-all duration-300 hover:shadow-md animate-fade-in-up"
           :style="{ animationDelay: (0.05 * i) + 's' }">
        <div class="flex gap-3 items-center">
          <div class="h-10 w-10 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
            <img v-if="org.logo_url" :src="org.logo_url" class="h-full w-full object-cover" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-sm font-medium text-sky-900 truncate">{{ org.name }}</p>
              <span v-if="org.plan && org.plan !== 'free'"
                    class="shrink-0 text-[10px] font-bold uppercase tracking-wide text-sky-950 bg-amber-400 rounded-full px-1.5 py-0.5">
                {{ org.plan === 'pro_plus' ? 'Pro+' : 'Pro' }}
              </span>
            </div>
            <p class="text-xs text-sky-500 capitalize">{{ org.type }} · {{ org.commune?.name }}</p>
          </div>
        </div>

        <div class="flex gap-2 mt-3" v-if="activeStatus !== 'rejected'">
          <button v-if="org.status !== 'approved'" @click="approve(org.id)"
                  class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Aprobar</button>
          <button v-if="org.status === 'approved'" @click="suspend(org.id)"
                  class="flex-1 text-xs font-medium rounded-full border border-sky-200 py-2">Suspender</button>
          <Link :href="`/admin/organizaciones/${org.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Editar</Link>
        </div>
        <div class="flex gap-2 mt-3" v-else>
          <button @click="approve(org.id)"
                  class="flex-1 text-xs font-medium rounded-full border border-sky-200 py-2">Reactivar</button>
          <Link :href="`/admin/organizaciones/${org.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Editar</Link>
        </div>
      </div>

      <p v-if="!organizations.data?.length" class="text-sm text-sky-400 text-center py-12">
        No hay operadores en este estado.
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
