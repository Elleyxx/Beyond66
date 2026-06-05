<template>
  <section class="planner-card budget-page">
    <div class="budget-left">
      <div class="budget-ticket">
        <div class="ticket-main">
          <p class="eyebrow">{{ t('planner.budget.eyebrow') }}</p>

          <h2>$ {{ totalAmount.toLocaleString() }}</h2>

          <p class="budget-meta">
            {{ estimate.pax || 1 }} pax · {{ t('planner.list.days', { count: estimate.duration || 0 }) }} ·
            {{ estimate.country || t('countryNames.nordic') }}
          </p>

          <div class="ticket-info budget-status-main">
            <strong class="budget-status" :class="statusClass">
              {{ budgetStatus }}
            </strong>
          </div>
        </div>

        <div class="ticket-side">
          <div>
            <span>{{ t('planner.budget.recommended') }}</span>
            <strong>$ {{ suggestedAmount.toLocaleString() }}</strong>
          </div>

          <div>
            <span>{{ t('planner.budget.difference') }}</span>
            <strong>{{ budgetDifferenceText }}</strong>
          </div>

        </div>
      </div>

      <div class="breakdown-card">
        <div v-for="item in breakdown" :key="item.key" class="budget-row">
          <div class="budget-row-head">
            <span>{{ item.label }}</span>
            <strong>$ {{ item.value.toLocaleString() }}</strong>
          </div>

          <input
            type="range"
            min="0"
            :max="sliderMax"
            step="10"
            :value="item.value"
            :style="{ '--slider-color': item.color }"
            @input="updateBudget(item.key, $event.target.value)"
          />
        </div>
      </div>
    </div>

    <div class="budget-chart-panel">
      <div class="donut-area">
        <div class="donut-chart" :style="{ background: donutBackground }">
          <div class="donut-center">
            <span>{{ t('planner.budget.total') }}</span>
            <strong>$ {{ totalAmount.toLocaleString() }}</strong>
          </div>
        </div>

        <div class="donut-legend">
          <div v-for="item in breakdownWithPercent" :key="item.key" class="legend-item">
            <span class="legend-dot" :style="{ background: item.color }"></span>

            <div>
              <strong>{{ item.label }}</strong>
              <p>{{ item.percent }}% · $ {{ item.value.toLocaleString() }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="tips-card">
        <h3>{{ t('planner.budget.tips') }}</h3>

        <ul v-if="estimate.tips?.length">
          <li v-for="(tip, index) in estimate.tips" :key="index">
            {{ tip }}
          </li>
        </ul>

        <p v-else>
          {{ t('planner.budget.tipsFallback') }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  estimate: { type: Object, required: true },
})

const emit = defineEmits(['update:estimate'])
const { t } = useI18n()

const totalAmount = computed(() => Number(props.estimate.total || 0))

const sliderMax = computed(() =>
  Math.max(1000, Math.ceil(totalAmount.value * 1.5 / 100) * 100),
)

const breakdown = computed(() => [
  { key: 'stay', label: t('planner.budget.stay'), value: Number(props.estimate.stay || 0), color: '#2563eb' },
  { key: 'food', label: t('planner.budget.food'), value: Number(props.estimate.food || 0), color: '#f97316' },
  { key: 'transport', label: t('planner.budget.transport'), value: Number(props.estimate.transport || 0), color: '#14b8a6' },
  { key: 'activities', label: t('planner.budget.activities'), value: Number(props.estimate.activities || 0), color: '#a855f7' },
])

const breakdownTotal = computed(() =>
  breakdown.value.reduce((sum, item) => sum + item.value, 0),
)

const breakdownWithPercent = computed(() => {
  const total = breakdownTotal.value || 1

  return breakdown.value.map((item) => ({
    ...item,
    percent: Math.round((item.value / total) * 100),
  }))
})

const donutBackground = computed(() => {
  const total = breakdownTotal.value || 1
  let progress = 0

  const stops = breakdown.value.map((item) => {
    const start = progress
    const size = (item.value / total) * 100
    progress += size

    return `${item.color} ${start}% ${progress}%`
  })

  return `conic-gradient(${stops.join(', ')})`
})

function updateBudget(key, value) {
  const next = {
    ...props.estimate,
    [key]: Number(value),
  }

  next.total =
    Number(next.stay || 0) +
    Number(next.food || 0) +
    Number(next.transport || 0) +
    Number(next.activities || 0)

  emit('update:estimate', next)
}

const suggestedAmount = computed(() =>
  Number(
    props.estimate.suggestedTotal ||
    props.estimate.aiTotal ||
    props.estimate.originalTotal ||
    props.estimate.recommendedTotal ||
    0,
  ),
)

const budgetDifference = computed(() => totalAmount.value - suggestedAmount.value)

const budgetDifferenceText = computed(() => {
  const diff = budgetDifference.value

  if (diff === 0) return t('planner.budget.onTarget')
  if (diff > 0) return `+$ ${diff.toLocaleString()}`

  return `-$ ${Math.abs(diff).toLocaleString()}`
})

const budgetStatus = computed(() => {
  const diff = budgetDifference.value
  const recommended = suggestedAmount.value

  if (!recommended) return t('planner.budget.noRecommendation')

  const percentage = Math.abs(diff) / recommended

  if (percentage <= 0.1) {
    return t('planner.budget.withinRange')
  }

  if (diff > 0) {
    return t('planner.budget.aboveRecommendation')
  }

  return t('planner.budget.belowRecommendation')
})

