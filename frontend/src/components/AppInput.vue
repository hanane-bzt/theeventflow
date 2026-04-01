<script setup lang="ts">
defineProps<{
  modelValue?: string
  label?: string
  error?: string
  hint?: string
  required?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

defineOptions({ inheritAttrs: false })
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <input
      v-bind="$attrs"
      :value="modelValue"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      :class="[
        'w-full px-4 py-2.5 rounded-xl border bg-white text-gray-900 text-sm transition-colors duration-150',
        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
        'placeholder:text-gray-400',
        error ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400',
      ]"
    />
    <p v-if="error" class="text-xs text-red-600 flex items-center gap-1">
      <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-xs text-gray-500">{{ hint }}</p>
  </div>
</template>
