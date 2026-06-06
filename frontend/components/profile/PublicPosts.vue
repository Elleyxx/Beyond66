<template>
  <section class="section-block">
    <div class="section-title">
      <p>SHARED TO COMMUNITY</p>
      <h2>Public Travel Stories</h2>
    </div>

    <div v-if="posts.length" class="post-list">
      <article v-for="post in posts" :key="post.id || post.title" class="post-card">
        <div class="post-icon">
          <v-icon>{{ post.icon }}</v-icon>
        </div>

        <div>
          <span>{{ post.type }}</span>
          <h3>{{ post.title }}</h3>
          <p>{{ post.desc }}</p>
          <small>{{ post.likes }} likes · {{ post.comments }} comments</small>
        </div>

        <v-btn variant="text" class="view-btn" :to="`/community/${post.id}`">
          View
          <v-icon end>mdi-arrow-right</v-icon>
        </v-btn>
      </article>
    </div>

    <div v-else class="empty-panel">
      <v-icon size="34">mdi-account-group</v-icon>
      <p>No public stories yet. Publish a saved journey to share it with the community.</p>
      <v-btn to="/trip-planner" color="primary" variant="flat">Open Planner</v-btn>
    </div>
  </section>
</template>

<script setup>
defineProps({
  posts: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.section-block {
  margin-top: 36px;
}

.section-title p {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.section-title h2 {
  margin-bottom: 22px;
  font-family: 'Playfair Display', serif;
  font-size: 2.3rem;
}

.post-list {
  display: grid;
  gap: 16px;
}

.post-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 18px;
  align-items: center;
  padding: 22px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
}

.post-icon {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 8px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
}

.post-card span {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.post-card h3 {
  margin: 4px 0;
}

.post-card p {
  margin: 0;
  opacity: 0.7;
}

.post-card small {
  display: inline-block;
  margin-top: 8px;
  opacity: 0.58;
}

.view-btn {
  color: rgb(var(--v-theme-primary)) !important;
}

.empty-panel {
  display: grid;
  justify-items: start;
  gap: 14px;
  padding: 26px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
}

.empty-panel .v-icon {
  color: rgb(var(--v-theme-primary));
}

.empty-panel p {
  margin: 0;
  opacity: 0.72;
}

@media (max-width: 760px) {
  .post-card {
    grid-template-columns: 1fr;
  }
}
</style>
