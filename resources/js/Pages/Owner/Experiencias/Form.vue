<script setup>
import { computed, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

const props = defineProps({
  experience: Object,
  destinations: Array,
  activityTypes: Array,
})

const isEditing = !!props.experience

// Mientras GO Chile está revisando, el Owner puede ver pero no editar
// (ver ExperiencePolicy::update() — el backend rechaza el guardado igual,
// esto es solo para que la UI no invite a un cambio que va a fallar).
const readOnly = computed(() => isEditing && props.experience.status === 'pending_review')
const canSubmitForReview = computed(() => isEditing && ['draft', 'rejected'].includes(props.experience.status))

const form = ref({
  destination_id: props.experience?.destination_id ?? null,
  activity_type_id: props.experience?.activity_type_id ?? null,
  name: props.experience?.name ?? '',
  description: props.experience?.description ?? '',
  difficulty: props.experience?.difficulty ?? '',
  duration_minutes: props.experience?.duration_minutes ?? null,
  capacity: props.experience?.capacity ?? null,
  price: props.experience?.price ?? null,
  cover_image: props.experience?.cover_image ?? '',
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/mi-organizacion/experiencias/${props.experience.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/mi-organizacion/experiencias', form.value, {
      onFinish: () => { saving.value = false },
    })
  }
}

function submitForReview() {
  router.post(`/mi-organizacion/experiencias/${props.experience.id}/enviar-revision`)
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <h1 class="text-lg font-bold text-sky-950 mb-4">{{ isEditing ? `Editar ${experience.name}` : 'Nueva experiencia' }}</h1>

    <p v-if="readOnly" class="text-xs text-blue-700 bg-blue-50 rounded-xl p-3 mb-4">
      GO Chile está revisando esta experiencia — no se puede editar hasta que la publiquen o rechacen.
    </p>
    <p v-else-if="isEditing && experience.status === 'published'" class="text-xs text-amber-700 bg-amber-50 rounded-xl p-3 mb-4">
      Esta experiencia está publicada — podés seguir editando el contenido, GO Chile revisa los cambios.
    </p>
    <p v-else-if="isEditing && experience.status === 'rejected'" class="text-xs text-red-700 bg-red-50 rounded-xl p-3 mb-4">
      GO Chile rechazó esta experiencia — corregí lo que haga falta y volvé a enviarla a revisión.
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
            <label class="block text-xs text-sky-500 mb-1">Tipo de actividad</label>
            <select v-model="form.activity_type_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400">
              <option :value="null">Seleccionar tipo…</option>
              <option v-for="c in activityTypes" :key="c.id" :value="c.id">{{ c.name }}</option>
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
            <label class="block text-xs text-sky-500 mb-1">Imagen de portada (URL)</label>
            <input v-model="form.cover_image" type="text" placeholder="https://…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Detalles</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Dificultad</label>
            <select v-model="form.difficulty"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400">
              <option value="">Sin especificar</option>
              <option value="facil">Fácil</option>
              <option value="medio">Medio</option>
              <option value="dificil">Difícil</option>
              <option value="experto">Experto</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-sky-500 mb-1">Duración (min)</label>
              <input v-model.number="form.duration_minutes" type="number" min="0"
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400" />
            </div>
            <div>
              <label class="block text-xs text-sky-500 mb-1">Capacidad</label>
              <input v-model.number="form.capacity" type="number" min="0"
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400" />
            </div>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Precio (CLP)</label>
            <input v-model.number="form.price" type="number" min="0"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-400" />
          </div>
        </div>
      </section>
      </fieldset>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/mi-organizacion/experiencias"
              class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2.5">Volver</Link>
        <button v-if="!readOnly" type="submit" :disabled="saving"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2.5 disabled:opacity-60">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </div>

      <button v-if="canSubmitForReview" type="button" @click="submitForReview"
              class="w-full text-xs font-medium rounded-full border border-sky-900 text-sky-900 py-2.5 mb-8">
        {{ experience.status === 'rejected' ? 'Volver a enviar a revisión' : 'Enviar a revisión' }}
      </button>
    </form>
  </div>
  </OwnerLayout>
</template>
