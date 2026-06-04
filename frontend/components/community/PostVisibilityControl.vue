<template>
  <section class="visibility-control">
    <h2>Visibility</h2>
    <select :value="status" @change="$emit('update:status', $event.target.value)">
      <option value="public">Public</option>
      <option value="private">Private</option>
    </select>
  </section>
</template>

<script setup>
defineProps({
  status: { type: String, default: 'private' },
})

defineEmits(['update:status'])
</script>

<style scoped>
.visibility-control {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  padding: 16px;
  background: rgba(var(--v-theme-surface), 0.94);
}

h2 {
  margin: 0 0 12px;
  font-size: 1rem;
}

select {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 8px;
  padding: 10px 12px;
  color: rgb(var(--v-theme-text));
  background: rgba(var(--v-theme-background), 0.52);
  font: inherit;
}
</style>
