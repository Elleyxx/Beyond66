<template>
  <main class="country-page" :class="{ 'is-dark': isDark, 'is-light': !isDark }">
    <section class="country-hero" :style="heroStyle">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <div class="title-blob">
          <h1>{{ country }}</h1>
        </div>
      </div>
    </section>

    <section class="country-body">
      <v-container class="py-5">
        <SlidingSectionTabs :tabs="tabs" :active-key="activeTab" @select="scrollToSection" />

        <section :id="sectionIds.overview" class="section-card tab-section mb-6">
          <div class="section-title-row">
            <h2>OVERVIEW</h2>
            <div class="section-title-line"></div>
          </div>
          <div class="overview-grid">
            <div class="overview-image-wrap">
              <img :src="overviewImage" :alt="`${country} overview`" class="overview-image" />
              <div class="overview-image-overlay">
                <p>{{ description }}</p>
              </div>
            </div>
            <div class="overview-side">
              <div class="quickfacts-grid" v-if="quickFactsList.length">
                <article
                  v-for="fact in quickFactsList"
                  :key="fact.label"
                  class="quickfact-card"
                >
                  <div class="quickfact-header">
                    <h3>{{ fact.label }}</h3>
                  </div>
                  <div class="quickfact-value-row">
                    <p>{{ fact.value }}</p>
                    <i v-if="fact.icon" :class="['bi', fact.icon, 'quickfact-inline-icon']" aria-hidden="true"></i>
                  </div>
                </article>
              </div>
              <div v-if="bestTimeSeasonsList.length" class="besttime-wrap">
                <div class="section-title-row section-title-row-sm">
                  <h3>Best Time to Visit</h3>
                </div>
                <div class="besttime-grid">
                  <article v-for="season in bestTimeSeasonsList" :key="season.season" class="besttime-card">
                    <h4>{{ season.season }}</h4>
                    <small>{{ season.months }}</small>
                    <p>{{ season.description }}</p>
                  </article>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section :id="sectionIds.highlights" class="feature-section tab-section">
          <div class="section-title-row">
            <h2>HIGHLIGHTS</h2>
            <div class="section-title-line"></div>
          </div>
          <div class="highlight-cards">
            <article
              v-for="highlight in highlightsList"
              :key="highlight.title"
              class="highlight-card"
            >
              <img v-if="highlight.image" :src="highlight.image" :alt="highlight.title" />
              <div class="highlight-content">
                <h3>{{ highlight.title }}</h3>
                <small v-if="highlight.subtitle">{{ highlight.subtitle }}</small>
                <p>{{ highlight.text }}</p>
              </div>
            </article>
          </div>
        </section>

        <section :id="sectionIds.destination" class="section-card tab-section mb-6">
          <div class="section-title-row">
            <h2>DESTINATION</h2>
            <div class="section-title-line"></div>
          </div>
          <div class="destination-grid">
            <article
              v-for="(destination, index) in destinationsList"
              :key="destination.name"
              class="destination-card"
            >
              <button
                type="button"
                class="save-destination-btn"
                :class="{ saved: isDestinationSaved(destination.slug) }"
                :aria-label="isDestinationSaved(destination.slug) ? 'Unsave destination' : 'Save destination'"
                :title="isDestinationSaved(destination.slug) ? 'Unsave destination' : 'Save destination'"
                @click.stop="toggleDestinationSave(destination.slug)"
              >
                <i :class="['bi', isDestinationSaved(destination.slug) ? 'bi-bookmark-fill' : 'bi-bookmark']"></i>
              </button>
              <div v-if="destination.images.length" class="destination-slider">
                <div
                  class="destination-slider-track"
                  :style="{
                    transform: `translateX(-${destinationImageIndex(index, destination.images.length) * 100}%)`,
                    transition: isDestinationTransitionEnabled(index) ? 'transform 0.55s ease' : 'none',
                  }"
                  @transitionend="onDestinationSlideTransitionEnd(index, destination.images.length)"
                >
                  <img
                    v-for="(img, imgIndex) in destination.images"
                    :key="`${destination.name}-${imgIndex}`"
                    :src="img"
                    :alt="`${destination.name} ${imgIndex + 1}`"
                  />
                  <img
                    v-if="destination.images.length > 1"
                    :src="destination.images[0]"
                    :alt="`${destination.name} loop`"
                  />
                </div>
                <button
                  v-if="destination.images.length > 1"
                  class="slide-btn prev"
                  @click="prevDestinationImage(index, destination.images.length)"
                >
                  ‹
                </button>
                <button
                  v-if="destination.images.length > 1"
                  class="slide-btn next"
                  @click="nextDestinationImage(index, destination.images.length)"
                >
                  ›
                </button>
              </div>
              <h3>{{ destination.name }}</h3>
              <p>{{ destination.description }}</p>
            </article>
          </div>
        </section>

        <section :id="sectionIds.experiences" class="section-card tab-section mb-6">
          <div class="section-title-row">
            <h2>TOP EXPERIENCE</h2>
            <div class="section-title-line"></div>
          </div>
          <div v-if="experiencesList.length" class="experience-layout">
            <article class="experience-hero" :style="experienceHeroStyle">
              <div class="experience-overlay">
                <h3>{{ activeExperience.title }}</h3>
                <p>{{ activeExperience.description }}</p>
              </div>
            </article>
            <div class="experience-grid">
              <article
                v-for="experience in secondaryExperiences"
                :key="experience.title"
                class="experience-card"
              >
                <img v-if="experience.image" :src="experience.image" :alt="experience.title" />
                <div class="experience-copy">
                  <h4>{{ experience.title }}</h4>
                  <p>{{ experience.description }}</p>
                </div>
              </article>
            </div>
          </div>
        </section>

        <section :id="sectionIds.gallery" class="section-card tab-section mb-6">
          <div class="section-title-row">
            <h2>GALLERY</h2>
            <div class="section-title-line"></div>
          </div>
          <div
            ref="galleryGridRef"
            class="gallery-grid"
            @mouseenter="pauseGalleryAutoscroll"
            @mouseleave="resumeGalleryAutoscroll"
            @wheel.passive="onGalleryUserScroll"
            @touchstart="onGalleryUserScroll"
          >
            <img
              v-for="(image, index) in galleryRenderList"
              :key="`${country}-gallery-loop-${index}`"
              :src="image"
              :alt="`${country} gallery ${(index % galleryList.length) + 1}`"
              class="gallery-image"
              :class="`gallery-span-${gallerySpan(index)}`"
            />
          </div>
        </section>

        <section :id="sectionIds.travelTips" class="section-card tab-section">
          <div class="section-title-row">
            <h2>TRAVEL TIPS</h2>
            <div class="section-title-line"></div>
          </div>
          <div class="tips-grid">
            <article v-for="(tip, index) in travelTipsList" :key="`${tip.title}-${index}`" class="tip-card">
              <h3>{{ tip.title }}</h3>
              <p>{{ tip.text }}</p>
            </article>
          </div>
        </section>
      </v-container>
    </section>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import SlidingSectionTabs from '@/components/common/SlidingSectionTabs.vue'
