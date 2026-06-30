import apiClient from '../api/apiClient'

const deviceName = 'Kitchen Store web'

export async function login(credentials) {
  const { data } = await apiClient.post('/auth/login', { ...credentials, device_name: deviceName })
  return data
}

export async function register(details) {
  const { data } = await apiClient.post('/auth/register', { ...details, device_name: deviceName })
  return data
}

export async function logout() {
  const { data } = await apiClient.post('/auth/logout')
  return data
}

export async function getCurrentUser() {
  const { data } = await apiClient.get('/me')
  return data.data
}

export async function forgotPassword(email) {
  const { data } = await apiClient.post('/auth/forgot-password', { email })
  return data
}

export async function resetPassword(details) {
  const { data } = await apiClient.post('/auth/reset-password', details)
  return data
}

export async function resendVerification() {
  const { data } = await apiClient.post('/auth/email/verification-notification')
  return data
}
