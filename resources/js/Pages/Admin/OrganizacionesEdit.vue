<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  organization: Object,
  regions: Array,
  categories: Array,
  users: Array,
})

function findRegionIdForCommune(communeId) {
  if (!communeId) return null
  const region = props.regions.find(r =>
    r.provinces.some(p => p.communes.some(c => c.id === communeId))
  )
  return region ? region.id : null
}

const form = ref({
  user_id: props.organization.user_id ?? null,
  description: props.organization.description ?? '',
  commune_id: props.organization.commune?.id ?? null,
  latitude: props.organization.location?.lat ?? null,
  longitude: props.organization.location?.lng ?? null,
  instagram: props.organization.instagram ?? '',
  website: props.organization.website ?? '',
  whatsapp: props.organization.whatsapp ?? '',
  logo_url: props.organization.logo_url ?? '',
  cover_image: props.organization.cover_image ?? '',
  verification_status: props.organization.verification_status ?? 'unverified',
  claim_status: props.organization.claim_status ?? 'unclaimed',
  plan: props.organization.plan ?? 'free',
  opening_hours: props.organization.opening_hours ? JSON.stringify(props.organization.opening_hours, null, 2) : '',
  category_ids: [...(props.organization.category_ids ?? [])],
})

const selectedRegionId = ref(findRegionIdForCommune(form.value.commune_id))

const communesForRegion = computed(() => {
  const region = props.regions.find(r => r.id === selectedRegionId.value)
  if (!region) return []
  return region.provinces.flatMap(p => p.communes)
})

function onRegionChange() {
  form.value.commune_id = null
}

function toggleCategory(id) {
  const idx = form.value.category_ids.indexOf(id)
  if (idx === -1) form.value.category_ids.push(id)
  else form.value.category_ids.splice(idx, 1)
}

const saving = ref(false)

function save() {
  saving.value = true
  router.put(`/admin/organizaciones/${props.organization.id}`, form.value, {
    onFinish: () => { saving.value = false },
  })
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up truncate">Editar {{ organization.name }}</h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <form class="space-y-3" @submit.prevent="save">
      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Datos básicos</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Nombre</label>
            <input :value="organization.name" disabled
                   class="w-full rounded-xl border border-sky-100 bg-sky-50 text-sky-400 text-sm px-3 py-2" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Tipo</label>
            <input :value="organization.type" disabled
                   class="w-full rounded-xl border border-sky-100 bg-sky-50 text-sky-400 text-sm px-3 py-2 capitalize" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Descripción</label>
            <textarea v-model="form.description" rows="4"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .05s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Ubicación</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Región</label>
            <select v-model="selectedRegionId" @change="onRegionChange"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Seleccionar región…</option>
              <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Comuna</label>
            <select v-model="form.commune_id" :disabled="!selectedRegionId"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300 disabled:bg-sky-50 disabled:text-sky-300">
              <option :value="null">Seleccionar comuna…</option>
              <option v-for="c in communesForRegion" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
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
          <p class="text-xs text-sky-400">
            Podés obtener las coordenadas buscando el lugar en Google Maps y copiando los números que aparecen en la URL.
          </p>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .1s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Contacto</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Instagram</label>
            <input v-model="form.instagram" type="text" placeholder="@usuario"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Sitio web</label>
            <input v-model="form.website" type="text" placeholder="https://…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">WhatsApp</label>
            <input v-model="form.whatsapp" type="text" placeholder="+56 9…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Logo (URL)</label>
            <input v-model="form.logo_url" type="text" placeholder="https://…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Imagen de portada (URL)</label>
            <input v-model="form.cover_image" type="text" placeholder="https://…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .12s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Plan</h2>
        <div>
          <label class="block text-xs text-sky-500 mb-1">Plan GO</label>
          <select v-model="form.plan"
                  class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
            <option value="free">GO Free</option>
            <option value="pro">GO Pro</option>
            <option value="pro_plus">GO Pro+</option>
          </select>
          <p class="text-xs text-sky-400 mt-1">
            GO Pro/Pro+ muestran el sello en la landing pública del operador.
          </p>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .15s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Verificación</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Dueño (usuario)</label>
            <select v-model="form.user_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Sin dueño asignado</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
            <p class="text-xs text-sky-400 mt-1">
              El dueño puede editar esta organización y crear sus propias experiencias/proyectos desde /mi-organizacion.
            </p>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Estado de verificación</label>
            <select v-model="form.verification_status"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="unverified">Sin verificar</option>
              <option value="pending">Pendiente</option>
              <option value="verified">Verificado</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Estado de reclamo</label>
            <select v-model="form.claim_status"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="unclaimed">Sin reclamar</option>
              <option value="pending">Pendiente</option>
              <option value="claimed">Reclamado</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Horario de atención (JSON, opcional)</label>
            <textarea v-model="form.opening_hours" rows="4" placeholder='{"lunes": "9:00-18:00"}'
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .15s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Categorías</h2>
        <div class="grid grid-cols-2 gap-2">
          <label v-for="cat in categories" :key="cat.id"
                 class="flex items-center gap-2 text-sm text-sky-800 rounded-xl border border-sky-100 px-3 py-2">
            <input type="checkbox" :value="cat.id" :checked="form.category_ids.includes(cat.id)"
                   @change="toggleCategory(cat.id)" class="rounded border-sky-300 text-sky-700" />
            {{ cat.name }}
          </label>
        </div>
      </section>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/admin/organizaciones"
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
