<template>
  <section class="planner-card weather-wrap">
    <div>
      <h2>Weather Forecast</h2>
      <div class="chips">
        <span v-for="(item, idx) in weather" :key="idx">Day {{ idx + 1 }} · {{ item.temp }}°C · {{ item.condition }}</span>
      </div>
    </div>
    <div class="aurora">
      <h3>Aurora Prediction</h3>
      <p>KP Index: <strong>{{ aurora.kp }}</strong></p>
      <p>Chance: <strong>{{ aurora.chance }}%</strong></p>
      <p class="note">Best time: {{ aurora.window }}</p>
    </div>
  </section>
</template>

<script setup>
defineProps({
  weather: { type: Array, required: true },
  aurora: { type: Object, required: true },
})
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

.weather-wrap { display: grid; gap: 14px; }
h2, h3 { margin: 0 0 8px; }
.chips { display: grid; gap: 6px; }
.chips span {
  background: rgba(var(--v-theme-background), 0.35);
  border-radius: 10px;
  padding: 6px 8px;
  font-size: 0.82rem;
}
.aurora { border-top: 1px solid rgba(var(--v-theme-on-surface), 0.1); padding-top: 10px; }
.aurora p { margin: 4px 0; font-size: 0.9rem; }
.note { opacity: 0.8; }
</style>
