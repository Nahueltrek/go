<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  project: Object,
  organizations: Array,
  destinations: Array,
})

const isEditing = !!props.project

const form = ref({
  organization_id: props.project?.organization_id ?? null,
  destination_id: props.project?.destination_id ?? null,
  name: props.project?.name ?? '',
  description: props.project?.description ?? '',
  category: props.project?.category ?? '',
  how_to_collaborate: props.project?.how_to_collaborate ?? '',
  status: props.project?.status ?? 'draft',
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/admin/proyectos/${props.project.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/admin/proyectos', form.value, {
      onFinish: () => { saving.value = false },
    })
  }
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up truncate">
        {{ isEditing ? `Editar ${project.name}` : 'Nuevo proyecto' }}
      </h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <form class="space-y-3" @submit.prevent="save">
      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Datos básicos</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Operador</label>
            <select v-model="form.organization_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Seleccionar operador…</option>
              <option v-for="o in organizations" :key="o.id" :value="o.id">{{ o.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Nombre</label>
            <input v-model="form.name" type="text"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Descripción</label>
            <textarea v-model="form.description" rows="4"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Categoría</label>
            <select v-model="form.category"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="" disabled>Seleccionar categoría…</option>
              <option value="conservacion">Conservación</option>
              <option value="geologia">Geología</option>
              <option value="biodiversidad">Biodiversidad</option>
              <option value="comunidad">Comunidad</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Destino (opcional)</label>
            <select v-model="form.destination_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Sin destino asociado</option>
              <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Cómo colaborar</label>
            <textarea v-model="form.how_to_collaborate" rows="3"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .05s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Publicación</h2>
        <div>
          <label class="block text-xs text-sky-500 mb-1">Estado</label>
          <select v-model="form.status"
                  class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
            <option value="draft">Borrador</option>
            <option value="pending_review">En revisión</option>
            <option value="published">Publicado</option>
            <option value="rejected">Rechazado</option>
            <option value="unpublished">Despublicado</option>
          </select>
        </div>
      </section>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/admin/proyectos"
              class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2.5">Volver</Link>
        <button type="submit" :disabled="saving"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2.5 disabled:opacity-60">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </div>
    </form>
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
