<template>
  <section class="planner-card summary-card">
    <div class="summary-hero">
      <div>
        <p class="eyebrow">Trip Summary</p>
        <h2>{{ tripTitle }}</h2>
        <p class="hero-meta">
          {{ dateRange }} · {{ meta.duration || 0 }} days · {{ meta.pax || 1 }} pax
        </p>
      </div>

    </div>

    <p v-if="summary" class="ai-summary">
      {{ summary }}
    </p>

    <div class="summary-grid">
      <div class="summary-section preferences-card">
        <h3>Travel Preferences</h3>

        <div class="info-list">
          <span>Countries</span>
          <strong>{{ countryRoute }}</strong>

          <span>Travel Style</span>
          <strong>{{ meta.style || '—' }}</strong>

          <span>Season</span>
          <strong>{{ meta.season || '—' }}</strong>

          <span>Budget</span>
          <strong>{{ meta.budget || '—' }}</strong>

          <span>Accommodation</span>
          <strong>{{ meta.accommodation || '—' }}</strong>

          <span>Transport</span>
          <strong>{{ meta.transport || '—' }}</strong>

          <span>Trip Type</span>
          <strong>{{ meta.tripType || '—' }}</strong>

          <span>Activity Level</span>
          <strong>{{ meta.activityLevel || '—' }}</strong>
        </div>
      </div>

      <div class="summary-section progress-card">
        <h3>Progress Overview</h3>

        <div class="stat-grid">
          <div>
            <strong>{{ totalActivities }}</strong>
            <span>Activities</span>
          </div>

          <div>
            <strong>{{ checkedCount }}/{{ checklistCount }}</strong>
            <span>Packed Items</span>
          </div>

          <div>
            <strong>{{ timeline.length }}</strong>
            <span>Itinerary Days</span>
          </div>
        </div>
      </div>

     <div v-if="budget" class="summary-section budget-card">
        <h3>Budget Summary</h3>

        <div class="budget-total-row">
          <span>Total Estimated Budget</span>
          <strong class="budget-total">
            $ {{ Number(budget.total || 0).toLocaleString() }}
          </strong>
        </div>

        <div class="budget-divider"></div>

        <div class="budget-breakdown">
          <div class="budget-item">
            <span>Stay</span>
            <strong>$ {{ Number(budget.stay || 0).toLocaleString() }}</strong>
          </div>

          <div class="budget-item">
            <span>Transport</span>
            <strong>$ {{ Number(budget.transport || 0).toLocaleString() }}</strong>
          </div>

          <div class="budget-item">
            <span>Food</span>
            <strong>$ {{ Number(budget.food || 0).toLocaleString() }}</strong>
          </div>

          <div class="budget-item">
            <span>Activities</span>
            <strong>$ {{ Number(budget.activities || 0).toLocaleString() }}</strong>
          </div>
        </div>
      </div>

      <div class="summary-section weather-card">
        <h3>Weather & Aurora</h3>

        <div class="info-list">
          <span>Weather</span>
          <strong>{{ weatherLabel }}</strong>

          <span>Aurora Chance</span>
          <strong>{{ auroraLabel }}</strong>

          <span>Best Window</span>
          <strong>{{ auroraWindow }}</strong>
        </div>
      </div>

      <div class="summary-section full">
        <h3>Itinerary Timeline</h3>

        <div class="timeline-preview">
          <article v-for="day in timeline" :key="day.day" class="timeline-day">
            <div class="day-badge">
              Day {{ day.day }}
            </div>

            <div>
              <h4>{{ day.destination || day.country || 'Destination' }}</h4>
              <p>{{ day.country || '—' }}</p>

              <ul>
                <li v-for="(item, index) in day.items" :key="`${day.day}-${item}-${index}`">
                  {{ item }}
                </li>
                <li v-if="!day.items?.length" class="empty">No activities added yet</li>
              </ul>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: { type: Object, required: true },
  summary: { type: String, default: '' },
  timeline: { type: Array, default: () => [] },
  budget: { type: Object, default: null },
  weather: { type: Array, default: () => [] },
  aurora: { type: [Object, Array], default: null },
  totalActivities: { type: Number, required: true },
  checkedCount: { type: Number, required: true },
  checklistCount: { type: Number, required: true },
})

defineEmits(['back', 'open-save-modal'])

const tripTitle = computed(() => {
  const countries = Array.isArray(props.meta.countryRoute) && props.meta.countryRoute.length
    ? props.meta.countryRoute.join(' → ')
    : props.meta.country || 'Nordic Trip'

  return `${countries} Journey`
})

const countryRoute = computed(() => {
  if (Array.isArray(props.meta.countryRoute) && props.meta.countryRoute.length) {
    return props.meta.countryRoute.join(' → ')
  }

  return props.meta.country || '—'
})

const dateRange = computed(() => {
  if (!props.meta.startDate || !props.meta.endDate) return 'Dates not set'
  return `${formatDate(props.meta.startDate)} - ${formatDate(props.meta.endDate)}`
})

const weatherLabel = computed(() => {
  if (!props.weather.length) return '—'

  const first = props.weather[0]
  return `${formatTemp(first.temp)} · ${first.condition || '—'}`
})

