<template>
  <main ref="landingPageRef" class="landing-page">
    <section class="splash-page snap-section">
      <div class="splash-overlay"></div>

      <div class="top-nav">
        <div class="nav-actions">
          <router-link to="/login">{{ t('landing.login') }} <i class="bi bi-person"></i></router-link>
          <router-link to="/explore">{{ t('landing.explore') }} <i class="bi bi-snow2"></i></router-link>
        </div>
      </div>

      <div class="splash-content">
        <p class="eyebrow">{{ t('landing.eyebrow') }}</p>

        <h1>Beyond 66&deg;</h1>

        <div class="divider">
          <span></span>
          <v-icon size="34">mdi-image-filter-hdr</v-icon>
          <span></span>
        </div>

        <p class="subtitle">
          {{ t('landing.subtitleLineOne') }}<br />
          {{ t('landing.subtitleLineTwo') }}
        </p>

        <div class="country-grid">
          <div
            v-for="country in countries"
            :key="country.slug"
            class="country-card"
            :style="{ backgroundImage: `url(${country.image})` }"
            @click="scrollToDestination(country.slug)"
          >
            <div class="card-glass">
              <v-icon size="38" class="country-icon">{{ country.icon }}</v-icon>
              <h3>{{ country.name }}</h3>
              <p>{{ country.desc }}</p>
              <div class="mini-line"></div>
            </div>
          </div>
        </div>

        <v-btn
          class="enter-btn"
          variant="outlined"
          size="large"
          rounded="pill"
          to="/home"
        >
          {{ t('landing.enterJourney') }}
          <v-icon end>mdi-arrow-right</v-icon>
        </v-btn>

      <div class="scroll-hint">
        <v-icon size="28">mdi-mouse</v-icon>
        <span>{{ t('landing.scroll') }}</span>
        <v-icon size="20">mdi-arrow-down</v-icon>
      </div>
    </div>
  </section>

    <section
      v-for="(destination, index) in destinations"
      :key="destination.slug"
      :id="`destination-${destination.slug}`"
      class="destination-section snap-section"
      :style="{ backgroundImage: `url(${destination.image})` }"
    >
      <div class="destination-overlay"></div>
      <div class="destination-content">
        <p class="destination-country">{{ destination.country }}</p>
        <h2>{{ destination.place }}</h2>
        <p class="destination-desc">{{ destination.description }}</p>
      </div>
      <div class="destination-enter-slot">
        <v-btn
          class="enter-btn destination-enter-btn"
          :class="{ 'is-visible': showDelayedEnterJourney && activeSectionIndex === index + 1 }"
          variant="outlined"
          size="large"
          rounded="pill"
          to="/home"
        >
          {{ t('landing.explore') }}
          <v-icon end>mdi-arrow-right</v-icon>
        </v-btn>
      </div>
    </section>

    <BackToTopButton target=".landing-page" />
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BackToTopButton from '@/components/common/BackToTopButton.vue'

const { t } = useI18n()
const landingPageRef = ref(null)
const activeSectionIndex = ref(0)
const showDelayedEnterJourney = ref(false)
const lastScrollTop = ref(0)
let isAnimatingScroll = false
let dwellTimer = null

const getSnapSections = () => {
  const container = landingPageRef.value
  return container ? Array.from(container.querySelectorAll('.snap-section')) : []
}

const easeInOutCubic = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2)

const startDwellTimer = () => {
  if (dwellTimer) {
    clearTimeout(dwellTimer)
  }
  showDelayedEnterJourney.value = false
  dwellTimer = setTimeout(() => {
    showDelayedEnterJourney.value = true
  }, 5000)
}

