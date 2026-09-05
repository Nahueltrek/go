<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

const props = defineProps({
  organization: Object,
  experiences: Object,
  projects: Object,
})

const page = usePage()

const statusLabel = {
  pending: 'Pendiente de aprobación GO Chile',
  approved: 'Aprobada',
  rejected: 'Rechazada',
}

const contentStatusLabel = {
  draft: 'Borrador',
  pending_review: 'En revisión',
  published: 'Publicada',
  rejected: 'Rechazada',
  unpublished: 'Despublicada',
}

const contentStatusClass = {
  draft: 'bg-amber-50 text-amber-700',
  pending_review: 'bg-blue-50 text-blue-700',
  published: 'bg-green-50 text-green-700',
  rejected: 'bg-red-50 text-red-700',
  unpublished: 'bg-sky-50 text-sky-500',
}

function actionFor(item, basePath) {
  if (['draft', 'rejected'].includes(item.status)) return { label: 'Editar', href: `${basePath}/${item.id}/editar` }
  if (item.status === 'pending_review') return { label: 'Ver', href: `${basePath}/${item.id}/editar` }
  return { label: 'Editar', href: `${basePath}/${item.id}/editar` }
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up truncate">{{ organization.name }}</h1>
      <p class="text-xs text-sky-200 mt-1 animate-fade-in-up" style="animation-delay: .1s">
        {{ statusLabel[organization.status] ?? organization.status }}
      </p>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <div v-if="page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>

    <p class="mb-4 text-xs text-sky-500 bg-sky-100/60 rounded-xl p-3">
      💡 GO Chile revisa y publica tu contenido — tus experiencias y proyectos nuevos quedan en borrador hasta que los envíes a revisión.
    </p>

    <section class="rounded-2xl bg-white border border-sky-100 p-4 mb-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide">Organización</h2>
        <Link href="/mi-organizacion/editar" class="text-xs font-medium text-sky-600">Editar organización</Link>
      </div>

      <p v-if="organization.description" class="text-sm text-sky-600 leading-relaxed mb-3">{{ organization.description }}</p>
      <p v-else class="text-sm text-sky-400 mb-3">Todavía no agregaste una descripción.</p>

      <dl class="space-y-1.5 text-xs">
        <div class="flex gap-2">
          <dt class="text-sky-400 shrink-0 w-20">Ubicación</dt>
          <dd class="text-sky-700">{{ organization.commune?.name ? `${organization.commune.name}, ${organization.commune.region ?? ''}` : 'Sin definir' }}</dd>
        </div>
        <div class="flex gap-2">
          <dt class="text-sky-400 shrink-0 w-20">Categorías</dt>
          <dd class="text-sky-700">{{ organization.categories?.length ? organization.categories.join(', ') : 'Sin definir' }}</dd>
        </div>
        <div class="flex gap-2">
          <dt class="text-sky-400 shrink-0 w-20">Contacto</dt>
          <dd class="text-sky-700">
            {{ [organization.whatsapp, organization.instagram, organization.website].filter(Boolean).join(' · ') || 'Sin definir' }}
          </dd>
        </div>
      </dl>
    </section>

    <section class="rounded-2xl bg-white border border-sky-100 p-4 mb-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide">Experiencias</h2>
        <Link href="/mi-organizacion/experiencias/nueva" class="text-xs font-medium rounded-full bg-sky-900 text-white px-3 py-1.5">+ Crear experiencia</Link>
      </div>
      <div v-if="experiences.data?.length" class="space-y-2">
        <div v-for="exp in experiences.data" :key="exp.id" class="flex items-center justify-between gap-2 rounded-xl border border-sky-100 p-3">
          <span class="text-sm text-sky-900 truncate">{{ exp.name }}</span>
          <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11px] font-medium rounded-full px-2 py-1" :class="contentStatusClass[exp.status]">
              {{ contentStatusLabel[exp.status] ?? exp.status }}
            </span>
            <Link :href="actionFor(exp, '/mi-organizacion/experiencias').href" class="text-xs font-medium text-sky-600">
              {{ actionFor(exp, '/mi-organizacion/experiencias').label }}
            </Link>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6">
        <p class="text-sm text-sky-400 mb-3">No tenés experiencias todavía.</p>
        <Link href="/mi-organizacion/experiencias/nueva" class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">Crear mi primera experiencia</Link>
      </div>
      <Link v-if="experiences.data?.length" href="/mi-organizacion/experiencias" class="block text-xs font-medium text-sky-600 mt-3">Ver todas →</Link>
    </section>

    <section class="rounded-2xl bg-white border border-sky-100 p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide">Proyectos</h2>
        <Link href="/mi-organizacion/proyectos/nuevo" class="text-xs font-medium rounded-full bg-sky-900 text-white px-3 py-1.5">+ Crear proyecto</Link>
      </div>
      <div v-if="projects.data?.length" class="space-y-2">
        <div v-for="p in projects.data" :key="p.id" class="flex items-center justify-between gap-2 rounded-xl border border-sky-100 p-3">
          <span class="text-sm text-sky-900 truncate">{{ p.name }}</span>
          <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11px] font-medium rounded-full px-2 py-1" :class="contentStatusClass[p.status]">
              {{ contentStatusLabel[p.status] ?? p.status }}
            </span>
            <Link :href="actionFor(p, '/mi-organizacion/proyectos').href" class="text-xs font-medium text-sky-600">
              {{ actionFor(p, '/mi-organizacion/proyectos').label }}
            </Link>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6">
        <p class="text-sm text-sky-400 mb-3">No tenés proyectos todavía.</p>
        <Link href="/mi-organizacion/proyectos/nuevo" class="text-xs font-medium rounded-full bg-sky-900 text-white px-4 py-2">Crear mi primer proyecto</Link>
      </div>
      <Link v-if="projects.data?.length" href="/mi-organizacion/proyectos" class="block text-xs font-medium text-sky-600 mt-3">Ver todos →</Link>
    </section>
  </div>
  </OwnerLayout>
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
