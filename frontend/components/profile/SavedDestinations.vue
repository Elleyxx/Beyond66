<template>
  <section class="section-block">
    <div class="section-title">
      <p>SAVED DESTINATIONS</p>
      <h2>Places to Remember</h2>
    </div>

    <div v-if="destinations.length" class="destination-grid">
      <article v-for="place in destinations" :key="place.slug || place.name" class="destination-card">
        <img :src="place.image" :alt="place.name" />
        <div>
          <p>{{ place.country }}</p>
          <h3>{{ place.name }}</h3>
        </div>
      </article>
    </div>

    <div v-else class="empty-panel">
      <v-icon size="34">mdi-map-marker-heart</v-icon>
      <p>No saved destinations yet. Bookmark places from the country pages to collect them here.</p>
      <v-btn to="/explore" color="primary" variant="flat">Explore Places</v-btn>
    </div>
  </section>
</template>

<script setup>
defineProps({
  destinations: {
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

.destination-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

.destination-card {
  position: relative;
  height: 240px;
  overflow: hidden;
  border-radius: 8px;
}

.destination-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.destination-card:hover img {
  transform: scale(1.04);
}

.destination-card div {
  position: absolute;
  inset: auto 0 0;
  padding: 22px;
  color: white;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.78), transparent);
}

.destination-card p {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.1em;
}

.destination-card h3 {
  margin: 0;
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

@media (max-width: 900px) {
  .destination-grid {
    grid-template-columns: 1fr;
  }
}
</style>
