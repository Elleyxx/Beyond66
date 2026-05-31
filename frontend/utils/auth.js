export const AUTH_TOKEN_KEY = 'token'
export const AUTH_USER_KEY = 'user'
export const AUTH_LAST_ACTIVITY_KEY = 'auth_last_activity_at'

// Auto logout after 30 minutes of inactivity.
const INACTIVITY_TIMEOUT_MS = 30 * 60 * 1000
const ACTIVITY_UPDATE_THROTTLE_MS = 15 * 1000

let inactivityCheckTimer = null
let activityListenerAttached = false
let lastActivityWriteAt = 0

export function isAuthenticated() {
  return !!localStorage.getItem(AUTH_TOKEN_KEY)
}

export function setAuthSession(token, user) {
  localStorage.setItem(AUTH_TOKEN_KEY, token)
  localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user))
  localStorage.setItem(AUTH_LAST_ACTIVITY_KEY, String(Date.now()))
}

export function clearAuthSession() {
  localStorage.removeItem(AUTH_TOKEN_KEY)
  localStorage.removeItem(AUTH_USER_KEY)
  localStorage.removeItem(AUTH_LAST_ACTIVITY_KEY)
}

function touchSessionActivity(force = false) {
  if (!isAuthenticated()) return

  const now = Date.now()
  if (!force && now - lastActivityWriteAt < ACTIVITY_UPDATE_THROTTLE_MS) return

  localStorage.setItem(AUTH_LAST_ACTIVITY_KEY, String(now))
  lastActivityWriteAt = now
}

function isSessionExpired() {
  if (!isAuthenticated()) return false

  const raw = localStorage.getItem(AUTH_LAST_ACTIVITY_KEY)
  const lastActivityAt = raw ? Number(raw) : 0
  if (!Number.isFinite(lastActivityAt) || lastActivityAt <= 0) {
    // Missing/invalid metadata: treat as just active now.
    touchSessionActivity(true)
    return false
  }

  return Date.now() - lastActivityAt > INACTIVITY_TIMEOUT_MS
}

function redirectToLoginOnExpiry() {
  const path = window.location.pathname
  if (path === '/') return
  window.location.href = '/'
}

function handlePossibleSessionExpiry() {
  if (!isSessionExpired()) return
  clearAuthSession()
  redirectToLoginOnExpiry()
}

export function initializeInactivitySessionGuard() {
  if (typeof window === 'undefined') return

  if (!activityListenerAttached) {
    const activityEvents = ['click', 'keydown', 'mousemove', 'scroll', 'touchstart']
    const onActivity = () => touchSessionActivity(false)
    activityEvents.forEach((eventName) => {
      window.addEventListener(eventName, onActivity, { passive: true })
    })

    // Keep tabs in sync: if another tab clears token, this tab follows.
    window.addEventListener('storage', (event) => {
      if (event.key === AUTH_TOKEN_KEY && !event.newValue) {
        clearAuthSession()
        redirectToLoginOnExpiry()
      }
    })
    activityListenerAttached = true
  }

  touchSessionActivity(true)
  handlePossibleSessionExpiry()

  if (!inactivityCheckTimer) {
    inactivityCheckTimer = window.setInterval(handlePossibleSessionExpiry, 60 * 1000)
  }
}
