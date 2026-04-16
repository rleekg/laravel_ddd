<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import NavBar from '@/components/NavBar.vue'
import SearchInput from '@/components/SearchInput.vue'
import SortControl from '@/components/SortControl.vue'
import OperationsTable from '@/components/OperationsTable.vue'
import Pagination from '@/components/Pagination.vue'
import { getOperations } from '@/api/balance'
import type { Operation, PaginationMeta } from '@/types'

const operations = ref<Operation[]>([])
const loading = ref(false)
const search = ref('')
const sort = ref<'asc' | 'desc'>('desc')
const page = ref(1)
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 })

async function fetchOperations(p = page.value) {
    loading.value = true
    try {
        const { data } = await getOperations({
            sort: sort.value,
            search: search.value || null,
            page: p,
        })
        operations.value = data.data
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        }
        page.value = p
    } finally {
        loading.value = false
    }
}

// Debounce search
let debounceTimer: ReturnType<typeof setTimeout> | null = null
watch(search, () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        page.value = 1
        fetchOperations(1)
    }, 300)
})

watch(sort, () => fetchOperations(1))

onMounted(() => fetchOperations(1))
</script>

<template>
  <NavBar />
  <main class="container mt-4">
    <div class="row g-3 mb-3 align-items-center">
      <div class="col-md-6">
        <SearchInput v-model="search" />
      </div>
      <div class="col-md-4">
        <SortControl v-model="sort" />
      </div>
    </div>

    <OperationsTable
      :operations="operations"
      :loading="loading"
    />

    <div class="d-flex justify-content-between align-items-center mt-3">
      <Pagination
        :meta="meta"
        @page="fetchOperations"
      />
      <span class="text-muted small">Всего: {{ meta.total }} операций</span>
    </div>
  </main>
</template>
