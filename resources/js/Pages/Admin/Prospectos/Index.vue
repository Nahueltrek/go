<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  prospects: Object,
  activeStatus: String,
})

const page = usePage()

// Pipeline comercial GO Chile §16 — orden del embudo primero, estados de
// salida (no interesado, cerrado, etc.) al final.
const statuses = [
  { key: 'all', label: 'Todos' },
  { key: 'prospecto', label: 'Prospecto' },
  { key: 'contactado', label: 'Contactado' },
  { key: 'respondio', label: 'Respondió' },
  { key: 'demo', label: 'Demo' },
  { key: 'fundador', label: 'Fundador' },
  { key: 'activo', label: 'Activo' },
  { key: 'conversion_pro', label: 'Conversión Pro' },
  { key: 'volver_a_contactar', label: 'Volver a contactar' },
  { key: 'requiere_informacion', label: 'Requiere información' },
  { key: 'no_responde', label: 'No responde' },
  { key: 'no_interesado', label: 'No interesado' },
  { key: 'cerrado', label: 'Cerrado' },
]

const statusLabel = Object.fromEntries(statuses.map(s => [s.key, s.label]))

function filterBy(status) {
  router.get('/admin/prospectos', { status }, { preserveState: true })
}

function convert(id) {
  if (!confirm('¿Convertir en organización GO Pro? Esto crea el perfil público del operador.')) return
  router.post(`/admin/prospectos/${id}/convertir`, {}, { preserveScroll: true })
}

function destroy(id) {
  if (!confirm('¿Eliminar este prospecto?')) return
  router.delete(`/admin/prospectos/${id}`, { preserveScroll: true })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white animate-fade-in-up">Prospectos</h1>
        <Link href="/admin/prospectos/nuevo"
              class="text-xs font-medium rounded-full bg-white text-sky-900 px-3 py-1.5 animate-fade-in-up">+ Nuevo</Link>
      </div>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">
        Programa GO 20 Fundadores — pipeline comercial
      </p>
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
      <div v-for="(p, i) in prospects.data" :key="p.id"
           class="rounded-2xl bg-white border border-sky-100 p-4 transition-all duration-300 hover:shadow-md animate-fade-in-up"
           :style="{ animationDelay: (0.05 * i) + 's' }">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ p.business_name }}</p>
            <p class="text-xs text-sky-500">{{ p.contact_name || 'Sin contacto' }} · {{ p.territory || 'Sin territorio' }}</p>
          </div>
          <span class="shrink-0 text-[11px] font-medium rounded-full bg-sky-100 text-sky-700 px-2 py-1">
            {{ statusLabel[p.status] ?? p.status }}
          </span>
        </div>

        <p v-if="p.whatsapp || p.email" class="text-xs text-sky-400 mt-1">
          {{ [p.whatsapp, p.email].filter(Boolean).join(' · ') }}
        </p>

        <p v-if="p.observed_problem" class="text-xs text-sky-500 mt-2 line-clamp-2">{{ p.observed_problem }}</p>

        <div class="flex gap-2 mt-3">
          <Link :href="`/admin/prospectos/${p.id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Editar</Link>
          <button v-if="!p.organization_id" @click="convert(p.id)"
                  class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Convertir en organización</button>
          <Link v-else :href="`/admin/organizaciones/${p.organization_id}/editar`"
                class="flex-1 text-center text-xs font-medium rounded-full bg-green-50 text-green-700 py-2">Ver organización</Link>
          <button @click="destroy(p.id)"
                  class="shrink-0 text-xs font-medium rounded-full border border-sky-200 text-sky-500 px-3 py-2">Eliminar</button>
        </div>
      </div>

      <p v-if="!prospects.data?.length" class="text-sm text-sky-400 text-center py-12">
        No hay prospectos en este estado.
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
