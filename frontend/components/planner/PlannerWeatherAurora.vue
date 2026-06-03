<template>
  <section class="planner-card weather-aurora-grid">
    <div class="forecast-card weather-card">
      <div class="card-head">
        <div>
          <p class="eyebrow">Weather</p>
          <h2>Weather Forecast</h2>
        </div>
        <i class="bi bi-cloud-sun"></i>
      </div>

      <div v-if="featuredWeather" class="weather-feature-card">
        <div class="weather-feature-meta">
          <div>
            <p>Day 1</p>
            <span>{{ formatDate(featuredWeather.date) }}</span>
          </div>
          <span class="forecast-badge" :class="featuredWeather.type || 'forecast'">
            {{ forecastTypeLabel(featuredWeather.type) }}
          </span>
        </div>
        <strong>{{ formatTemp(featuredWeather.temp) }}</strong>
        <span class="feature-condition">
          <i :class="['bi', weatherIcon(featuredWeather.condition)]"></i>
          {{ featuredWeather.condition }}
        </span>
      </div>

      <div v-if="remainingWeather.length" class="weather-slider">
        <div
          v-for="(item, idx) in remainingWeather"
          :key="`${item.date || 'weather'}-${idx}`"
          class="weather-mini-card"
        >
          <div>
            <p>Day {{ idx + 2 }}</p>
            <span>{{ formatDate(item.date) }}</span>
            <small :class="['forecast-badge', item.type || 'forecast']">
              {{ forecastTypeLabel(item.type) }}
            </small>
          </div>
          <div class="mini-weather-meta">
            <strong>{{ formatTemp(item.temp) }}</strong>
            <em>
              <i :class="['bi', weatherIcon(item.condition)]"></i>
              {{ item.condition }}
            </em>
          </div>
        </div>
      </div>
    </div>

    <div class="forecast-card aurora-card">
      <div class="card-head">
        <div>
          <p class="eyebrow">Aurora</p>
          <h2>Best Aurora Night</h2>
        </div>
        <i class="bi bi-stars"></i>
      </div>

      <div class="best-aurora">
        <p class="best-date">{{ formatDate(bestAurora.date) }}</p>
        <p class="chance">{{ bestAurora.chance }}%</p>
        <p class="label">Highest chance to view aurora</p>
      </div>

      <div class="aurora-meta">
        <span
          >KP Index: <strong>{{ bestAurora.kp }}</strong></span
        >
        <span
          >Best Time: <strong>{{ bestAurora.window }}</strong></span
        >
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  weather: { type: Array, required: true },
  aurora: { type: [Object, Array], required: true },
})

const featuredWeather = computed(() => props.weather[0] || null)
const remainingWeather = computed(() => props.weather.slice(1))

const bestAurora = computed(() => {
  if (Array.isArray(props.aurora)) {
    return props.aurora.reduce((best, item) => {
      return Number(item.chance || 0) > Number(best.chance || 0) ? item : best
    }, props.aurora[0] || fallbackAurora())
  }

  return {
    date: props.aurora.date || props.aurora.bestDate || 'Best available night',
    chance: props.aurora.chance || 0,
    kp: props.aurora.kp || 0,
    window: props.aurora.window || '10:00 PM - 2:00 AM',
  }
})

function fallbackAurora() {
  return {
    date: 'Best available night',
    chance: 0,
    kp: 0,
    window: '10:00 PM - 2:00 AM',
  }
}

