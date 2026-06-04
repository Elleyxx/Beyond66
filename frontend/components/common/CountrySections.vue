<template>
  <section class="country-content">
    <section
      v-for="(country, index) in countries"
      :key="country.slug"
      :id="country.slug"
      class="country-destination-section"
    >
      <div class="country-row" :class="{ 'country-row-reverse': index % 2 === 1 }">
        <div class="country-name-panel">
          <h2 class="split-country-name">
            <span>{{ country.displayName[0] }}</span>
            <span>{{ country.displayName[1] }}</span>
          </h2>
          <p class="country-intro">{{ country.description }}</p>
          <router-link :to="`/country/${country.slug}`" class="more-btn">
            MORE →
          </router-link>
        </div>

        <div class="carousel-panel" v-if="country.randomDestinations?.length">
          <div class="carousel-track">
            <article
              v-for="(item, itemIndex) in country.randomDestinations"
              :key="`${country.slug}-${item.name}`"
              class="destination-card"
              :style="getCardStyle(country.slug, itemIndex, country.randomDestinations.length)"
            >
              <img :src="item.image || country.image" :alt="item.name" />
              <div class="destination-card-body">
                <h3>{{ item.name }}</h3>
                <p>{{ item.summary || t('countries.map.noSummary') }}</p>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import norwayData from '../../src/locales/en/countries/norway.json'
import swedenData from '../../src/locales/en/countries/sweden.json'
import finlandData from '../../src/locales/en/countries/finland.json'
import denmarkData from '../../src/locales/en/countries/denmark.json'
import icelandRaw from '../../src/locales/en/countries/iceland.json'

const { t } = useI18n()

const fallbackCountries = [
  { id: 'is', slug: 'iceland', name: 'Iceland', displayName: ['ICE', 'LAND'], description: '', image: '/assets/images/Iceland/iceland.jpg', randomDestinations: [] },
  { id: 'no', slug: 'norway', name: 'Norway', displayName: ['NOR', 'WAY'], description: '', image: '/assets/images/Norway/norway.jpg', randomDestinations: [] },
  { id: 'se', slug: 'sweden', name: 'Sweden', displayName: ['SWE', 'DEN'], description: '', image: '/assets/images/Sweden/sweden.jpg', randomDestinations: [] },
  { id: 'fi', slug: 'finland', name: 'Finland', displayName: ['FIN', 'LAND'], description: '', image: '/assets/images/Finland/finland.jpg', randomDestinations: [] },
  { id: 'dk', slug: 'denmark', name: 'Denmark', displayName: ['DEN', 'MARK'], description: '', image: '/assets/images/Denmark/denmark.jpg', randomDestinations: [] },
]

const countries = ref([...fallbackCountries])
const carouselIndexBySlug = ref({})
let carouselTimer = null

const countryJsonList = [
  norwayData,
  swedenData,
  finlandData,
  denmarkData,
  icelandRaw?.countries?.iceland || icelandRaw,
]

const initializeCarouselIndexes = () => {
  const next = {}
  countries.value.forEach((country) => {
    next[country.slug] = 0
  })
  carouselIndexBySlug.value = next
}

const tickCarousel = () => {
  const next = { ...carouselIndexBySlug.value }
  countries.value.forEach((country) => {
    const total = country.randomDestinations?.length || 0
    if (total <= 1) return
    next[country.slug] = ((next[country.slug] || 0) + 1) % total
  })
  carouselIndexBySlug.value = next
}

const getCardStyle = (slug, index, total) => {
  const current = carouselIndexBySlug.value[slug] || 0
  if (!total) return {}

  let offset = index - current
  if (offset > total / 2) offset -= total
  if (offset < -total / 2) offset += total

  const distance = Math.abs(offset)
  const hidden = distance > 2
  const x = offset * 78
  const y = distance * 26
  const scale = 1 - distance * 0.1
  const rotate = offset * 2.8

  return {
    transform: `translate(${x}px, ${y}px) scale(${scale}) rotate(${rotate}deg)`,
    zIndex: `${100 - distance * 10}`,
    opacity: hidden ? '0' : `${1 - distance * 0.22}`,
    pointerEvents: hidden ? 'none' : 'auto',
  }
}

const randomPick = (items, count) => {
  const list = [...items]
  for (let i = list.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1))
    const temp = list[i]
    list[i] = list[j]
    list[j] = temp
  }
  return list.slice(0, count)
}

const normalizeDestination = (destination, fallbackImage) => ({
  name: destination?.name || 'Destination',
  summary: destination?.description || '',
  image:
    (Array.isArray(destination?.images) && destination.images[0]) ||
    fallbackImage,
})

