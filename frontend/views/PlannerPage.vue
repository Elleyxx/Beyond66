<template>
  <main class="planner-page" :class="{ 'is-dark': isDark }">
    <header class="planner-header">
      <h1>Nordic Trip Planner</h1>
      <img
        class="planner-border-image"
        src="/assets/images/plan_border.png"
        alt="Planner border"
      />
    </header>

    <div class="planner-layout">
      <section class="planning-column">
        <div class="stepper-header" :style="{ '--step-count': steps.length }">
          <div class="progress-timeline">
            <div
              v-for="(step, index) in steps"
              :key="step.title"
              class="timeline-step"
              :class="{
                completed: index < currentStep,
                active: index === currentStep,
                pending: index > currentStep,
              }"
              role="button"
              tabindex="0"
              :aria-label="`Go to step ${index + 1}: ${step.title}`"
              @click="goToStep(index)"
              @keydown.enter="goToStep(index)"
              @keydown.space.prevent="goToStep(index)"
            >
              <span class="timeline-icon-ring" aria-hidden="true">
                <i :class="['bi', step.iconClass, 'timeline-icon-bi']"></i>
              </span>
              <div class="timeline-dot"></div>
              <p>{{ step.title }}</p>
            </div>
          </div>

          <div class="timeline-line-track">
            <div class="timeline-line-fill" :style="{ width: `${progressPercent}%` }"></div>
          </div>
          <p class="stepper-count">Step {{ currentStep + 1 }} of {{ steps.length }}</p>
        </div>

        <div class="planning-grid">
          <PlannerControls
            v-if="currentStep === 0"
            v-model="tripMeta"
          />

          <PlannerTimeline
            v-if="currentStep === 1"
            :timeline="timelineDays"
            @update:timeline="timelineDays = $event"
          />

          <PlannerBudget
            v-if="currentStep === 2"
            :estimate="budgetEstimate"
          />

          <PlannerWeatherAurora
            v-if="currentStep === 3"
            :weather="weatherForecast"
            :aurora="auroraForecast"
          />

          <PlannerChecklist
            v-if="currentStep === 4"
            :items="packingList"
            @toggle="togglePackingItem"
            @print="printChecklist"
          />

          <PlannerSummary
            v-if="currentStep === 5"
            :meta="tripMeta"
            :total-activities="totalActivities"
            :checked-count="checkedPackingCount"
            :checklist-count="packingList.length"
            @save="saveTrip"
          />
        </div>

        <div class="stepper-actions">
          <button
            class="step-btn secondary"
            :disabled="currentStep === 0"
            @click="prevStep"
          >
            Back
          </button>
          <button
            v-if="currentStep < steps.length - 1"
            class="step-btn primary"
            @click="nextStep"
          >
            Next
          </button>
          <button
            v-else
            class="step-btn primary"
            @click="saveTrip"
          >
            Save Trip
          </button>
        </div>
      </section>

      <aside class="route-aside">
        <PlannerRouteMap :timeline="timelineDays" />
      </aside>
    </div>

    <p v-if="saveMessage" class="save-msg">{{ saveMessage }}</p>
  </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useTheme } from 'vuetify'
import PlannerControls from '@/components/planner/PlannerControls.vue'
import PlannerTimeline from '@/components/planner/PlannerTimeline.vue'
import PlannerRouteMap from '@/components/planner/PlannerRouteMap.vue'
import PlannerBudget from '@/components/planner/PlannerBudget.vue'
import PlannerWeatherAurora from '@/components/planner/PlannerWeatherAurora.vue'
import PlannerChecklist from '@/components/planner/PlannerChecklist.vue'
import PlannerSummary from '@/components/planner/PlannerSummary.vue'

const theme = useTheme()
const isDark = computed(() => theme.global.current.value.dark)
const steps = [
  { title: 'Basics', iconClass: 'bi-passport' },
  { title: 'Timeline', iconClass: 'bi-signpost-split' },
  { title: 'Budget', iconClass: 'bi-cash-stack' },
  { title: 'Weather', iconClass: 'bi-cloud-sun' },
  { title: 'Checklist', iconClass: 'bi-list-check' },
  { title: 'Save', iconClass: 'bi-journal-check' },
]
const currentStep = ref(0)

