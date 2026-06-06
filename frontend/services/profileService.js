import { AUTH_TOKEN_KEY } from '../utils/auth'
import { API_BASE } from './apiBase'

function authHeaders() {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export async function loadProfileDashboard() {
  const response = await fetch(`${API_BASE}/api/dashboard/profile`, {
    method: 'GET',
    headers: {
      ...authHeaders(),
    },
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to load profile data')
  }

  return result.data
}