const updateActiveSection = () => {
  const container = landingPageRef.value
  if (!container) {
    return
  }

  const currentTop = container.scrollTop
  if (Math.abs(currentTop - lastScrollTop.value) > 1) {
    startDwellTimer()
    lastScrollTop.value = currentTop
  }

  const sections = getSnapSections()
  if (!sections.length) {
    return
  }

  let nextIndex = 0
  let nearestDistance = Number.POSITIVE_INFINITY

  sections.forEach((section, index) => {
    const distance = Math.abs(section.offsetTop - currentTop)
    if (distance < nearestDistance) {
      nearestDistance = distance
      nextIndex = index
    }
  })

  if (nextIndex !== activeSectionIndex.value) {
    activeSectionIndex.value = nextIndex
    startDwellTimer()
  }
}

const animateContainerScroll = (container, targetTop, duration = 820) => {
  const startTop = container.scrollTop
  const distance = targetTop - startTop

  if (Math.abs(distance) < 2) {
    return
  }

  isAnimatingScroll = true
  const startTime = performance.now()

  const tick = (now) => {
    const elapsed = now - startTime
    const progress = Math.min(elapsed / duration, 1)
    const eased = easeInOutCubic(progress)
    container.scrollTop = startTop + distance * eased

    if (progress < 1) {
      requestAnimationFrame(tick)
      return
    }
    isAnimatingScroll = false
  }

  requestAnimationFrame(tick)
}

const handleSectionWheel = (event) => {
  const container = landingPageRef.value
  if (!container) {
    return
  }

  if (Math.abs(event.deltaY) < 10) {
    return
  }

  event.preventDefault()

  if (isAnimatingScroll) {
    return
  }

  const sections = getSnapSections()
  if (sections.length < 2) {
    return
  }

  const currentTop = container.scrollTop
  let currentIndex = 0
  let nearestDistance = Number.POSITIVE_INFINITY

  sections.forEach((section, index) => {
    const distance = Math.abs(section.offsetTop - currentTop)
    if (distance < nearestDistance) {
      nearestDistance = distance
      currentIndex = index
    }
  })

  const direction = event.deltaY > 0 ? 1 : -1
  const nextIndex = Math.max(0, Math.min(sections.length - 1, currentIndex + direction))

  if (nextIndex === currentIndex) {
    return
  }

  animateContainerScroll(container, sections[nextIndex].offsetTop)
}

onMounted(() => {
  startDwellTimer()
  updateActiveSection()
  landingPageRef.value?.addEventListener('wheel', handleSectionWheel, { passive: false })
  landingPageRef.value?.addEventListener('scroll', updateActiveSection)
})

onBeforeUnmount(() => {
  landingPageRef.value?.removeEventListener('wheel', handleSectionWheel)
  landingPageRef.value?.removeEventListener('scroll', updateActiveSection)
  if (dwellTimer) {
    clearTimeout(dwellTimer)
  }
})

