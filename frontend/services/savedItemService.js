import axios from 'axios'
import { AUTH_TOKEN_KEY } from '../utils/auth'

const API_BASE = 'http://127.0.0.1:8000'

function authHeaders() {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export async function getSavedDestinations() {
  const response = await axios.get(`${API_BASE}/api/saved-items`, {
    headers: authHeaders(),
  })

  return response.data?.data?.destinations || []
}

export async function toggleSavedDestination(itemSlug) {
  const response = await axios.post(
    `${API_BASE}/api/saved-items/destinations/toggle`,
    { item_slug: itemSlug },
    { headers: authHeaders() },
  )

  return response.data?.data
}
