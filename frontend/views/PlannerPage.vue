<template>
  <main class="planner-page" :class="{ 'is-dark': isDark }">
    <div class="planner-wrap">
    <header class="planner-header">
      <h1 :style="{ color: plannerTitleColor }">{{ t('planner.title') }}</h1>
      <img class="planner-border-image" src="/assets/images/plan_border.png" :alt="t('planner.borderAlt')" />
    </header>

    <div class="planner-layout" :class="{ 'is-split': isSplitLayout }">
      <PlannerTripListPanel
        :trips="trips"
        :selected-trip-id="selectedTripId"
        @add="startAddTrip"
        @select="selectTrip"
      />

      <PlannerCreatePanel
        v-if="mode === 'create'"
        :steps="steps"
        :current-step="currentStep"
        :max-unlocked-step="maxUnlockedStep"
        :progress-percent="progressPercent"
        :trip-meta="tripMeta"
        :timeline-days="timelineDays"
        :budget-estimate="budgetEstimate"
        :weather-forecast="weatherForecast"
        :aurora-forecast="auroraForecast"
        :packing-list="packingList"
        :total-activities="totalActivities"
        :checked-packing-count="checkedPackingCount"
        :ai-summary="aiSummary"
        :ai-error="aiError"
        :is-generating-ai="isGeneratingAi"
        :is-editing="isEditing"
        @go-step="goToStep"
        @next-step="nextStep"
        @prev-step="prevStep"
        @open-save-modal="isSaveModalOpen = true"
        @generate-ai-plan="generatePlannerFromAi"
        @toggle-packing="togglePackingItem"
        @print-checklist="printChecklist"
        @update:trip-meta="tripMeta = $event"
        @update:timeline-days="timelineDays = $event"
        @update:budget-estimate="updateBudgetEstimate"
      />

      <PlannerTripDetailPanel
        v-else-if="mode === 'view'"
        :trip="selectedTrip"
        @edit="editSelectedTrip"
        @add="startAddTrip"
      />

      <PlannerSaveModal
        v-if="isSaveModalOpen"
        :default-title="defaultSaveTitle"
        :default-description="defaultSaveDescription"
        :default-tags="defaultSaveTags"
        :suggested-tags="suggestedSaveTags"
        :default-visibility="defaultSaveVisibility"
        :is-editing="isEditing"
        @close="isSaveModalOpen = false"
        @save-trip="saveTrip"
      />
    </div>

    <p v-if="saveMessage" class="save-msg">{{ saveMessage }}</p>
    </div>
  </main>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useTheme } from 'vuetify'
import PlannerTripListPanel from '@/components/planner/PlannerTripListPanel.vue'
import PlannerCreatePanel from '@/components/planner/PlannerCreatePanel.vue'
import PlannerTripDetailPanel from '@/components/planner/PlannerTripDetailPanel.vue'
import PlannerSaveModal from '@/components/planner/PlannerSaveModal.vue'
import { generateAiPlanner, loadPlannerDrafts, savePlannerDraft } from '@/services/plannerService'
import { getAuroraPrediction, getTripWeather } from '@/services/weatherService'

const theme = useTheme()
const route = useRoute()
const { t } = useI18n()
const isDark = computed(() => theme.global.current.value.dark)
const plannerTitleColor = computed(() =>
  isDark.value ? 'rgb(var(--v-theme-primary))' : 'rgb(var(--v-theme-headerText))',
)

const steps = computed(() => [
  { title: t('planner.steps.basics'), iconClass: 'bi-passport' },
  { title: t('planner.steps.timeline'), iconClass: 'bi-signpost-split' },
  { title: t('planner.steps.budget'), iconClass: 'bi-cash-stack' },
  { title: t('planner.steps.weather'), iconClass: 'bi-cloud-sun' },
  { title: t('planner.steps.checklist'), iconClass: 'bi-list-check' },
  { title: t('planner.steps.save'), iconClass: 'bi-journal-check' },
])