function formatDate(dateValue) {
  if (!dateValue || dateValue === 'Best available night') return dateValue || '—'

  const date = new Date(`${dateValue}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateValue

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
  }).format(date)
}

function formatTemp(temp) {
  if (temp === null || temp === undefined || temp === '') return '—'
  if (typeof temp === 'number') return `${Math.round(temp)}°C`

  const value = String(temp).replaceAll('Â', '').trim()
  return value.includes('°C') ? value : `${value}°C`
}

function forecastTypeLabel(type) {
  return type === 'seasonal' ? 'Estimate' : 'Forecast'
}

function weatherIcon(condition) {
  const value = String(condition || '').toLowerCase()

  if (value.includes('clear') || value.includes('sun')) return 'bi-sun-fill'
  if (value.includes('partly')) return 'bi-cloud-sun-fill'
  if (value.includes('snow')) return 'bi-snow'
  if (value.includes('rain')) return 'bi-cloud-rain-fill'
  if (value.includes('drizzle')) return 'bi-cloud-drizzle-fill'
  if (value.includes('fog') || value.includes('mist')) return 'bi-cloud-fog-fill'
  if (value.includes('thunder')) return 'bi-cloud-lightning-rain-fill'
  if (value.includes('wind')) return 'bi-wind'
  return 'bi-cloud-fill'
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 24px;
  padding: 22px;
}

.weather-aurora-grid {
  display: grid;
  grid-template-columns: minmax(0, 6fr) minmax(260px, 4fr);
  gap: 24px;
}

.forecast-card {
  min-height: 280px;
  border-radius: 30px;
  padding: 24px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: rgba(var(--v-theme-background), 0.38);
  display: grid;
  align-content: start;
  gap: 18px;
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  font-weight: 800;
  color: rgb(var(--v-theme-primary));
}

h2 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 900;
  color: rgb(var(--v-theme-text));
}

.card-head i {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  font-size: 1.3rem;
}

.weather-feature-card {
  min-height: 150px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 38px;
  padding: 24px;
  background:
    radial-gradient(circle at top right, rgba(var(--v-theme-primary), 0.16), transparent 38%),
    rgba(var(--v-theme-surface), 0.72);
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: start;
  gap: 14px;
}

.weather-feature-meta {
  display: grid;
  gap: 12px;
}

.weather-feature-card p,
.weather-mini-card p {
  margin: 0;
  text-transform: uppercase;
  font-size: 1rem;
  font-weight: 830;
  color: rgb(var(--v-theme-text));
}

.weather-feature-card span,
.weather-mini-card span {
  display: block;
  margin-top: 4px;
  font-size: 0.86rem;
  font-weight: 650;
  color: rgba(var(--v-theme-text), 0.62);
}

.weather-feature-card strong {
  font-size: clamp(1.8rem, 4.0vw, 3.0rem);
  line-height: 0.95;
  font-weight: 600;
  color: rgb(var(--v-theme-text));
  text-align: right;
  white-space: nowrap;
}

.feature-condition {
  grid-column: 2;
  justify-self: end;
  align-self: end;
  text-transform: uppercase;
  font-size: 1rem;
  color: rgba(var(--v-theme-text), 0.78);
  display: inline-flex !important;
  align-items: center;
  gap: 8px;
  text-align: right;
}

.forecast-badge {
  width: fit-content;
  margin-top: 0 !important;
  border-radius: 999px;
  padding: 5px 10px;
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary)) !important;
  font-size: 0.7rem !important;
  font-weight: 850 !important;
  text-transform: uppercase;
}

.forecast-badge.seasonal {
  background: rgba(245, 158, 11, 0.16);
  color: #d97706 !important;
}

.weather-slider {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding: 4px 4px 14px;
  scrollbar-color: rgba(var(--v-theme-primary), 0.6) rgba(var(--v-theme-on-surface), 0.14);
  scroll-snap-type: x proximity;
}

.weather-mini-card {
  flex: 0 0 240px;
  min-height: 110px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 24px;
  padding: 18px;
  background: rgba(var(--v-theme-surface), 0.72);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  scroll-snap-align: start;
}

.mini-weather-meta {
  display: grid;
  justify-items: end;
  gap: 8px;
}

.mini-weather-meta strong {
  font-size: 1.45rem;
  line-height: 1;
  font-weight: 600;
  color: rgb(var(--v-theme-text));
  text-align: right;
  white-space: nowrap;
}

.mini-weather-meta em {
  text-transform: uppercase;
  font-style: normal;
  font-size: 0.78rem;
  font-weight: 600;
  color: rgba(var(--v-theme-text), 0.72);
  text-align: right;
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.aurora-card {
  background:
    radial-gradient(circle at top right, rgba(var(--v-theme-primary), 0.2), transparent 35%),
    rgba(var(--v-theme-background), 0.38);
}

.best-aurora {
  text-align: center;
  padding: 24px 16px;
  border-radius: 24px;
  background: rgba(var(--v-theme-surface), 0.72);
}

.best-date {
  margin: 0;
  font-size: 1rem;
  font-weight: 850;
  color: rgba(var(--v-theme-text), 0.78);
}

.chance {
  margin: 10px 0 2px;
  font-size: 3.2rem;
  line-height: 1;
  font-weight: 950;
  color: rgb(var(--v-theme-primary));
}

.label {
  margin: 0;
  font-size: 0.85rem;
  color: rgba(var(--v-theme-text), 0.68);
}

.aurora-meta {
  display: grid;
  gap: 8px;
  font-size: 0.9rem;
  color: rgba(var(--v-theme-text), 0.78);
}

.aurora-meta strong {
  color: rgb(var(--v-theme-text));
}

@media (max-width: 900px) {
  .weather-aurora-grid {
    grid-template-columns: 1fr;
  }

  .weather-feature-card {
    grid-template-columns: 1fr;
  }

  .weather-feature-card strong,
  .feature-condition {
    grid-column: auto;
    justify-self: start;
    text-align: left;
  }
}
</style>