const tripMeta = ref({
  country: 'Norway',
  duration: 5,
  style: 'Adventure',
  budget: 'Medium',
  season: 'Winter',
})

const timelineDays = ref(buildDays(tripMeta.value.duration))
const saveMessage = ref('')

const packingList = ref([
  { id: 1, name: 'Thermal base layers', checked: false },
  { id: 2, name: 'Waterproof jacket', checked: false },
  { id: 3, name: 'Power bank', checked: false },
  { id: 4, name: 'Travel documents', checked: false },
  { id: 5, name: 'Camera / phone tripod', checked: false },
])

watch(
  () => tripMeta.value.duration,
  (days) => {
    const safeDays = Math.min(30, Math.max(1, Number(days) || 1))
    const existing = timelineDays.value
    timelineDays.value = Array.from({ length: safeDays }, (_, i) => ({
      day: i + 1,
      items: existing[i]?.items ? [...existing[i].items] : [],
    }))
  },
)

const totalActivities = computed(() => timelineDays.value.reduce((sum, day) => sum + day.items.length, 0))
const checkedPackingCount = computed(() => packingList.value.filter((item) => item.checked).length)

const weatherForecast = computed(() => {
  return Array.from({ length: Math.min(tripMeta.value.duration, 7) }, (_, idx) => ({
    temp: temperatureByCountry(tripMeta.value.country) + idx,
    condition: ['Cloudy', 'Snow', 'Clear', 'Windy'][idx % 4],
  }))
})

const auroraForecast = computed(() => {
  const winterBoost = tripMeta.value.season === 'Winter' ? 20 : 0
  const nordicBoost = ['Norway', 'Sweden', 'Finland', 'Iceland'].includes(tripMeta.value.country) ? 15 : 0
  const chance = Math.min(95, 35 + winterBoost + nordicBoost)
  return {
    kp: Math.max(2.5, (chance / 20).toFixed(1)),
    chance,
    window: '22:00 - 02:30 local time',
  }
})

const budgetEstimate = computed(() => {
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
  }
})

const progressPercent = computed(() => {
  if (steps.length <= 1) return 100
  return (currentStep.value / (steps.length - 1)) * 100
})

function buildDays(days) {
  return Array.from({ length: days }, (_, i) => ({ day: i + 1, items: [] }))
}

function temperatureByCountry(country) {
  const map = { Norway: -4, Sweden: -3, Finland: -6, Iceland: 0, Denmark: 3 }
  return map[country] ?? 2
}

function togglePackingItem(id) {
  packingList.value = packingList.value.map((item) =>
    item.id === id ? { ...item, checked: !item.checked } : item,
  )
}

function printChecklist() {
  window.print()
}

function nextStep() {
  if (currentStep.value >= steps.length - 1) return
  currentStep.value += 1
}

function prevStep() {
  if (currentStep.value <= 0) return
  currentStep.value -= 1
}

function goToStep(index) {
  if (index < 0 || index > steps.length - 1) return
  currentStep.value = index
}

function saveTrip() {
  const payload = {
    tripMeta: tripMeta.value,
    timelineDays: timelineDays.value,
    budgetEstimate: budgetEstimate.value,
    packingList: packingList.value,
    savedAt: new Date().toISOString(),
  }

  localStorage.setItem('trip_planner_draft', JSON.stringify(payload))
  saveMessage.value = 'Trip saved locally.'
  window.setTimeout(() => {
    saveMessage.value = ''
  }, 2000)
}
</script>

<style scoped>
.planner-page {
  padding: 50px 18px 40px;
  background: rgb(var(--v-theme-background));
  height: calc(100vh - 24px);
  overflow: hidden;
}

.planner-header {
  margin-bottom: 80px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.planner-header h1 {
  margin: 0;
  line-height: 1.3;
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3.2vw, 2.7rem);
  font-weight: 700;
  letter-spacing: 0.02em;
  color: rgb(var(--v-theme-menuText));
}

