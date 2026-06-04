import { AUTH_TOKEN_KEY } from '../utils/auth'

const API_BASE = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000'

function authHeaders() {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function request(path, options = {}) {
  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders(),
      ...(options.headers || {}),
    },
  })

  const result = await response.json()
  if (!response.ok || result.success === false) {
    throw new Error(result.message || 'Community request failed')
  }

  return result.data ?? result
}

export function getCommunityPosts(params = {}) {
  const query = new URLSearchParams(params).toString()
  return request(`/api/community/posts${query ? `?${query}` : ''}`)
}

export function getCommunityPost(postId) {
  return request(`/api/community/posts/${postId}`)
}

export function saveCommunityPost(postId) {
  return request(`/api/community/posts/${postId}/save`, { method: 'POST' })
}

export function updatePostVisibility(postId, status) {
  return request(`/api/community/posts/${postId}/visibility`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  }).then((data) => data.status || status)
}

export function addPostComment(postId, comment) {
  return request(`/api/community/posts/${postId}/comments`, {
    method: 'POST',
    body: JSON.stringify({ comment }),
  })
}
