export const AUTH_TOKEN_KEY = 'token'
export const AUTH_USER_KEY = 'user'

export function isAuthenticated() {
  return !!localStorage.getItem(AUTH_TOKEN_KEY)
}

export function setAuthSession(token, user) {
  localStorage.setItem(AUTH_TOKEN_KEY, token)
  localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user))
}

export function clearAuthSession() {
  localStorage.removeItem(AUTH_TOKEN_KEY)
  localStorage.removeItem(AUTH_USER_KEY)
}