.planner-page.is-dark .planner-header h1 {
  color: rgb(var(--v-theme-primary));
}

.planner-border-image {
  display: block;
  width: min(100%, 920px);
  height: auto;
  margin-top: -30px;
}

.planner-layout {
  display: grid;
  grid-template-columns: minmax(0, 7fr) minmax(320px, 3fr);
  gap: 14px;
  align-items: start;
  height: calc(100% - 130px);
  min-height: 0;
}

.planning-column {
  min-height: 0;
  height: 100%;
  overflow-y: auto;
  padding-right: 8px;
}

.planning-column::-webkit-scrollbar {
  width: 8px;
}

.planning-column::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.25);
  border-radius: 999px;
}

.planning-column::-webkit-scrollbar-track {
  background: transparent;
}

.planning-grid {
  display: grid;
  gap: 12px;
}

.stepper-header {
  margin-bottom: 12px;
  position: relative;
}

.progress-timeline {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 6px;
  align-items: end;
  margin-bottom: 8px;
  position: relative;
  z-index: 2;
}

.timeline-step {
  text-align: center;
  cursor: pointer;
  outline: none;
}

.timeline-step:focus-visible {
  border-radius: 10px;
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary), 0.35);
}

.timeline-icon-ring {
  width: 46px;
  height: 46px;
  margin: 0 auto;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-primary), 0.5);
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-primary), 0.08);
}

.timeline-icon-bi {
  font-size: 1.35rem;
  line-height: 1;
  color: rgb(var(--v-theme-primary));
}

.timeline-dot {
  width: 14px;
  height: 14px;
  margin: 8px auto 6px;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
  background: rgba(var(--v-theme-surface), 1);
  position: relative;
  z-index: 3;
}

.timeline-step p {
  margin: 0;
  font-size: 0.78rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.42);
}

.timeline-step.pending .timeline-icon-ring {
  border-color: rgba(var(--v-theme-on-surface), 0.24);
  background: rgba(var(--v-theme-on-surface), 0.04);
}

.timeline-step.pending .timeline-icon-bi {
  color: rgba(var(--v-theme-on-surface), 0.42);
}

.timeline-step.completed .timeline-dot,
.timeline-step.active .timeline-dot {
  border-color: rgba(var(--v-theme-primary), 0.95);
  background: rgba(var(--v-theme-primary), 0.95);
}

.timeline-step.completed p,
.timeline-step.active p {
  color: rgb(var(--v-theme-text));
}

.timeline-step.active p {
  color: rgb(var(--v-theme-primary));
}

.timeline-line-track {
  position: absolute;
  left: calc(100% / (var(--step-count) * 2));
  right: calc(100% / (var(--step-count) * 2));
  top: 61px;
  height: 4px;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.18);
  overflow: hidden;
  z-index: 1;
}

.timeline-line-fill {
  height: 100%;
  background: linear-gradient(90deg, rgba(var(--v-theme-primary), 0.55), rgba(var(--v-theme-primary), 1));
  transition: width 0.3s ease;
}

.stepper-count {
  margin: 8px 0 0;
  font-size: 0.82rem;
  color: rgba(var(--v-theme-text), 0.75);
}

.stepper-actions {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.step-btn {
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
}

.step-btn.primary {
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
}

.step-btn.secondary {
  color: rgb(var(--v-theme-text));
  background: rgba(var(--v-theme-surface), 0.9);
  border-color: rgba(var(--v-theme-on-surface), 0.2);
}

.step-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.route-aside {
  position: sticky;
  top: 12px;
  align-self: start;
}

.save-msg {
  margin-top: 12px;
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

@media (max-width: 1200px) {
  .planner-page {
    height: auto;
    overflow: visible;
  }

  .planner-layout {
    grid-template-columns: 1fr;
    height: auto;
  }

  .planning-column {
    height: auto;
    overflow: visible;
    padding-right: 0;
  }

  .route-aside {
    position: static;
  }
}

@media print {
  .planner-page {
    background: #fff;
    color: #000;
  }

  .planner-layout {
    grid-template-columns: 1fr;
  }
}
</style>
