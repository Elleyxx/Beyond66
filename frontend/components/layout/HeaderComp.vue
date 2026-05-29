<script setup>
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue'
import logo from '@/assets/images/logo.png'
import logo_black from '@/assets/images/logo_black.png'
import { useTheme } from 'vuetify'
import { useI18n } from 'vue-i18n'
import { useRouter, useRoute } from 'vue-router'
import { clearAuthSession, isAuthenticated } from '../../utils/auth'

const theme = useTheme()
const { t, locale } = useI18n()
const router = useRouter()
const route = useRoute()

const isScrolled = ref(false)
const mobileDrawer = ref(false)
const isLoggedIn = ref(isAuthenticated())
const isDark = computed(() => theme.global.current.value.dark)

const currentLogo = computed(() => {
  return isDark.value ? logo : logo_black
})

const currentLanguageLabel = computed(() => {
  return locale.value === 'zh' ? 'English' : '中文'
})

// Language changer logic
function toggleLanguage() {
  const nextLanguage = locale.value === 'en' ? 'zh' : 'en'
  locale.value = nextLanguage
  localStorage.setItem('locale', nextLanguage)
}
// Light/dark theme toggle logic
function toggleTheme() {
  const nextTheme = isDark.value ? 'light' : 'dark'
  theme.global.name.value = nextTheme
  localStorage.setItem('theme', nextTheme)
}
// Logout logic
function logout() {
  clearAuthSession()
  isLoggedIn.value = false
  mobileDrawer.value = false
  router.push('/')
}

function goToLogin() {
  mobileDrawer.value = false
  router.push('/login')
}

function syncAuthState() {
  isLoggedIn.value = isAuthenticated()
}

function handleScroll() {
  isScrolled.value = window.scrollY > 60
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('storage', syncAuthState)
  syncAuthState()
  handleScroll()
})

watch(() => route.fullPath, syncAuthState)

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('storage', syncAuthState)
})
</script>

<template>
  <v-navigation-drawer
    v-model="mobileDrawer"
    temporary
    location="left"
    width="280"
    class="mobile-drawer"
  >
    <v-list nav>
      <v-list-item to="/home" @click="mobileDrawer = false" title="t('nav.home')" />
      <v-list-item to="/explore" @click="mobileDrawer = false" :title="t('nav.explore')" />
      <v-list-item
        class="sidebar-subitem"
        to="/country/norway"
        title="Norway"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/sweden"
        title="Sweden"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/finland"
        title="Finland"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/iceland"
        title="Iceland"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/denmark"
        title="Denmark"
        @click="mobileDrawer = false"
      />

      <v-list-item to="/trip-planner" @click="mobileDrawer = false" :title="t('nav.planner')" />
      <v-list-item to="/community" @click="mobileDrawer = false" :title="t('nav.community')" />
      <v-divider class="my-2" />
      <v-list-item to="/dashboard" @click="mobileDrawer = false" title="Dashboard" />
      <v-list-item @click="toggleLanguage" :title="currentLanguageLabel" />
      <v-list-item v-if="isLoggedIn" @click="logout" title="Logout" />
      <v-list-item v-else @click="goToLogin" title="Login" />
    </v-list>
  </v-navigation-drawer>

  <v-app-bar flat class="site-header" :class="{ scrolled: isScrolled }">
    <div class="header-inner">
      <div class="header-side header-left">
        <v-btn to="/home" variant="text" class="nav-link">
          {{ t('nav.home') }}
        </v-btn>

        <v-menu
          open-on-hover
          :open-on-click="false"
          location="bottom"
          offset="10"
          content-class="explore-menu"
        >
          <template #activator="{ props }">
            <v-btn v-bind="props" variant="text" class="nav-link" @click="router.push('/explore')">
              {{ t('nav.explore') }}
              <v-icon>mdi-menu-down</v-icon>
            </v-btn>
          </template>

          <v-list>
            <v-list-item to="/country/norway" title="Norway" />
            <v-list-item to="/country/sweden" title="Sweden" />
            <v-list-item to="/country/finland" title="Finland" />
            <v-list-item to="/country/iceland" title="Iceland" />
            <v-list-item to="/country/denmark" title="Denmark" />
          </v-list>
        </v-menu>

        <v-btn to="/trip-planner" variant="text" class="nav-link">
          {{ t('nav.planner') }}
        </v-btn>

        <v-btn to="/community" variant="text" class="nav-link">
          {{ t('nav.community') }}
        </v-btn>
      </div>

      <div class="header-center">
        <router-link to="/home" class="brand-link">
          <img :src="currentLogo" alt="Beyond 66 logo" class="brand-logo" />
          <span class="brand-title">Beyond 66°</span>
        </router-link>
      </div>

      <div class="header-side header-right">
        <v-text-field
          density="compact"
          variant="solo-filled"
          flat
          hide-details
          rounded
          prepend-inner-icon="mdi-magnify"
          :placeholder="t('nav.search')"
          class="search-box"
        />

        <v-btn icon variant="text" @click="toggleTheme" class="theme-toggle-btn">
          <i :class="isDark ? 'bi bi-sun' : 'bi bi-moon-stars'"></i>
        </v-btn>

        <v-menu location="bottom end" offset="14" content-class="profile-fab-menu">
          <template #activator="{ props }">
            <v-btn icon v-bind="props" class="profile-main-btn">
              <i class="bi bi-gear"></i>
            </v-btn>
          </template>

          <div class="profile-actions">
              <v-btn
                icon
                class="profile-action-btn"
                :to="isLoggedIn ? '/dashboard' : undefined"
                @click="!isLoggedIn && goToLogin()"
              >
                <i class="bi bi-person"></i>
              </v-btn>

              <v-btn icon class="profile-action-btn" @click="toggleLanguage">
                <i class="bi bi-translate"></i>
              </v-btn>

              <v-btn v-if="isLoggedIn" icon class="profile-action-btn" @click="logout">
                <i class="bi bi-box-arrow-right"></i>
              </v-btn>
            </div>
        </v-menu>
      </div>
    </div>

    <div class="mobile-header">
      <div class="mobile-top-row">
        <v-btn icon variant="text" class="mobile-icon-btn" @click="mobileDrawer = true">
          <i class="bi bi-list"></i>
        </v-btn>

        <router-link to="/home" class="brand-link mobile-brand-link">
          <img :src="currentLogo" alt="Beyond 66 logo" class="brand-logo" />
          <span class="brand-title">Beyond 66°</span>
        </router-link>

        <div class="mobile-right-icons">
          <v-btn icon variant="text" @click="toggleTheme" class="theme-toggle-btn mobile-icon-btn">
            <i :class="isDark ? 'bi bi-sun' : 'bi bi-moon-stars'"></i>
          </v-btn>

          <v-menu location="bottom end" offset="14" content-class="profile-fab-menu">
            <template #activator="{ props }">
              <v-btn icon v-bind="props" class="profile-main-btn mobile-icon-btn">
                <i class="bi bi-gear"></i>
              </v-btn>
            </template>

            <div class="profile-actions">
              <v-btn
                icon
                class="profile-action-btn"
                :to="isLoggedIn ? '/dashboard' : undefined"
                @click="!isLoggedIn && goToLogin()"
              >
                <i class="bi bi-person"></i>
              </v-btn>

              <v-btn icon class="profile-action-btn" @click="toggleLanguage">
                <i class="bi bi-translate"></i>
              </v-btn>

              <v-btn v-if="isLoggedIn" icon class="profile-action-btn" @click="logout">
                <i class="bi bi-box-arrow-right"></i>
              </v-btn>
            </div>
          </v-menu>
        </div>
      </div>
      <div>
        <v-text-field
          density="compact"
          variant="solo-filled"
          flat
          hide-details
          rounded
          prepend-inner-icon="mdi-magnify"
          :placeholder="t('nav.search')"
          class="search-box mobile-search-box"
        />
      </div>
    </div>
  </v-app-bar>
