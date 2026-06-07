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
            {{ t('explore.moreBtn') }}
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
import { onBeforeUnmount, onMounted, watch } from 'vue'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, tm, locale } = useI18n()

const slugOrder = ['norway', 'sweden', 'finland', 'denmark', 'iceland']

const displayNames = {
  iceland: ['ICE', 'LAND'],
  norway: ['NOR', 'WAY'],
  sweden: ['SWE', 'DEN'],
  finland: ['FIN', 'LAND'],
  denmark: ['DEN', 'MARK'],
}

const fallbackImages = {
  iceland: '/assets/images/Iceland/iceland.jpg',
  norway: '/assets/images/Norway/norway.jpg',
  sweden: '/assets/images/Sweden/sweden.jpg',
  finland: '/assets/images/Finland/finland.jpg',
  denmark: '/assets/images/Denmark/denmark.jpg',
}

const countries = ref([])
const carouselIndexBySlug = ref({})
let carouselTimer = null

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
    countries.value = slugOrder.map((slug) => {
      const data = tm(`countries.${slug}`) || {}
      const fallbackImage = fallbackImages[slug] || '/assets/images/logo.png'
      const destinations = Array.isArray(data.destinations) ? data.destinations : []
      const randomDestinations = randomPick(destinations, Math.min(5, destinations.length))

      return {
        slug,
        name: data.country || slug,
        displayName: displayNames[slug] || [slug.toUpperCase()],
        description: data.description || '',
        image: data.heroImage || fallbackImage,
        randomDestinations: randomDestinations.map((item) =>
          normalizeDestination(item, data.heroImage || fallbackImage),
        ),
      }
    })

    initializeCarouselIndexes()
  } catch (error) {
    console.error('Failed loading country sections:', error)
  }
}

watch(locale, () => {
  loadCountries()
})

onMounted(() => {
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
@media (max-width: 1250px) {
  .country-content {
    gap: 24px;
    padding: 20px;
  }

  .country-destination-section {
    padding: 30px 24px;
  }

  .country-row {
    gap: 34px;
  }

  .country-row:not(.country-row-reverse) .country-name-panel {
    padding-left: 40px;
  }

  .country-row.country-row-reverse .country-name-panel {
    padding-right: 40px;
  }

  .split-country-name {
    font-size: clamp(4rem, 9vw, 7rem);
  }

  .carousel-track {
    height: 480px;
  }

  .destination-card {
    width: min(92%, 400px);
    height: 430px;
  }
}

@media (max-width: 900px) {
  .country-content {
    gap: 22px;
    padding: 18px;
  }

  .country-destination-section {
    padding: 32px 20px;
  }

  .country-row,
  .country-row.country-row-reverse {
    display: flex;
    flex-direction: column;
    gap: 28px;
    align-items: center;
  }

  .country-row .country-name-panel,
  .country-row.country-row-reverse .country-name-panel {
    order: 1;
    width: 100%;
    padding: 0;
    align-items: center;
    text-align: center;
  }

  .country-row .carousel-panel,
  .country-row.country-row-reverse .carousel-panel {
    order: 2;
    width: 100%;
  }

  .country-row .split-country-name,
  .country-row.country-row-reverse .split-country-name {
    align-items: center;
    text-align: center;
    font-size: clamp(3.6rem, 14vw, 6.5rem);
    letter-spacing: -0.06em;
  }

  .country-row .country-intro,
  .country-row.country-row-reverse .country-intro {
    max-width: 620px;
    margin: 20px auto 0;
    text-align: center;
  }

  .country-row .more-btn,
  .country-row.country-row-reverse .more-btn {
    align-self: center;
    margin-top: 24px;
  }

  .carousel-panel {
    display: flex;
    justify-content: center;
  }

  .carousel-track {
    width: 100%;
    height: 470px;
    justify-content: center;
  }

  .destination-card {
    width: min(94%, 500px);
    height: 420px;
  }
}

@media (max-width: 600px) {
  .country-content {
    gap: 18px;
    padding: 12px;
  }

  .country-destination-section {
    padding: 26px 10px;
  }

  .country-row,
  .country-row.country-row-reverse {
    gap: 22px;
    align-items: center;
  }

  .country-row .country-name-panel,
  .country-row.country-row-reverse .country-name-panel {
    align-items: center !important;
    text-align: center !important;
    padding: 0 !important;
  }

  .country-row .split-country-name,
  .country-row.country-row-reverse .split-country-name {
    align-items: center !important;
    text-align: center !important;
    font-size: clamp(3rem, 18vw, 4.8rem);
    letter-spacing: -0.05em;
  }

  .country-row .country-intro,
  .country-row.country-row-reverse .country-intro {
    max-width: 340px;
    margin: 16px auto 0 !important;
    text-align: center !important;
    font-size: 0.92rem;
    line-height: 1.65;
  }

  .country-row .more-btn,
  .country-row.country-row-reverse .more-btn {
    align-self: center !important;
    margin-top: 20px;
    font-size: 0.82rem;
  }

  .carousel-panel {
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .carousel-track {
    width: 100%;
    height: 380px;
    justify-content: center;
  }

  .destination-card {
    width: min(94%, 340px);
    height: 340px;
  }

  .destination-card-body {
    left: 18px;
    right: 18px;
    bottom: 18px;
  }

  .destination-card-body h3 {
    font-size: 1.15rem;
  }

  .destination-card-body p {
    font-size: 0.82rem;
    line-height: 1.55;
  }
}
</style>


