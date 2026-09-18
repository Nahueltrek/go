<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  prospect: Object,
})

const isEditing = !!props.prospect

const form = ref({
  business_name: props.prospect?.business_name ?? '',
  contact_name: props.prospect?.contact_name ?? '',
  territory: props.prospect?.territory ?? '',
  category: props.prospect?.category ?? '',
  instagram: props.prospect?.instagram ?? '',
  website: props.prospect?.website ?? '',
  whatsapp: props.prospect?.whatsapp ?? '',
  email: props.prospect?.email ?? '',
  observed_problem: props.prospect?.observed_problem ?? '',
  notes: props.prospect?.notes ?? '',
  source: props.prospect?.source ?? '',
  status: props.prospect?.status ?? 'prospecto',
  last_contacted_at: props.prospect?.last_contacted_at?.slice(0, 10) ?? '',
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/admin/prospectos/${props.prospect.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/admin/prospectos', form.value, {
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
        {{ isEditing ? `Editar ${prospect.business_name}` : 'Nuevo prospecto' }}
      </h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <form class="space-y-3" @submit.prevent="save">
      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Datos del negocio</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Nombre del negocio/proyecto</label>
            <input v-model="form.business_name" type="text"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Persona de contacto</label>
            <input v-model="form.contact_name" type="text"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-sky-500 mb-1">Territorio</label>
              <input v-model="form.territory" type="text" placeholder="Cajón del Maipo"
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
            </div>
            <div>
              <label class="block text-xs text-sky-500 mb-1">Categoría</label>
              <input v-model="form.category" type="text" placeholder="Rafting"
                     class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .05s">
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
            <label class="block text-xs text-sky-500 mb-1">Email</label>
            <input v-model="form.email" type="text" placeholder="contacto@negocio.cl"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .1s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Pipeline comercial</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Estado</label>
            <select v-model="form.status"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="prospecto">Prospecto</option>
              <option value="contactado">Contactado</option>
              <option value="respondio">Respondió</option>
              <option value="demo">Demo</option>
              <option value="fundador">Fundador</option>
              <option value="activo">Activo</option>
              <option value="conversion_pro">Conversión Pro</option>
              <option value="volver_a_contactar">Volver a contactar</option>
              <option value="requiere_informacion">Requiere información</option>
              <option value="no_responde">No responde</option>
              <option value="no_interesado">No interesado</option>
              <option value="cerrado">Cerrado</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Último contacto</label>
            <input v-model="form.last_contacted_at" type="date"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Origen</label>
            <input v-model="form.source" type="text" placeholder="Instagram, referido, evento…"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Problema digital observado</label>
            <textarea v-model="form.observed_problem" rows="3"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Notas</label>
            <textarea v-model="form.notes" rows="3"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
        </div>
      </section>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/admin/prospectos"
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
