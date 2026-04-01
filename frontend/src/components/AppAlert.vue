<script setup lang="ts">
defineProps<{
  type?: 'error' | 'success' | 'warning' | 'info'
  message: string
}>()
</script>

<template>
  <div
    v-if="message"
    :class="[
      'flex items-start gap-3 rounded-xl px-4 py-3 text-sm',
      {
        'bg-red-50 text-red-800 border border-red-200': (type ?? 'error') === 'error',
        'bg-green-50 text-green-800 border border-green-200': type === 'success',
        'bg-yellow-50 text-yellow-800 border border-yellow-200': type === 'warning',
        'bg-blue-50 text-blue-800 border border-blue-200': type === 'info',
      },
    ]"
  >
    <!-- Error icon -->
    <svg v-if="(type ?? 'error') === 'error'" class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
    </svg>
    <!-- Success icon -->
    <svg v-else-if="type === 'success'" class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    <!-- Warning icon -->
    <svg v-else-if="type === 'warning'" class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
    </svg>
    <span>{{ message }}</span>
  </div>
</template>