import { isAuthenticated } from '@/utils/auth'
import { getSavedDestinations, toggleSavedDestination } from '@/services/savedItemService'

const theme = useTheme()
const router = useRouter()
const isDark = computed(() => theme.global.current.value.dark)
const props = defineProps({
  country: { type: String, required: true },
  title: { type: String, required: true },
  description: { type: String, required: true },
  heroImage: { type: String, required: true },
  heroCtaLabel: { type: String, default: '' },
  heroCtaTo: { type: String, default: '' },
  heroWave: { type: Boolean, default: false },
  highlights: { type: Array, required: true },
  destinations: { type: Array, default: () => [] },
  experiences: { type: Array, required: true },
  gallery: { type: Array, default: () => [] },
  travelTips: { type: Array, default: () => [] },
  quickFacts: { type: [Array, Object], default: () => ({}) },
  bestTime: { type: String, required: true },
  bestTimeSeasons: { type: Array, default: () => [] },
})

const heroStyle = computed(() => {
  return { backgroundImage: `url(${props.heroImage})` }
})

const tabs = [
  { key: 'overview', label: 'OVERVIEW' },
  { key: 'highlights', label: 'HIGHLIGHTS' },
  { key: 'destination', label: 'DESTINATION' },
  { key: 'experiences', label: 'EXPERIENCE' },
  { key: 'gallery', label: 'GALLERY' },
  { key: 'travelTips', label: 'TRAVEL TIPS' },
]

