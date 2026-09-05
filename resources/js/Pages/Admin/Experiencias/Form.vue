<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  experience: Object,
  organizations: Array,
  destinations: Array,
  activityTypes: Array,
})

const isEditing = !!props.experience

const form = ref({
  organization_id: props.experience?.organization_id ?? null,
  destination_id: props.experience?.destination_id ?? null,
  activity_type_id: props.experience?.activity_type_id ?? null,
  name: props.experience?.name ?? '',
  description: props.experience?.description ?? '',
  difficulty: props.experience?.difficulty ?? '',
  duration_minutes: props.experience?.duration_minutes ?? null,
  capacity: props.experience?.capacity ?? null,
  price: props.experience?.price ?? null,
  cover_image: props.experience?.cover_image ?? '',
  status: props.experience?.status ?? 'draft',
  is_featured: props.experience?.is_featured ?? false,
  latitude: props.experience?.latitude ?? null,
  longitude: props.experience?.longitude ?? null,
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/admin/experiencias/${props.experience.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/admin/experiencias', form.value, {
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
        {{ isEditing ? `Editar ${experience.name}` : 'Nueva experiencia' }}
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
            <label class="block text-xs text-sky-500 mb-1">Tipo de actividad</label>
            <select v-model="form.activity_type_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Seleccionar tipo…</option>
              <option v-for="c in activityTypes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Imagen de portada (URL)</label>
            <input v-model="form.cover_image" type="text" placeholder="https://…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Destino (opcional)</label>
            <select v-model="form.destination_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Sin destino asociado</option>
              <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .05s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Detalles</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Dificultad</label>
            <select v-model="form.difficulty"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
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
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
            </div>
            <div>
              <label class="block text-xs text-sky-500 mb-1">Capacidad</label>
              <input v-model.number="form.capacity" type="number" min="0"
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
            </div>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Precio (CLP)</label>
            <input v-model.number="form.price" type="number" min="0"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .1s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Ubicación</h2>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Latitud</label>
            <input v-model.number="form.latitude" type="number" step="any"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Longitud</label>
            <input v-model.number="form.longitude" type="number" step="any"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
        </div>
        <p class="text-xs text-sky-400 mt-2">
          Podés obtener las coordenadas buscando el lugar en Google Maps y copiando los números que aparecen en la URL.
        </p>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .15s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Publicación</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Estado</label>
            <select v-model="form.status"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="draft">Borrador</option>
              <option value="pending_review">En revisión</option>
              <option value="published">Publicada</option>
              <option value="rejected">Rechazada</option>
              <option value="unpublished">Despublicada</option>
            </select>
          </div>
          <label class="flex items-center gap-2 text-sm text-sky-800">
            <input type="checkbox" v-model="form.is_featured" class="rounded border-sky-300 text-sky-700" />
            Destacar en el sitio
          </label>
        </div>
      </section>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/admin/experiencias"
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