</template>

<style scoped>
.site-header {
  background: transparent !important;
  box-shadow: none !important;
  border: none !important;
  transition:
    background 0.35s ease,
    box-shadow 0.35s ease,
    backdrop-filter 0.35s ease;
}

.site-header::after {
  content: '';
  position: absolute;
  left: 3%;
  right: 3%;
  bottom: 0;
  height: 1.5px;
  background: rgba(var(--v-theme-headerText), 0.5);
  pointer-events: none;
}

.site-header :deep(.v-toolbar),
.site-header :deep(.v-toolbar__content),
.site-header :deep(.v-btn),
.site-header :deep(.v-list-item) {
  border: none !important;
  outline: none !important;
}

.site-header.scrolled {
  background: transparent !important;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 20px rgba(var(--v-theme-background), 0.28);
}

.site-header :deep(.v-toolbar__content) {
  min-height: 48px !important;
  padding-top: 2px;
  padding-bottom: 2px;
}

.site-header.scrolled :deep(.v-toolbar__content) {
  min-height: 48px !important;
  padding-top: 2px;
  padding-bottom: 2px;
}

.mobile-header {
  display: none;
}

.header-inner {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  width: 100%;
  gap: 16px;
}

.header-side {
  display: flex;
  align-items: center;
  min-width: 0;
  padding-left: 80px;
  padding-right: 80px;
}

.header-left {
  justify-content: flex-start;
  flex-wrap: nowrap;
  gap: 50px;
}

.header-right {
  justify-content: flex-end;
  gap: 20px;
}

.header-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.brand-logo {
  width: 48px;
  object-fit: contain;
  display: block;
  padding-right: 5px;
}

.brand-title {
  font-size: 1.2rem;
  font-weight: 500;
  color: rgb(var(--v-theme-headerText));
  margin: 0;
  line-height: 1;
  font-family: 'Playfair Display', serif;
  transition: color 0.35s ease;
}

.site-header.scrolled .brand-title {
  color: rgb(var(--v-theme-headerTextScrolled));
}

.search-box {
  flex: 0 0 180px;
  max-width: 180px;
  margin-bottom: 8px;
}