const currentStep = ref(0)
const maxUnlockedStep = ref(0)
const mode = ref('list')
const selectedTripId = ref(null)
const editingTripId = ref(null)
const trips = ref([])
const saveMessage = ref('')
const aiError = ref('')
const aiSummary = ref('')
const isGeneratingAi = ref(false)
const isSaveModalOpen = ref(false)
const aiBudgetEstimate = ref(null)
const aiWeatherForecast = ref(null)
const aiAuroraForecast = ref(null)

const tripMeta = ref(createDefaultTripMeta())

function createDefaultTripMeta() {
  return {
    country: 'Norway',
    countryRoute: ['Norway'],
    startDate: '2027-01-15',
    endDate: '2027-01-19',
    dateRange: ['2027-01-15', '2027-01-19'],
    pax: 2,
    duration: 5,
    style: 'Adventure',
    budget: 'Medium',
    accommodation: 'Hotel',
    transport: 'Train',
    season: 'Winter',
    tripType: 'Couple',
    activityLevel: 'Moderate',
    interests: ['Northern Lights', 'Nature'],
  }
}

tripMeta.value.duration = calculateDuration(tripMeta.value.startDate, tripMeta.value.endDate)
tripMeta.value.season = deriveSeason(tripMeta.value.startDate)

const timelineDays = ref(buildDays(tripMeta.value.duration, tripMeta.value.startDate))

const packingList = ref(createDefaultPackingList())

function createDefaultPackingList() {
  return [
    { id: 1, name: 'Thermal base layers', checked: false },
    { id: 2, name: 'Waterproof jacket', checked: false },
    { id: 3, name: 'Power bank', checked: false },
    { id: 4, name: 'Travel documents', checked: false },
    { id: 5, name: 'Camera / phone tripod', checked: false },
  ]
}

watch(
  () => [tripMeta.value.startDate, tripMeta.value.endDate],
  ([startDate, endDate]) => {
    const safeDays = calculateDuration(startDate, endDate)
    const existing = timelineDays.value
    tripMeta.value.duration = safeDays
    tripMeta.value.season = deriveSeason(startDate)
    tripMeta.value.dateRange = startDate && endDate ? [startDate, endDate] : []
    timelineDays.value = Array.from({ length: safeDays }, (_, i) => ({
      ...existing[i],
      day: i + 1,
      date: addDays(startDate, i),
      items: existing[i]?.items ? [...existing[i].items] : [],
    }))
  },
  { immediate: true },
)

const totalActivities = computed(() => timelineDays.value.reduce((sum, day) => sum + day.items.length, 0))
const checkedPackingCount = computed(() => packingList.value.filter((item) => item.checked).length)

const weatherForecast = computed(() => {
  if (Array.isArray(aiWeatherForecast.value) && aiWeatherForecast.value.length) {
    return withForecastDates(aiWeatherForecast.value)
  }

  return Array.from({ length: Math.min(tripMeta.value.duration, 7) }, (_, idx) => ({
    date: addDays(tripMeta.value.startDate, idx),
    temp: temperatureByCountry(tripMeta.value.country) + idx,
    condition: ['Cloudy', 'Snow', 'Clear', 'Windy'][idx % 4],
  }))
})

const auroraForecast = computed(() => {
  if (Array.isArray(aiAuroraForecast.value) && aiAuroraForecast.value.length) {
    return withForecastDates(aiAuroraForecast.value)
  }

  if (aiAuroraForecast.value) {
    if (!aiAuroraForecast.value.date && !aiAuroraForecast.value.bestDate) {
      return { ...aiAuroraForecast.value, date: tripMeta.value.startDate || 'Best available night' }
    }
    return aiAuroraForecast.value
  }

  const winterBoost = tripMeta.value.season === 'Winter' ? 20 : 0
  const nordicBoost = ['Norway', 'Sweden', 'Finland', 'Iceland'].includes(tripMeta.value.country) ? 15 : 0
  const chance = Math.min(95, 35 + winterBoost + nordicBoost)
  return auroraArrayFromPrediction({
    kp: Math.max(2.5, (chance / 20).toFixed(1)),
    chance,
    window: '22:00 - 02:30 local time',
  })
})