const activeTab = ref('overview')
const sectionIds = {
  overview: 'country-overview',
  highlights: 'country-highlights',
  destination: 'country-destination',
  experiences: 'country-experiences',
  gallery: 'country-gallery',
  travelTips: 'country-travel-tips',
}

const galleryList = computed(() => {
  if (Array.isArray(props.gallery) && props.gallery.length) return props.gallery
  return [props.heroImage]
})

const galleryRenderList = computed(() => {
  if (!galleryList.value.length) return []
  return [...galleryList.value, ...galleryList.value]
})

const overviewImage = computed(() => galleryList.value[0] || props.heroImage)

const quickFactsList = computed(() => {
  const base = []
  if (Array.isArray(props.quickFacts)) {
    base.push(...props.quickFacts)
  } else if (props.quickFacts && typeof props.quickFacts === 'object') {
    base.push(...Object.entries(props.quickFacts).map(([label, value]) => ({ label, value })))
  }
  if (props.bestTime && !props.bestTimeSeasons?.length) {
    base.push({ label: 'Best Time To Visit', value: props.bestTime })
  }
  return base
})

const bestTimeSeasonsList = computed(() => {
  if (!Array.isArray(props.bestTimeSeasons)) return []
  return props.bestTimeSeasons
})

const highlightsList = computed(() => {
  if (!Array.isArray(props.highlights)) return []
  return props.highlights.map((highlight) => ({
    title: highlight.title || 'Highlight',
    subtitle: highlight.subtitle || '',
    text: highlight.description || highlight.text || '',
    image: highlight.image || '',
  }))
})

const destinationsList = computed(() => {
  if (!Array.isArray(props.destinations) || !props.destinations.length) return []
  return props.destinations.map((destination) => {
    if (typeof destination === 'string') {
      return { name: destination, description: '', images: [], slug: buildDestinationSlug(destination) }
    }
    return {
      name: destination.name || 'Destination',
      description: destination.description || '',
      slug: destination.slug || buildDestinationSlug(destination.name || 'Destination'),
      images: Array.isArray(destination.images)
        ? destination.images
        : [destination.image].filter(Boolean),
    }
  })
})

const experiencesList = computed(() => {
  if (!Array.isArray(props.experiences)) return []
  return props.experiences.map((experience, index) => {
    if (typeof experience === 'string') {
      return {
        title: experience,
        description: '',
        image: galleryList.value[index % galleryList.value.length] || '',
      }
    }
    return {
      title: experience.title || 'Experience',
      description: experience.description || '',
      image: experience.image || galleryList.value[index % galleryList.value.length] || '',
    }
  })
})

const activeExperienceIndex = ref(0)
let experienceAutoplayId = 0

const activeExperience = computed(() => {
  if (!experiencesList.value.length) return { title: '', description: '', image: '' }
  const index = activeExperienceIndex.value % experiencesList.value.length
  return experiencesList.value[index]
})

const secondaryExperiences = computed(() => {
  if (experiencesList.value.length <= 1) return []
  const len = experiencesList.value.length
  const start = activeExperienceIndex.value % len
  const ordered = []
  for (let i = 1; i < len; i += 1) {
    ordered.push(experiencesList.value[(start + i) % len])
  }
  return ordered
})

function autoplayExperiences() {
  const len = experiencesList.value.length
  if (len <= 1) return
  activeExperienceIndex.value = (activeExperienceIndex.value + 1) % len
}

const experienceHeroStyle = computed(() => {
  const image = activeExperience.value?.image || galleryList.value[0] || props.heroImage
  return { backgroundImage: `url(${image})` }
})

