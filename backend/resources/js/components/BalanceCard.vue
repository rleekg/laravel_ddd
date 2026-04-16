<script setup lang="ts">
defineProps<{
    balance: string
    loading: boolean
}>()
</script>

<template>
  <div class="card border-primary mb-4">
    <div class="card-body text-center py-4">
      <h5 class="card-title text-muted mb-3">
        Текущий баланс
      </h5>
      <div
        v-if="loading"
        class="d-flex justify-content-center"
      >
        <div
          class="spinner-border text-primary"
          role="status"
        >
          <span class="visually-hidden">Загрузка...</span>
        </div>
      </div>
      <div
        v-else
        class="balance-amount"
      >
        {{ balance }} ₽
      </div>
    </div>
  </div>
</template>
