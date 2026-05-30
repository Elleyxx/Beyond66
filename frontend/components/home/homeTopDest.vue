<template>
  <section class="tours-section">
    <div class="tours-header">
      <div class="title-block">
        <h2>DEST</h2>
        <p>
          Explore Nordic destinations, from snowy villages to aurora skies,
          fjords, lakes, mountains and historical cities.
        </p>
      </div>
      <div class="tour-tabs">
        <button
          v-for="country in countries"
          :key="country.slug"
          :class="{ active: selectedCountry.slug === country.slug }"
          @click="setSelectedCountry(country.slug)"
        >
          {{ country.name }}
        </button>
      </div>
    </div>

    <div class="cards-wrap">
      <div ref="cardsRef" class="tour-cards">
        <article
          v-for="place in selectedCountry.places"
          :key="place.title"
          class="tour-card"
        >
          <img :src="place.image" :alt="place.title" />

          <div class="card-overlay">
            <h3>{{ place.title }}</h3>
            <p>{{ place.desc }}</p>
            <span class="more-link">MORE →</span>
          </div>
        </article>
      </div>

      <div class="carousel-controls">
        <button class="chevron-btn" @click="scrollCards('left')">‹</button>
        <button class="chevron-btn" @click="scrollCards('right')">›</button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  countries: Array,
})

const selectedSlug = ref('norway')

const selectedCountry = computed(() => {
  return (
    props.countries?.find((country) => country.slug === selectedSlug.value) ||
    props.countries?.find((country) => country.slug === 'norway') ||
    props.countries?.[0] ||
    { slug: 'norway', places: [] }
  )
})

function setSelectedCountry(slug) {
  selectedSlug.value = slug
}

const cardsRef = ref(null)

const scrollCards = (direction) => {
  if (!cardsRef.value) return

  cardsRef.value.scrollBy({
    left: direction === 'right' ? 330 : -330,
    behavior: 'smooth',
  })
}
</script>

<style scoped>
.tours-section {
  position: relative;
  padding: 90px 8vw 110px;
  background: transparent;
  overflow: hidden;
}

.tours-header {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 60px;
  margin-bottom: 50px;
}

.title-block {
  position: relative;
}

.title-block h2 {
  font-size: clamp(4.5rem, 9vw, 8rem);
  line-height: 0.8;
  color: rgba(var(--v-theme-on-surface), 0.08);
  font-weight: 900;
  margin: 0;
}

.title-block p {
  position: absolute;
  left: 100%;
  bottom: -15px;
  width: 320px;
  color: rgb(var(--v-theme-text));
  font-size: 0.8rem;
  line-height: 1.7;
}

.tour-tabs {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
  flex-wrap: wrap;
}

.tour-tabs button {
  background: transparent;
  border: none;
  padding: 0 0 6px;
  color: rgb(var(--v-theme-subtleText));
  font-size: 0.9rem;
  font-weight: 800;
  text-transform: uppercase;
  cursor: pointer;
}

.tour-tabs button.active {
  color: rgb(var(--v-theme-primary));
  border-bottom: 2px solid rgb(var(--v-theme-primary));
}

.cards-wrap {
  position: relative;
  z-index: 2;
}

.tour-cards {
  display: flex;
  gap: 24px;
  overflow-x: auto;
  padding: 36px 6px 28px;
  scroll-behavior: smooth;
  scrollbar-width: none;
}

.carousel-controls {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-right: 28px;
  margin-top: 4px;
}

.chevron-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(var(--v-theme-primary), 0.5);
  background: rgba(var(--v-theme-surface), 0.75);
  color: rgb(var(--v-theme-primary));
  font-size: 1.8rem;
  line-height: 1;
  display: grid;
  place-items: center;
  cursor: pointer;
  backdrop-filter: blur(12px);
  transition: 0.25s ease;
}

.chevron-btn:hover {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
  transform: scale(1.08);
}

.tour-cards::-webkit-scrollbar {
  display: none;
}

.tour-card {
  position: relative;
  min-width: 250px;
  height: 360px;
  border-radius: 28px;
  overflow: hidden;
  flex-shrink: 0;
  cursor: pointer;
  transform: translateY(0);
  transition:
    transform 0.35s ease,
    box-shadow 0.35s ease;
  box-shadow: 0 18px 36px rgba(var(--v-theme-background), 0.18);
}

.tour-card:hover {
  transform: translateY(-26px);
  box-shadow: 0 28px 48px rgba(var(--v-theme-background), 0.26);
}

.tour-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: 0.45s ease;
}

.tour-card:hover img {
  transform: scale(1.08);
}

.card-overlay {
  position: absolute;
  inset: auto 0 0;
  padding: 26px 22px;
  color: white;
  text-align: center;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.82),
    rgba(0, 0, 0, 0.28),
    transparent
  );
}

.card-overlay h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
}

.card-overlay p {
  max-height: 0;
  margin: 0;
  overflow: hidden;
  opacity: 0;
  font-size: 1rem;
  line-height: 1.5;
  transition: 0.3s ease;
}

.more-link {
  display: inline-block;
  margin-top: 0;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  color: rgb(var(--v-theme-primary));
  font-size: 0.9rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  transition: 0.3s ease;
}

.tour-card:hover .card-overlay p {
  max-height: 60px;
  margin-top: 10px;
  opacity: 1;
}

.tour-card:hover .more-link {
  max-height: 24px;
  margin-top: 10px;
  opacity: 1;
}

.chevron-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(var(--v-theme-primary), 0.5);
  background: rgba(var(--v-theme-surface), 0.75);
  color: rgb(var(--v-theme-primary));
  font-size: 1.8rem;
  line-height: 1;
  display: grid;
  place-items: center;
  cursor: pointer;
  backdrop-filter: blur(12px);
  transition: 0.25s ease;
}

.chevron-btn:hover {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
  transform: scale(1.08);
}

@media (max-width: 900px) {
  .tours-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .title-block p {
    position: static;
    width: 100%;
    margin-top: 12px;
  }

  .tour-tabs {
    justify-content: flex-start;
  }

  .tour-card {
    min-width: 230px;
    height: 330px;
  }
}

@media (max-width: 600px) {
  .tours-section {
    padding: 70px 20px 90px;
  }

  .chevron-btn {
    display: none;
  }

  .tour-cards {
    padding-top: 26px;
  }
}
</style>
