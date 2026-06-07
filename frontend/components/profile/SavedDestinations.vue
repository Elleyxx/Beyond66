<template>
  <section class="section-block">
    <div class="section-title">
      <p>{{ t('profilePage.saved.eyebrow') }}</p>
      <h2>{{ t('profilePage.saved.title') }}</h2>
    </div>

    <div v-if="destinations.length" class="destination-grid">
      <article
        v-for="place in destinations"
        :key="place.slug || place.name"
        class="destination-card"
        role="link"
        tabindex="0"
        @click="goToDestination(place)"
        @keydown.enter="goToDestination(place)"
      >
        <button
          type="button"
          class="unsave-btn"
          :aria-label="`Remove ${place.name} from saved`"
          title="Remove from saved"
          @click.stop="$emit('unsave', place.slug)"
        >
          <i class="bi bi-bookmark-fill"></i>
        </button>

        <div class="card-image">
          <img :src="place.image" :alt="place.name" />
        </div>

        <div class="card-body">
          <p class="card-country">{{ place.country }}</p>
          <h3>{{ place.name }}</h3>
          <small v-if="place.savedAt" class="card-date">
            <i class="bi bi-clock"></i>
            {{ t('profilePage.saved.savedAt', { date: formatDate(place.savedAt) }) }}
          </small>
        </div>

        <div class="card-hover-cta">
          <span>{{ t('profilePage.saved.viewCta') }} <i class="bi bi-arrow-right"></i></span>
        </div>
      </article>
    </div>

    <div v-else class="empty-panel">
      <v-icon size="34">mdi-map-marker-heart</v-icon>
      <p>{{ t('profilePage.saved.emptyText') }}</p>
      <v-btn to="/country/norway" color="primary" variant="flat">{{ t('profilePage.saved.emptyButton') }}</v-btn>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  destinations: {
    type: Array,
    required: true,
  },
})

defineEmits(['unsave'])

const router = useRouter()

const countryRouteMap = {
  norway: '/country/norway',
  sweden: '/country/sweden',
  finland: '/country/finland',
  iceland: '/country/iceland',
  denmark: '/country/denmark',
}

function goToDestination(place) {
  const countrySlug = (place.slug || '').split('-')[0]
  const route = countryRouteMap[countrySlug]
  if (route) {
    router.push({ path: route, hash: '#country-destination' })
  }
}

function formatDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.section-block {
  margin: 50px 0 100px;
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

.destination-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

/* Card — matches CountryLayout destination card */
.destination-card {
  position: relative;
  border-radius: 28px;
  overflow: hidden;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-text), 0.12);
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  outline: none;
}

.destination-card:hover,
.destination-card:focus-visible {
  transform: translateY(-6px);
  box-shadow: 0 18px 36px rgba(var(--v-theme-background), 0.18);
}

/* Unsave bookmark button */
.unsave-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 4;
  width: 38px;
  height: 38px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.38);
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
  backdrop-filter: blur(10px);
  cursor: pointer;
  transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.unsave-btn:hover {
  transform: translateY(-2px) scale(1.08);
  background: rgba(var(--v-theme-primary), 0.8);
}

/* Image */
.card-image {
  position: relative;
  height: 230px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.45s ease;
}

.destination-card:hover .card-image img {
  transform: scale(1.06);
}

/* Text body */
.card-body {
  padding: 20px 22px 18px;
}

.card-country {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  margin: 0 0 4px;
}

.card-body h3 {
  margin: 0 0 8px;
  font-size: 1.2rem;
  line-height: 1.25;
}

.card-date {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.76rem;
  opacity: 0.56;
}

.card-date i {
  font-size: 0.72rem;
}

/* Hover CTA bar that slides up */
.card-hover-cta {
  padding: 0 22px 20px;
  display: flex;
  align-items: center;
}

.card-hover-cta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  opacity: 0;
  transform: translateY(6px);
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.destination-card:hover .card-hover-cta span,
.destination-card:focus-visible .card-hover-cta span {
  opacity: 1;
  transform: translateY(0);
}

/* Empty state */
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

@media (max-width: 1100px) {
  .destination-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 700px) {
  .destination-grid {
    grid-template-columns: 1fr;
  }
}
</style>
