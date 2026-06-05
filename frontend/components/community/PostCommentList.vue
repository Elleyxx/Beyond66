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
  border: 0;
  border-radius: 0;
  padding: 0;
  background: transparent;
}

h2,
p {
  margin-top: 0;
}

h2 {
  font-size: 1.15rem;
  font-weight: 900;
}

.comment {
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-background), 0.36);
}

.comment + .comment {
  margin-top: 10px;
}

.empty {
  color: rgba(var(--v-theme-text), 0.62);
}
</style>
