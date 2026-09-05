<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  experiences: Array,
  projects: Array,
})

const page = usePage()

function approve(item) {
  router.post(item.approve_url, {}, { preserveScroll: true })
}

function reject(item) {
  router.post(item.reject_url, {}, { preserveScroll: true })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">Contenido pendiente</h1>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">
        Experiencias y proyectos creados por Owners, en espera de revisión
      </p>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div v-if="page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>

    <section class="mb-6">
      <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Experiencias ({{ experiences.length }})</h2>
      <div v-if="experiences.length" class="space-y-3">
        <div v-for="item in experiences" :key="`experience-${item.id}`" class="rounded-2xl bg-white border border-sky-100 p-4">
          <div class="flex items-start gap-3">
            <div class="h-14 w-14 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
              <img v-if="item.cover_image" :src="item.cover_image" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-sky-900 truncate">{{ item.name }}</p>
              <p class="text-xs text-sky-500">
                {{ item.organization ?? 'Sin organización' }}
                <span v-if="item.owner"> · {{ item.owner }}</span>
              </p>
              <p class="text-[11px] text-sky-400 mt-0.5">{{ item.created_at }}</p>
            </div>
          </div>

          <div class="flex gap-2 mt-3">
            <Link :href="item.edit_url" class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Revisar</Link>
            <button @click="approve(item)" class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Publicar</button>
            <button @click="reject(item)" class="flex-1 text-xs font-medium rounded-full border border-red-200 text-red-600 py-2">Rechazar</button>
          </div>
        </div>
      </div>
      <p v-else class="text-sm text-sky-400 text-center py-8">No hay experiencias pendientes de revisión.</p>
    </section>

    <section>
      <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Proyectos ({{ projects.length }})</h2>
      <div v-if="projects.length" class="space-y-3">
        <div v-for="item in projects" :key="`project-${item.id}`" class="rounded-2xl bg-white border border-sky-100 p-4">
          <div class="min-w-0">
            <p class="text-sm font-medium text-sky-900 truncate">{{ item.name }}</p>
            <p class="text-xs text-sky-500">
              {{ item.organization ?? 'Sin organización' }}
              <span v-if="item.owner"> · {{ item.owner }}</span>
            </p>
            <p class="text-[11px] text-sky-400 mt-0.5">{{ item.created_at }}</p>
          </div>

          <div class="flex gap-2 mt-3">
            <Link :href="item.edit_url" class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2">Revisar</Link>
            <button @click="approve(item)" class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2">Publicar</button>
            <button @click="reject(item)" class="flex-1 text-xs font-medium rounded-full border border-red-200 text-red-600 py-2">Rechazar</button>
          </div>
        </div>
      </div>
      <p v-else class="text-sm text-sky-400 text-center py-8">No hay proyectos pendientes de revisión.</p>
    </section>
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
