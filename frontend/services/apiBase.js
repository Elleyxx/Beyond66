const LOCAL_API_BASE = 'http://127.0.0.1:8000'
const PRODUCTION_API_BASE = 'https://beyond66.freedev.app'

function trimTrailingSlash(url) {
  return url.replace(/\/$/, '')
}

function resolveApiBase(value) {
  if (!value) {
    return import.meta.env.PROD ? PRODUCTION_API_BASE : LOCAL_API_BASE
  }

  if (import.meta.env.PROD && /^https?:\/\/(127\.0\.0\.1|localhost)(:\d+)?/i.test(value)) {
    return PRODUCTION_API_BASE
  }

  return value
}

export const API_BASE = trimTrailingSlash(resolveApiBase(import.meta.env.VITE_API_BASE))

export const AI_API_BASE = trimTrailingSlash(resolveApiBase(import.meta.env.VITE_AI_API_BASE || API_BASE))

export function resolveAssetUrl(value) {
  if (!value || typeof value !== 'string') return ''

  if (/^(https?:|data:|blob:)/i.test(value)) return value

  if (value.startsWith('/uploads/')) {
    return `${API_BASE}${value}`
  }

  if (value.startsWith('uploads/')) {
    return `${API_BASE}/${value}`
  }

  return value
}
