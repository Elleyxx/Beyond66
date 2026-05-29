<template>
  <section ref="heroSectionRef" class="explore-hero-section">
    <img class="hero-bg-image" :src="backgroundImage" alt="Nordic preview" />

    <div class="hero-dark-overlay"></div>
    <div class="hero-bottom-blend"></div>

    <div ref="mapPanelRef" class="hero-map-overlay" @click="onMapClick">
      <SvgMap
        :map="world"
        :location-attributes="getLocationAttributes"
        @location-click="goToCountry"
        @location-mouseover="onCountryEnter"
        @location-mousemove="onCountryMove"
        @location-mouseout="onCountryLeave"
      />

      <div class="country-pin-layer" aria-hidden="false">
        <button
          v-for="marker in countryMarkers"
          :key="marker.id"
          type="button"
          class="country-pin"
          :class="`country-pin-${marker.slug}`"
          :style="pinStyle(marker)"
          :aria-label="`Go to ${marker.name}`"
          @mouseenter="onMarkerEnter(marker)"
          @mouseleave="onCountryLeave"
          @click="goToCountryBySlug(marker.slug)"
        >
          <span aria-hidden="true">&#128205;</span>
        </button>
      </div>
    </div>

    <div
      v-if="popup.visible && popup.country"
      class="country-popup"
      :style="{ left: `${popup.x}px`, top: `${popup.y}px` }"
    >
      <div class="popup-text">
        <h4>{{ popup.country.name }}</h4>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'
import { SvgMap } from 'vue-svg-map'
import world from '@svg-maps/world'

const API_BASE = 'http://127.0.0.1:8000'
const theme = useTheme()

const heroSectionRef = ref(null)
const mapPanelRef = ref(null)
const hoveredCountry = ref(null)
const pinPositions = ref({})
const isDark = computed(() => theme.global.current.value.dark)
const backgroundImage = computed(() =>
  isDark.value ? '/assets/images/night_view.png' : '/assets/images/day_view.png',
)
const popup = ref({
  visible: false,
  x: 0,
  y: 0,
  country: null,
})

const fallbackCountries = [
  { id: 'is', slug: 'iceland', name: 'Iceland', image: '/assets/images/Iceland/iceland-hero.jpg' },
  { id: 'no', slug: 'norway', name: 'Norway', image: '/assets/images/Norway/norway-hero.jpg' },
  { id: 'se', slug: 'sweden', name: 'Sweden', image: '/assets/images/Sweden/sweden-hero.jpg' },
  { id: 'fi', slug: 'finland', name: 'Finland', image: '/assets/images/Finland/finland-hero.jpg' },
  { id: 'dk', slug: 'denmark', name: 'Denmark', image: '/assets/images/Denmark/denmark-hero.jpg' },
]

const countries = ref([...fallbackCountries])

const countryIdBySlug = {
  iceland: 'is',
  norway: 'no',
  sweden: 'se',
  finland: 'fi',
  denmark: 'dk',
}

const countryMarkers = [
  { id: 'is', slug: 'iceland', name: 'Iceland' },
  { id: 'no', slug: 'norway', name: 'Norway' },
  { id: 'se', slug: 'sweden', name: 'Sweden' },
  { id: 'fi', slug: 'finland', name: 'Finland' },
  { id: 'dk', slug: 'denmark', name: 'Denmark' },
]

const loadCountries = async () => {
  try {
    const response = await fetch(`${API_BASE}/api/countries`)
    const result = await response.json()
    if (!response.ok || !result.success)
      throw new Error(result.message || 'Failed to load countries')

    const rawCountries = (result.data || []).filter((item) => countryIdBySlug[item.slug])

    countries.value = rawCountries.map((country) => ({
      id: countryIdBySlug[country.slug],
      slug: country.slug,
      name: country.name,
      image: country.hero_image_url || '/assets/images/logo.png',
    }))
  } catch (error) {
    console.error('Failed loading map countries:', error)
    countries.value = [...fallbackCountries]
  }
}

const findCountry = (countryId) => countries.value.find((c) => c.id === countryId)

const resolveCountryId = (payload) => {
  if (payload?.id) return String(payload.id).toLowerCase()
  if (payload?.location?.id) return String(payload.location.id).toLowerCase()
  return ''
}

const updatePopupPosition = (eventLike) => {
  const panelRect = mapPanelRef.value?.getBoundingClientRect?.()
  const sectionRect = heroSectionRef.value?.getBoundingClientRect?.()
  if (!panelRect || !sectionRect) return

  const point = eventLike?.event || eventLike
  if (typeof point?.clientX !== 'number' || typeof point?.clientY !== 'number') return

  popup.value.x = point.clientX - sectionRect.left + 14
  popup.value.y = point.clientY - sectionRect.top - 14
}

