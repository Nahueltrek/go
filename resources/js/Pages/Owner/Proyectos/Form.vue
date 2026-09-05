<script setup>
import { computed, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

const props = defineProps({
  project: Object,
  destinations: Array,
})

const isEditing = !!props.project

const readOnly = computed(() => isEditing && props.project.status === 'pending_review')
const canSubmitForReview = computed(() => isEditing && ['draft', 'rejected'].includes(props.project.status))

const form = ref({
  destination_id: props.project?.destination_id ?? null,
  name: props.project?.name ?? '',
  description: props.project?.description ?? '',
  category: props.project?.category ?? '',
  how_to_collaborate: props.project?.how_to_collaborate ?? '',
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/mi-organizacion/proyectos/${props.project.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/mi-organizacion/proyectos', form.value, {
      onFinish: () => { saving.value = false },
    })
  }
}

function submitForReview() {
  router.post(`/mi-organizacion/proyectos/${props.project.id}/enviar-revision`)
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <h1 class="text-lg font-bold text-sky-950 mb-4">{{ isEditing ? `Editar ${project.name}` : 'Nuevo proyecto' }}</h1>

    <p v-if="readOnly" class="text-xs text-blue-700 bg-blue-50 rounded-xl p-3 mb-4">
      GO Chile está revisando este proyecto — no se puede editar hasta que lo publiquen o rechacen.
    </p>
    <p v-else-if="isEditing && project.status === 'published'" class="text-xs text-amber-700 bg-amber-50 rounded-xl p-3 mb-4">
      Este proyecto está publicado — podés seguir editando el contenido, GO Chile revisa los cambios.
    </p>
    <p v-else-if="isEditing && project.status === 'rejected'" class="text-xs text-red-700 bg-red-50 rounded-xl p-3 mb-4">
      GO Chile rechazó este proyecto — corregí lo que haga falta y volvé a enviarlo a revisión.
    </p>

    <form class="space-y-3" @submit.prevent="save">
      <fieldset :disabled="readOnly" class="space-y-3">
      <section class="rounded-2xl bg-white border border-sky-100 p-4">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Datos básicos</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Nombre</label>
            <input v-model="form.name" type="text"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Descripción</label>
            <textarea v-model="form.description" rows="4"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400"></textarea>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Categoría</label>
            <select v-model="form.category"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400">
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
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400">
              <option :value="null">Sin destino asociado</option>
              <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Cómo colaborar</label>
            <textarea v-model="form.how_to_collaborate" rows="3"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400"></textarea>
          </div>
        </div>
      </section>
      </fieldset>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/mi-organizacion/proyectos"
              class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2.5">Volver</Link>
        <button v-if="!readOnly" type="submit" :disabled="saving"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2.5 disabled:opacity-60">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </div>

      <button v-if="canSubmitForReview" type="button" @click="submitForReview"
              class="w-full text-xs font-medium rounded-full border border-sky-900 text-sky-900 py-2.5 mb-8">
        {{ project.status === 'rejected' ? 'Volver a enviar a revisión' : 'Enviar a revisión' }}
      </button>
    </form>
  </div>
  </OwnerLayout>
</template>
