<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import EntityHero from '@/Components/Public/EntityHero.vue'
import CategoryChips from '@/Components/Public/CategoryChips.vue'
import ContactButtons from '@/Components/Public/ContactButtons.vue'
import LocationMap from '@/Components/Public/LocationMap.vue'
import SeoHead from '@/Components/SeoHead.vue'

const props = defineProps({
  experience: Object,
  reviews: Array,
})

const seoDescription = computed(() =>
  props.experience.description
    || `Experiencia outdoor${props.experience.organization ? ` ofrecida por ${props.experience.organization.name}` : ''} en GO Chile.`
)

const categories = computed(() => {
  const difficulty = props.experience.difficulty
    ? props.experience.difficulty.charAt(0).toUpperCase() + props.experience.difficulty.slice(1)
    : null
  return [props.experience.activity_type, difficulty].filter(Boolean)
})

const hasStats = computed(() =>
  props.experience.duration_minutes || props.experience.capacity || props.experience.price
)

// La experiencia puede no tener ubicación propia cargada — en ese caso se
// usa la del operador que la ofrece, si la tiene.
const mapPoint = computed(() => props.experience.location ?? props.experience.organization?.location ?? {})
</script>

<template>
  <SeoHead
    :title="`${experience.name} — GO Chile`"
    :description="seoDescription"
    :image="experience.cover_image"
  />
  <div class="min-h-screen bg-white pb-12">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-sky-100 px-4 py-2.5 flex items-center gap-2">
      <Link href="/" class="shrink-0">
        <img src="/images/logo.png" alt="GO Chile" class="h-7 w-7 rounded-full" />
      </Link>
      <span class="text-xs font-bold text-sky-950">GO Chile</span>
    </header>

    <EntityHero
      :title="experience.name"
      :subtitle="experience.activity_type"
      :cover-image="experience.cover_image"
      :logo-url="experience.organization?.logo_url"
    />

    <!-- Datos destacados -->
    <section class="px-4 pt-4 animate-fade-in-up" style="animation-delay: .05s">
      <CategoryChips :categories="categories" />

      <div v-if="hasStats" class="flex gap-6 flex-wrap mt-4">
        <div v-if="experience.duration_minutes">
          <p class="text-xs text-sky-400">Duración</p>
          <p class="text-lg font-semibold text-sky-900">{{ experience.duration_minutes }} min</p>
        </div>
        <div v-if="experience.capacity">
          <p class="text-xs text-sky-400">Capacidad</p>
          <p class="text-lg font-semibold text-sky-900">{{ experience.capacity }} personas</p>
        </div>
        <div v-if="experience.price">
          <p class="text-xs text-sky-400">Precio</p>
          <p class="text-lg font-semibold text-sky-900">${{ Number(experience.price).toLocaleString('es-CL') }} CLP</p>
        </div>
      </div>

      <p v-if="experience.description" class="text-sm text-sky-600 mt-5 leading-relaxed">
        {{ experience.description }}
      </p>

      <ContactButtons
        :whatsapp="experience.organization?.whatsapp"
        :instagram="experience.organization?.instagram"
        :website="experience.organization?.website"
      />

      <Link
        v-if="experience.organization"
        :href="`/operadores/${experience.organization.slug}`"
        class="mt-2 inline-block text-sm font-medium rounded-full border border-sky-200 text-sky-700 px-4 py-2"
      >Ver operador: {{ experience.organization.name }}</Link>
    </section>

    <LocationMap
      :lat="mapPoint.lat"
      :lng="mapPoint.lng"
      :name="experience.name"
      :category="experience.activity_type ?? ''"
    />

    <!-- Reseñas -->
    <section v-if="reviews?.length" class="px-4 pt-6 space-y-3 animate-fade-in-up" style="animation-delay: .15s">
      <h2 class="text-sm font-semibold text-sky-900">Reseñas</h2>
      <div v-for="(r, i) in reviews" :key="i" class="rounded-xl border border-sky-100 p-3">
        <p class="text-sm text-sky-900">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</p>
        <p v-if="r.comment" class="text-sm text-sky-600 mt-1">{{ r.comment }}</p>
        <p class="text-xs text-sky-400 mt-1">{{ r.user_name }}</p>
      </div>
    </section>

    <!-- CTA fijo -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-sky-100 px-4 py-3">
      <a
        v-if="experience.organization?.whatsapp"
        :href="`https://wa.me/${experience.organization.whatsapp}`"
        class="block text-center text-sm font-medium rounded-full bg-sky-900 text-white py-3"
      >Reservar por WhatsApp</a>
    </div>
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
