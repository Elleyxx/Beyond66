<template>
  <section class="planner-card">
    <div class="head">
      <h2>Packing Checklist</h2>
      <button @click="$emit('print')">Export PDF</button>
    </div>
    <ul>
      <li v-for="item in items" :key="item.id">
        <label>
          <input type="checkbox" :checked="item.checked" @change="$emit('toggle', item.id)" />
          <span>{{ item.name }}</span>
        </label>
      </li>
    </ul>
  </section>
</template>

<script setup>
defineProps({ items: { type: Array, required: true } })
defineEmits(['toggle', 'print'])
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 18px;
}
.head { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
h2 { margin: 0; font-size: 1.05rem; }
button {
  border: 1px solid rgba(var(--v-theme-primary), 0.5);
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  border-radius: 999px;
  padding: 6px 10px;
  cursor: pointer;
}
ul { list-style: none; margin: 12px 0 0; padding: 0; display: grid; gap: 8px; }
label { display: flex; gap: 8px; align-items: center; }
span { font-size: 0.9rem; }
</style>