const fallbackBudgetEstimate = computed(() => {
  const baseByBudget = { Low: 90, Medium: 180, High: 320 }
  const styleFactor = { Adventure: 1.1, Relax: 1.25, Culture: 1.0, Nature: 0.95 }
  const perDay = Math.round((baseByBudget[tripMeta.value.budget] || 180) * (styleFactor[tripMeta.value.style] || 1))
  const total = perDay * tripMeta.value.duration

  return {
    stay: Math.round(total * 0.42),
    food: Math.round(total * 0.2),
    transport: Math.round(total * 0.18),
    activities: Math.round(total * 0.2),
    total,
    currency: 'USD',
    pax: tripMeta.value.pax || 1,
    duration: tripMeta.value.duration,
    country: tripMeta.value.countryRoute?.join(', ') || tripMeta.value.country,
  }
})
const budgetEstimate = computed(() => aiBudgetEstimate.value || fallbackBudgetEstimate.value)
const defaultSaveTitle = computed(() => {
  if (isEditing.value && selectedTrip.value?.title) return selectedTrip.value.title

  const route = Array.isArray(tripMeta.value.countryRoute) && tripMeta.value.countryRoute.length
    ? tripMeta.value.countryRoute.join(' → ')
    : tripMeta.value.country || 'Nordic'

  return `${route} Trip Plan`
})
const defaultSaveDescription = computed(() => {
  return isEditing.value ? selectedTrip.value?.description || aiSummary.value : aiSummary.value
})
const defaultSaveTags = computed(() => {
  return isEditing.value && Array.isArray(selectedTrip.value?.tags) ? selectedTrip.value.tags : []
})
const suggestedSaveTags = computed(() => {
  if (isEditing.value && Array.isArray(selectedTrip.value?.tags)) return selectedTrip.value.tags

  const routeTags = Array.isArray(tripMeta.value.countryRoute)
    ? tripMeta.value.countryRoute
    : [tripMeta.value.country]
  const preferenceTags = [
    tripMeta.value.season,
    tripMeta.value.style,
    tripMeta.value.transport,
    tripMeta.value.tripType,
    ...(Array.isArray(tripMeta.value.interests) ? tripMeta.value.interests : []),
  ]

  return normalizeTags([...routeTags, ...preferenceTags].map(toCommunityTag))
})
const defaultSaveVisibility = computed(() => {
  return isEditing.value ? selectedTrip.value?.visibility || 'private' : 'private'
})

const countryCoordinates = {
  Norway: { lat: 69.6492, lon: 18.9553 },
  Sweden: { lat: 67.8558, lon: 20.2253 },
  Finland: { lat: 66.5039, lon: 25.7294 },
  Iceland: { lat: 64.1466, lon: -21.9426 },
  Denmark: { lat: 55.6761, lon: 12.5683 },
}

const progressPercent = computed(() => {
  if (steps.value.length <= 1) return 100
  return (currentStep.value / (steps.value.length - 1)) * 100
})

const selectedTrip = computed(() => trips.value.find((trip) => trip.id === selectedTripId.value) || null)
const isEditing = computed(() => editingTripId.value !== null)
const isSplitLayout = computed(() => mode.value === 'create' || mode.value === 'view')

function scrollPlannerToTop(behavior = 'smooth') {
  nextTick(() => {
    window.scrollTo({ top: 0, left: 0, behavior })
  })
}

function buildDays(days, startDate = tripMeta.value?.startDate) {
  return Array.from({ length: days }, (_, i) => ({ day: i + 1, date: addDays(startDate, i), items: [] }))
}

function calculateDuration(startDate, endDate) {
  if (!startDate || !endDate) return 1

  const start = new Date(`${startDate}T00:00:00`)
  const end = new Date(`${endDate}T00:00:00`)
  const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1

  if (!Number.isFinite(diff)) return 1
  return Math.min(30, Math.max(1, diff))
}

function deriveSeason(startDate) {
  if (!startDate) return 'Winter'

  const month = new Date(`${startDate}T00:00:00`).getMonth() + 1
  if ([12, 1, 2].includes(month)) return 'Winter'
  if ([3, 4, 5].includes(month)) return 'Spring'
  if ([6, 7, 8].includes(month)) return 'Summer'
  return 'Autumn'
}

