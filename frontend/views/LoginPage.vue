<template>
  <section class="login-page">
    <video class="login-video" autoplay muted loop playsinline>
      <source src="@/assets/videos/aurora.mp4" type="video/mp4" />
    </video>
    <div class="login-overlay"></div>

    <div class="login-card">
      <router-link to="/" class="back-link" aria-label="Back to landing page">
        <i class="bi bi-arrow-left"></i>
        <span>Back</span>
      </router-link>
      <h1>Login</h1>
      <p class="subtitle">Welcome to Beyond 66°</p>

      <label for="username">Username</label>
      <v-text-field
        id="username"
        v-model="username"
        variant="outlined"
        density="comfortable"
        hide-details
        class="field"
      />

      <label for="password">Password</label>
      <v-text-field
        id="password"
        v-model="password"
        :type="showPassword ? 'text' : 'password'"
        variant="outlined"
        density="comfortable"
        hide-details
        class="field"
        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        @click:append-inner="showPassword = !showPassword"
      />

      <v-btn class="login-btn" block :loading="isLoading" :disabled="isLoading" @click="handleLogin">Login</v-btn>
      <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

      <label class="remember-wrap">
        <input v-model="rememberMe" type="checkbox" />
        <span>Remember me</span>
      </label>

      <p class="register-text">
        Dont have an account?
        <router-link to="/register">Register</router-link>
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { setAuthSession } from '../utils/auth'

const username = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const isLoading = ref(false)
const router = useRouter()
const route = useRoute()
const API_BASE = 'http://127.0.0.1:8000'

async function handleLogin() {
  errorMessage.value = ''

  if (!username.value.trim() || !password.value) {
    errorMessage.value = 'Please enter username and password.'
    return
  }

  isLoading.value = true
  try {
    const response = await fetch(`${API_BASE}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: username.value.trim(),
        password: password.value,
      }),
    })

    const result = await response.json()
    if (!response.ok || !result.success) {
      errorMessage.value = result.message || 'Login failed.'
      return
    }

    setAuthSession(result.data.token, result.data.user)
    if (rememberMe.value) {
      localStorage.setItem('remembered_username', username.value.trim())
    } else {
      localStorage.removeItem('remembered_username')
    }
    const redirectPath = typeof route.query.redirect === 'string' ? route.query.redirect : '/home'
    router.push(redirectPath)
  } catch {
    errorMessage.value = 'Cannot connect to backend. Please start API server.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-page {
  position: relative;
  min-height: 100vh;
  display: grid;
  place-items: center;
  overflow: hidden;
}

.login-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.login-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(rgba(0, 0, 0, 0.30), rgba(0, 0, 0, 0.30));
}

.login-card {
  position: relative;
  z-index: 2;
  width: min(92vw, 430px);
  padding: 32px 26px 22px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.34);
  background:
    linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.06)),
    rgba(8, 18, 32, 0.28);
  backdrop-filter: blur(18px) saturate(135%);
  -webkit-backdrop-filter: blur(18px) saturate(135%);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.24),
    0 26px 70px rgba(0, 0, 0, 0.38);
  color: rgba(255, 255, 255, 0.94);
  text-align: center;
  font-family: var(--font-heading);
}

.back-link {
  position: absolute;
  top: 18px;
  left: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: rgba(var(--v-theme-surface), 0.9);
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition:
    color 0.2s ease,
    transform 0.2s ease;
}

.back-link:hover {
  color: rgb(var(--v-theme-primary));
  transform: translateX(-3px);
}

h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
}

.subtitle {
  margin: 6px 0 20px;
  color: rgba(255, 255, 255, 0.86);
}

label {
  display: block;
  margin: 10px 0 8px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  text-align: left;
}

.field {
  margin-bottom: 6px;
  text-align: left;
}

.field :deep(.v-field) {
  background: rgba(255, 255, 255, 0.86);
  border-radius: 12px;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

.field :deep(input) {
  color: rgb(var(--v-theme-text));
}

.login-btn {
  margin-top: 14px;
  border-radius: 12px;
  height: 44px !important;
  text-transform: none;
  font-weight: 600;
  letter-spacing: 0.2px;
  background: rgb(var(--v-theme-secondary)) !important;
  color: rgb(var(--v-theme-text)) !important;
}

.remember-wrap {
  margin-top: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.86);
}

.remember-wrap input {
  width: 15px;
  height: 15px;
}

.register-text {
  margin: 16px 0 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
}

.register-text a {
  margin-left: 4px;
  color: white;
  font-weight: 600;
  text-decoration: none;
}

.register-text a:hover {
  text-decoration: underline;
}

.error-text {
  margin-top: 10px;
  color: rgb(var(--v-theme-error));
  font-size: 0.9rem;
  text-align: left;
}

@media (max-width: 600px) {
  .login-card {
    padding: 26px 18px 18px;
    border-radius: 16px;
  }

  h1 {
    font-size: 1.7rem;
  }
}
</style>