const statusClass = computed(() => {
  const diff = budgetDifference.value
  const recommended = suggestedAmount.value

  if (!recommended) return 'neutral'

  const percentage = Math.abs(diff) / recommended

  if (percentage <= 0.1) {
    return 'good'
  }

  if (diff > 0) {
    return 'warning'
  }

  return 'good'
})
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 24px;
  padding: 22px;
}

.budget-page {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
  gap: 28px;
  align-items: stretch;
}

.budget-left {
  display: grid;
  gap: 18px;
}

.budget-ticket {
  position: relative;
  overflow: hidden;
  border-radius: 34px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.18);
  background:
    linear-gradient(135deg, rgba(var(--v-theme-primary), 0.12), transparent 45%),
    rgba(var(--v-theme-background), 0.42);
  display: grid;
  grid-template-columns: minmax(0, 1fr) 190px;
}

.ticket-main {
  padding: 0 30px;
  align-content: center;
}

.ticket-main .eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.9rem;
  font-weight: 750;
  color: rgb(var(--v-theme-primary));
}

.ticket-main h2 {
  margin: 8px 0;
  font-size: clamp(2rem, 5vw, 3rem);
  line-height: 1;
  font-weight: 680;
  color: rgb(var(--v-theme-text));
}

.ticket-side {
  position: relative;
  padding: 26px 24px;
  border-left: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  display: grid;
  align-content: center;
  gap: 18px;
}

.ticket-side span {
  display: block;
  margin-bottom: 5px;
  font-size: 0.75rem;
  font-weight: 650;
  text-transform: uppercase;
  color: rgba(var(--v-theme-text), 0.55);
}

.ticket-side strong {
  font-size: 1rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

.budget-status {
  font-size: 0.9rem !important;
  font-weight: 900;
}

.budget-status.good {
  color: #10b981;
  border: 1px solid rgba(var(--v-theme-success));
  border-radius: 12px;
  padding: 4px 10px;
}

.budget-status.warning {
  color: #ef4444;
  border: 1px solid rgba(var(--v-theme-error));
  border-radius: 12px;
  padding: 4px 10px;
}

.budget-status.neutral {
  color: rgb(var(--v-theme-primary));
}

@media (max-width: 700px) {
  .budget-ticket {
    grid-template-columns: 1fr;
  }

  .ticket-side {
    border-left: 0;
    border-top: 2px dashed rgba(var(--v-theme-on-surface), 0.24);
  }
}

.budget-meta {
  margin: 0;
  font-size: 1rem;
  font-weight: 650;
  color: rgba(var(--v-theme-text), 0.65);
}

.budget-status-main {
  margin-top: 30px;
}

.breakdown-card,
.budget-chart-panel {
  border-radius: 30px;
  padding: 24px 28px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.18);
  background: rgba(var(--v-theme-background), 0.38);
}

.budget-chart-panel {
  display: grid;
  gap: 22px;
  align-content: start;
}

.donut-area {
  display: grid;
  gap: 22px;
  justify-items: center;
}

.donut-chart {
  width: min(100%, 250px);
  aspect-ratio: 1;
  border-radius: 50%;
  padding: 34px;
  box-shadow: 0 18px 38px rgba(0, 0, 0, 0.12);
}

.donut-center {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgb(var(--v-theme-surface));
  display: grid;
  place-items: center;
  text-align: center;
  align-content: center;
  gap: 4px;
}

.donut-center span {
  font-size: 0.75rem;
  font-weight: 800;
  color: rgba(var(--v-theme-text), 0.55);
  text-transform: uppercase;
}

.donut-center strong {
  font-size: 1.15rem;
  font-weight: 900;
  color: rgb(var(--v-theme-text));
}

.donut-legend {
  width: 100%;
  display: grid;
  gap: 10px;
}

.legend-item {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 10px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 16px;
  background: rgba(var(--v-theme-surface), 0.72);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-item strong {
  display: block;
  font-size: 0.9rem;
  color: rgb(var(--v-theme-text));
}

.legend-item p {
  margin: 3px 0 0;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-text), 0.65);
}

.budget-row {
  display: grid;
  gap: 10px;
  padding: 10px 0;
}

.budget-row-head {
  display: grid;
  grid-template-columns: minmax(120px, 1fr) auto;
  align-items: center;
  gap: 16px;
}

.budget-row-head span {
  color: rgb(var(--v-theme-text));
  font-size: 1rem;
  font-weight: 900;
}

.budget-row-head strong {
  padding-left: 16px;
  border-left: 2px solid rgba(var(--v-theme-on-surface), 0.2);
  color: rgb(var(--v-theme-text));
  font-size: 1rem;
}

.budget-row input[type='range'] {
  width: 100%;
  accent-color: var(--slider-color);
  cursor: pointer;
}

.tips-card {
  border-radius: 24px;
  padding: 18px 20px;
  border: 2px solid rgba(var(--v-theme-primary), 0.18);
  background: rgba(var(--v-theme-primary), 0.06);
}

.tips-card h3 {
  margin: 0 0 12px;
  font-size: 1rem;
  font-weight: 900;
}

.tips-card ul {
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 8px;
}

.tips-card li,
.tips-card p {
  margin: 0;
  color: rgba(var(--v-theme-text), 0.78);
  line-height: 1.5;
  font-size: 0.88rem;
}

@media (max-width: 900px) {
  .budget-page,
  .budget-hero {
    grid-template-columns: 1fr;
  }

  .budget-hero h2 {
    justify-self: start;
  }
}
</style>
