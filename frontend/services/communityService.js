import { AUTH_TOKEN_KEY } from '../utils/auth'
import { API_BASE } from './apiBase'

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

export function updateCommunityPost(postId, payload) {
  return request(`/api/community/posts/${postId}`, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
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

export function togglePostLike(postId) {
  return request(`/api/community/posts/${postId}/likes`, { method: 'POST' })
}
