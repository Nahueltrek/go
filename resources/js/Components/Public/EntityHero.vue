<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  meta: { type: String, default: '' },
  coverImage: { type: String, default: '' },
  logoUrl: { type: String, default: '' },
})
</script>

<template>
  <section
    class="relative overflow-hidden animate-fade-in-up"
    :class="!coverImage && 'bg-gradient-to-br from-sky-800 to-sky-950'"
    :style="coverImage
      ? { backgroundImage: `url(${coverImage})`, backgroundSize: 'cover', backgroundPosition: 'center' }
      : {}"
  >
    <div v-if="coverImage" class="absolute inset-0 bg-gradient-to-t from-sky-950/90 via-sky-950/40 to-sky-950/10"></div>
    <div class="relative px-4 pt-10 pb-6">
      <div class="flex items-center gap-3">
        <div class="h-16 w-16 shrink-0 rounded-2xl bg-white/10 backdrop-blur overflow-hidden ring-1 ring-white/20">
          <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-cover" />
        </div>
        <div class="min-w-0">
          <p v-if="subtitle" class="text-xs uppercase tracking-wide text-sky-200">{{ subtitle }}</p>
          <h1 class="text-xl font-bold text-white truncate">{{ title }}</h1>
          <p v-if="meta" class="text-xs text-sky-200">{{ meta }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
