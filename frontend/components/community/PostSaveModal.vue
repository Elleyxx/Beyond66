<template>
  <teleport to="body">
    <div class="backdrop" @click.self="$emit('close')">
      <section class="modal">
        <h2>Save Post</h2>
        <p>Save "{{ post?.title || 'this trip' }}" to your saved posts?</p>
        <div class="actions">
          <button type="button" class="secondary" @click="$emit('close')">Cancel</button>
          <button type="button" class="primary" @click="$emit('confirm', post)">Save</button>
        </div>
      </section>
    </div>
  </teleport>
</template>

<script setup>
defineProps({
  post: { type: Object, default: null },
})

defineEmits(['close', 'confirm'])
</script>

<style scoped>
.backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 24px;
  background: rgba(0, 0, 0, 0.48);
}

.modal {
  width: min(100%, 420px);
  border-radius: 8px;
  padding: 22px;
  background: rgb(var(--v-theme-surface));
}

h2 {
  margin: 0 0 8px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
}

button {
  border-radius: 999px;
  padding: 10px 14px;
  font-weight: 850;
  cursor: pointer;
}

.secondary {
  border: 1px solid rgba(var(--v-theme-primary), 0.34);
  color: rgb(var(--v-theme-primary));
  background: transparent;
}

.primary {
  border: 0;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
}
</style>
