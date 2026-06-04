<template>
  <section class="panel">
    <h2>Trip Summary</h2>
    <div class="summary-grid">
      <span>Country</span>
      <strong>{{ meta.country || routeLabel }}</strong>
      <span>Dates</span>
      <strong>{{ dateRange }}</strong>
      <span>Duration</span>
      <strong>{{ meta.duration || 0 }} days</strong>
      <span>Budget</span>
      <strong>{{ meta.budget || 'Not set' }}</strong>
      <span>Style</span>
      <strong>{{ meta.style || 'Not set' }}</strong>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  trip: { type: Object, default: null },
})

const meta = computed(() => props.trip?.meta || props.trip?.tripMeta || {})
const routeLabel = computed(() => Array.isArray(meta.value.countryRoute) ? meta.value.countryRoute.join(' -> ') : 'Nordic')
const dateRange = computed(() => {
  if (!meta.value.startDate || !meta.value.endDate) return 'Dates not set'
  return `${meta.value.startDate} - ${meta.value.endDate}`
})
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  padding: 18px;
  background: rgba(var(--v-theme-surface), 0.94);
}

h2 {
  margin: 0 0 14px;
  font-size: 1.1rem;
}

.summary-grid {
  display: grid;
  grid-template-columns: 130px 1fr;
  gap: 10px 14px;
}

span {
  color: rgba(var(--v-theme-text), 0.58);
  font-weight: 750;
}
</style>
