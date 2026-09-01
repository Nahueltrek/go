<script setup>
import { Link } from '@inertiajs/vue3'
import { useForm, usePage } from '@inertiajs/vue3'

defineProps({
  regions: Array,
  types: Array,
})

const typeLabels = {
  guia: 'Guía', operador: 'Operador', agencia: 'Agencia', emprendimiento: 'Emprendimiento',
  alojamiento: 'Alojamiento', marca: 'Marca', proyecto: 'Proyecto', organizacion: 'Organización',
}

const form = useForm({
  type: '', name: '', region_id: '', instagram: '', website: '', whatsapp: '', description: '',
})

const page = usePage()

function submit() {
  form.post('/colaboradores', { preserveScroll: true, onSuccess: () => form.reset() })
}
</script>

<template>
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <header class="relative px-4 pt-7 pb-8 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up">🤝 Quiero ser parte de GO Chile</h1>
      <p class="text-xs text-sky-200 mt-2 leading-relaxed max-w-xs animate-fade-in-up" style="animation-delay: .1s">
        Guías, operadores, agencias, alojamientos, marcas y proyectos territoriales — sumate a la red.
      </p>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="white" />
      </svg>
    </header>

    <div v-if="page.props.flash?.success" class="mx-4 mt-4 rounded-xl bg-green-50 text-green-800 text-sm p-3">
      {{ page.props.flash.success }}
    </div>

    <form @submit.prevent="submit" class="px-4 py-5 space-y-4 animate-fade-in-up" style="animation-delay: .2s">
      <div>
        <label class="text-xs font-medium text-sky-600">Tipo de colaborador</label>
        <select v-model="form.type" required class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none">
          <option value="" disabled>Seleccionar…</option>
          <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] || t }}</option>
        </select>
        <p v-if="form.errors.type" class="text-xs text-red-600 mt-1">{{ form.errors.type }}</p>
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">Nombre</label>
        <input v-model="form.name" required type="text"
               class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">Región</label>
        <select v-model="form.region_id" class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none">
          <option value="">Seleccionar…</option>
          <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name }}</option>
        </select>
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">Instagram</label>
        <input v-model="form.instagram" type="text" placeholder="@tuemprendimiento"
               class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none" />
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">Sitio web</label>
        <input v-model="form.website" type="url" placeholder="https://"
               class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none" />
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">WhatsApp</label>
        <input v-model="form.whatsapp" type="text" placeholder="+56 9…"
               class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none" />
      </div>

      <div>
        <label class="text-xs font-medium text-sky-600">Contanos qué hacés</label>
        <textarea v-model="form.description" rows="4"
                  class="w-full mt-1 rounded-xl border border-sky-200 px-3 py-2.5 text-sm transition-colors focus:border-sky-500 focus:outline-none"></textarea>
      </div>

      <button type="submit" :disabled="form.processing"
              class="w-full text-sm font-medium rounded-full bg-sky-900 text-white py-3 disabled:opacity-50 transition-transform duration-300 hover:scale-[1.01] active:scale-95">
        {{ form.processing ? 'Enviando…' : 'Enviar postulación' }}
      </button>
    </form>
  </div>
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