function addDays(startDate, offset) {
  if (!startDate) return ''

  const date = new Date(`${startDate}T00:00:00`)
  date.setDate(date.getDate() + offset)
  return date.toISOString().slice(0, 10)
}

function temperatureByCountry(country) {
  const map = { Norway: -4, Sweden: -3, Finland: -6, Iceland: 0, Denmark: 3 }
  return map[country] ?? 2
}

function auroraArrayFromPrediction(aurora) {
  const days = Math.min(tripMeta.value.duration, 7)
  const baseChance = Number(aurora?.chance || 0)
  const kp = aurora?.kp || 0
  const window = aurora?.window || '10:00 PM - 2:00 AM'

  return Array.from({ length: days }, (_, index) => ({
    date: addDays(tripMeta.value.startDate, index),
    chance: Math.max(0, Math.min(95, Math.round(baseChance - Math.abs(index - Math.floor(days / 2)) * 4))),
    kp,
    window,
  }))
}

function withForecastDates(items) {
  return items.map((item, index) => ({
    ...item,
    date: item.date || addDays(tripMeta.value.startDate, index),
  }))
}

async function loadLiveWeatherAurora() {
  const primaryCountry = tripMeta.value.countryRoute?.[0] || tripMeta.value.country
  const coords = countryCoordinates[primaryCountry] || countryCoordinates.Norway

  try {
    const weather = await getTripWeather(
      coords.lat,
      coords.lon,
      tripMeta.value.startDate,
      tripMeta.value.endDate,
      primaryCountry,
      tripMeta.value.season,
    )
    const aurora = await getAuroraPrediction(coords.lat, coords.lon)

    aiWeatherForecast.value = weather
    aiAuroraForecast.value = auroraArrayFromPrediction(aurora)
  } catch {
    // Keep generated or local fallback weather when live APIs are unavailable.
  }
}

function buildAiPayload() {
  return {
    countryRoute: tripMeta.value.countryRoute || [tripMeta.value.country],
    startDate: tripMeta.value.startDate,
    endDate: tripMeta.value.endDate,
    duration: tripMeta.value.duration,
    budget: tripMeta.value.budget,
    season: tripMeta.value.season,
    pax: tripMeta.value.pax || 1,
    style: tripMeta.value.style,
    interests: tripMeta.value.interests || [],
    tripType: tripMeta.value.tripType,
    transport: tripMeta.value.transport,
    accommodation: tripMeta.value.accommodation,
    activityLevel: tripMeta.value.activityLevel,
  }
}

function normalizeTimelineDays(days) {
  if (!Array.isArray(days)) return []

  return days.map((day, index) => ({
    day: Number(day.day || index + 1),
    date: day.date || addDays(tripMeta.value.startDate, index),
    country: day.country || '',
    destination: day.destination || '',
    imageSearchQuery: day.imageSearchQuery || '',
    items: Array.isArray(day.items)
      ? day.items.map((item) => (typeof item === 'string' ? item : item?.name || item?.title || String(item))).filter(Boolean)
      : Array.isArray(day.activities)
        ? day.activities.map((item) => item?.name || item?.title || String(item)).filter(Boolean)
        : [],
  }))
}

function normalizePackingList(items) {
  if (!Array.isArray(items)) return []

  return items.map((item, index) => ({
    id: Number(item.id || index + 1),
    name: String(item.name || item.item || item.title || 'Item'),
    checked: Boolean(item.checked),
  }))
}

