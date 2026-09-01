<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<template>
  <div class="min-h-screen bg-glacier text-ink font-body antialiased flex items-center justify-center px-6">
    <div class="w-full max-w-sm">
      <h1 class="font-display text-2xl mb-1">Ruta 360</h1>
      <p class="font-mono text-xs uppercase tracking-wide text-ink/50 mb-8">Acceso administración</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            autofocus
            class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river"
          />
          <p v-if="form.errors.email" class="text-xs text-rock mt-1">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Contraseña</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river"
          />
          <p v-if="form.errors.password" class="text-xs text-rock mt-1">{{ form.errors.password }}</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-ink/70">
          <input v-model="form.remember" type="checkbox" class="rounded border-ink/30" />
          Recordarme
        </label>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-river text-paper rounded-lg py-2.5 text-sm font-medium hover:bg-river-dark transition-colors disabled:opacity-50"
        >
          Iniciar sesión
        </button>
      </form>
    </div>
  </div>
</template>
