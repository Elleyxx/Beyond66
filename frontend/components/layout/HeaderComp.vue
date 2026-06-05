<script setup>
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue'
import logo from '@/assets/images/logo.png'
import logo_black from '@/assets/images/logo_black.png'
import { useTheme } from 'vuetify'
import { useI18n } from 'vue-i18n'
import { useRouter, useRoute } from 'vue-router'
import { clearAuthSession, isAuthenticated } from '../../utils/auth'
import { searchSite } from '@/services/siteSearch'

const theme = useTheme()
const { t, locale } = useI18n()
const router = useRouter()
const route = useRoute()

const isScrolled = ref(false)
const isInHeroScroll = ref(false)
const mobileDrawer = ref(false)
const isLoggedIn = ref(isAuthenticated())
const searchQuery = ref('')
const isSearchOpen = ref(false)
const isDark = computed(() => theme.global.current.value.dark)

const currentLogo = computed(() => {
  if (isInHeroScroll.value) return logo
  return isDark.value ? logo : logo_black
})

const currentLanguageLabel = computed(() => {
  return locale.value === 'zh' ? t('menu.languageEnglish') : t('menu.languageChinese')
})

const searchSuggestions = computed(() => searchSite(searchQuery.value, t, { limit: 5 }))
const hasSearchQuery = computed(() => searchQuery.value.trim().length > 0)

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

function openSearch() {
  isSearchOpen.value = true
}

function closeSearchSoon() {
  window.setTimeout(() => {
    isSearchOpen.value = false
  }, 120)
}

function submitSearch() {
  const query = searchQuery.value.trim()
  isSearchOpen.value = false
  mobileDrawer.value = false
  router.push({
    path: '/search',
    query: query ? { q: query } : {},
  })
}

function openSearchResult(result) {
  searchQuery.value = ''
  isSearchOpen.value = false
  mobileDrawer.value = false
  router.push(result.path)
}

function syncAuthState() {
  isLoggedIn.value = isAuthenticated()
}

function handleScroll() {
  const scrollY = window.scrollY
  isScrolled.value = scrollY > 60

  const heroEl = document.querySelector('.home-hero, .country-hero, .explore-hero-section')
  if (!heroEl) {
    isInHeroScroll.value = false
    return
  }

  const heroRect = heroEl.getBoundingClientRect()
  const headerHeight = 96
  const isOverHero = heroRect.top <= headerHeight && heroRect.bottom > headerHeight
  isInHeroScroll.value = scrollY > 0 && isOverHero
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('storage', syncAuthState)
  syncAuthState()
  handleScroll()
})

