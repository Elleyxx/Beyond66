<template>
  <section class="planner-card controls-card">
    <h2>Plan Basics</h2>
    <div class="grid">
      <label>
        Country
        <select :value="modelValue.country" @change="emitPatch({ country: $event.target.value })">
          <option v-for="item in countries" :key="item" :value="item">{{ item }}</option>
        </select>
      </label>
      <label>
        Duration (days)
        <input type="number" min="1" max="30" :value="modelValue.duration" @input="emitPatch({ duration: Number($event.target.value || 1) })" />
      </label>
      <label>
        Travel Style
        <select :value="modelValue.style" @change="emitPatch({ style: $event.target.value })">
          <option v-for="item in styles" :key="item" :value="item">{{ item }}</option>
        </select>
      </label>
      <label>
        Budget
        <select :value="modelValue.budget" @change="emitPatch({ budget: $event.target.value })">
          <option v-for="item in budgets" :key="item" :value="item">{{ item }}</option>
        </select>
      </label>
      <label>
        Season
        <select :value="modelValue.season" @change="emitPatch({ season: $event.target.value })">
          <option v-for="item in seasons" :key="item" :value="item">{{ item }}</option>
        </select>
      </label>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue'])

const countries = ['Norway', 'Sweden', 'Finland', 'Iceland', 'Denmark']
const styles = ['Adventure', 'Relax', 'Culture', 'Nature']
const budgets = ['Low', 'Medium', 'High']
const seasons = ['Winter', 'Spring', 'Summer', 'Autumn']

function emitPatch(patch) {
  emit('update:modelValue', { ...props.modelValue, ...patch })
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}

h2 {
  margin: 0 0 12px;
  font-size: 1.1rem;
}

.grid {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

label {
  display: grid;
  gap: 6px;
  font-size: 0.86rem;
  font-weight: 700;
}

input,
select {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 10px;
  padding: 8px 10px;
  background: rgba(var(--v-theme-background), 0.5);
  color: rgb(var(--v-theme-text));
}

@media (max-width: 700px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
