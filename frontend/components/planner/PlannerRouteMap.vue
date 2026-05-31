<template>
  <section class="planner-card">
    <h2>Route Visualisation</h2>
    <div class="map-box">
      <div class="route-line"></div>
      <div v-for="(stop, index) in stops" :key="`${stop}-${index}`" class="route-stop" :style="stopStyle(index)">
        <span>{{ index + 1 }}</span>
        <small>{{ stop }}</small>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ timeline: { type: Array, required: true } })

const stops = computed(() => {
  const flattened = props.timeline.flatMap((day) => day.items)
  const unique = []
  for (const item of flattened) {
    if (!unique.includes(item)) unique.push(item)
  }
  return unique.slice(0, 6)
})

function stopStyle(index) {
  const x = 12 + (index % 3) * 30
  const y = 22 + Math.floor(index / 3) * 42
  return { left: `${x}%`, top: `${y}%` }
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

h2 { margin: 0 0 10px; font-size: 1.05rem; }

.map-box {
  height: 230px;
  border-radius: 16px;
  background:
    radial-gradient(circle at 20% 20%, rgba(var(--v-theme-primary), 0.2), transparent 40%),
    linear-gradient(140deg, rgba(var(--v-theme-background), 0.65), rgba(var(--v-theme-surface), 0.8));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  position: relative;
  overflow: hidden;
}

.route-line {
  position: absolute;
  left: 10%;
  top: 25%;
  width: 80%;
  height: 50%;
  border: 2px dashed rgba(var(--v-theme-primary), 0.6);
  border-radius: 999px;
}

.route-stop {
  position: absolute;
  transform: translate(-50%, -50%);
  text-align: center;
}

.route-stop span {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: inline-grid;
  place-items: center;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
  font-size: 0.75rem;
  font-weight: 800;
}

.route-stop small {
  display: block;
  margin-top: 4px;
  font-size: 0.68rem;
  background: rgba(var(--v-theme-surface), 0.88);
  border-radius: 8px;
  padding: 2px 6px;
}
</style>
