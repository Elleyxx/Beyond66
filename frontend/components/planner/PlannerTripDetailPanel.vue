<template>
  <section class="tripdetail-panel">
    <div v-if="trip" class="tripdetail-content">
      <div class="tripdetail-head">
        <h2>{{ trip.title }}</h2>
        <div class="tripdetail-actions">
          <button class="step-btn secondary" @click="$emit('edit')">{{ t('planner.actions.editTrip') }}</button>
          <button class="step-btn primary" @click="$emit('add')">{{ t('planner.actions.addTrip') }}</button>
        </div>
      </div>

      <p class="tripdetail-meta">
        {{ trip.country }} · {{ tripDateRange }} · {{ t('planner.list.days', { count: trip.duration }) }} · {{ trip.style }} · {{ trip.season }}
      </p>

      <PlannerSummary
        :meta="trip.tripMeta || {}"
        :summary="trip.summary || ''"
        :timeline="trip.timelineDays || []"
        :budget="trip.budgetEstimate || null"
        :weather="trip.weatherForecast || []"
        :aurora="trip.auroraForecast || null"
        :total-activities="totalActivities"
        :checked-count="checkedPackingCount"
        :checklist-count="checklistCount"
      />
    </div>

    <div v-else class="tripdetail-empty">
      <h3>{{ t('planner.detail.selectTrip') }}</h3>
      <p>{{ t('planner.detail.selectTripHint') }}</p>
      <button class="step-btn primary" @click="$emit('add')">{{ t('planner.actions.addTrip') }}</button>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PlannerSummary from '@/components/planner/PlannerSummary.vue'

const props = defineProps({
  trip: { type: Object, default: null },
})
defineEmits(['edit', 'add'])
const { t } = useI18n()

const tripDateRange = computed(() => {
  if (!props.trip?.startDate || !props.trip?.endDate) return t('planner.summary.datesNotSet')
  return `${formatDate(props.trip.startDate)} - ${formatDate(props.trip.endDate)}`
})

const totalActivities = computed(() => {
  return (props.trip?.timelineDays || []).reduce((sum, day) => sum + (day.items?.length || 0), 0)
})

const checkedPackingCount = computed(() => {
  return (props.trip?.packingList || []).filter((item) => item.checked).length
})

const checklistCount = computed(() => (props.trip?.packingList || []).length)

function formatDate(value) {
  return new Intl.DateTimeFormat('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(`${value}T00:00:00`))
}
</script>

<style scoped>
.tripdetail-panel {
  min-height: fit-content;
  height: auto;
  overflow: visible;
}

.tripdetail-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.tripdetail-head h2 {
  margin: 0;
}

.tripdetail-actions {
  display: flex;
  gap: 8px;
}

.tripdetail-meta {
  margin: 8px 0 14px;
  font-size: 0.86rem;
  color: rgba(var(--v-theme-text), 0.72);
}

.tripdetail-empty {
  min-height: 260px;
  display: grid;
  align-content: center;
  justify-items: start;
  gap: 8px;
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
</style>


