<script setup lang="ts">
import type { Operation } from '@/types'

defineProps<{
    operations: Operation[]
    loading: boolean
}>()

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('ru-RU')
}
</script>

<template>
  <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Дата</th>
          <th>Тип</th>
          <th>Сумма</th>
          <th>Описание</th>
          <th>Статус</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td
            colspan="6"
            class="text-center py-4"
          >
            <div
              class="spinner-border text-primary"
              role="status"
            >
              <span class="visually-hidden">Загрузка...</span>
            </div>
          </td>
        </tr>
        <tr v-else-if="operations.length === 0">
          <td
            colspan="6"
            class="text-center text-muted py-4"
          >
            Нет результатов
          </td>
        </tr>
        <template v-else>
          <tr
            v-for="op in operations"
            :key="op.id"
          >
            <td class="text-muted small">
              {{ op.id }}
            </td>
            <td>{{ formatDate(op.created_at) }}</td>
            <td>
              <span
                class="badge"
                :class="op.type === 'credit' ? 'bg-success' : 'bg-danger'"
              >
                {{ op.type === 'credit' ? 'Зачисление' : 'Списание' }}
              </span>
            </td>
            <td
              :class="op.type === 'credit' ? 'text-success' : 'text-danger'"
              class="fw-semibold"
            >
              {{ op.type === 'credit' ? '+' : '-' }}{{ op.amount }} ₽
            </td>
            <td>{{ op.description }}</td>
            <td>
              <span
                class="badge"
                :class="{
                  'bg-success': op.status === 'completed',
                  'bg-warning text-dark': op.status === 'pending',
                  'bg-danger': op.status === 'failed',
                }"
              >
                {{ op.status }}
              </span>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>