const loadCountries = () => {
  try {
    countries.value = countryJsonList.map((country) => {
      const destinations = Array.isArray(country?.destinations) ? country.destinations : []
      const randomDestinations = randomPick(destinations, Math.min(5, destinations.length))
      const fallback = fallbackCountries.find((item) => item.slug === country.slug)

      return {
        id: fallback?.id || country.slug,
        slug: country.slug,
        name: country.country || country.slug,
        displayName: fallback?.displayName || [country.country || country.slug],
        description: country.description || '',
        image: country.heroImage || '/assets/images/logo.png',
        randomDestinations: randomDestinations.map((item) =>
          normalizeDestination(item, country.heroImage || '/assets/images/logo.png'),
        ),
      }
    })

    initializeCarouselIndexes()
  } catch (error) {
    console.error('Failed loading country sections from JSON:', error)
    countries.value = [...fallbackCountries]
    initializeCarouselIndexes()
  }
}

onMounted(async () => {
  loadCountries()
  carouselTimer = window.setInterval(tickCarousel, 3200)
})

onBeforeUnmount(() => {
  if (carouselTimer) window.clearInterval(carouselTimer)
})
</script>

<style scoped>
.country-content {
  display: flex;
  flex-direction: column;
  gap: 40px;
  padding: 24px 40px 40px;
}

.country-destination-section {
  scroll-margin-top: 120px;
  padding: 36px;
}

.country-row {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 48px;
  align-items: center;
}

.country-row.country-row-reverse {
  grid-template-columns: 2fr 1fr;
}

.country-row.country-row-reverse .country-name-panel {
  order: 2;
}

.country-row.country-row-reverse .carousel-panel {
  order: 1;
}

.country-name-panel {
  display: flex;
  flex-direction: column;
  border: none;
  background: transparent;
  padding: 0 18px;
}

.split-country-name {
  display: flex;
  flex-direction: column;
  margin: 0;
  font-size: clamp(4rem, 10vw, 9rem);
  line-height: 0.82;
  font-weight: 900;
  letter-spacing: -0.08em;
  text-transform: uppercase;
}

.country-row.country-row-reverse .split-country-name {
  align-items: flex-end;
  text-align: right;
}

.country-row:not(.country-row-reverse) .country-name-panel {
  padding-left: 120px;
}

.country-row.country-row-reverse .country-name-panel {
  padding-right: 120px;
}

.country-intro {
  margin-top: 24px;
  max-width: 360px;
  line-height: 1.8;
  opacity: 0.72;
}

.more-btn {
  margin-top: 34px;
  width: fit-content;
  align-self: center;
  text-decoration: none;
  font-size: 0.92rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgb(var(--v-theme-text));
  position: relative;
  z-index: 5;
  transition:
    transform 0.3s ease,
    letter-spacing 0.3s ease,
    opacity 0.3s ease;
}

.country-row.country-row-reverse .more-btn {
  align-self: center;
}

.more-btn:hover {
  transform: translateX(6px);
  letter-spacing: 0.2em;
  opacity: 0.8;
  text-decoration: underline;
}

.country-row.country-row-reverse .country-intro {
  margin-left: auto;
  text-align: right;
}

.carousel-panel {
  overflow: hidden;
  border-radius: 28px;
}

.carousel-track {
  position: relative;
  height: 520px;
  margin: 12px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 1200px;
}

.destination-card {
  position: absolute;
  width: min(92%, 430px);
  height: 470px;
  border-radius: 28px;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
  transition:
    transform 0.55s cubic-bezier(0.22, 0.61, 0.36, 1),
    opacity 0.45s ease,
    filter 0.45s ease;
  will-change: transform, opacity;
  box-shadow:
    0 22px 48px rgba(var(--v-theme-background), 0.34),
    0 8px 18px rgba(var(--v-theme-background), 0.2);
}

.destination-card::after {
  content: '';

  position: absolute;
  inset: 0;

  background:
    linear-gradient(
      to top,
      rgba(var(--v-theme-background), 0.85) 8%,
      rgba(var(--v-theme-background), 0.2) 45%,
      transparent 70%
    );
}

.destination-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.destination-card-body {
  position: absolute;
  left: 24px;
  right: 24px;
  bottom: 24px;
  z-index: 2;
  padding: 0;
  background: transparent;
  color: rgb(var(--v-theme-on-surface));
}

.destination-card-body h3 {
  margin: 0 0 10px;
  font-size: 1.4rem;
  font-weight: 800;
}

.destination-card-body p {
  margin: 0;
  opacity: 0.86;
  line-height: 1.7;
  font-size: 0.92rem;
}
@media (max-width: 1024px) {
  .country-content {
    gap: 24px;
    padding: 20px;
  }
}

@media (max-width: 900px) {
  .country-destination-section {
    padding: 24px;
  }

  .country-row,
  .country-row.country-row-reverse {
    grid-template-columns: 1fr;
  }

  .carousel-track {
    height: 470px;
  }

  .destination-card {
    width: min(94%, 500px);
    height: 420px;
  }
}
</style>


