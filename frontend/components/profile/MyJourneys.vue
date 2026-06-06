<template>
  <section class="section-block">
    <div class="section-title">
      <p>MY JOURNEYS</p>
      <h2>Planners & Travel Diaries</h2>
    </div>

    <div v-if="journeys.length" class="journey-grid">
      <article v-for="journey in journeys" :key="journey.id || journey.title" class="journey-card">
        <img :src="resolveAssetUrl(journey.image)" :alt="journey.title" />

        <div class="journey-content">
          <div>
            <p class="country">{{ journey.country }}</p>
            <h3>{{ journey.title }}</h3>
            <small>
              {{ journey.days ? `${journey.days} Days · ` : '' }}{{ journey.date }}
            </small>
          </div>

          <div class="journey-tags">
            <span :class="{ active: journey.hasPlanner }">Planner</span>
            <span :class="{ active: journey.hasDiary }">Diary</span>
            <span :class="{ active: journey.isPublic }">
              {{ journey.isPublic ? 'Public' : 'Private' }}
            </span>
          </div>

          <div class="journey-actions">
            <button type="button" @click="$emit('view', journey)">View</button>
            <button type="button" @click="$emit('edit', journey)">Edit</button>
            <button type="button" @click="$emit('toggle-share', journey)">
              {{ journey.isPublic ? 'Unshare' : 'Share' }}
            </button>
            <button type="button" @click="$emit('add-diary', journey)">
              {{ journey.hasDiary ? 'Edit Diary' : 'Add Diary' }}
            </button>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="empty-panel">
      <v-icon size="34">mdi-map-plus</v-icon>
      <p>No journeys yet. Create a planner to start filling your passport.</p>
      <v-btn to="/trip-planner" color="primary" variant="flat">Create Journey</v-btn>
    </div>
  </section>
</template>

<script setup>
import { resolveAssetUrl } from '@/services/apiBase'

defineProps({
  journeys: {
    type: Array,
    required: true,
  },
})

defineEmits(['view', 'edit', 'toggle-share', 'add-diary'])
</script>

<style scoped>
.section-block {
  margin-top: 36px;
}

.section-title p {
  margin-bottom: 8px;
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

.journey-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

.journey-card {
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.journey-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 36px rgba(var(--v-theme-background), 0.18);
}

.journey-card img {
  width: 100%;
  height: 210px;
  object-fit: cover;
}

.journey-content {
  padding: 22px;
}

.country {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.journey-content h3 {
  margin: 4px 0 6px;
  font-size: 1.25rem;
}

.journey-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.journey-tags span {
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.08);
  font-size: 0.78rem;
  opacity: 0.65;
}

.journey-tags span.active {
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.14);
  opacity: 1;
}

.journey-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.journey-actions button {
  border: 1px solid rgba(var(--v-theme-primary), 0.28);
  border-radius: 999px;
  padding: 7px 10px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
  font-size: 0.78rem;
  font-weight: 850;
  cursor: pointer;
}

.journey-actions button:hover {
  background: rgba(var(--v-theme-primary), 0.16);
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
  .journey-grid {
    grid-template-columns: 1fr;
  }
}
</style>