watch(
  () => route.fullPath,
  () => {
    syncAuthState()
    isSearchOpen.value = false
  },
)

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
      <v-list-item to="/home" @click="mobileDrawer = false" :title="t('nav.home')" />
      <v-list-item to="/explore" @click="mobileDrawer = false" :title="t('nav.explore')" />
      <v-list-item
        class="sidebar-subitem"
        to="/country/norway"
        :title="t('countryNames.norway')"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/sweden"
        :title="t('countryNames.sweden')"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/finland"
        :title="t('countryNames.finland')"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/iceland"
        :title="t('countryNames.iceland')"
        @click="mobileDrawer = false"
      />
      <v-list-item
        class="sidebar-subitem"
        to="/country/denmark"
        :title="t('countryNames.denmark')"
        @click="mobileDrawer = false"
      />

      <v-list-item to="/trip-planner" @click="mobileDrawer = false" :title="t('nav.planner')" />
      <v-list-item to="/community" @click="mobileDrawer = false" :title="t('nav.community')" />
      <v-divider class="my-2" />
      <v-list-item to="/dashboard" @click="mobileDrawer = false" :title="t('nav.dashboard')" />
      <v-list-item @click="toggleLanguage" :title="currentLanguageLabel" />
      <v-list-item v-if="isLoggedIn" @click="logout" :title="t('nav.logout')" />
      <v-list-item v-else @click="goToLogin" :title="t('nav.login')" />
    </v-list>
  </v-navigation-drawer>

  <v-app-bar flat class="site-header" :class="{ scrolled: isScrolled, 'hero-contrast': isInHeroScroll }">
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
            <v-list-item to="/country/norway" :title="t('countryNames.norway')" />
            <v-list-item to="/country/sweden" :title="t('countryNames.sweden')" />
            <v-list-item to="/country/finland" :title="t('countryNames.finland')" />
            <v-list-item to="/country/iceland" :title="t('countryNames.iceland')" />
            <v-list-item to="/country/denmark" :title="t('countryNames.denmark')" />
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
          <span class="brand-title">{{ t('brand.name') }}</span>
        </router-link>
      </div>

      <div class="header-side header-right">
        <div class="search-shell">
          <v-text-field
            v-model="searchQuery"
            density="compact"
            variant="solo-filled"
            flat
            hide-details
            rounded
            prepend-inner-icon="mdi-magnify"
            :placeholder="t('nav.search')"
            class="search-box"
            @focus="openSearch"
            @blur="closeSearchSoon"
            @keydown.enter.prevent="submitSearch"
            @keydown.esc="isSearchOpen = false"
          />

          <div v-if="isSearchOpen && searchSuggestions.length" class="search-popover">
            <button
              v-for="result in searchSuggestions"
              :key="`${result.path}-${result.title}`"
              type="button"
              class="search-result"
              @mousedown.prevent="openSearchResult(result)"
            >
              <span>{{ result.category }}</span>
              <strong>{{ result.title }}</strong>
              <small>{{ result.description }}</small>
            </button>
            <button
              v-if="hasSearchQuery"
              type="button"
              class="search-submit-row"
              @mousedown.prevent="submitSearch"
            >
              {{ t('search.viewAllResults') }}
            </button>
          </div>
        </div>

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
                class="profile-action-btn profile-action-expand-btn"
                :to="isLoggedIn ? '/dashboard' : undefined"
                @click="!isLoggedIn && goToLogin()"
              >
                <i class="bi bi-person"></i>
                <span class="profile-action-label">{{ isLoggedIn ? t('nav.dashboard') : t('nav.login') }}</span>
              </v-btn>

              <v-btn class="profile-action-btn profile-action-expand-btn" @click="toggleLanguage">
                <i class="bi bi-translate"></i>
                <span class="profile-action-label">{{ currentLanguageLabel }}</span>
              </v-btn>

              <v-btn v-if="isLoggedIn" class="profile-action-btn profile-action-expand-btn" @click="logout">
                <i class="bi bi-box-arrow-right"></i>
                <span class="profile-action-label">{{ t('nav.logout') }}</span>
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
          <span class="brand-title">{{ t('brand.name') }}</span>
        </router-link>

        <div class="mobile-right-icons">
          <v-btn icon variant="text" @click="toggleTheme" class="theme-toggle-btn mobile-icon-btn">
            <i :class="isDark ? 'bi bi-sun' : 'bi bi-moon-stars'"></i>
          </v-btn>
        </div>
      </div>
      <div class="search-shell mobile-search-shell">
        <v-text-field
          v-model="searchQuery"
          density="compact"
          variant="solo-filled"
          flat
          hide-details
          rounded
          prepend-inner-icon="mdi-magnify"
          :placeholder="t('nav.search')"
          class="search-box mobile-search-box"
          @focus="openSearch"
          @blur="closeSearchSoon"
          @keydown.enter.prevent="submitSearch"
          @keydown.esc="isSearchOpen = false"
        />

        <div v-if="isSearchOpen && searchSuggestions.length" class="search-popover">
          <button
            v-for="result in searchSuggestions"
            :key="`${result.path}-${result.title}-mobile`"
            type="button"
            class="search-result"
            @mousedown.prevent="openSearchResult(result)"
          >
            <span>{{ result.category }}</span>
            <strong>{{ result.title }}</strong>
            <small>{{ result.description }}</small>
          </button>
          <button
            v-if="hasSearchQuery"
            type="button"
            class="search-submit-row"
            @mousedown.prevent="submitSearch"
          >
            {{ t('search.viewAllResults') }}
          </button>
        </div>
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
  background: rgba(var(--v-theme-surface), 0.88) !important;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 20px rgba(var(--v-theme-background), 0.28);
}