const scrollToDestination = (countrySlug) => {
  const target = document.getElementById(`destination-${countrySlug}`)
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const countries = computed(() => [
  {
    slug: 'norway',
    name: t('landing.countries.norway.name'),
    desc: t('landing.countries.norway.desc'),
    icon: 'mdi-image-filter-hdr',
    image: new URL('@/assets/images/Norway/norway.jpg', import.meta.url).href
  },
  {
    slug: 'sweden',
    name: t('landing.countries.sweden.name'),
    desc: t('landing.countries.sweden.desc'),
    icon: 'mdi-pine-tree',
    image: new URL('@/assets/images/Sweden/sweden.jpg', import.meta.url).href
  },
  {
    slug: 'finland',
    name: t('landing.countries.finland.name'),
    desc: t('landing.countries.finland.desc'),
    icon: 'mdi-snowflake',
    image: new URL('@/assets/images/Finland/finland.jpg', import.meta.url).href
  },
  {
    slug: 'iceland',
    name: t('landing.countries.iceland.name'),
    desc: t('landing.countries.iceland.desc'),
    icon: 'mdi-volcano',
    image: new URL('@/assets/images/Iceland/iceland.jpg', import.meta.url).href
  },
  {
    slug: 'denmark',
    name: t('landing.countries.denmark.name'),
    desc: t('landing.countries.denmark.desc'),
    icon: 'mdi-lighthouse',
    image: new URL('@/assets/images/Denmark/denmark.jpg', import.meta.url).href
  }
])

const destinations = computed(() => [
  {
    slug: 'norway',
    country: t('landing.destinations.norway.country'),
    place: t('landing.destinations.norway.place'),
    description: t('landing.destinations.norway.description'),
    image: new URL('@/assets/images/Norway/Geirangerfjord.jpg', import.meta.url).href
  },
  {
    slug: 'sweden',
    country: t('landing.destinations.sweden.country'),
    place: t('landing.destinations.sweden.place'),
    description: t('landing.destinations.sweden.description'),
    image: new URL('@/assets/images/Sweden/AbiskoNationalPark.jpg', import.meta.url).href
  },
  {
    slug: 'finland',
    country: t('landing.destinations.finland.country'),
    place: t('landing.destinations.finland.place'),
    description: t('landing.destinations.finland.description'),
    image: new URL('@/assets/images/Finland/Rovaniemi.jpg', import.meta.url).href
  },
  {
    slug: 'iceland',
    country: t('landing.destinations.iceland.country'),
    place: t('landing.destinations.iceland.place'),
    description: t('landing.destinations.iceland.description'),
    image: new URL('@/assets/images/Iceland/JokulsarlonGlacierLagoon.jpg', import.meta.url).href
  },
  {
    slug: 'denmark',
    country: t('landing.destinations.denmark.country'),
    place: t('landing.destinations.denmark.place'),
    description: t('landing.destinations.denmark.description'),
    image: new URL('@/assets/images/Denmark/Nyhavn.jpg', import.meta.url).href
  }
])
</script>

<style scoped>
.landing-page {
  --v-theme-primary: 44, 246, 179;
  --v-theme-on-surface: 255, 255, 255;
  color: white;
  height: 100vh;
  overflow-y: auto;
}

.splash-page {
  min-height: 100vh;
  position: relative;
  color: white;
  background-image: url('@/assets/images/aurora_mountain.jpg');
  background-size: cover;
  background-position: center;
}

.splash-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at center, rgba(0, 0, 0, 0.12), transparent 35%),
    linear-gradient(to bottom, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.82));
  z-index: 1;
}

