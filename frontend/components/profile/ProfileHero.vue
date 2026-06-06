<template>
  <section class="profile-hero" :style="{ backgroundImage: `url(${coverImage})` }">
    <div class="hero-overlay"></div>

    <div class="hero-card">
      <img v-if="user.avatar" :src="user.avatar" alt="User avatar" class="avatar" />
      <div v-else class="avatar avatar-fallback">{{ initials }}</div>

      <div class="hero-copy">
        <p class="eyebrow">MY PROFILE</p>
        <h1>{{ user.name }}</h1>
        <p>{{ user.title }} · {{ user.joined }}</p>
      </div>

      <v-btn class="edit-btn" variant="outlined" rounded="pill" to="/trip-planner">
        <v-icon start>mdi-map-plus</v-icon>
        Add Journey
      </v-btn>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const coverImage = computed(() => props.user.cover || '/assets/images/aurora_mountain.jpg')
const initials = computed(() =>
  String(props.user.name || 'Traveller')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('') || 'T',
)
</script>

<style scoped>
.profile-hero {
  position: relative;
  min-height: 320px;
  overflow: hidden;
  border-radius: 30px;
  background-size: cover;
  background-position: center;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(120deg, rgba(0, 0, 0, 0.72), rgba(0, 0, 0, 0.22));
}

.hero-card {
  position: relative;
  z-index: 2;
  min-height: 320px;
  display: flex;
  align-items: center;
  gap: 26px;
  padding: 42px;
  color: white;
}

.avatar {
  width: 108px;
  height: 108px;
  flex: 0 0 108px;
  border: 3px solid rgba(255, 255, 255, 0.75);
  border-radius: 50%;
  object-fit: cover;
}

.avatar-fallback {
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-primary), 0.84);
  color: white;
  font-size: 2.2rem;
  font-weight: 900;
}

.hero-copy {
  min-width: 0;
}

.eyebrow {
  margin-bottom: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

h1 {
  margin: 0;
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.2rem, 5vw, 4.8rem);
  line-height: 1;
}

.hero-copy p:last-child {
  margin-top: 12px;
  opacity: 0.86;
}

.edit-btn {
  margin-left: auto;
  color: white !important;
  border: 2px solid rgba(var(--v-theme-primary)) !important;
  text-decoration: none;
  padding: 20px 18px !important;
}

@media (max-width: 760px) {
  .hero-card {
    flex-direction: column;
    padding: 32px 20px;
    text-align: center;
  }

  .edit-btn {
    margin-left: 0;
  }
}
</style>
