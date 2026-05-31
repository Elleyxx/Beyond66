<template>
  <section class="planner-card timeline-card">
    <div class="timeline-head">
      <h2>Drag & Drop Timeline</h2>
      <div class="pill">Hover and drag activities</div>
    </div>

    <div class="activity-pool">
      <button v-for="activity in activityPool" :key="activity" draggable="true" @dragstart="onDragStart(activity)">{{ activity }}</button>
    </div>

    <div class="days-grid">
      <article
        v-for="(day, dayIndex) in timeline"
        :key="day.day"
        class="day-card"
        @dragover.prevent
        @drop="onDrop(dayIndex)"
      >
        <h3>Day {{ day.day }}</h3>
        <ul>
          <li v-for="(item, itemIndex) in day.items" :key="`${item}-${itemIndex}`">
            {{ item }}
            <button class="remove" @click="removeItem(dayIndex, itemIndex)">×</button>
          </li>
          <li v-if="!day.items.length" class="empty">Drop activity here</li>
        </ul>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  timeline: { type: Array, required: true },
})

const emit = defineEmits(['update:timeline'])

const dragged = ref('')

const activityPool = [
  'City Walk',
  'Museum Visit',
  'Hiking Trail',
  'Scenic Drive',
  'Food Tour',
  'Northern Lights Spotting',
]

function onDragStart(activity) {
  dragged.value = activity
}

function onDrop(dayIndex) {
  if (!dragged.value) return
  const next = props.timeline.map((d) => ({ ...d, items: [...d.items] }))
  next[dayIndex].items.push(dragged.value)
  emit('update:timeline', next)
  dragged.value = ''
}

function removeItem(dayIndex, itemIndex) {
  const next = props.timeline.map((d) => ({ ...d, items: [...d.items] }))
  next[dayIndex].items.splice(itemIndex, 1)
  emit('update:timeline', next)
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

.timeline-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

h2 {
  margin: 0;
  font-size: 1.1rem;
}

.pill {
  font-size: 0.75rem;
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
  border-radius: 999px;
  padding: 4px 10px;
}

.activity-pool {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 12px 0 16px;
}

.activity-pool button {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.28);
  background: rgba(var(--v-theme-background), 0.45);
  color: rgb(var(--v-theme-text));
  border-radius: 999px;
  padding: 6px 10px;
  cursor: grab;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 12px;
}

.day-card {
  border-radius: 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.2);
  background: rgba(var(--v-theme-background), 0.35);
  min-height: 150px;
  padding: 10px;
}

.day-card h3 {
  margin: 0 0 8px;
  font-size: 0.95rem;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 8px;
}

li {
  background: rgba(var(--v-theme-surface), 0.9);
  border-radius: 10px;
  padding: 8px;
  font-size: 0.85rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.empty {
  opacity: 0.55;
  font-style: italic;
}

.remove {
  border: 0;
  background: transparent;
  color: rgba(var(--v-theme-text), 0.7);
  cursor: pointer;
}
</style>
