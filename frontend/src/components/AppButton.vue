<script setup lang="ts">
defineProps<{
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  loading?: boolean
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
  fullWidth?: boolean
}>()
</script>

<template>
  <button
    :type="type ?? 'button'"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
      {
        'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-sm': (variant ?? 'primary') === 'primary',
        'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-primary-500': variant === 'secondary',
        'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500': variant === 'danger',
        'text-primary-600 hover:bg-primary-50 focus:ring-primary-500': variant === 'ghost',
        'px-3 py-1.5 text-sm': size === 'sm',
        'px-5 py-2.5 text-sm': (size ?? 'md') === 'md',
        'px-6 py-3 text-base': size === 'lg',
        'w-full': fullWidth,
      },
    ]"
  >
    <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
    <slot />
  </button>
</template>
