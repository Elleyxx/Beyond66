<template>
  <section class="panel">
    <h2>Itinerary Preview</h2>
    <div class="timeline">
      <article v-for="day in timeline" :key="day.day">
        <strong>Day {{ day.day }}</strong>
        <ul>
          <li v-for="(item, index) in day.items || []" :key="`${day.day}-${index}`">{{ item }}</li>
          <li v-if="!day.items?.length" class="empty">No activities listed</li>
        </ul>
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
  border-radius: 8px;
  padding: 18px;
  background: rgba(var(--v-theme-surface), 0.94);
}

h2 {
  margin: 0 0 14px;
  font-size: 1.1rem;
}

.timeline {
  display: grid;
  gap: 12px;
}

article {
  display: grid;
  grid-template-columns: 88px 1fr;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  background: rgba(var(--v-theme-background), 0.46);
}

ul {
  margin: 0;
  padding-left: 18px;
}

.empty {
  opacity: 0.58;
  font-style: italic;
}
</style>
