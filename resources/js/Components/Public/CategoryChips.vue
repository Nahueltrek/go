<script setup>
defineProps({
  categories: { type: Array, default: () => [] },
})
</script>

<template>
  <div v-if="categories.length" class="flex gap-2 flex-wrap">
    <span
      v-for="cat in categories"
      :key="cat"
      class="text-xs rounded-full border border-sky-200 px-2.5 py-1 text-sky-600"
    >{{ cat }}</span>
  </div>
</template>
