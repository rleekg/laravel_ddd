import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/login',
        component: () => import('@/pages/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/operations',
        component: () => import('@/pages/OperationsPage.vue'),
        meta: { requiresAuth: true },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

let isAuthenticated: boolean = document.querySelector('meta[name="auth"]')?.getAttribute('content') === '1'

export function setAuthenticated(value: boolean): void {
    isAuthenticated = value
}

router.beforeEach((to) => {
    if (to.meta.requiresAuth && !isAuthenticated) {
        return { path: '/login' }
    }
    if (to.meta.guest && isAuthenticated) {
        return { path: '/' }
    }
})

export default router
