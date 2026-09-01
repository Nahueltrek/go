<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  article: Object,
  canPublish: Boolean,
});

const isEditing = !!props.article;

const form = useForm({
  title: props.article?.title ?? '',
  slug: props.article?.slug ?? '',
  excerpt: props.article?.excerpt ?? '',
  body: props.article?.body ?? '',
  status: props.article?.status ?? 'draft',
  tags: props.article?.tags ?? '',
});

function submit() {
  if (isEditing) {
    form.put(`/admin/articles/${props.article.id}`);
  } else {
    form.post('/admin/articles');
  }
}
</script>

<template>
  <AdminLayout>
    <h1 class="font-display text-2xl mb-6">{{ isEditing ? 'Editar artículo' : 'Nuevo artículo' }}</h1>

    <form @submit.prevent="submit" class="space-y-5 max-w-2xl">
      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Título</label>
        <input v-model="form.title" type="text" required class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
        <p v-if="form.errors.title" class="text-xs text-rock mt-1">{{ form.errors.title }}</p>
      </div>

      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Slug (opcional — se genera del título si lo dejás vacío)</label>
        <input v-model="form.slug" type="text" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-river" />
        <p v-if="form.errors.slug" class="text-xs text-rock mt-1">{{ form.errors.slug }}</p>
      </div>

      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Bajada / resumen</label>
        <textarea v-model="form.excerpt" rows="2" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
      </div>

      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Cuerpo (texto plano, respeta saltos de línea)</label>
        <textarea v-model="form.body" rows="12" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm leading-relaxed focus:outline-none focus:border-river" />
      </div>

      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Etiquetas (separadas por coma)</label>
        <input v-model="form.tags" type="text" placeholder="trekking, familia, invierno" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river" />
      </div>

      <div>
        <label class="font-mono text-xs uppercase tracking-wide text-ink/60">Estado</label>
        <select v-model="form.status" class="mt-1.5 w-full rounded-lg border border-ink/15 bg-paper px-3.5 py-2.5 text-sm focus:outline-none focus:border-river">
          <option value="draft">Borrador</option>
          <option value="published" :disabled="!canPublish">Publicado{{ canPublish ? '' : ' (requiere admin)' }}</option>
        </select>
        <p v-if="!canPublish" class="text-xs text-ink/45 mt-1">
          Tu rol puede crear y editar, pero solo un administrador puede publicar.
        </p>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="bg-river text-paper rounded-lg px-5 py-2.5 text-sm font-medium hover:bg-river-dark transition-colors disabled:opacity-50"
      >
        Guardar
      </button>
    </form>
  </AdminLayout>
</template>
