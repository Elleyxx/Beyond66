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

const { t, tm, getLocaleMessage } = useI18n()
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
    timeZone: 'Europe/Stockholm',
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
    const weather = await getCurrentWeather(59.3293, 18.0686)
    const temp = weather?.main?.temp
    currentTemp.value = typeof temp === 'number' ? Math.round(temp) : null
    const iconCode = weather?.weather?.[0]?.icon
    currentWeatherIcon.value = weather?.weather?.[0]?.bootstrapIcon || mapWeatherToBiIcon(iconCode)
  } catch {
    currentTemp.value = null
    currentWeatherIcon.value = 'bi-cloudy-fill'
  }
}

const swedenData = computed(() => {
  const localized = tm('countries.sweden') || {}
  if (localized && typeof localized === 'object' && Object.keys(localized).length) return localized
  return getLocaleMessage('en')?.countries?.sweden || {}
})

const pageData = computed(() => {
  const data = swedenData.value || {}
  const baseQuickFacts = data.quickFacts ?? {}
  const quickFacts = [
    ...Object.entries(baseQuickFacts)
      .filter(([key]) => key !== 'timeZone')
      .map(([label, value]) => ({ label, value })),
    { label: t('countryPage.currentTime'), value: currentTime.value || '--' },
    {
      label: t('countryPage.currentTemperature'),
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
    country: data.country || 'SWEDEN',
    title: data.title || 'Sweden',
    description: data.description || '',
    heroImage: data.heroImage || '/assets/images/Sweden/sweden.jpg',
    bestTime: bestTimeText || 'June - August',
    bestTimeSeasons: bestTimeList,
    quickFacts,
    highlights: Array.isArray(data.highlights) ? data.highlights : [],
    destinations: normalizedDestinations,
    experiences,
    gallery,
    travelTips: Array.isArray(data.travelTips) ? data.travelTips : [],
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

