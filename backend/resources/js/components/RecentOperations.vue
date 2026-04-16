<script setup lang="ts">
import type { Operation } from '@/types'

defineProps<{
    operations: Operation[]
    loading: boolean
}>()

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('ru-RU')
}

function typeLabel(type: string): string {
    return type === 'credit' ? 'Зачисление' : 'Списание'
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">
        Последние операции
      </h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th>Дата</th>
              <th>Тип</th>
              <th>Сумма</th>
              <th>Описание</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="loading">
              <tr
                v-for="i in 5"
                :key="i"
              >
                <td
                  v-for="j in 5"
                  :key="j"
                >
                  <span class="skeleton" />
                </td>
              </tr>
            </template>
            <template v-else-if="operations.length === 0">
              <tr>
                <td
                  colspan="5"
                  class="text-center text-muted py-3"
                >
                  Нет операций
                </td>
              </tr>
            </template>
            <template v-else>
              <tr
                v-for="op in operations"
                :key="op.id"
              >
                <td>{{ formatDate(op.created_at) }}</td>
                <td>
                  <span
                    class="badge"
                    :class="op.type === 'credit' ? 'bg-success' : 'bg-danger'"
                  >
                    {{ typeLabel(op.type) }}
                  </span>
                </td>
                <td>{{ op.amount }} ₽</td>
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
    </div>
  </div>
</template>