const measureCountryPins = () => {
  const panelRect = mapPanelRef.value?.getBoundingClientRect?.()
  if (!panelRect) return

  const nextPositions = {}

  countryMarkers.forEach((marker) => {
    const countryNode = mapPanelRef.value.querySelector(`.country-${marker.slug}`)
    const landPoint = findRenderedLandPoint(countryNode)
    if (!landPoint) return

    nextPositions[marker.id] = {
      left: `${landPoint.x - panelRect.left}px`,
      top: `${landPoint.y - panelRect.top}px`,
    }
  })

  pinPositions.value = nextPositions
}

const findRenderedLandPoint = (countryNode) => {
  if (!countryNode) return null

  const svg = countryNode.ownerSVGElement
  const ctm = countryNode.getScreenCTM?.()
  const bbox = countryNode.getBBox?.()
  if (!svg || !ctm || !bbox) {
    const rect = countryNode.getBoundingClientRect?.()
    return rect ? { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 } : null
  }

  const point = svg.createSVGPoint()
  let best = null

  for (let row = 0; row <= 24; row += 1) {
    const y = bbox.y + (bbox.height * row) / 24
    let runStart = null
    let bestRunForRow = null

    for (let column = 0; column <= 48; column += 1) {
      const x = bbox.x + (bbox.width * column) / 48
      point.x = x
      point.y = y

      const isInside = typeof countryNode.isPointInFill === 'function'
        ? countryNode.isPointInFill(point)
        : false

      if (isInside && runStart === null) {
        runStart = x
      }

      if ((!isInside || column === 48) && runStart !== null) {
        const runEnd = isInside ? x : bbox.x + (bbox.width * (column - 1)) / 48
        const width = runEnd - runStart

        if (!bestRunForRow || width > bestRunForRow.width) {
          bestRunForRow = {
            x: runStart + width / 2,
            y,
            width,
          }
        }

        runStart = null
      }
    }

    if (bestRunForRow && (!best || bestRunForRow.width > best.width)) {
      best = bestRunForRow
    }
  }

  if (!best) {
    best = {
      x: bbox.x + bbox.width / 2,
      y: bbox.y + bbox.height / 2,
    }
  }

  point.x = best.x
  point.y = best.y
  const renderedPoint = point.matrixTransform(ctm)

  return { x: renderedPoint.x, y: renderedPoint.y }
}

const pinStyle = (marker) => {
  const position = pinPositions.value[marker.id]
  if (!position) return { opacity: 0 }

  return {
    left: position.left,
    top: position.top,
    opacity: 1,
  }
}

const goToCountry = (payload) => {
  const countryId = resolveCountryId(payload)
  const country = findCountry(countryId)
  if (!country) return

  goToCountryBySlug(country.slug)
}

const goToCountryBySlug = (slug) => {
  document.getElementById(slug)?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  })
}

const resolveSlugFromMapTarget = (target) => {
  const location = target?.closest?.('.svg-map__location')
  if (!location) return ''

  const matchedMarker = countryMarkers.find((marker) =>
    location.classList.contains(`country-${marker.slug}`),
  )

  return matchedMarker?.slug || ''
}

const onMapClick = (event) => {
  const slug = resolveSlugFromMapTarget(event.target)
  if (!slug) return

  goToCountryBySlug(slug)
}

const onCountryEnter = (payload) => {
  const countryId = resolveCountryId(payload)
  const country = findCountry(countryId)
  if (!country) return

  hoveredCountry.value = countryId
  popup.value.visible = true
  popup.value.country = country
  updatePopupPosition(payload)
}

const onCountryMove = (payload) => {
  if (!popup.value.visible) return
  updatePopupPosition(payload)
}

const onMarkerEnter = (marker) => {
  const country = findCountry(marker.id) || marker
  const position = pinPositions.value[marker.id]
  if (!position) return

  popup.value.visible = true
  popup.value.country = country
  popup.value.x = parseFloat(position.left)
  popup.value.y = parseFloat(position.top)
}

const onCountryLeave = () => {
  hoveredCountry.value = null
  popup.value.visible = false
}

const getCountryClass = (location) => {
  const locationId = String(location.id || '').toLowerCase()

  if (locationId === 'is') return 'country-iceland'
  if (locationId === 'no') return 'country-norway'
  if (locationId === 'se') return 'country-sweden'
  if (locationId === 'fi') return 'country-finland'
  if (locationId === 'dk') return 'country-denmark'

  return 'country-muted'
}

const getLocationAttributes = (location) => ({
  class: [
    getCountryClass(location),
    String(location.id || '').toLowerCase() === hoveredCountry.value ? 'country-hovered' : '',
  ]
    .join(' ')
    .trim(),
})

onMounted(async () => {
  await loadCountries()
  await nextTick()
  measureCountryPins()
  window.addEventListener('resize', measureCountryPins)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', measureCountryPins)
})
</script>

<style scoped>
.explore-hero-section {
  position: relative;
  width: 100vw;
  height: 80vh;
  margin: 0;
  overflow: hidden;
  left: 50%;
  transform: translateX(-50%);
}

