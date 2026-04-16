import axios from './axios'
import type { DashboardData, PaginatedOperations } from '@/types'

export const getDashboard = () =>
    axios.get<DashboardData>('/api/dashboard')

export const getOperations = ({
    sort = 'desc',
    search = null,
    page = 1,
}: {
    sort?: string
    search?: string | null
    page?: number
} = {}) => {
    const params: Record<string, unknown> = { sort, page }
    if (search) params.search = search
    return axios.get<PaginatedOperations>('/api/operations', { params })
}