function normalizeBudgetEstimate(estimate, options = {}) {
  if (!estimate || typeof estimate !== 'object') return null

  const stay = Number(estimate.stay ?? estimate.accommodation ?? 0)
  const food = Number(estimate.food ?? 0)
  const transport = Number(estimate.transport ?? estimate.transportation ?? 0)
  const activities = Number(estimate.activities ?? 0)
  const total = Number(estimate.total ?? stay + food + transport + activities)
  const shouldKeepSuggestedTotals =
    options.includeSuggestedTotals ||
    estimate.suggestedTotal !== undefined ||
    estimate.originalTotal !== undefined

  const normalized = {
    stay,
    food,
    transport,
    activities,
    total,
    currency: estimate.currency || 'USD',
    pax: Number(estimate.pax || tripMeta.value.pax || 1),
    duration: Number(estimate.duration || tripMeta.value.duration || 0),
    country: estimate.country || tripMeta.value.countryRoute?.join(', ') || tripMeta.value.country,
  }

  if (shouldKeepSuggestedTotals) {
    normalized.suggestedTotal = Number(estimate.suggestedTotal ?? total)
    normalized.originalTotal = Number(estimate.originalTotal ?? estimate.suggestedTotal ?? total)
  }

  return normalized
}

function updateBudgetEstimate(estimate) {
  const nextBudget = normalizeBudgetEstimate(estimate)
  if (nextBudget) aiBudgetEstimate.value = nextBudget
}

async function generatePlannerFromAi() {
  if (isGeneratingAi.value) return false

  aiError.value = ''
  isGeneratingAi.value = true

  try {
    const result = await generateAiPlanner(buildAiPayload())
    const nextTimeline = normalizeTimelineDays(result.timelineDays)
    const nextPackingList = normalizePackingList(result.packingList)
    const nextBudget = normalizeBudgetEstimate(result.budgetEstimate, {
      includeSuggestedTotals: true,
    })

    if (nextTimeline.length) timelineDays.value = nextTimeline
    if (nextPackingList.length) packingList.value = nextPackingList
    if (nextBudget) aiBudgetEstimate.value = nextBudget
    if (Array.isArray(result.weatherForecast)) aiWeatherForecast.value = result.weatherForecast
    if (
      Array.isArray(result.auroraForecast) ||
      (result.auroraForecast && typeof result.auroraForecast === 'object')
    ) {
      aiAuroraForecast.value = result.auroraForecast
    }
    await loadLiveWeatherAurora()
    aiSummary.value = String(result.summary || '')
    maxUnlockedStep.value = Math.max(maxUnlockedStep.value, 1)
    currentStep.value = 1
    return true
  } catch (error) {
    aiError.value = error?.message || t('planner.messages.aiFailed')
    return false
  } finally {
    isGeneratingAi.value = false
  }
}

function togglePackingItem(id) {
  packingList.value = packingList.value.map((item) =>
    item.id === id ? { ...item, checked: !item.checked } : item,
  )
}

function printChecklist() {
  window.print()
}

async function nextStep() {
  if (currentStep.value >= steps.value.length - 1) return

  if (currentStep.value === 0 && !isEditing.value) {
    await generatePlannerFromAi()
    return
  }

  const nextIndex = currentStep.value + 1
  maxUnlockedStep.value = Math.max(maxUnlockedStep.value, nextIndex)
  currentStep.value = nextIndex
}

function prevStep() {
  if (currentStep.value <= 0) return
  currentStep.value -= 1
}

function goToStep(index) {
  if (index < 0 || index > steps.value.length - 1) return
  if (index > maxUnlockedStep.value) return
  currentStep.value = index
}

function selectTrip(id) {
  selectedTripId.value = id
  editingTripId.value = null
  mode.value = 'view'
  scrollPlannerToTop()
}

function startAddTrip() {
  resetTripForm()
  mode.value = 'create'
  selectedTripId.value = null
  editingTripId.value = null
  currentStep.value = 0
  maxUnlockedStep.value = 0
  scrollPlannerToTop()
}

