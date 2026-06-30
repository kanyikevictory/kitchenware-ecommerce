import axios from 'axios'
import { ApiError } from './apiError'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1',
  headers: {
    Accept: 'application/json',
  },
  timeout: 15_000,
})

let getAccessToken = () => null

export function setAccessTokenGetter(tokenGetter) {
  getAccessToken = typeof tokenGetter === 'function' ? tokenGetter : () => null
}

apiClient.interceptors.request.use((config) => {
  const token = getAccessToken()

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const apiError = ApiError.fromAxiosError(error)

    if (apiError.status === 401 && typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('auth:unauthorized'))
    }

    return Promise.reject(apiError)
  },
)

export default apiClient
