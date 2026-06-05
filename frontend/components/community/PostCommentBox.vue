<template>
  <form class="comment-box" @submit.prevent="submit">
    <textarea v-model="comment" rows="3" placeholder="Write a comment"></textarea>
    <button type="submit" :disabled="!comment.trim()">Post Comment</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['submit'])
const comment = ref('')

function submit() {
  const value = comment.value.trim()
  if (!value) return
  emit('submit', value)
  comment.value = ''
}
</script>

<style scoped>
.comment-box {
  display: grid;
  gap: 10px;
  border: 0;
  border-radius: 20px;
  padding: 18px;
  background: rgba(var(--v-theme-background), 0.36);
}

textarea {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 8px;
  padding: 12px;
  color: rgb(var(--v-theme-text));
  background: rgba(var(--v-theme-background), 0.52);
  font: inherit;
  resize: vertical;
}

button {
  justify-self: end;
  border: 0;
  border-radius: 999px;
  padding: 10px 14px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 850;
  cursor: pointer;
}

button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
</style>