function editSelectedTrip() {
  if (!selectedTrip.value) return

  tripMeta.value = {
    ...tripMeta.value,
    ...selectedTrip.value.tripMeta,
    country: selectedTrip.value.country,
    duration: selectedTrip.value.duration,
    style: selectedTrip.value.style,
    season: selectedTrip.value.season,
  }

  timelineDays.value = selectedTrip.value.timelineDays.map((day) => ({
    ...day,
    day: day.day,
    items: [...day.items],
  }))

  if (Array.isArray(selectedTrip.value.packingList) && selectedTrip.value.packingList.length) {
    packingList.value = selectedTrip.value.packingList.map((item) => ({ ...item }))
  }

  aiBudgetEstimate.value = selectedTrip.value.budgetEstimate
    ? normalizeBudgetEstimate(selectedTrip.value.budgetEstimate)
    : null
  aiWeatherForecast.value = Array.isArray(selectedTrip.value.weatherForecast)
    ? selectedTrip.value.weatherForecast.map((item) => ({ ...item }))
    : null
  aiAuroraForecast.value = Array.isArray(selectedTrip.value.auroraForecast)
    ? selectedTrip.value.auroraForecast.map((item) => ({ ...item }))
    : selectedTrip.value.auroraForecast || null
  aiSummary.value = selectedTrip.value.summary || ''
  editingTripId.value = selectedTrip.value.id
  mode.value = 'create'
  currentStep.value = 0
  maxUnlockedStep.value = steps.value.length - 1
  scrollPlannerToTop()
}

function resetTripForm() {
  tripMeta.value = createDefaultTripMeta()
  tripMeta.value.duration = calculateDuration(tripMeta.value.startDate, tripMeta.value.endDate)
  tripMeta.value.season = deriveSeason(tripMeta.value.startDate)
  timelineDays.value = buildDays(tripMeta.value.duration, tripMeta.value.startDate)
  packingList.value = createDefaultPackingList()
  aiBudgetEstimate.value = null
  aiWeatherForecast.value = null
  aiAuroraForecast.value = null
  aiSummary.value = ''
  aiError.value = ''
  isSaveModalOpen.value = false
}

function upsertTrip(nextTrip) {
  const index = trips.value.findIndex((trip) => trip.id === nextTrip.id)
  if (index >= 0) {
    trips.value.splice(index, 1, nextTrip)
    return
  }
  trips.value.unshift(nextTrip)
}

async function saveTrip(saveOptions = {}) {
  const wasEditing = isEditing.value
  const title = saveOptions.title || defaultSaveTitle.value
  const description = saveOptions.description || ''
  const tags = normalizeTags(saveOptions.tags)
  const visibility = saveOptions.visibility || 'private'
  const coverImage = saveOptions.coverImage || ''

  const payload = {
    ...(wasEditing ? { plan_id: editingTripId.value } : {}),
    tripMeta: tripMeta.value,
    timelineDays: timelineDays.value,
    budgetEstimate: budgetEstimate.value,
    packingList: packingList.value,
    weatherForecast: weatherForecast.value,
    auroraForecast: auroraForecast.value,
    summary: aiSummary.value,
    tags,
    title,
    description,
    visibility,
    coverImage,
    savedAt: new Date().toISOString(),
  }

  try {
    const result = await savePlannerDraft(payload)
    const tripRecord = {
      id: Number(result?.plan_id || Date.now()),
      title,
      country: tripMeta.value.country,
      duration: tripMeta.value.duration,
      startDate: tripMeta.value.startDate,
      endDate: tripMeta.value.endDate,
      style: tripMeta.value.style,
      season: tripMeta.value.season,
      tripType: tripMeta.value.tripType,
      tripMeta: { ...tripMeta.value },
      timelineDays: timelineDays.value.map((day) => ({ ...day, items: [...day.items] })),
      packingList: packingList.value.map((item) => ({ ...item })),
      budgetEstimate: { ...budgetEstimate.value },
      weatherForecast: weatherForecast.value.map((item) => ({ ...item })),
      auroraForecast: Array.isArray(auroraForecast.value)
        ? auroraForecast.value.map((item) => ({ ...item }))
        : auroraForecast.value,
      summary: aiSummary.value,
      tags,
      description,
      visibility,
      coverImage,
      savedAt: result?.savedAt || new Date().toISOString(),
    }

    upsertTrip(tripRecord)
    selectedTripId.value = tripRecord.id
    editingTripId.value = null
    mode.value = 'view'
    currentStep.value = 0
    maxUnlockedStep.value = 0
    isSaveModalOpen.value = false
    saveMessage.value = wasEditing
      ? t('planner.messages.editSaved')
      : visibility === 'public'
        ? t('planner.messages.published')
        : t('planner.messages.privateSaved')
  } catch {
    localStorage.setItem('trip_planner_draft', JSON.stringify(payload))
    isSaveModalOpen.value = false
    saveMessage.value = t('planner.messages.localSaved')
  }

  window.setTimeout(() => {
    saveMessage.value = ''
  }, 2200)
}

