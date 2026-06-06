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

export async function saveJourneyDiary(tripId, payload) {
  const response = await fetch(`${API_BASE}/api/planner/${tripId}/diary`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders(),
    },
    body: JSON.stringify(payload),
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to save diary')
  }

  return result.data
}

export async function updateJourneyVisibility(tripId, visibility) {
  const response = await fetch(`${API_BASE}/api/planner/${tripId}/visibility`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders(),
    },
    body: JSON.stringify({ visibility }),
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to update journey visibility')
  }

  return result.data
}

export async function uploadDiaryImages(files) {
  const formData = new FormData()
  files.forEach((file) => {
    formData.append('images[]', file)
  })

  const response = await fetch(`${API_BASE}/api/planner/diary-images`, {
    method: 'POST',
    headers: {
      ...authHeaders(),
    },
    body: formData,
  })

  const result = await response.json()
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Failed to upload diary images')
  }

  return Array.isArray(result.data?.urls) ? result.data.urls : []
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
