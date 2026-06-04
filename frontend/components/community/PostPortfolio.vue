<template>
  <section class="panel">
    <h2>Portfolio</h2>
    <p v-if="!portfolios.length" class="empty">No travel reflections added yet.</p>

    <article v-for="portfolio in portfolios" :key="portfolio.id" class="portfolio">
      <h3>{{ portfolio.title || 'Travel notes' }}</h3>
      <p>{{ portfolio.content }}</p>

      <div v-if="portfolio.images?.length" class="image-grid">
        <img v-for="image in portfolio.images" :key="image.id || image.image_url || image" :src="image.image_url || image" alt="" />
      </div>
    </article>
  </section>
</template>

<script setup>
defineProps({
  portfolios: { type: Array, default: () => [] },
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
h3,
p {
  margin-top: 0;
}

.portfolio + .portfolio {
  margin-top: 18px;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
}

img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border-radius: 8px;
}

.empty {
  color: rgba(var(--v-theme-text), 0.62);
}
</style>