onMounted(async () => {
  scrollPlannerToTop('auto')

  try {
    const drafts = await loadPlannerDrafts()
    if (!drafts.length) return

    trips.value = drafts.map((draft) => buildTripRecordFromDraft(draft))
    const requestedJourneyId = Number(route.query.journey || 0)
    if (requestedJourneyId && trips.value.some((trip) => trip.id === requestedJourneyId)) {
      selectedTripId.value = requestedJourneyId
      mode.value = 'view'
    } else {
      selectedTripId.value = null
      mode.value = 'list'
    }
  } catch {
    const local = localStorage.getItem('trip_planner_draft')
    if (!local) return

    try {
      const parsed = JSON.parse(local)
      if (parsed.tripMeta) tripMeta.value = { ...tripMeta.value, ...parsed.tripMeta }
      if (Array.isArray(parsed.timelineDays) && parsed.timelineDays.length) timelineDays.value = parsed.timelineDays
      if (Array.isArray(parsed.packingList) && parsed.packingList.length) packingList.value = parsed.packingList
      if (parsed.budgetEstimate) aiBudgetEstimate.value = normalizeBudgetEstimate(parsed.budgetEstimate)
      if (Array.isArray(parsed.weatherForecast)) aiWeatherForecast.value = parsed.weatherForecast
      if (parsed.auroraForecast) aiAuroraForecast.value = parsed.auroraForecast
      if (parsed.summary) aiSummary.value = String(parsed.summary)

      const localTrip = {
        id: Date.now(),
        title: `${parsed.tripMeta?.country || 'Nordic'} Trip Plan`,
        country: parsed.tripMeta?.country || 'Norway',
        duration: Number(parsed.tripMeta?.duration || 1),
        startDate: parsed.tripMeta?.startDate || '',
        endDate: parsed.tripMeta?.endDate || '',
        style: parsed.tripMeta?.style || 'Adventure',
        season: parsed.tripMeta?.season || 'Winter',
        tripType: parsed.tripMeta?.tripType || 'Couple',
        tripMeta: parsed.tripMeta || {},
        timelineDays: Array.isArray(parsed.timelineDays) ? parsed.timelineDays : [],
        packingList: Array.isArray(parsed.packingList) ? parsed.packingList : [],
        budgetEstimate: parsed.budgetEstimate || null,
        weatherForecast: Array.isArray(parsed.weatherForecast) ? parsed.weatherForecast : [],
        auroraForecast: parsed.auroraForecast || null,
        summary: parsed.summary || '',
        tags: normalizeTags(parsed.tags),
        description: parsed.description || '',
        visibility: parsed.visibility || 'private',
        coverImage: parsed.coverImage || '',
        savedAt: parsed.savedAt || new Date().toISOString(),
      }

      trips.value = [localTrip]
      selectedTripId.value = null
      mode.value = 'list'
    } catch {
      // ignore invalid local cache
    }
  }
})

watch(currentStep, () => {
  scrollPlannerToTop()
})

watch(mode, () => {
  scrollPlannerToTop()
})

