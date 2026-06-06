<template>
  <section class="profile-panel">
    <div class="section-head">
      <p>ACHIEVEMENTS</p>
      <h2>Travel Badges</h2>
    </div>

    <div class="achievement-list">
      <article v-for="item in achievements" :key="item.title" class="achievement-card">
        <v-icon size="30">{{ item.icon }}</v-icon>
        <div>
          <h3>{{ item.title }}</h3>
          <p>{{ item.desc }}</p>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  achievements: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.profile-panel {
  padding: 30px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
}

.section-head p {
  margin-bottom: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.section-head h2 {
  margin-bottom: 22px;
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
}

.achievement-list {
  display: grid;
  gap: 16px;
}

.achievement-card {
  display: flex;
  gap: 16px;
  padding: 18px;
  border-radius: 8px;
  background: rgba(var(--v-theme-primary), 0.06);
}

.achievement-card .v-icon {
  flex: 0 0 auto;
  color: rgb(var(--v-theme-primary));
}

.achievement-card h3 {
  margin: 0 0 4px;
}

.achievement-card p {
  margin: 0;
  opacity: 0.7;
}
</style>