const destinationSlideIndex = ref({})
const destinationTransitionEnabled = ref({})
const savedDestinationSlugs = ref(new Set())
const savingDestinationSlugs = ref(new Set())
let destinationAutoplayId = 0

function slugify(value) {
  return String(value || '')
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

function buildDestinationSlug(name) {
  return `${slugify(props.country)}-${slugify(name)}`
}

function isDestinationSaved(slug) {
  return savedDestinationSlugs.value.has(slug)
}

async function loadSavedDestinationSlugs() {
  if (!isAuthenticated()) return

  try {
    const slugs = await getSavedDestinations()
    savedDestinationSlugs.value = new Set(slugs)
  } catch {
    savedDestinationSlugs.value = new Set()
  }
}

async function toggleDestinationSave(slug) {
  if (!isAuthenticated()) {
    router.push({ path: '/login', query: { redirect: window.location.pathname } })
    return
  }

  if (!slug || savingDestinationSlugs.value.has(slug)) return

  const wasSaved = savedDestinationSlugs.value.has(slug)
  const optimisticSaved = new Set(savedDestinationSlugs.value)

  if (wasSaved) {
    optimisticSaved.delete(slug)
  } else {
    optimisticSaved.add(slug)
  }

  savedDestinationSlugs.value = optimisticSaved
  savingDestinationSlugs.value = new Set([...savingDestinationSlugs.value, slug])

  try {
    const result = await toggleSavedDestination(slug)
    const next = new Set(savedDestinationSlugs.value)

    if (result?.saved) {
      next.add(slug)
    } else {
      next.delete(slug)
    }

    savedDestinationSlugs.value = next
  } catch {
    const rollback = new Set(savedDestinationSlugs.value)

    if (wasSaved) {
      rollback.add(slug)
    } else {
      rollback.delete(slug)
    }

    savedDestinationSlugs.value = rollback
  } finally {
    const nextSaving = new Set(savingDestinationSlugs.value)
    nextSaving.delete(slug)
    savingDestinationSlugs.value = nextSaving
  }
}

function destinationImageIndex(cardIndex, total) {
  const index = destinationSlideIndex.value[cardIndex] || 0
  if (!total) return 0
  return Math.max(0, Math.min(total, index))
}

function isDestinationTransitionEnabled(cardIndex) {
  return destinationTransitionEnabled.value[cardIndex] !== false
}

function nextDestinationImage(cardIndex, total) {
  if (!total) return
  const current = destinationSlideIndex.value[cardIndex] || 0
  if (total <= 1) return
  destinationSlideIndex.value[cardIndex] = current + 1
}

function prevDestinationImage(cardIndex, total) {
  if (!total) return
  const current = destinationSlideIndex.value[cardIndex] || 0
  destinationSlideIndex.value[cardIndex] = (current - 1 + total) % total
}

function autoplayDestinationImages() {
  destinationsList.value.forEach((destination, cardIndex) => {
    const total = destination.images.length
    if (total > 1) {
      const current = destinationSlideIndex.value[cardIndex] || 0
      destinationSlideIndex.value[cardIndex] = current + 1
    }
  })
}

function onDestinationSlideTransitionEnd(cardIndex, total) {
  const current = destinationSlideIndex.value[cardIndex] || 0
  if (total > 1 && current >= total) {
    destinationTransitionEnabled.value[cardIndex] = false
    destinationSlideIndex.value[cardIndex] = 0
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        destinationTransitionEnabled.value[cardIndex] = true
      })
    })
  }
}

const travelTipsList = computed(() => {
  if (Array.isArray(props.travelTips) && props.travelTips.length) {
    return props.travelTips.map((tip, index) => {
      if (typeof tip === 'string') return { title: `Tip ${index + 1}`, text: tip }
      return { title: tip.title || `Tip ${index + 1}`, text: tip.text || '' }
    })
  }
  return [
    { title: 'Tip 1', text: 'Carry layered clothing for weather shifts.' },
    { title: 'Tip 2', text: 'Book popular activities early in peak season.' },
    { title: 'Tip 3', text: 'Check local transport schedules in advance.' },
  ]
})

