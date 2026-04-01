<script setup lang="ts">
defineProps({
  title: String,
  description: String
});
</script>

<template>
  <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
    <h2 class="text-lg font-bold">{{ title }}</h2>
    <p class="text-gray-600 mt-2">{{ description }}</p>
  </div>
</template>