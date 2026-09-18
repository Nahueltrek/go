<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  post: Object,
  categories: Object,
  organizations: Array,
  destinations: Array,
  canPublish: Boolean,
})

const isEditing = !!props.post

const form = ref({
  title: props.post?.title ?? '',
  slug: props.post?.slug ?? '',
  excerpt: props.post?.excerpt ?? '',
  content: props.post?.content ?? '',
  category: props.post?.category ?? '',
  cover_image_url: props.post?.cover_image_url ?? '',
  related_organization_id: props.post?.related_organization_id ?? null,
  related_destination_id: props.post?.related_destination_id ?? null,
  status: props.post?.status ?? 'draft',
})

const saving = ref(false)

function save() {
  saving.value = true
  if (isEditing) {
    router.put(`/admin/bitacora/${props.post.id}`, form.value, {
      onFinish: () => { saving.value = false },
    })
  } else {
    router.post('/admin/bitacora', form.value, {
      onFinish: () => { saving.value = false },
    })
  }
}

// --- Subida de imagen de portada (drag & drop / clic) ---
const fileInput = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadError = ref('')
const localPreview = ref(null)

const previewSrc = computed(() => localPreview.value || form.value.cover_image_url || null)

function triggerFileSelect() {
  if (uploading.value) return
  fileInput.value?.click()
}

function onFileSelected(e) {
  const file = e.target.files?.[0]
  if (file) handleFile(file)
  e.target.value = ''
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) handleFile(file)
}

function handleFile(file) {
  uploadError.value = ''

  if (!file.type.startsWith('image/')) {
    uploadError.value = 'Ese archivo no es una imagen.'
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    uploadError.value = 'La imagen pesa más de 5MB — probá con una versión más liviana.'
    return
  }

  if (localPreview.value) URL.revokeObjectURL(localPreview.value)
  localPreview.value = URL.createObjectURL(file)
  uploadImage(file)
}

function uploadImage(file) {
  uploading.value = true
  uploadProgress.value = 0

  const data = new FormData()
  data.append('image', file)

  window.axios.post('/admin/bitacora/subir-imagen', data, {
    onUploadProgress: (evt) => {
      if (evt.total) uploadProgress.value = Math.round((evt.loaded / evt.total) * 100)
    },
  }).then((res) => {
    form.value.cover_image_url = res.data.url
  }).catch((err) => {
    uploadError.value = err.response?.data?.message
      || err.response?.data?.errors?.image?.[0]
      || 'No se pudo subir la imagen. Probá de nuevo.'
  }).finally(() => {
    uploading.value = false
    if (localPreview.value) {
      URL.revokeObjectURL(localPreview.value)
      localPreview.value = null
    }
  })
}

function removeImage() {
  form.value.cover_image_url = ''
  uploadError.value = ''
  if (localPreview.value) {
    URL.revokeObjectURL(localPreview.value)
    localPreview.value = null
  }
}
</script>