let scrollRafId = 0
const galleryGridRef = ref(null)
let galleryAutoscrollId = 0
let galleryResumeTimeoutId = 0

function autoScrollGallery() {
  const el = galleryGridRef.value
  if (!el) return
  const loopWidth = el.scrollWidth / 2
  if (loopWidth <= el.clientWidth) return
  if (el.scrollLeft >= loopWidth) el.scrollLeft -= loopWidth
  el.scrollBy({ left: 1, behavior: 'auto' })
}

function pauseGalleryAutoscroll() {
  if (galleryAutoscrollId) {
    window.clearInterval(galleryAutoscrollId)
    galleryAutoscrollId = 0
  }
}

function resumeGalleryAutoscroll() {
  if (galleryAutoscrollId) return
  galleryAutoscrollId = window.setInterval(autoScrollGallery, 24)
}

function onGalleryUserScroll() {
  const el = galleryGridRef.value
  if (el) {
    const loopWidth = el.scrollWidth / 2
    if (loopWidth > 0 && el.scrollLeft >= loopWidth) {
      el.scrollLeft -= loopWidth
    }
  }
  pauseGalleryAutoscroll()
  if (galleryResumeTimeoutId) window.clearTimeout(galleryResumeTimeoutId)
  galleryResumeTimeoutId = window.setTimeout(() => {
    resumeGalleryAutoscroll()
  }, 1400)
}

function gallerySpan(index) {
  const pattern = [1, 2, 1, 1, 2, 1, 2, 1]
  return pattern[index % pattern.length]
}

function scrollToSection(tabKey) {
  activeTab.value = tabKey
  const el = document.getElementById(sectionIds[tabKey])
  if (!el) return
  el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function updateActiveTabOnScroll() {
  const viewportAnchor = 140
  let current = tabs[0].key
  let bestDistance = Number.POSITIVE_INFINITY

  for (const tab of tabs) {
    const el = document.getElementById(sectionIds[tab.key])
    if (!el) continue
    const rect = el.getBoundingClientRect()
    const distance = Math.abs(rect.top - viewportAnchor)
    if (distance < bestDistance) {
      bestDistance = distance
      current = tab.key
    }
  }
  activeTab.value = current
}

function onScroll() {
  if (scrollRafId) return
  scrollRafId = window.requestAnimationFrame(() => {
    updateActiveTabOnScroll()
    scrollRafId = 0
  })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  updateActiveTabOnScroll()
  loadSavedDestinationSlugs()
  destinationAutoplayId = window.setInterval(autoplayDestinationImages, 4800)
  experienceAutoplayId = window.setInterval(autoplayExperiences, 5200)
  resumeGalleryAutoscroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  if (scrollRafId) window.cancelAnimationFrame(scrollRafId)
  if (destinationAutoplayId) window.clearInterval(destinationAutoplayId)
  if (experienceAutoplayId) window.clearInterval(experienceAutoplayId)
  pauseGalleryAutoscroll()
  if (galleryResumeTimeoutId) window.clearTimeout(galleryResumeTimeoutId)
})

watch(experiencesList, (list) => {
  if (!list.length) {
    activeExperienceIndex.value = 0
    return
  }
  activeExperienceIndex.value = activeExperienceIndex.value % list.length
})
</script>

<style scoped>
.country-page {
  color: rgb(var(--v-theme-on-surface));
  background: rgb(var(--v-theme-background));
  min-height: 100vh;
}

.country-hero {
  min-height: 35vh;
  position: relative;
  background-size: cover;
  background-position: center;
  display: grid;
  place-items: center;
  overflow: visible;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.28),
    rgba(0, 0, 0, 0.82)
  );
}

.hero-content {
  position: relative;
  z-index: 3;
  text-align: center;
  width: min(920px, 94vw);
  padding: 22px;
}

.title-blob {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: clamp(22px, 3.5vw, 40px) clamp(38px, 6vw, 72px);
  background: rgba(var(--v-theme-surface), 0.28);
  border-radius: 20px;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  box-shadow: none;
}

