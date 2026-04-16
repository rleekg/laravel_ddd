<script setup lang="ts">
import { useRouter } from 'vue-router'
import { logout } from '@/api/auth'
import { setAuthenticated } from '@/router'

const router = useRouter()

async function handleLogout() {
    try {
        await logout()
    } finally {
        setAuthenticated(false)
        await router.push('/login')
    }
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <span class="navbar-brand fw-bold">Балансы</span>
      <div class="navbar-nav me-auto">
        <RouterLink
          class="nav-link"
          to="/"
        >
          Главная
        </RouterLink>
        <RouterLink
          class="nav-link"
          to="/operations"
        >
          История
        </RouterLink>
      </div>
      <button
        class="btn btn-outline-light btn-sm"
        @click="handleLogout"
      >
        Выйти
      </button>
    </div>
  </nav>
</template>
