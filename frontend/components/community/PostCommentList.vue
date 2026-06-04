<template>
  <section class="panel">
    <h2>Comments</h2>
    <p v-if="!comments.length" class="empty">No comments yet.</p>

    <article v-for="comment in comments" :key="comment.id || comment.created_at" class="comment">
      <strong>{{ comment.author || comment.username || 'Traveler' }}</strong>
      <p>{{ comment.comment || comment.body }}</p>
    </article>
  </section>
</template>

<script setup>
defineProps({
  comments: { type: Array, default: () => [] },
})
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  padding: 18px;
  background: rgba(var(--v-theme-surface), 0.94);
}

h2,
p {
  margin-top: 0;
}

.comment {
  padding: 12px 0;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.1);
}

.comment:first-of-type {
  border-top: 0;
}

.empty {
  color: rgba(var(--v-theme-text), 0.62);
}
</style>
