import { AUTH_TOKEN_KEY } from '../utils/auth'
import { AI_API_BASE, API_BASE } from './apiBase'

function authHeaders() {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export async function savePlannerDraft(payload) {
  const response = await fetch(`${API_BASE}/api/planner/save`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders(),
    },
    body: JSON.stringify(payload),
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to save planner draft')
  }

  return result.data
}

export async function loadLatestPlannerDraft() {
  const response = await fetch(`${API_BASE}/api/planner/latest`, {
    method: 'GET',
    headers: {
      ...authHeaders(),
    },
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to load planner draft')
  }

  return result.data
}

export async function loadPlannerDrafts() {
  const response = await fetch(`${API_BASE}/api/planner/list`, {
    method: 'GET',
    headers: {
      ...authHeaders(),
    },
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to load planner drafts')
  }

  return Array.isArray(result.data) ? result.data : []
}

export async function generateAiPlanner(payload) {
  const response = await fetch(`${AI_API_BASE}/api/planner/ai/generate`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders(),
    },
    body: JSON.stringify(payload),
  })

  const result = await response.json()
  if (!response.ok) {
    throw new Error(result.message || result.error || 'Failed to generate AI planner')
  }

  return result.data || result
}
