<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

const props = defineProps({
  organization: Object,
})

const form = ref({
  description: props.organization.description ?? '',
  instagram: props.organization.instagram ?? '',
  website: props.organization.website ?? '',
  whatsapp: props.organization.whatsapp ?? '',
  logo_url: props.organization.logo_url ?? '',
  cover_image: props.organization.cover_image ?? '',
})

const saving = ref(false)

function save() {
  saving.value = true
  router.put('/mi-organizacion', form.value, {
    onFinish: () => { saving.value = false },
  })
}
</script>

<template>
  <OwnerLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up truncate">Editar {{ organization.name }}</h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <form class="space-y-3" @submit.prevent="save">
      <section class="rounded-2xl bg-white border border-sky-100 p-4">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Datos básicos</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Descripción</label>
            <textarea v-model="form.description" rows="4"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4">
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

      <p class="text-xs text-sky-400 px-1">
        La región/comuna, categorías y ubicación en el mapa las administra GO Chile — escribinos si necesitás actualizarlas.
      </p>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/mi-organizacion"
              class="flex-1 text-center text-xs font-medium rounded-full border border-sky-200 text-sky-700 py-2.5">Volver</Link>
        <button type="submit" :disabled="saving"
                class="flex-1 text-xs font-medium rounded-full bg-sky-900 text-white py-2.5 disabled:opacity-60">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </div>
    </form>
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
