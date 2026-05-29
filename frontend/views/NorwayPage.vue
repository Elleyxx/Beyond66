<template>
  <CountryLayout
    :country="pageData.country"
    :title="pageData.title"
    :description="pageData.description"
    :hero-image="pageData.heroImage"
    hero-cta-label="Explore Now"
    hero-cta-to="/explore"
    :hero-wave="true"
    :highlights="pageData.highlights"
    :experiences="pageData.experiences"
    :best-time="pageData.bestTime"
    :best-time-seasons="pageData.bestTimeSeasons"
    :quick-facts="pageData.quickFacts"
    :destinations="pageData.destinations"
    :gallery="pageData.gallery"
    :travel-tips="pageData.travelTips"
  />
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import CountryLayout from '@/components/common/CountryLayout.vue'
import { getCurrentWeather } from '../services/weatherService'

const { tm, getLocaleMessage } = useI18n()
const currentTemp = ref(null)
const currentTime = ref('--')
const currentWeatherIcon = ref('')
let clockTimer = 0
let weatherTimer = 0

const updateCurrentTime = () => {
  const formatter = new Intl.DateTimeFormat('en-GB', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone: 'Europe/Oslo',
    timeZoneName: 'short',
  })
  currentTime.value = formatter.format(new Date())
}

const mapWeatherToBiIcon = (iconCode) => {
  if (!iconCode) return 'bi-cloudy-fill'
  if (iconCode.startsWith('01')) return 'bi-brightness-high-fill'
  if (iconCode.startsWith('02')) return 'bi-cloud-sun-fill'
  if (iconCode.startsWith('03') || iconCode.startsWith('04')) return 'bi-cloud-fill'
  if (iconCode.startsWith('09')) return 'bi-cloud-rain-heavy-fill'
  if (iconCode.startsWith('10')) return 'bi-cloud-rain-fill'
  if (iconCode.startsWith('11')) return 'bi-cloud-lightning-rain-fill'
  if (iconCode.startsWith('13')) return 'bi-cloud-snow-fill'
  if (iconCode.startsWith('50')) return 'bi-cloud-fog2-fill'
  return 'bi-cloudy-fill'
}

const loadCurrentWeather = async () => {
  try {
    const weather = await getCurrentWeather(59.9139, 10.7522)
    const temp = weather?.main?.temp
    currentTemp.value = typeof temp === 'number' ? Math.round(temp) : null
    const iconCode = weather?.weather?.[0]?.icon
    currentWeatherIcon.value = weather?.weather?.[0]?.bootstrapIcon || mapWeatherToBiIcon(iconCode)
  } catch {
    currentTemp.value = null
    currentWeatherIcon.value = 'bi-cloudy-fill'
  }
}

const getCountryData = () => {
  const localized = tm('countries.norway') || {}
  if (localized && typeof localized === 'object' && Object.keys(localized).length) return localized

  const enCountries = getLocaleMessage('en')?.countries || {}
  if (enCountries.norway && typeof enCountries.norway === 'object') return enCountries.norway

  const fallbackRoot = getLocaleMessage('en') || {}
  if (fallbackRoot.country && fallbackRoot.slug === 'norway') return fallbackRoot

  return {}
}

const norwayData = computed(() => getCountryData())

const pageData = computed(() => {
  const data = norwayData.value || {}
  const baseQuickFacts = data.quickFacts ?? {}
  const quickFacts = [
    ...Object.entries(baseQuickFacts)
      .filter(([key]) => key !== 'timeZone')
      .map(([label, value]) => ({ label, value })),
    { label: 'Current Time & Date', value: currentTime.value || '--' },
    {
      label: 'Current Temperature',
      value: currentTemp.value != null ? `${currentTemp.value}°C` : '--',
      icon: currentWeatherIcon.value || '',
    },
  ]

  const bestTimeList = Array.isArray(data.bestTime) ? data.bestTime : []
  const bestTimeText = bestTimeList.length
    ? bestTimeList.map((item) => `${item.season}: ${item.months} - ${item.description}`).join(' | ')
    : data.bestTime || ''

  const destinations = Array.isArray(data.destinations) ? data.destinations : []
  const normalizedDestinations = destinations.map((item) => ({
    ...item,
    images: Array.isArray(item.images) ? item.images : [item.image].filter(Boolean),
  }))

  const gallery = normalizedDestinations.flatMap((item) => item.images || [])

  const experiences = (Array.isArray(data.experiences) ? data.experiences : []).map((item, index) => {
    if (typeof item === 'string') {
      return {
        title: item,
        description: '',
        image: gallery[index % (gallery.length || 1)] || '',
      }
    }
    return {
      title: item?.title || `Experience ${index + 1}`,
      description: item?.description || '',
      image: item?.image || gallery[index % (gallery.length || 1)] || '',
    }
  })

  return {
    country: data.country || 'NORWAY',
    title: data.title || 'Norway',
    description: data.description || '',
    heroImage: data.heroImage || '/assets/images/Norway/norway-hero.jpg',
    bestTime: bestTimeText || 'June - August',
    bestTimeSeasons: bestTimeList,
    quickFacts,
    highlights: Array.isArray(data.highlights) ? data.highlights : [],
    destinations: normalizedDestinations,
    experiences,
    gallery,
    travelTips: [
      { title: 'Weather', text: 'Pack waterproof layers and warm outerwear year-round.' },
      { title: 'Road Safety', text: 'Check weather and ferry schedules before long fjord drives.' },
      { title: 'Bookings', text: 'Reserve scenic rail and Arctic activities early in peak season.' },
    ],
  }
})

onMounted(() => {
  updateCurrentTime()
  clockTimer = window.setInterval(updateCurrentTime, 60_000)
  loadCurrentWeather()
  weatherTimer = window.setInterval(loadCurrentWeather, 10 * 60_000)
})

onBeforeUnmount(() => {
  if (clockTimer) window.clearInterval(clockTimer)
  if (weatherTimer) window.clearInterval(weatherTimer)
})
</script>
