<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import NavBar from '@/components/NavBar.vue'
import BalanceCard from '@/components/BalanceCard.vue'
import RecentOperations from '@/components/RecentOperations.vue'
import { getDashboard } from '@/api/balance'
import type { Operation } from '@/types'

const REFRESH_INTERVAL = 30_000

const balance = ref('0.00')
const recentOps = ref<Operation[]>([])
const loading = ref(true)
const networkError = ref(false)
const lastUpdated = ref('')
const countdown = ref(REFRESH_INTERVAL / 1000)

let intervalId: ReturnType<typeof setInterval> | null = null
let countdownId: ReturnType<typeof setInterval> | null = null

async function fetchDashboard() {
    networkError.value = false
    loading.value = true
    try {
        const { data } = await getDashboard()
        balance.value = data.balance
        recentOps.value = data.recent_operations
        lastUpdated.value = new Date().toLocaleTimeString('ru-RU')
        countdown.value = REFRESH_INTERVAL / 1000
    } catch {
        networkError.value = true
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchDashboard()

    intervalId = setInterval(fetchDashboard, REFRESH_INTERVAL)

    countdownId = setInterval(() => {
        countdown.value = Math.max(0, countdown.value - 1)
    }, 1000)
})

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId)
    if (countdownId) clearInterval(countdownId)
})
</script>

<template>
  <NavBar />
  <main class="container mt-4">
    <div
      v-if="networkError"
      class="alert alert-warning"
      role="alert"
    >
      Ошибка соединения. Данные могут быть устаревшими.
    </div>

    <BalanceCard
      :balance="balance"
      :loading="loading"
    />
    <RecentOperations
      :operations="recentOps"
      :loading="loading"
    />

    <p class="last-updated mt-2">
      <span v-if="lastUpdated">Обновлено: {{ lastUpdated }} · </span>
      Следующее через {{ countdown }} с
    </p>
  </main>
</template>
