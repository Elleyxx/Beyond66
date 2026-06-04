<template>
  <section class="post-feed">
    <p v-if="loading" class="state">Loading community trips...</p>
    <p v-else-if="!posts.length" class="state">No community trips yet.</p>

    <div v-else class="feed-grid">
      <PostCard v-for="post in posts" :key="post.id" :post="post" @save="$emit('save', $event)" />
    </div>
  </section>
</template>

<script setup>
import PostCard from '@/components/community/PostCard.vue'

defineProps({
  posts: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

defineEmits(['save'])
</script>

<style scoped>
.post-feed {
  max-width: 90%;
  margin: 0 auto;
}

.feed-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 18px;
}

.state {
  margin: 0;
  padding: 24px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  color: rgba(var(--v-theme-text), 0.66);
  background: rgba(var(--v-theme-surface), 0.84);
}
</style>