/* image only on right side */
.hero-bg-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;

  clip-path: polygon(56% 0, 100% 0, 100% 100%, 34% 100%);
}

/* black overlay on image side */
.hero-dark-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  background: linear-gradient(
    to left,
    rgba(0, 0, 0, 0.08),
    rgba(0, 0, 0, 0.28)
  );

  clip-path: polygon(56% 0, 100% 0, 100% 100%, 34% 100%);
}

.hero-bottom-blend {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 5;
  height: 34%;
  pointer-events: none;
  background: linear-gradient(
    to bottom,
    rgba(var(--v-theme-background), 0),
    rgba(var(--v-theme-background), 0.52) 58%,
    rgb(var(--v-theme-background)) 100%
  );
}

/* transparent map area */
.hero-map-overlay {
  position: absolute;
  left: 0;
  top: 0;
  width: 58%;
  height: 100%;
  z-index: 4;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  background: transparent;

  clip-path: polygon(0 0, 100% 0, 58% 100%, 0 100%);
}

/* diagonal divider line */
.hero-map-overlay::after {
  content: '';
  position: absolute;
  top: -10%;
  right: 7%;
  width: 3px;
  height: 125%;
  background: rgba(var(--v-theme-on-surface), 0.75);
  transform: rotate(30deg);
  transform-origin: center;
  pointer-events: none;
}

.country-pin {
  position: absolute;
  z-index: 8;
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  cursor: pointer;
  transform: translate(-50%, -100%);
  transition:
    transform 0.2s ease,
    filter 0.2s ease;
}

.country-pin span {
  font-size: 1.65rem;
  line-height: 1;
  filter: drop-shadow(0 3px 8px rgba(var(--v-theme-background), 0.72))
    drop-shadow(0 0 6px rgba(var(--v-theme-primary), 0.45));
}

.country-pin:hover {
  transform: translate(-50%, -100%) scale(1.14);
  filter: brightness(1.08);
}

.country-pin-layer {
  position: absolute;
  inset: 0;
  z-index: 8;
  pointer-events: none;
}

.country-pin-layer .country-pin {
  pointer-events: auto;
}

/* map svg */
:deep(.svg-map) {
  width: 100%;
  max-width: 720px;
  transform: scale(5) translate(0%, 15%);
  transform-origin: center;
  opacity: 1;
}

/* countries outside Nordic area */
:deep(.country-muted) {
  fill: transparent;
  stroke: rgba(var(--v-theme-on-surface), 0.18);
  opacity: 0.25;
  cursor: default !important;
  pointer-events: none;
}

/* general map line */
:deep(.svg-map__location) {
  stroke: rgba(var(--v-theme-on-surface), 0.45);
  stroke-width: 0.8;
  transition: 0.3s ease;
}

/* Nordic countries */
:deep(.country-iceland),
:deep(.country-norway),
:deep(.country-sweden),
:deep(.country-finland),
:deep(.country-denmark) {
  fill: rgba(var(--v-theme-primary), 0.55) !important;
  stroke: rgba(var(--v-theme-on-surface), 0.85);
  cursor: pointer;
}

/* hover */
:deep(.country-iceland:hover),
:deep(.country-norway:hover),
:deep(.country-sweden:hover),
:deep(.country-finland:hover),
:deep(.country-denmark:hover),
:deep(.country-hovered) {
  fill: rgba(var(--v-theme-primary), 0.9) !important;
  filter: drop-shadow(0 0 10px rgba(var(--v-theme-primary), 0.7));
}

/* popup keep same */
.country-popup {
  position: absolute;
  z-index: 20;
  min-width: 130px;
  padding: 10px 14px;
  pointer-events: none;
  transform: translate(18px, -110%);
  display: flex;
  align-items: center;
  background: rgba(var(--v-theme-surface), 0.9);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 999px;
  box-shadow: 0 12px 30px rgba(var(--v-theme-background), 0.3);
  justify-content: center;
  text-align: center;
}

.popup-text {
  width: 100%;
  text-align: center;
}

.country-popup h4 {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 800;
  color: rgb(var(--v-theme-text));
  text-align: center;
}

@media (max-width: 768px) {
  .explore-hero-section {
    width: calc(100% - 24px);
    height: 58vh;
    margin: 20px 12px 40px;
  }

  .hero-bg-image,
  .hero-dark-overlay {
    clip-path: polygon(0 48%, 100% 30%, 100% 100%, 0 100%);
  }

  .hero-bottom-blend {
    height: 42%;
  }

  .hero-map-overlay {
    width: 100%;
    height: 58%;
    clip-path: polygon(0 0, 100% 0, 100% 80%, 0 100%);
  }

  .hero-map-overlay::after {
    display: none;
  }

  :deep(.svg-map) {
    transform: scale(2.95) translate(4%, 12%);
  }

  .country-pin {
    width: 30px;
    height: 30px;
  }

  .country-pin span {
    font-size: 1.45rem;
  }
}
</style>
