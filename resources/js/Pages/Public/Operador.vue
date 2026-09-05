<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import EntityHero from '@/Components/Public/EntityHero.vue'
import CategoryChips from '@/Components/Public/CategoryChips.vue'
import ContactButtons from '@/Components/Public/ContactButtons.vue'
import LocationMap from '@/Components/Public/LocationMap.vue'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  organization: Object,
  experiences: Object,
  projects: Object,
})

const locationLabel = computed(() => {
  const commune = props.organization.commune
  if (!commune) return null
  return commune.region ? `${commune.name}, ${commune.region}` : commune.name
})

const seoDescription = computed(() =>
  props.organization.description || `Conocé a ${props.organization.name}, parte de la red de operadores y colaboradores de GO Chile.`
)
</script>

<template>
  <SeoHead
    :title="`${organization.name} — GO Chile`"
    :description="seoDescription"
    :image="organization.cover_image || organization.logo_url"
  />
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <EntityHero
      :title="organization.name"
      :subtitle="organization.type"
      :meta="locationLabel"
      :cover-image="organization.cover_image"
      :logo-url="organization.logo_url"
    />

    <!-- Datos destacados -->
    <section class="px-4 pt-4 animate-fade-in-up" style="animation-delay: .05s">
      <CategoryChips :categories="organization.categories ?? []" />

      <p v-if="organization.description" class="text-sm text-sky-600 mt-4 leading-relaxed">
        {{ organization.description }}
      </p>

      <ContactButtons
        :whatsapp="organization.whatsapp"
        :instagram="organization.instagram"
        :website="organization.website"
      />
    </section>

    <LocationMap
      :lat="organization.location?.lat"
      :lng="organization.location?.lng"
      :name="organization.name"
      :category="organization.categories?.[0] ?? ''"
    />

    <!-- Experiencias -->
    <section v-if="experiences.data?.length" class="px-4 py-6 space-y-3 animate-fade-in-up" style="animation-delay: .15s">
      <h2 class="text-sm font-semibold text-sky-900">Experiencias</h2>
      <Link
        v-for="exp in experiences.data"
        :key="exp.id"
        :href="`/experiencias/${exp.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="exp.images?.[0]" :src="exp.images[0]" class="h-full w-full object-cover" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-sky-900 truncate">{{ exp.name }}</p>
          <p class="text-xs text-sky-500">{{ exp.activity_type }} · {{ exp.difficulty }}</p>
        </div>
      </Link>
    </section>

    <!-- Proyectos -->
    <section v-if="projects.data?.length" class="px-4 py-6 space-y-3 animate-fade-in-up" style="animation-delay: .2s">
      <h2 class="text-sm font-semibold text-sky-900">Proyectos</h2>
      <Link
        v-for="p in projects.data"
        :key="p.id"
        :href="`/proyectos/${p.slug}`"
        class="flex gap-3 rounded-2xl border border-sky-100 p-3 active:bg-sky-50"
      >
        <div class="h-16 w-16 shrink-0 rounded-xl bg-sky-100 overflow-hidden">
          <img v-if="p.images?.[0]" :src="p.images[0]" class="h-full w-full object-cover" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-sky-900 truncate">{{ p.name }}</p>
        </div>
      </Link>
    </section>
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
