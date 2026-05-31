<template>
  <main class="planner-page">
    <div class="planner-grid">
      <aside class="left-col">
        <PlannerControls v-model="tripMeta" />
        <PlannerBudget :estimate="budgetEstimate" />
        <PlannerWeatherAurora :weather="weatherForecast" :aurora="auroraForecast" />
      </aside>

      <section class="center-col">
        <PlannerTimeline :timeline="timelineDays" @update:timeline="timelineDays = $event" />
      </section>

      <aside class="right-col">
        <PlannerRouteMap :timeline="timelineDays" />
        <PlannerChecklist :items="packingList" @toggle="togglePackingItem" @print="printChecklist" />
        <PlannerSummary
          :meta="tripMeta"
          :total-activities="totalActivities"
          :checked-count="checkedPackingCount"
          :checklist-count="packingList.length"
          @save="saveTrip"
        />
      </aside>
    </div>

    <p v-if="saveMessage" class="save-msg">{{ saveMessage }}</p>
  </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import PlannerControls from '@/components/planner/PlannerControls.vue'
import PlannerTimeline from '@/components/planner/PlannerTimeline.vue'
import PlannerRouteMap from '@/components/planner/PlannerRouteMap.vue'
import PlannerBudget from '@/components/planner/PlannerBudget.vue'
import PlannerWeatherAurora from '@/components/planner/PlannerWeatherAurora.vue'
import PlannerChecklist from '@/components/planner/PlannerChecklist.vue'
import PlannerSummary from '@/components/planner/PlannerSummary.vue'

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
  padding: 24px 18px 40px;
  background: rgb(var(--v-theme-background));
}

.planner-grid {
  display: grid;
  grid-template-columns: minmax(240px, 320px) minmax(440px, 1fr) minmax(240px, 340px);
  gap: 14px;
  align-items: start;
}

.left-col,
.right-col {
  display: grid;
  gap: 12px;
}

.save-msg {
  margin-top: 12px;
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

@media (max-width: 1200px) {
  .planner-grid {
    grid-template-columns: 1fr;
  }
}

@media print {
  .planner-page {
    background: #fff;
    color: #000;
  }

  .planner-grid {
    grid-template-columns: 1fr;
  }
}
</style>