const bestAurora = computed(() => {
  if (Array.isArray(props.aurora)) {
    return props.aurora.reduce((max, item) => {
      return Number(item.chance || 0) > Number(max.chance || 0) ? item : max
    }, props.aurora[0] || {})
  }

  return props.aurora || {}
})

const auroraLabel = computed(() => {
  return bestAurora.value?.chance !== undefined ? `${bestAurora.value.chance || 0}%` : '—'
})

const auroraWindow = computed(() => bestAurora.value?.window || '—')

function formatDate(value) {
  return new Intl.DateTimeFormat('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(`${value}T00:00:00`))
}

function formatTemp(temp) {
  if (temp === null || temp === undefined || temp === '') return '—'
  if (typeof temp === 'number') return `${Math.round(temp)}°C`

  const value = String(temp).replaceAll('Â', '').trim()
  return value.includes('°C') ? value : `${value}°C`
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 24px;
  padding: 24px;
}

.summary-card {
  display: grid;
  gap: 20px;
}

.summary-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  padding: 24px;
  border-radius: 28px;
  background:
    linear-gradient(135deg, rgba(var(--v-theme-primary), 0.14), transparent 55%),
    rgba(var(--v-theme-background), 0.42);
  border: 1px solid rgba(var(--v-theme-primary), 0.18);
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

h2 {
  margin: 0;
  font-size: clamp(1.6rem, 3vw, 2.4rem);
  font-weight: 900;
}

.hero-meta {
  margin: 8px 0 0;
  color: rgba(var(--v-theme-text), 0.68);
  font-weight: 700;
}

.ai-summary {
  margin: 0;
  padding: 16px 18px;
  border-radius: 18px;
  background: rgba(var(--v-theme-primary), 0.09);
  color: rgba(var(--v-theme-text), 0.82);
  line-height: 1.6;
}

.summary-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-areas:
    "preferences progress"
    "preferences weather"
    "budget budget"
    "itinerary itinerary";
  gap: 18px;
}

.preferences-card {
  grid-area: preferences;
}

.progress-card {
  grid-area: progress;
}

.weather-card {
  grid-area: weather;
}

.budget-card {
  grid-area: budget;
}

.budget-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.budget-total-row span {
  font-size: 0.9rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.65);
}

.budget-total {
  font-size: 2rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

.budget-divider {
  height: 1px;
  background: rgba(var(--v-theme-on-surface), 0.12);
  margin-bottom: 16px;
}

.budget-breakdown {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px 24px;
}

.budget-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  border-radius: 14px;
  background: rgba(var(--v-theme-surface), 0.65);
}

.budget-item span {
  font-size: 0.88rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.65);
}

.budget-item strong {
  font-size: 1rem;
  font-weight: 800;
}

.summary-section.full {
  grid-area: itinerary;
}

.summary-section {
  border-radius: 22px;
  padding: 20px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: rgba(var(--v-theme-background), 0.36);
}

.summary-section h3 {
  margin: 0 0 14px;
  font-size: 1rem;
  font-weight: 900;
}

.info-list {
  display: grid;
  grid-template-columns: 130px 1fr;
  gap: 10px 14px;
  align-items: start;
}

.info-list span {
  color: rgba(var(--v-theme-text), 0.55);
  font-size: 0.86rem;
  font-weight: 700;
}

.info-list strong {
  color: rgb(var(--v-theme-text));
  font-size: 0.9rem;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.stat-grid div {
  padding: 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.75);
  text-align: center;
}

.stat-grid strong {
  display: block;
  font-size: 1.5rem;
  color: rgb(var(--v-theme-primary));
}

.stat-grid span {
  font-size: 0.8rem;
  font-weight: 800;
  color: rgba(var(--v-theme-text), 0.62);
}

.timeline-preview {
  display: grid;
  gap: 14px;
}

.timeline-day {
  display: grid;
  grid-template-columns: 90px 1fr;
  gap: 16px;
  padding: 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.7);
}

.day-badge {
  height: fit-content;
  border-radius: 999px;
  padding: 8px 10px;
  text-align: center;
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  font-size: 0.82rem;
  font-weight: 900;
}

.timeline-day h4 {
  margin: 0;
  font-size: 1rem;
}

.timeline-day p {
  margin: 4px 0 10px;
  color: rgba(var(--v-theme-text), 0.62);
  font-size: 0.86rem;
}

.timeline-day ul {
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 4px;
}

.empty {
  opacity: 0.55;
  font-style: italic;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

button {
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 850;
  cursor: pointer;
}

button.primary {
  border: 0;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
}

button.secondary {
  border: 1px solid rgba(var(--v-theme-primary), 0.42);
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.budget-total {
  font-size: 1.2rem !important;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

.budget-divider {
  grid-column: 1 / -1;
  height: 1px;
  background: rgba(var(--v-theme-on-surface), 0.12);
  margin: 4px 0 8px;
}

@media (max-width: 900px) {
  .summary-hero,
  .actions {
    flex-direction: column;
  }

  .summary-grid {
    grid-template-columns: 1fr;
    grid-template-areas:
      "preferences"
      "progress"
      "weather"
      "budget"
      "itinerary";
  }

  .timeline-day {
    grid-template-columns: 1fr;
  }
}
</style>
