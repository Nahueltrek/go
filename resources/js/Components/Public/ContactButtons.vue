<script setup>
const props = defineProps({
  whatsapp: { type: String, default: '' },
  instagram: { type: String, default: '' },
  website: { type: String, default: '' },
  // slug de la organización, para el beacon de métricas (§21) — opcional:
  // sin él, los botones funcionan igual, simplemente no trackean el clic.
  organizationSlug: { type: String, default: '' },
})

function trackClick(type) {
  if (!props.organizationSlug) return
  const url = `/operadores/${props.organizationSlug}/click`
  const data = new FormData()
  data.append('type', type)
  if (navigator.sendBeacon) {
    navigator.sendBeacon(url, data)
  } else {
    fetch(url, { method: 'POST', body: data, keepalive: true })
  }
}
</script>

<template>
  <div class="flex gap-2 mt-4 flex-wrap">
    <a v-if="whatsapp" :href="`https://wa.me/${whatsapp}`" @click="trackClick('whatsapp_click')"
       class="text-sm font-medium rounded-full bg-sky-900 text-white px-4 py-2">WhatsApp</a>
    <a v-if="instagram" :href="instagram" @click="trackClick('instagram_click')"
       class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Instagram</a>
    <a v-if="website" :href="website" @click="trackClick('website_click')"
       class="text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2">Sitio web</a>
  </div>
</template>