.top-nav {
  position: fixed;
  top: 34px;
  left: 42px;
  right: 42px;
  z-index: 50;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.nav-actions {
  display: flex;
  gap: 34px;
  align-items: center;
}

.nav-actions a {
  color: rgb(var(--v-theme-on-surface));
  text-decoration: none;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.nav-actions a:hover {
  color: rgb(var(--v-theme-primary));
}

.splash-content {
  position: relative;
  z-index: 2;
  min-height: 100vh;
  padding: 130px 6vw 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.eyebrow {
  color: rgb(var(--v-theme-primary));
  letter-spacing: 0.35em;
  font-size: 0.9rem;
  font-weight: 700;
  margin-bottom: 28px;
}

h1 {
  font-family: 'Times New Roman', Times, Georgia, serif;
  font-size: clamp(3.5rem, 8vw, 7.5rem);
  letter-spacing: 0.22em;
  font-weight: 800;
  line-height: 1;
  text-shadow: 0 8px 35px rgba(var(--v-theme-background), 0.45);
  margin: 0;
}

.divider {
  margin: 30px 0 20px;
  display: flex;
  align-items: center;
  gap: 22px;
  color: rgb(var(--v-theme-on-surface));
}

.divider span {
  width: 100px;
  height: 2px;
  background: rgba(var(--v-theme-primary), 0.65);
}

.subtitle {
  font-size: 1.25rem;
  color: rgba(var(--v-theme-on-surface), 0.9);
  line-height: 1.45;
  margin-bottom: 42px;
}

.country-grid {
  width: min(1200px, 100%);
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 22px;
  margin-bottom: 36px;
}

.country-card {
  height: 230px;
  border-radius: 22px;
  background-size: cover;
  background-position: center;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.45);
  box-shadow: 0 18px 45px rgba(var(--v-theme-background), 0.32);
  transition: 0.35s ease;
}

.country-card:hover {
  transform: translateY(-10px) scale(1.02);
  border-color: rgb(var(--v-theme-primary));
  box-shadow: 0 22px 60px rgba(var(--v-theme-primary), 0.25);
}

.card-glass {
  height: 100%;
  padding: 26px 18px;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.82));
  backdrop-filter: blur(2px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.country-icon {
  color: rgb(var(--v-theme-primary));
  margin-bottom: 18px;
}

.country-card h3 {
  letter-spacing: 0.22em;
  font-size: 1rem;
  margin-bottom: 14px;
}

.country-card p {
  font-size: 0.9rem;
  color: rgba(var(--v-theme-on-surface), 0.86);
  line-height: 1.4;
  margin: 0;
}

.mini-line {
  width: 46px;
  height: 2px;
  background: rgb(var(--v-theme-primary));
  margin-top: 20px;
}

.enter-btn {
  color: white !important;
  border-color: #2cf6b3 !important;
  padding-inline: 34px;
  letter-spacing: 0.18em;
  font-weight: 700;
  background: rgba(var(--v-theme-background), 0.25);
  text-decoration: none !important;
}

.enter-btn:hover {
  background: rgba(var(--v-theme-primary), 0.15);
  box-shadow: 0 0 25px rgba(var(--v-theme-primary), 0.35);
}

.scroll-hint {
  margin-top: 28px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
  color: rgba(var(--v-theme-on-surface), 0.86);
  opacity: 0.85;
  font-size: 0.7rem;
  letter-spacing: 0.35em;
}

.destination-section {
  min-height: 100vh;
  position: relative;
  background-size: cover;
  background-position: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.destination-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.82));
}

.destination-content {
  position: relative;
  z-index: 2;
  width: min(760px, 90vw);
  text-align: center;
  padding: 28px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.24);
  border-radius: 24px;
  background: rgba(var(--v-theme-background), 0.24);
  backdrop-filter: blur(6px);
}

.destination-country {
  margin: 0;
  color: rgb(var(--v-theme-primary));
  letter-spacing: 0.24em;
  font-weight: 700;
  font-size: 0.85rem;
}

.destination-content h2 {
  margin: 14px 0 16px;
  font-size: clamp(2rem, 4.4vw, 4rem);
  letter-spacing: 0.06em;
  font-family: 'Times New Roman', Times, Georgia, serif;
}

.destination-desc {
  margin: 0 auto;
  max-width: 60ch;
  color: rgba(var(--v-theme-on-surface), 0.88);
  line-height: 1.55;
}

.destination-enter-btn {
  position: relative;
  z-index: 2;
  margin-top: 2px;
  opacity: 0;
  pointer-events: none;
  transform: translateY(6px);
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.destination-enter-btn.is-visible {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0);
}

.destination-enter-slot {
  min-height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
}

.destination-enter-btn:hover {
  background: rgba(var(--v-theme-primary), 0.15);
}

@media (max-width: 1100px) {
  .country-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 760px) {
  .top-nav {
    top: 24px;
    left: 22px;
    right: 22px;
  }

  .nav-actions {
    gap: 18px;
  }

  .splash-content {
    padding-top: 120px;
  }

  h1 {
    letter-spacing: 0.12em;
  }

  .country-grid {
    grid-template-columns: 1fr;
    max-width: 360px;
  }

  .country-card {
    height: 170px;
  }

  .divider span {
    width: 60px;
  }

  .destination-content {
    padding: 22px 18px;
  }
}
</style>
