import axios, { updateCsrfToken } from './axios'

export const login = async (login: string, password: string) => {
    const response = await axios.post<{ redirect: string; csrf_token: string }>('/login', { login, password })
    updateCsrfToken(response.data.csrf_token)
    return response
}

export const logout = () =>
    axios.post('/logout')

export const getUser = () =>
    axios.get<{ id: number }>('/api/user')
