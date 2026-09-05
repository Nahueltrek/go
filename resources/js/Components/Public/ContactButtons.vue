<script setup>
defineProps({
  whatsapp: { type: String, default: '' },
  instagram: { type: String, default: '' },
  website: { type: String, default: '' },
})
</script>

<template>
  <div class="flex gap-2 mt-4 flex-wrap">
    <a v-if="whatsapp" :href="`https://wa.me/${whatsapp}`"
       class="text-sm font-medium rounded-full bg-sky-900 text-white px-4 py-2">WhatsApp</a>
    <a v-if="instagram" :href="instagram"
       class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Instagram</a>
    <a v-if="website" :href="website"
       class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Sitio web</a>
  </div>
</template>