.site-header.hero-contrast,
.site-header.scrolled.hero-contrast {
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.38),
    rgba(0, 0, 0, 0.12),
    transparent
  ) !important;
  box-shadow: none !important;
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
  color: rgb(var(--v-theme-text)) !important;
  text-shadow: none;
}

.search-shell {
  position: relative;
  flex: 0 0 180px;
  max-width: 180px;
  margin-bottom: 8px;
}

.search-box {
  width: 100%;
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

.search-popover {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  z-index: 100;
  width: min(320px, 80vw);
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 18px 42px rgba(var(--v-theme-background), 0.22);
}

.search-result,
.search-submit-row {
  width: 100%;
  border: 0;
  background: transparent;
  color: rgb(var(--v-theme-text));
  cursor: pointer;
  font: inherit;
  text-align: left;
}

.search-result {
  display: grid;
  gap: 4px;
  padding: 12px 14px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.search-result:hover,
.search-submit-row:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.search-result span {
  color: rgb(var(--v-theme-primary));
  font-size: 0.68rem;
  font-weight: 900;
  text-transform: uppercase;
}

.search-result strong {
  font-size: 0.92rem;
  line-height: 1.25;
}

.search-result small {
  color: rgba(var(--v-theme-text), 0.62);
  line-height: 1.35;
}

.search-submit-row {
  padding: 12px 14px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.86rem;
  font-weight: 900;
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
  color: inherit !important;
  transition: color 0.35s ease;
}

.site-header.scrolled .nav-link {
  color: rgb(var(--v-theme-text)) !important;
  text-shadow: none;
}

.site-header.scrolled .nav-link :deep(.v-icon) {
  color: inherit !important;
}

.mobile-icon-btn {
  color: rgb(var(--v-theme-headerText)) !important;
}

.site-header.scrolled .mobile-icon-btn {
  color: rgb(var(--v-theme-text)) !important;
  text-shadow: none;
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
  color: rgb(var(--v-theme-text)) !important;
  text-shadow: none;
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
  color: rgb(var(--v-theme-text)) !important;
  text-shadow: none;
}

.site-header.hero-contrast .nav-link,
.site-header.hero-contrast .brand-title,
.site-header.hero-contrast .theme-toggle-btn,
.site-header.hero-contrast .profile-main-btn,
.site-header.hero-contrast .mobile-icon-btn {
  color: white !important;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.65);
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
  align-items: flex-end;
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

.profile-action-expand-btn {
  align-self: flex-end;
  margin-left: auto;
  border-radius: 50% !important;
  position: relative;
  justify-content: center !important;
  gap: 0;
  padding: 0 !important;
  overflow: hidden;
  transition:
    width 0.28s ease,
    border-radius 0.28s ease,
    box-shadow 0.28s ease,
    padding 0.28s ease,
    gap 0.28s ease;
}

.profile-action-expand-btn:hover {
  width: 165px !important;
  border-radius: 30px !important;
  justify-content: center !important;
  padding: 0 18px !important;
  box-shadow: 0 8px 18px rgba(var(--v-theme-background), 0.28) !important;
}

.profile-action-expand-btn:hover i {
  position: absolute;
  left: 16px;
}

.profile-action-label {
  white-space: nowrap;
  opacity: 0;
  max-width: 0;
  transform: translateX(-8px);
  transition:
    opacity 0.2s ease,
    max-width 0.2s ease,
    transform 0.2s ease;
  font-size: 0.92rem;
  font-weight: 500;
}

.profile-action-expand-btn:hover .profile-action-label {
  position: absolute;
  left: 50%;
  opacity: 1;
  max-width: 130px;
  transform: translateX(-50%);
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

  .mobile-search-shell {
    width: 100%;
    max-width: none;
    margin-bottom: 0;
  }

  .mobile-search-shell .search-popover {
    left: 0;
    right: 0;
    width: 100%;
  }

  .brand-title {
    font-size: 1rem;
  }

  .brand-logo {
    width: 38px;
  }
}
</style>
