<script setup lang="ts">
defineProps<{ modelValue: 'asc' | 'desc' }>()
const emit = defineEmits<{ 'update:modelValue': [value: 'asc' | 'desc'] }>()
</script>

<template>
  <div
    class="btn-group"
    role="group"
    aria-label="Сортировка"
  >
    <button
      type="button"
      class="btn btn-outline-secondary"
      :class="{ active: modelValue === 'desc' }"
      @click="emit('update:modelValue', 'desc')"
    >
      Сначала новые
    </button>
    <button
      type="button"
      class="btn btn-outline-secondary"
      :class="{ active: modelValue === 'asc' }"
      @click="emit('update:modelValue', 'asc')"
    >
      Сначала старые
    </button>
  </div>
</template>
