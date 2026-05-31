<template>
  <section class="planner-card route-card">
    <h2>Journey Preview</h2>

    <div class="route-flow" role="list" aria-label="Trip route by day">
      <div
        v-for="(day, index) in routeDays"
        :key="day.day"
        class="route-step"
        :class="index % 2 === 0 ? 'align-right' : 'align-left'"
        role="listitem"
      >
        <div class="route-node">Day {{ day.day }}</div>
        <div v-if="index < routeDays.length - 1" class="route-connector" aria-hidden="true"></div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ timeline: { type: Array, required: true } })

const routeDays = computed(() => props.timeline.slice(0, 10))
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

h2 {
  margin: 0 0 14px;
  font-size: 1.05rem;
}

.route-flow {
  display: grid;
  gap: 8px;
  padding: 4px 0 2px;
}

.route-step {
  position: relative;
  min-height: 70px;
}

.route-step.align-right {
  display: grid;
  justify-items: end;
}

.route-step.align-left {
  display: grid;
  justify-items: start;
}

.route-node {
  min-width: 120px;
  text-align: center;
  padding: 9px 14px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.16);
  border: 1px solid rgba(var(--v-theme-primary), 0.55);
  color: rgb(var(--v-theme-text));
  font-weight: 700;
  font-size: 0.88rem;
}

.route-connector {
  position: absolute;
  left: 50%;
  top: 40px;
  width: 2px;
  height: 34px;
  transform: translateX(-50%);
  background: linear-gradient(
    to bottom,
    rgba(var(--v-theme-primary), 0.85),
    rgba(var(--v-theme-primary), 0.2)
  );
}
</style>
