<template>
  <section class="tripdetail-panel">
    <div v-if="trip" class="tripdetail-content">
      <div class="tripdetail-head">
        <h2>{{ trip.title }}</h2>
        <div class="tripdetail-actions">
          <button class="step-btn secondary" @click="$emit('edit')">Edit Trip</button>
          <button class="step-btn primary" @click="$emit('add')">Add Trip</button>
        </div>
      </div>

      <p class="tripdetail-meta">
        {{ trip.country }} · {{ tripDateRange }} · {{ trip.duration }} days · {{ trip.style }} · {{ trip.season }}
      </p>

      <div class="plannedtrip-list">
        <article v-for="day in trip.timelineDays" :key="day.day" class="plannedtrip-day">
          <h3>Day {{ day.day }}</h3>
          <ul>
            <li v-for="(item, idx) in day.items" :key="`${day.day}-${idx}`">{{ item }}</li>
            <li v-if="!day.items.length" class="empty">No activities yet</li>
          </ul>
        </article>
      </div>
    </div>

    <div v-else class="tripdetail-empty">
      <h3>Select a trip</h3>
      <p>Pick a planned trip from the left panel, or create a new one.</p>
      <button class="step-btn primary" @click="$emit('add')">Add Trip</button>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  trip: { type: Object, default: null },
})
defineEmits(['edit', 'add'])

const tripDateRange = computed(() => {
  if (!props.trip?.startDate || !props.trip?.endDate) return 'Dates not set'
  return `${formatDate(props.trip.startDate)} - ${formatDate(props.trip.endDate)}`
})

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

.plannedtrip-list {
  display: grid;
  gap: 10px;
}

.plannedtrip-day {
  border: none;
  border-radius: 12px;
  padding: 10px;
  background: rgba(var(--v-theme-background), 0.35);
}

.plannedtrip-day h3 {
  margin: 0 0 8px;
  font-size: 0.86rem;
}

.plannedtrip-day ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 6px;
}

.plannedtrip-day li {
  font-size: 0.82rem;
}

.plannedtrip-day li.empty {
  opacity: 0.55;
  font-style: italic;
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