.title-blob h1 {
  margin: 0;
  font-family: 'Times New Roman', Times, Georgia, serif;
  font-size: clamp(2.8rem, 8vw, 6.3rem);
  line-height: 1;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgb(var(--v-theme-text));
}

.country-page.is-dark .title-blob {
  background: rgba(var(--v-theme-surface), 0.2);
  box-shadow: none;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
}

.country-page.is-dark .title-blob::before,
.country-page.is-dark .title-blob::after {
  display: none;
}

.country-body {
  position: relative;
  background: rgb(var(--v-theme-background));
}

.tab-section {
  scroll-margin-top: 120px;
  margin-bottom: 80px;
}

.section-title-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
}

.section-title-row h2 {
  margin: 0;
  white-space: nowrap;
}

.section-title-line {
  flex: 1;
  height: 2px;
  background: rgba(var(--v-theme-text), 1);
}

/* Shared section card */
.section-card,
.feature-section {
  border-radius: 0;
  padding: 0;
  background: transparent;
  border: none;
  box-shadow: none;
}

/* Overview */
.overview-grid {
  display: grid;
  grid-template-columns: 4fr 6fr;
  gap: 20px;
  align-items: stretch;
  margin-top: 24px;
}

.overview-image-wrap {
  position: relative;
  overflow: hidden;
  border-radius: 28px;
  height: 100%;
}

.overview-side {
  display: grid;
  gap: 16px;
  align-content: start;
}

.overview-image {
  width: 100%;
  height: 100%;
  min-height: 360px;
  object-fit: cover;
}

.overview-image-overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 20px 22px;
  background: linear-gradient(to top, rgba(0, 0, 0, 1), rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0));
}

.overview-image-overlay p {
  margin: 0;
  font-size: 1.2rem;
  line-height: 1.65;
  color: white;
}

.quickfacts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  align-content: start;
  gap: 18px;
}

.quickfact-card {
  border-radius: 24px;
  padding: 22px;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
}

.quickfact-card h3 {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  opacity: 0.6;
}

.quickfact-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.quickfact-card p {
  margin: 8px 0 0;
  font-weight: 700;
}

.quickfact-value-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.quickfact-inline-icon {
  padding-left: 10px;
  font-size: 25px;
  opacity: 0.9;
}

.besttime-wrap {
  margin-top: 6px;
  padding-top: 18px;
}

.besttime-wrap h3 {
  margin: 0;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  opacity: 0.72;
}

.section-title-row-sm {
  margin-bottom: 12px;
}

.besttime-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

.besttime-card {
  border-radius: 20px;
  padding: 16px;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
}

.besttime-card h4 {
  margin: 0;
}

.besttime-card small {
  display: block;
  margin-top: 4px;
  opacity: 0.7;
}

.besttime-card p {
  margin: 10px 0 0;
  opacity: 0.78;
}

/* Highlights */
.highlight-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

.highlight-card {
  overflow: hidden;
  border-radius: 28px;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
  transition: transform 0.3s ease;
}

.highlight-card:hover {
  transform: translateY(-6px);
}

.highlight-card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.highlight-content {
  padding: 24px;
}

.highlight-content h3 {
  margin: 0 0 6px;
}

.highlight-content small {
  display: block;
  margin-bottom: 12px;
  opacity: 0.62;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.highlight-content p {
  line-height: 1.6;
  opacity: 0.78;
}

/* Destinations */
.destination-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: 28px;
}

.destination-card {
  position: relative;
  border-radius: 28px;
  overflow: hidden;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
}

.save-destination-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 4;
  width: 38px;
  height: 38px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.38);
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgb(var(--v-theme-on-surface));
  background: rgba(var(--v-theme-background), 0.42);
  backdrop-filter: blur(10px);
  cursor: pointer;
  transition:
    transform 0.2s ease,
    background 0.2s ease,
    border-color 0.2s ease;
}

.save-destination-btn:hover {
  transform: translateY(-2px);
  background: rgba(var(--v-theme-primary), 0.24);
  border-color: rgba(var(--v-theme-primary), 0.8);
}