function buildTripRecordFromDraft(draft) {
  return {
    id: Number(draft.plan_id || Date.now()),
    title: draft.title || `${draft.tripMeta?.country || 'Nordic'} Trip Plan`,
    country: draft.tripMeta?.country || 'Norway',
    duration: Number(draft.tripMeta?.duration || 1),
    startDate: draft.tripMeta?.startDate || '',
    endDate: draft.tripMeta?.endDate || '',
    style: draft.tripMeta?.style || 'Adventure',
    season: draft.tripMeta?.season || 'Winter',
    tripType: draft.tripMeta?.tripType || 'Couple',
    tripMeta: draft.tripMeta || {},
    timelineDays: Array.isArray(draft.timelineDays) ? draft.timelineDays : [],
    packingList: Array.isArray(draft.packingList) ? draft.packingList : [],
    budgetEstimate: draft.budgetEstimate || null,
    weatherForecast: Array.isArray(draft.weatherForecast) ? draft.weatherForecast : [],
    auroraForecast: draft.auroraForecast || null,
    summary: draft.summary || '',
    tags: normalizeTags(draft.tags),
    description: draft.description || '',
    visibility: draft.visibility || 'private',
    coverImage: draft.coverImage || '',
    savedAt: draft.savedAt || new Date().toISOString(),
    updatedAt: draft.updatedAt || draft.savedAt || new Date().toISOString(),
  }
}

function toCommunityTag(value) {
  return String(value || '')
    .trim()
    .replace(/&/g, ' and ')
    .split(/[^A-Za-z0-9]+/)
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('')
}

function normalizeTags(tags) {
  if (!Array.isArray(tags)) return []
  const seen = new Set()
  return tags
    .map((tag) => String(tag || '').trim().replace(/^#/, ''))
    .filter(Boolean)
    .filter((tag) => {
      const key = tag.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return true
    })
    .slice(0, 8)
}
</script>

<style scoped>
.planner-page {
  width: 100%;
  min-height: calc(100vh - 24px);
  padding: 50px 0 110px;
  background: rgb(var(--v-theme-background));
  overflow: visible;
}

.planner-wrap {
  width: min(90%, 1380px);
  margin: 0 auto;
}

.planner-header {
  margin-bottom: 70px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.planner-header h1 {
  margin-top: 40px;
  line-height: 1.3;
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3.2vw, 2.7rem);
  font-weight: 700;
  letter-spacing: 0.02em;
}

.planner-border-image {
  display: block;
  width: min(100%, 920px);
  height: auto;
  margin-top: -30px;
}

.planner-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
  align-items: start;
  overflow: visible;
}

.planner-layout.is-split {
  grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
}

.save-msg {
  margin-top: 18px;
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

@media (max-width: 1250px) {
  .planner-page {
    padding: 108px 0 90px;
  }

  .planner-wrap {
    width: min(95%, 920px);
  }

  .planner-header {
    margin-bottom: 54px;
  }

  .planner-header h1 {
    margin-top: 28px;
    font-size: clamp(1.7rem, 4vw, 2.4rem);
  }

  .planner-border-image {
    width: min(100%, 780px);
    margin-top: -24px;
  }

  .planner-layout,
  .planner-layout.is-split {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .planner-layout :deep(.triplist-panel) {
    order: 1;
    position: relative !important;
    top: auto !important;
    max-height: none !important;
    overflow: visible !important;
    z-index: 1;
  }
}

@media (max-width: 900px) {
  .planner-page {
    padding: 104px 0 84px;
  }

  .planner-wrap {
    width: min(94%, 760px);
  }

  .planner-header {
    margin-bottom: 44px;
  }

  .planner-header h1 {
    margin-top: 20px;
    font-size: clamp(1.55rem, 5vw, 2.1rem);
  }

  .planner-border-image {
    width: min(100%, 620px);
    margin-top: -18px;
  }

  .planner-layout,
  .planner-layout.is-split {
    gap: 22px;
  }

  .planner-layout > * {
    width: 100%;
    min-width: 0;
  }
}

@media (max-width: 600px) {
  .planner-page {
    padding: 98px 0 72px;
  }

  .planner-wrap {
    width: 100%;
    padding: 0 16px;
  }

  .planner-header {
    margin-bottom: 34px;
  }

  .planner-header h1 {
    margin-top: 12px;
    font-size: clamp(1.35rem, 6vw, 1.8rem);
  }

  .planner-border-image {
    width: min(100%, 430px);
    margin-top: -12px;
  }

  .planner-layout,
  .planner-layout.is-split {
    gap: 18px;
  }
}

@media print {
  .planner-page {
    background: #fff;
    color: #000;
  }

  .planner-layout,
  .planner-layout.is-split {
    grid-template-columns: 1fr;
  }
}
</style>
