<template>
  <section class="community-section">
    <div class="section-head">
      <h2>Trending This Week</h2>
      <button type="button">View All</button>
    </div>
    <div class="section-divider" aria-hidden="true"></div>

    <div class="trending-row">
      <article v-for="post in posts" :key="post.id" class="trending-card">
        <img v-if="coverImage(post)" :src="coverImage(post)" :alt="post.title" />

        <div>
          <span>{{ post.country || post.trip?.meta?.country || 'Nordic' }}</span>
          <h3>{{ post.title || 'Untitled trip' }}</h3>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  posts: { type: Array, default: () => [] },
})

function coverImage(post) {
  return post.coverImage || post.cover_image || ''
}
</script>

<style scoped>
.community-section {
  max-width: 90%;
  margin: 0 auto 34px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.section-head h2 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 900;
}

.section-head button {
  border: 0;
  background: transparent;
  color: rgb(var(--v-theme-primary));
  font-weight: 800;
  cursor: pointer;
}

.section-divider h2{
  width: 70%;
  height: 1px;
  margin: -6px 0 18px;
  background: rgba(var(--v-theme-on-surface), 0.18);
}

.trending-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.trending-card {
  min-height: 180px;
  border-radius: 18px;
  overflow: hidden;
  position: relative;
  background: rgba(var(--v-theme-primary), 0.14);
}

.trending-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.trending-card div {
  position: absolute;
  inset: auto 0 0;
  padding: 18px;
  color: white;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
}

.trending-card h3 {
  margin: 4px 0 0;
}

@media (max-width: 1000px) {
  .trending-row {
    grid-template-columns: 1fr;
  }
}
</style>
