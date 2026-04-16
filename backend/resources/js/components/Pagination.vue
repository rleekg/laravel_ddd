<script setup lang="ts">
import { computed } from 'vue'
import type { PaginationMeta } from '@/types'

const props = defineProps<{ meta: PaginationMeta }>()
const emit = defineEmits<{ page: [n: number] }>()

const pages = computed(() => {
    const { current_page, last_page } = props.meta
    const delta = 2
    const start = Math.max(1, current_page - delta)
    const end = Math.min(last_page, current_page + delta)
    const result: number[] = []
    for (let i = start; i <= end; i++) result.push(i)
    return result
})
</script>

<template>
  <nav
    v-if="meta.last_page > 1"
    aria-label="Пагинация"
  >
    <ul class="pagination mb-0">
      <li
        class="page-item"
        :class="{ disabled: meta.current_page === 1 }"
      >
        <button
          class="page-link"
          @click="emit('page', meta.current_page - 1)"
        >
          &laquo;
        </button>
      </li>
      <li
        v-for="p in pages"
        :key="p"
        class="page-item"
        :class="{ active: p === meta.current_page }"
      >
        <button
          class="page-link"
          @click="emit('page', p)"
        >
          {{ p }}
        </button>
      </li>
      <li
        class="page-item"
        :class="{ disabled: meta.current_page === meta.last_page }"
      >
        <button
          class="page-link"
          @click="emit('page', meta.current_page + 1)"
        >
          &raquo;
        </button>
      </li>
    </ul>
  </nav>
</template>