<template>
  <AdminLayout>
  <div class="px-4 py-6">
    <div class="relative -mx-4 px-4 pt-6 pb-8 mb-5 overflow-hidden bg-gradient-to-br from-sky-800 to-sky-950">
      <h1 class="text-xl font-bold text-white animate-fade-in-up truncate">
        {{ isEditing ? `Editar ${post.title}` : 'Nueva historia' }}
      </h1>
      <svg class="absolute bottom-0 left-0 w-full h-6" viewBox="0 0 400 24" preserveAspectRatio="none">
        <path d="M0,24 L0,14 L50,4 L100,16 L150,2 L200,14 L250,6 L300,16 L350,8 L400,14 L400,24 Z" fill="#eff6ff" />
      </svg>
    </div>

    <form class="space-y-3" @submit.prevent="save">
      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Contenido</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Título</label>
            <input v-model="form.title" type="text" required
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Slug (opcional — se genera del título si lo dejás vacío)</label>
            <input v-model="form.slug" type="text"
                   class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-sky-300" />
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Bajada / resumen</label>
            <textarea v-model="form.excerpt" rows="2"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Contenido</label>
            <textarea v-model="form.content" rows="12"
                      class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 leading-relaxed focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Imagen de portada</label>

            <div
              class="relative rounded-2xl border-2 border-dashed transition-colors overflow-hidden"
              :class="isDragging ? 'border-sky-400 bg-sky-50' : 'border-sky-200'"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="onDrop"
            >
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelected" />

              <div v-if="previewSrc" class="relative group">
                <img :src="previewSrc" class="w-full h-40 object-cover" />

                <div v-if="!uploading"
                     class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                  <button type="button" @click="triggerFileSelect"
                          class="text-xs font-medium bg-white text-sky-900 rounded-full px-3 py-1.5">Cambiar</button>
                  <button type="button" @click="removeImage"
                          class="text-xs font-medium bg-white text-red-600 rounded-full px-3 py-1.5">Quitar</button>
                </div>

                <div v-if="uploading" class="absolute inset-0 bg-white/85 flex flex-col items-center justify-center gap-1.5">
                  <div class="h-1 w-24 bg-sky-100 rounded-full overflow-hidden">
                    <div class="h-full bg-sky-600 transition-all" :style="{ width: uploadProgress + '%' }"></div>
                  </div>
                  <span class="text-[11px] text-sky-600">Subiendo… {{ uploadProgress }}%</span>
                </div>
              </div>

              <button v-else type="button" @click="triggerFileSelect"
                      class="w-full h-40 flex flex-col items-center justify-center gap-2 text-sky-400 hover:text-sky-600 hover:bg-sky-50/50 transition-colors">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-xs font-medium">Arrastrá una imagen o hacé clic para elegirla</span>
                <span class="text-[11px] text-sky-300">JPG, PNG o WEBP · máx. 5MB</span>
              </button>
            </div>

            <p v-if="uploadError" class="text-xs text-red-500 mt-1.5">{{ uploadError }}</p>

            <details class="mt-2">
              <summary class="text-[11px] text-sky-400 cursor-pointer select-none">o pegar una URL manualmente</summary>
              <input v-model="form.cover_image_url" type="text" placeholder="https://…"
                     class="w-full mt-1.5 rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300" />
            </details>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Categoría</label>
            <select v-model="form.category" required
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option value="" disabled>Seleccionar categoría…</option>
              <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .05s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">
          Conexión con el ecosistema GO Chile (opcional)
        </h2>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-sky-500 mb-1">Organización relacionada</label>
            <select v-model="form.related_organization_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Sin organización asociada</option>
              <option v-for="o in organizations" :key="o.id" :value="o.id">{{ o.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-sky-500 mb-1">Territorio / destino relacionado</label>
            <select v-model="form.related_destination_id"
                    class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
              <option :value="null">Sin territorio asociado</option>
              <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
      </section>

      <section class="rounded-2xl bg-white border border-sky-100 p-4 animate-fade-in-up" style="animation-delay: .1s">
        <h2 class="text-xs font-bold text-sky-900 uppercase tracking-wide mb-3">Publicación</h2>
        <div>
          <label class="block text-xs text-sky-500 mb-1">Estado</label>
          <select v-model="form.status"
                  class="w-full rounded-xl border border-sky-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-300">
            <option value="draft">Borrador</option>
            <option value="published" :disabled="!canPublish">Publicado{{ canPublish ? '' : ' (requiere admin)' }}</option>
            <option value="unpublished" :disabled="!canPublish">Despublicado{{ canPublish ? '' : ' (requiere admin)' }}</option>
          </select>
          <p v-if="!canPublish" class="text-xs text-sky-400 mt-1">
            Tu rol puede crear y editar, pero solo un administrador puede publicar o despublicar.
          </p>
        </div>
      </section>

      <div class="flex gap-2 pt-1 pb-8">
        <Link href="/admin/bitacora"
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
