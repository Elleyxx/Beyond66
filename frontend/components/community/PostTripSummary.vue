<template>
  <section class="panel">
    <p class="eyebrow">Trip Summary</p>
    <h2>{{ meta.country || routeLabel }} Journey</h2>
    <p v-if="summary" class="summary-text">{{ summary }}</p>

    <div class="summary-grid">
      <div>
        <span>Country</span>
        <strong>{{ meta.country || routeLabel }}</strong>
      </div>

      <div>
        <span>Dates</span>
        <strong>{{ dateRange }}</strong>
      </div>

      <div>
        <span>Duration</span>
        <strong>{{ meta.duration || 0 }} days</strong>
      </div>

      <div>
        <span>Budget</span>
        <strong>{{ meta.budget || 'Not set' }}</strong>
      </div>

      <div>
        <span>Style</span>
        <strong>{{ meta.style || 'Not set' }}</strong>
      </div>

      <div>
        <span>Season</span>
        <strong>{{ meta.season || 'Any season' }}</strong>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  trip: { type: Object, default: null },
})

const meta = computed(() => props.trip?.meta || props.trip?.tripMeta || {})
const summary = computed(() => props.trip?.summary || '')
const routeLabel = computed(() => Array.isArray(meta.value.countryRoute) ? meta.value.countryRoute.join(' -> ') : 'Nordic')
const dateRange = computed(() => {
  if (!meta.value.startDate || !meta.value.endDate) return 'Dates not set'
  return `${meta.value.startDate} - ${meta.value.endDate}`
})
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 28px;
  padding: clamp(20px, 3vw, 28px);
  background:
    linear-gradient(135deg, rgba(var(--v-theme-primary), 0.12), transparent 58%),
    rgba(var(--v-theme-background), 0.36);
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

h2 {
  margin: 0;
  font-size: clamp(1.5rem, 3vw, 2.35rem);
  line-height: 1.12;
  font-weight: 900;
}

.summary-text {
  margin: 14px 0 0;
  max-width: 680px;
  color: rgba(var(--v-theme-text), 0.76);
  line-height: 1.65;
}

.summary-grid {
  margin-top: 22px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.summary-grid div {
  min-height: 86px;
  padding: 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.74);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

span {
  display: block;
  margin-bottom: 6px;
  color: rgba(var(--v-theme-text), 0.58);
  font-size: 0.82rem;
  font-weight: 800;
}

strong {
  color: rgb(var(--v-theme-text));
  font-size: 1rem;
  font-weight: 900;
}

@media (max-width: 720px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>
