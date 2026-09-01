<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  business: Object,
  contact: Object,
});

const form = useForm({
  description: props.business.description ?? '',
  address: props.business.address ?? '',
  phone: props.contact.phone ?? '',
  whatsapp: props.contact.whatsapp ?? '',
  email: props.contact.email ?? '',
  website: props.contact.website ?? '',
});

function submit() {
  form.put(`/dashboard/negocios/${props.business.slug}`, { preserveScroll: true });
}
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased">
    <header class="px-6 md:px-10 py-5 max-w-2xl mx-auto">
      <Link href="/dashboard" class="font-mono text-xs uppercase tracking-wide text-river-dark hover:underline">
        ← Mis negocios
      </Link>
    </header>

    <main class="px-6 md:px-10 max-w-2xl mx-auto pb-16">
      <h1 class="font-display text-2xl mb-1">{{ business.name }}</h1>
      <p class="font-mono text-xs text-ink/45 mb-8">
        Podés editar la descripción, dirección y datos de contacto. El nombre, categoría y
        estado SERNATUR los administra Ruta 360.
      </p>

      <div v-if="form.recentlySuccessful" class="bg-river/10 border border-river/30 rounded-lg px-4 py-2.5 text-sm text-river-dark mb-6">
        Cambios guardados.
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Descripción</label>
          <textarea
            v-model="form.description"
            rows="4"
            class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river"
          />
        </div>

        <div>
          <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Dirección</label>
          <input
            v-model="form.address"
            type="text"
            class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Teléfono</label>
            <input v-model="form.phone" type="text" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
          </div>
          <div>
            <label class="font-mono text-xs uppercase tracking-wide text-ink/60">WhatsApp</label>
            <input v-model="form.whatsapp" type="text" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
          </div>
          <div>
            <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Email</label>
            <input v-model="form.email" type="email" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
          </div>
          <div>
            <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Sitio web</label>
            <input v-model="form.website" type="text" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
          </div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="bg-river text-paper rounded-lg px-5 py-2.5 text-sm font-medium hover:bg-river-dark transition-colors disabled:opacity-50"
        >
          Guardar cambios
        </button>
      </form>
    </main>
  </div>
</template>
