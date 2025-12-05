import { apiConfig } from '@shared/config/api'
import type { AxiosInstance, AxiosRequestConfig } from 'axios'
import axios from 'axios'

export const api: AxiosInstance = axios.create({
    baseURL: apiConfig.baseUrl,
    withCredentials: true,
})

// Если baseURL уже содержит "/api", убираем ведущий "/api/" у относительных путей,
// чтобы избежать двойного префикса "/api/api/..." в конечном URL.
api.interceptors.request.use((config) => {
    const base = api.defaults.baseURL ?? ''
    const baseEndsWithApi = /\/api\/?$/.test(base)
    if (baseEndsWithApi && typeof config.url === 'string') {
        config.url = config.url.replace(/^\/api\//, '/')
    }
    return config
})

export const apiMutator = async <T>(config: AxiosRequestConfig): Promise<T> => {
    const response = await api.request<T>(config)
    return response.data
}
