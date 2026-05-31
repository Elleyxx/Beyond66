<template>
  <section class="planner-card split">
    <div>
      <h2>Budget Estimator</h2>
      <p class="total">$ {{ estimate.total.toLocaleString() }}</p>
    </div>
    <ul>
      <li>Stay: $ {{ estimate.stay }}</li>
      <li>Food: $ {{ estimate.food }}</li>
      <li>Transport: $ {{ estimate.transport }}</li>
      <li>Activities: $ {{ estimate.activities }}</li>
    </ul>
  </section>
</template>

<script setup>
defineProps({
  estimate: { type: Object, required: true },
})
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

.split { display: grid; gap: 8px; }
h2 { margin: 0; font-size: 1.05rem; }
.total { margin: 6px 0 0; font-size: 1.6rem; font-weight: 900; }
ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; font-size: 0.9rem; }
</style>
