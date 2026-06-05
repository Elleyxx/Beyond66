import { ref } from 'vue'
import { API_BASE } from '../services/apiBase'

export function useCountryPage(slug, fallback) {
  const pageData = ref({ ...fallback })
  const isLoading = ref(false)
  const error = ref('')

  const load = async () => {
    isLoading.value = true
    error.value = ''
    try {
      const response = await fetch(`${API_BASE}/api/countries/${slug}`)
      const result = await response.json()
      if (!response.ok || !result.success) {
        throw new Error(result.message || 'Failed to load country data')
      }

      const { country, experiences, highlights } = result.data
      const language = country.language || fallback.language || 'English'
      const timezone = country.timezone || fallback.timezone || 'UTC'
      const bestTravelMonth = country.best_travel_month || fallback.bestTravelMonth || fallback.bestTime || 'Varies by season'

      pageData.value = {
        country: country.name.toUpperCase(),
        title: country.hero_title || fallback.title || `${country.name}: Signature Experiences`,
        description: country.intro || fallback.description,
        // Keep a distinct curated hero image per country view.
        heroImage: fallback.heroImage || country.hero_image_url,
        bestTime: country.best_time_summary || fallback.bestTime,
        highlights: [
          { icon: 'mdi-translate', title: 'Language', text: language },
          { icon: 'mdi-clock-outline', title: 'Timezone', text: timezone },
          { icon: 'mdi-calendar-month-outline', title: 'Best Travel Month', text: bestTravelMonth },
        ],
        destinations: (highlights || []).map((item) => item.name),
        experiences: (experiences || []).map((item) => item.name),
        gallery: fallback.gallery || [fallback.heroImage].filter(Boolean),
        travelTips: fallback.travelTips || [],
      }
    } catch (loadError) {
      error.value = loadError.message
      pageData.value = { ...fallback }
    } finally {
      isLoading.value = false
    }
  }

  return {
    pageData,
    isLoading,
    error,
    load,
  }
}
