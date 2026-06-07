<template>
  <section class="register-page">
    <video class="register-video" autoplay muted loop playsinline>
      <source src="@/assets/videos/aurora2.mp4" type="video/mp4" />
    </video>
    <div class="register-overlay"></div>

    <div class="register-card">
      <router-link to="/" class="back-link" :aria-label="t('auth.backToLanding')">
        <i class="bi bi-arrow-left"></i>
        <span>{{ t('auth.back') }}</span>
      </router-link>
      <h1>{{ t('auth.register.title') }}</h1>
      <p class="subtitle">{{ t('auth.register.subtitle') }}</p>

      <div class="form-grid">
        <div class="field-block">
          <label for="name">{{ t('auth.register.name') }}</label>
          <v-text-field
            id="name"
            v-model="name"
            variant="outlined"
            density="comfortable"
            hide-details
            class="field"
          />
        </div>

        <div class="field-block">
          <label for="email">{{ t('auth.register.email') }}</label>
          <v-text-field
            id="email"
            v-model="email"
            type="email"
            variant="outlined"
            density="comfortable"
            hide-details
            class="field"
          />
        </div>

        <div class="field-block">
          <label for="contact">{{ t('auth.register.contact') }}</label>
          <v-text-field
            id="contact"
            v-model="contact"
            type="tel"
            variant="outlined"
            density="comfortable"
            hide-details
            class="field"
          />
        </div>

        <div class="field-block">
          <label for="username">{{ t('auth.register.username') }}</label>
          <v-text-field
            id="username"
            v-model="username"
            variant="outlined"
            density="comfortable"
            hide-details
            class="field"
          />
        </div>

        <div class="field-block">
          <label for="password">{{ t('auth.register.password') }}</label>
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
        </div>

        <div class="field-block">
          <label for="confirmPassword">{{ t('auth.register.confirmPassword') }}</label>
          <v-text-field
            id="confirmPassword"
            v-model="confirmPassword"
            :type="showConfirmPassword ? 'text' : 'password'"
            variant="outlined"
            density="comfortable"
            hide-details
            class="field"
            :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showConfirmPassword = !showConfirmPassword"
          />
        </div>
      </div>

      <v-btn class="register-btn" block :loading="isLoading" :disabled="isLoading" @click="handleRegister">{{ t('auth.register.submit') }}</v-btn>
      <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
      <p v-if="successMessage" class="success-text">{{ successMessage }}</p>

      <p class="login-text">
        {{ t('auth.register.hasAccount') }}
        <router-link to="/login">{{ t('auth.register.login') }}</router-link>
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { API_BASE } from '../services/apiBase'

const name = ref('')
const username = ref('')
const email = ref('')
const contact = ref('')
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const isLoading = ref(false)
const router = useRouter()
const { t } = useI18n()

async function handleRegister() {
  errorMessage.value = ''
  successMessage.value = ''

  if (!name.value.trim() || !email.value.trim() || !password.value) {
    errorMessage.value = t('auth.register.missing')
    return
  }

  if (!username.value.trim()) {
    errorMessage.value = t('auth.register.usernameRequired')
    return
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailPattern.test(email.value.trim())) {
    errorMessage.value = t('auth.register.invalidEmail')
    return
  }

  const usernamePattern = /^[A-Za-z0-9_]{3,30}$/
  if (!usernamePattern.test(username.value.trim())) {
    errorMessage.value = t('auth.register.invalidUsername')
    return
  }

  if (contact.value.trim()) {
    const contactPattern = /^[0-9+\-\s]{8,20}$/
    if (!contactPattern.test(contact.value.trim())) {
      errorMessage.value = t('auth.register.invalidContact')
      return
    }
  }

  if (password.value.length < 6) {
    errorMessage.value = t('auth.register.shortPassword')
    return
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = t('auth.register.passwordMismatch')
    return
  }

  isLoading.value = true
  try {
    const response = await fetch(`${API_BASE}/api/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value.trim(),
        email: email.value.trim(),
        username: username.value.trim(),
        contact: contact.value.trim(),
        password: password.value,
      }),
    })

    const result = await response.json()
    if (!response.ok || !result.success) {
      errorMessage.value = result.message || t('auth.register.failed')
      return
    }

    successMessage.value = t('auth.register.success')
    setTimeout(() => {
      router.push('/login')
    }, 800)
  } catch {
    errorMessage.value = t('auth.register.backend')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.register-page {
  position: relative;
  min-height: 100vh;
  display: grid;
  place-items: center;
  overflow: hidden;
  padding: 24px;
}

.register-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.register-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(rgba(0, 0, 0, 0.30), rgba(0, 0, 0, 0.30));
}

.register-card {
  position: relative;
  z-index: 2;
  width: min(94vw, 520px);
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

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px 12px;
}

.field-block {
  text-align: left;
}

.field-span-2 {
  grid-column: 1 / -1;
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
  color: rgb(var(--v-theme-on-surface));
}

.field :deep(.v-field) {
  background: rgba(255, 255, 255, 0.86);
  border-radius: 12px;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

.field :deep(input) {
  color: rgba(0, 0, 0, 0.87);
}

.field :deep(input::placeholder) {
  color: rgba(0, 0, 0, 0.38);
}

.field :deep(.v-field__outline) {
  --v-field-border-opacity: 0.32;
  color: rgba(0, 0, 0, 0.6);
}

.field :deep(.v-field__append-inner .v-icon) {
  color: rgba(0, 0, 0, 0.55);
  opacity: 1;
}

.field :deep(.v-field__append-inner .v-icon:hover) {
  color: rgba(0, 0, 0, 0.85);
}

.register-btn {
  margin-top: 14px;
  border-radius: 12px;
  height: 44px !important;
  text-transform: none;
  font-weight: 600;
  letter-spacing: 0.2px;
  background: rgb(var(--v-theme-secondary)) !important;
  color: rgb(var(--v-theme-text)) !important;
}

.login-text {
  margin: 16px 0 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
}

.login-text a {
  margin-left: 4px;
  color: white;
  font-weight: 600;
  text-decoration: none;
}

.login-text a:hover {
  text-decoration: underline;
}

.error-text {
  margin-top: 10px;
  color: rgb(var(--v-theme-error));
  font-size: 0.9rem;
  text-align: left;
}

.success-text {
  margin-top: 10px;
  color: rgb(var(--v-theme-success));
  font-size: 0.9rem;
  text-align: left;
}

@media (max-width: 900px) {
  .register-page {
    padding: 20px var(--page-gutter);
  }
}

@media (max-width: 600px) {
  .register-page {
    padding: 16px var(--page-gutter);
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .field-span-2 {
    grid-column: auto;
  }

  .register-card {
    padding: 26px 18px 18px;
    border-radius: 16px;
  }

  h1 {
    font-size: 1.7rem;
  }
}
</style>
