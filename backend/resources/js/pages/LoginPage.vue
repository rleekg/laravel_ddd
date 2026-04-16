<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/api/auth'
import { setAuthenticated } from '@/router'

const router = useRouter()

const form = ref({ login: '', password: '' })
const error = ref<string | null>(null)
const loading = ref(false)

async function submit() {
    error.value = null
    loading.value = true
    try {
        await login(form.value.login, form.value.password)
        setAuthenticated(true)
        await router.push('/')
    } catch (e: unknown) {
        const err = e as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }
        if (err.response?.status === 401) {
            error.value = 'Неверный логин или пароль.'
        } else if (err.response?.status === 422) {
            const errors = err.response.data?.errors ?? {}
            error.value = Object.values(errors).flat().join(' ')
        } else {
            error.value = 'Произошла ошибка. Попробуйте снова.'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="col-md-4 col-sm-8 col-11">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="card-title text-center mb-4">
            Вход в систему
          </h4>

          <div
            v-if="error"
            class="alert alert-danger"
            role="alert"
          >
            {{ error }}
          </div>

          <form @submit.prevent="submit">
            <div class="mb-3">
              <label
                for="login"
                class="form-label"
              >Логин</label>
              <input
                id="login"
                v-model="form.login"
                type="text"
                class="form-control"
                required
                autocomplete="username"
              >
            </div>
            <div class="mb-4">
              <label
                for="password"
                class="form-label"
              >Пароль</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-control"
                required
                autocomplete="current-password"
              >
            </div>
            <button
              type="submit"
              class="btn btn-primary w-100"
              :disabled="loading"
            >
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-2"
              />
              Войти
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
