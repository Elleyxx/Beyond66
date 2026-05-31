<template>
  <section class="planner-card summary-card">
    <h2>Trip Summary</h2>
    <p>{{ meta.country }} · {{ meta.duration }} days · {{ meta.style }} · {{ meta.season }}</p>
    <p>Planned Activities: <strong>{{ totalActivities }}</strong></p>
    <p>Checklist Done: <strong>{{ checkedCount }}/{{ checklistCount }}</strong></p>
    <div class="actions">
      <button @click="$emit('save')">Save Trip</button>
    </div>
  </section>
</template>

<script setup>
defineProps({
  meta: { type: Object, required: true },
  totalActivities: { type: Number, required: true },
  checkedCount: { type: Number, required: true },
  checklistCount: { type: Number, required: true },
})
defineEmits(['save'])
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}
h2 { margin: 0 0 8px; font-size: 1.05rem; }
p { margin: 6px 0; font-size: 0.9rem; }
.actions { margin-top: 10px; }
button {
  border: 0;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 700;
  cursor: pointer;
}
</style>