.save-destination-btn.saved {
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}

.destination-slider {
  position: relative;
  height: 230px;
  overflow: hidden;
}

.destination-slider img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.destination-slider-track {
  display: flex;
  width: 100%;
  height: 100%;
}

.destination-slider-track img {
  flex: 0 0 100%;
}

.destination-card h3,
.destination-card p {
  padding: 0 22px;
}

.destination-card h3 {
  margin: 20px 0 8px;
}

.destination-card p {
  margin-bottom: 24px;
  line-height: 1.55;
  opacity: 0.75;
}

.slide-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(var(--v-theme-text), 0.16);
  background: rgba(var(--v-theme-on-surface), 0.78);
  color: rgb(var(--v-theme-text));
  cursor: pointer;
  font-size: 1.4rem;
  padding: 0;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.country-page.is-dark .slide-btn {
  background: rgba(var(--v-theme-surface), 0.9);
  color: rgba(var(--v-theme-on-surface), 0.96);
  border-color: rgba(var(--v-theme-on-surface), 0.24);
}

.slide-btn.prev {
  left: 12px;
}

.slide-btn.next {
  right: 12px;
}

/* Experiences */
.experience-layout {
  display: grid;
  grid-template-columns: 1.25fr 1fr;
  gap: 24px;
  margin-top: 28px;
}

.experience-hero {
  min-height: 520px;
  border-radius: 36px;
  overflow: hidden;
  background-size: cover;
  background-position: center;
  position: relative;
}

.experience-overlay {
  position: absolute;
  inset: 0;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  color: white;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.75), transparent 70%);
}

.experience-overlay h3 {
  font-size: clamp(2rem, 4vw, 4rem);
  line-height: 1;
  margin-bottom: 16px;
}

.experience-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 18px;
}

.experience-card {
  display: grid;
  grid-template-columns: 150px 1fr;
  gap: 18px;
  align-items: center;
  border-radius: 24px;
  padding: 8px;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
}

.experience-card img {
  width: 140px;
  height: 110px;
  object-fit: cover;
  border-radius: 18px;
}

.experience-card h4 {
  margin: 0 0 8px;
}

.experience-copy {
  align-self: center;
}

.experience-card p {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.5;
  opacity: 0.75;
}

/* Gallery */
.gallery-grid {
  display: grid;
  grid-auto-flow: column;
  grid-template-rows: repeat(2, 220px);
  grid-auto-columns: 170px;
  gap: 18px;
  margin-top: 28px;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 10px;
  scrollbar-width: thin;
  scrollbar-color: rgba(var(--v-theme-text), 0.35) transparent;
}

.gallery-grid::-webkit-scrollbar {
  height: 10px;
}

.gallery-grid::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-text), 0.08);
  border-radius: 999px;
}

.gallery-grid::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-text), 0.32);
  border-radius: 999px;
}

.gallery-image {
  width: 100%;
  height: 100%;
  border-radius: 15px;
  object-fit: cover;
}

.gallery-span-1 {
  grid-column: span 1;
}

.gallery-span-2 {
  grid-column: span 2;
}

/* Travel tips */
.tips-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  margin-top: 28px;
}

.tip-card {
  border-radius: 24px;
  padding: 24px;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
}

.tip-card h3 {
  margin: 0 0 10px;
}

.tip-card p {
  margin: 0;
  line-height: 1.6;
  opacity: 0.75;
}

/* Responsive */
@media (max-width: 960px) {
  .section-card,
  .feature-section {
    padding: 28px;
  }

  .overview-grid,
  .experience-layout {
    grid-template-columns: 1fr;
  }

  .quickfacts-grid,
  .highlight-cards,
  .destination-grid,
  .tips-grid {
    grid-template-columns: 1fr;
  }

  .besttime-grid {
    grid-template-columns: 1fr;
  }

  .experience-card {
    grid-template-columns: 110px 1fr;
  }

  .experience-card img {
    width: 110px;
    height: 90px;
  }
}
</style>


