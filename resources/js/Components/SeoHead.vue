<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  image: { type: String, default: '' },
  type: { type: String, default: 'website' },
  // Uno o varios bloques de datos estructurados (schema.org), p. ej.
  // { "@context": "https://schema.org", "@type": "Article", ... }. Se
  // serializan tal cual a <script type="application/ld+json">.
  jsonLd: { type: [Object, Array], default: null },
})

const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') return ''
  return window.location.origin + window.location.pathname
})

const resolvedImage = computed(() => props.image || '/images/logo.png')

const metaDescription = computed(() => {
  if (!props.description) return ''
  const clean = props.description.trim()
  return clean.length > 160 ? clean.slice(0, 157).trimEnd() + '…' : clean
})

const jsonLdBlocks = computed(() => {
  if (!props.jsonLd) return []
  return Array.isArray(props.jsonLd) ? props.jsonLd : [props.jsonLd]
})
</script>

<template>
  <Head>
    <title>{{ title }}</title>
    <meta v-if="metaDescription" name="description" :content="metaDescription" />
    <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />

    <meta property="og:type" :content="type" />
    <meta property="og:title" :content="title" />
    <meta v-if="metaDescription" property="og:description" :content="metaDescription" />
    <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
    <meta property="og:image" :content="resolvedImage" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="title" />
    <meta v-if="metaDescription" name="twitter:description" :content="metaDescription" />
    <meta name="twitter:image" :content="resolvedImage" />

    <script
      v-for="(block, i) in jsonLdBlocks"
      :key="i"
      type="application/ld+json"
    >{{ JSON.stringify(block) }}</script>
  </Head>
</template>
