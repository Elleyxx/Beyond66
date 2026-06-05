<template>
  <section class="panel">
    <p class="eyebrow">Itinerary</p>
    <h2>Trip Timeline</h2>

    <div class="timeline">
      <article v-for="day in timeline" :key="day.day">
        <div class="day-label">
          <strong>Day {{ day.day }}</strong>
          <span v-if="day.date">{{ day.date }}</span>
        </div>

        <div class="day-line">
          <span></span>
        </div>

        <div class="day-content">
          <h3>{{ day.destination || day.country || 'Activities' }}</h3>
          <ul>
            <li v-for="(item, index) in day.items || []" :key="`${day.day}-${index}`">{{ item }}</li>
            <li v-if="!day.items?.length" class="empty">No activities listed</li>
          </ul>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  timeline: { type: Array, default: () => [] },
})
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 28px;
  padding: 22px;
  background: rgba(var(--v-theme-background), 0.36);
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
  margin: 0 0 14px;
  font-size: 1.1rem;
  font-weight: 900;
}

.timeline {
  display: grid;
  gap: 0;
}

article {
  display: grid;
  grid-template-columns: minmax(62px, 76px) 24px minmax(0, 1fr);
  min-height: 112px;
  padding: 10px 0;
}

.day-label {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.day-label strong {
  color: rgb(var(--v-theme-text));
  font-size: 1rem;
  font-weight: 900;
}

.day-label span {
  margin-top: 4px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.7rem;
  font-weight: 800;
}

.day-line {
  position: relative;
  display: flex;
  justify-content: center;
}

.day-line::before {
  content: '';
  width: 2px;
  height: 100%;
  background: rgba(var(--v-theme-on-surface), 0.18);
}

.day-line span {
  position: absolute;
  top: 50%;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: rgb(var(--v-theme-surface));
  border: 2px solid rgba(var(--v-theme-primary), 0.52);
  transform: translateY(-50%);
}

.day-content {
  min-width: 0;
  padding: 12px 14px;
  border-radius: 16px;
  background: rgba(var(--v-theme-surface), 0.7);
}

h3 {
  margin: 0 0 10px;
  font-size: 0.95rem;
  font-weight: 900;
}

ul {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 8px;
}

li {
  position: relative;
  padding-left: 14px;
  color: rgba(var(--v-theme-text), 0.78);
  font-size: 0.84rem;
  line-height: 1.45;
}

li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.7em;
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
}

.empty {
  opacity: 0.58;
  font-style: italic;
}

@media (max-width: 700px) {
  article {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .day-line {
    display: none;
  }
}
</style>
