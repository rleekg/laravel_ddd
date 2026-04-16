export interface Operation {
    id: number
    type: 'credit' | 'debit'
    amount: string
    description: string
    status: 'pending' | 'completed' | 'failed'
    created_at: string
}

export interface DashboardData {
    balance: string
    recent_operations: Operation[]
}

export interface PaginatedOperations {
    data: Operation[]
    current_page: number
    last_page: number
    total: number
}

export interface PaginationMeta {
    current_page: number
    last_page: number
    total: number
}