.search-box :deep(.v-field) {
  border: 1px solid rgb(var(--v-theme-searchBorder)) !important;
  border-radius: 50px;
  background: rgba(var(--v-theme-on-surface), 0.2);
  backdrop-filter: blur(8px);
  height: 40px !important;
}

.search-box :deep(.v-field__overlay) {
  opacity: 0;
}

.nav-link {
  color: rgb(var(--v-theme-headerText)) !important;
  text-decoration: none !important;
  transition: color 0.35s ease;
  margin-top: 0;
  min-height: 40px;
  display: inline-flex;
  align-items: center;
}

.nav-link :deep(.v-icon) {
  color: rgb(var(--v-theme-headerText)) !important;
  transition: color 0.35s ease;
}

.site-header.scrolled .nav-link {
  color: rgb(var(--v-theme-headerTextScrolled)) !important;
}

.site-header.scrolled .nav-link :deep(.v-icon) {
  color: rgb(var(--v-theme-headerTextScrolled)) !important;
}

.nav-link :deep(.v-btn__overlay) {
  opacity: 0 !important;
}

.nav-link :deep(.v-btn--active .v-btn__overlay) {
  opacity: 0 !important;
}

.profile-btn {
  padding: 4px !important;
  border: none !important;
  border-radius: 50%;
  min-width: auto !important;
  text-decoration: none;
  transition: border-color 0.35s ease;
}

.site-header.scrolled .profile-btn {
  border-color: transparent;
}

.profile-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-menu {
  min-width: 220px;
}

.profile-menu :deep(.v-list-item-title) {
  color: rgb(var(--v-theme-menuText));
}

.theme-toggle-btn {
  color: rgb(var(--v-theme-headerText)) !important;
  transition: color 0.35s ease;
}

.theme-toggle-btn i {
  font-size: 1.2rem;
  color: inherit;
}

.site-header.scrolled .theme-toggle-btn {
  color: rgb(var(--v-theme-headerTextScrolled)) !important;
}

.menu-bi {
  font-size: 1rem;
  width: 24px;
  text-align: center;
}

.profile-main-btn {
  background: transparent !important;
  box-shadow: none !important;
  color: rgb(var(--v-theme-headerText)) !important;
  transition: color 0.35s ease;
}

.site-header.scrolled .profile-main-btn {
  color: rgb(var(--v-theme-headerTextScrolled)) !important;
}

.profile-main-btn i {
  font-size: 1.2rem;
  color: inherit;
}

:deep(.profile-fab-menu) {
  background: transparent !important;
  box-shadow: none !important;
  border-radius: 0 !important;
  padding: 0 !important;
}

.profile-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding-top: 8px;
}

.profile-action-btn {
  width: 56px !important;
  height: 56px !important;
  border-radius: 50% !important;
  background: rgb(var(--v-theme-surface)) !important;
  color: rgb(var(--v-theme-text)) !important;
  box-shadow: 0 4px 9px rgba(var(--v-theme-background), 0.2) !important;
}

.site-header.scrolled .search-box :deep(.v-field) {
  border: 1px solid rgb(var(--v-theme-searchBorder)) !important;
  background: rgba(var(--v-theme-surface), 0.95);
}

.profile-action-btn :deep(.v-icon) {
  font-size: 25px;
}

:deep(.explore-menu) {
  min-width: 260px;
}

:deep(.explore-menu .v-list) {
  padding: 8px;
}

:deep(.explore-menu .v-list-item) {
  color: rgb(var(--v-theme-menuText)) !important;
  text-decoration: none;
}

:deep(.explore-menu .v-list-item-title) {
  color: rgb(var(--v-theme-menuText)) !important;
}

:deep(.v-list-item--active) {
  background: transparent !important;
}

:deep(.v-list-item--active .v-list-item__overlay) {
  opacity: 0 !important;
  background: transparent !important;
}

.mobile-drawer :deep(.v-list-item-title) {
  color: rgb(var(--v-theme-menuText));
}

.sidebar-subitem {
  padding-inline-start: 28px !important;
}

@media (max-width: 1250px) {
  .site-header {
    height: auto !important;
  }

  .site-header :deep(.v-toolbar__content) {
    height: auto !important;
    align-items: stretch;
  }

  .header-inner {
    display: none;
  }

  .mobile-header {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 6px 16px 4px;
    gap: 8px;
  }

  .site-header.scrolled .mobile-header {
    padding-bottom: 2px;
    gap: 6px;
  }

  .mobile-top-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    column-gap: 8px;
  }

  .mobile-brand-link {
    justify-self: center;
  }

  .mobile-right-icons {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .mobile-icon-btn {
    min-width: 36px !important;
    width: 36px !important;
    height: 36px !important;
  }

  .mobile-icon-btn i {
    font-size: 1.1rem;
  }

  .mobile-search-box {
    width: 100%;
    max-width: none;
  }

  .brand-title {
    font-size: 1rem;
  }

  .brand-logo {
    width: 38px;
  }
}
</style>



