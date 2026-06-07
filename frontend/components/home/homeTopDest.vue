<template>
  <section class="tours-section">
    <div class="tours-header">
      <div class="title-block">
        <h2>{{ t('home.tours.title') }}</h2>
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

    <div class="dest-layout">
      <!-- LEFT: Film frame + video -->
      <div class="film-column">
        <div class="film-frame">
          <div class="film-window">
            <video
              class="film-video"
              :src="selectedCountry.video || '/assets/videos/aurora.mp4'"
              autoplay
              muted
              loop
              playsinline
            ></video>
          </div>

          <img
            class="film-border"
            src="/assets/images/frame.png"
            :alt="t('home.tours.filmAlt')"
          />
        </div>
      </div>

      <!-- RIGHT: Destination cards -->
      <div ref="cardsRef" class="tour-cards">
        <article
          v-for="place in selectedCountry.places.slice(0, 3)"
          :key="place.title"
          class="tour-card"
          @click="openCountry(selectedCountry.slug)"
        >
          <img :src="place.image" :alt="place.title" />

          <div class="card-overlay">
            <h3>{{ place.title }}</h3>
            <p>{{ place.desc }}</p>
            <span class="more-link">{{ t('home.tours.more') }}</span>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const props = defineProps({
  countries: Array,
})

const router = useRouter()
const { t } = useI18n()
const selectedSlug = ref('norway')
const cardsRef = ref(null)

const selectedCountry = computed(() => {
  return (
    props.countries?.find((country) => country.slug === selectedSlug.value) ||
    props.countries?.find((country) => country.slug === 'norway') ||
    props.countries?.[0] ||
    { name: 'Norway', slug: 'norway', places: [] }
  )
})

function setSelectedCountry(slug) {
  selectedSlug.value = slug
}

function openCountry(slug) {
  if (!slug) return
  router.push(`/country/${slug}`)
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
  margin-bottom: 45px;
}

.title-block h2 {
  font-size: clamp(4.5rem, 9vw, 8rem);
  line-height: 0.8;
  color: rgba(var(--v-theme-on-surface), 0.35);
  font-weight: 900;
  margin: 0;
}

.tour-tabs {
  display: flex;
  justify-content: flex-end;
  gap: 28px;
  flex-wrap: wrap;
}

.tour-tabs button {
  background: transparent;
  border: none;
  padding: 0 0 8px;
  color: rgb(var(--v-theme-subtleText));
  font-size: 0.9rem;
  font-weight: 900;
  text-transform: uppercase;
  cursor: pointer;
}

.tour-tabs button.active {
  color: rgb(var(--v-theme-primary));
  border-bottom: 2px solid rgb(var(--v-theme-primary));
}

/* main layout */
.dest-layout {
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: 500px 1fr;
  gap: 42px;
  align-items: end;
}

/* film left */
.film-column {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.film-frame {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  overflow: hidden;
}

.film-window {
  position: absolute;
  top: 19.0%;
  left: 2%;
  width: 96%;
  height: 64.5%;
  overflow: hidden;
  z-index: 1;
  background: #000;
}

.film-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.film-border {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  z-index: 2;
  pointer-events: none;
}

/* cards right */
.tour-cards {
  display: flex;
  justify-content: flex-end;
  gap: 24px;
  overflow-x: auto;
  padding: 36px 6px 28px;
  scroll-behavior: smooth;
  scrollbar-width: none;
}

.tour-cards::-webkit-scrollbar {
  display: none;
}

.tour-card {
  position: relative;
  width: clamp(210px, 15vw, 260px);
  min-width: clamp(210px, 15vw, 260px);
  aspect-ratio: 3 / 5;
  border-radius: 28px;
  overflow: hidden;
  flex: 0 0 auto;
  cursor: pointer;
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
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.82),
    rgba(0, 0, 0, 0.28),
    transparent
  );
}

.card-overlay h3 {
  margin: 0;
  font-size: 1.55rem;
  line-height: 1.1;
  font-weight: 900;
}

.card-overlay p {
  max-height: 0;
  margin: 0;
  overflow: hidden;
  opacity: 0;
  font-size: 0.9rem;
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
  font-size: 0.85rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  transition: 0.3s ease;
}

.more-link {
  margin-top: 34px;
  width: fit-content;
  align-self: center;
  text-decoration: none;
  font-size: 0.92rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgb(var(--v-theme-primary));
  position: relative;
  z-index: 5;
  transition:
    transform 0.3s ease,
    letter-spacing 0.3s ease,
    opacity 0.3s ease;
}

.more-link:hover {
  transform: translateX(6px);
  letter-spacing: 0.2em;
  opacity: 0.8;
  text-decoration: underline;
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

@media (max-width: 1250px) {
  .tours-section {
    padding: 80px 5vw 95px;
  }

  .tours-header {
    gap: 36px;
    margin-bottom: 36px;
  }

  .title-block h2 {
    font-size: clamp(3.8rem, 8vw, 6.2rem);
  }

  .tour-tabs {
    gap: 18px;
  }

  .dest-layout {
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 28px;
    align-items: center;
  }

  .film-frame {
    width: 100%;
    max-width: 360px;
  }

  .tour-cards {
    justify-content: flex-start;
    overflow-x: auto;
    gap: 18px;
    padding: 24px 6px 24px;
    scrollbar-width: none;
  }

  .tour-cards::-webkit-scrollbar {
    display: none;
  }

  .tour-card {
    width: 220px;
    min-width: 220px;
  }
}

@media (max-width: 900px) {
  .tours-section {
    padding: 70px 32px 90px;
  }

  .tours-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 24px;
  }

  .dest-layout {
    grid-template-columns: 1fr;
    gap: 26px;
  }

  .film-column {
    align-items: center;
  }

  .film-frame {
    width: min(100%, 420px);
    max-width: 420px;
  }

  .tour-cards {
    display: flex;
    justify-content: flex-start;
    gap: 18px;
    overflow-x: auto;
    padding: 10px 24px 20px;
    scrollbar-width: none;
  }

  .tour-cards::-webkit-scrollbar {
    display: none;
  }

  .tour-card {
    width: 220px;
    min-width: 220px;
  }
}

@media (max-width: 600px) {
  .tours-section {
    padding: 64px 20px 80px;
  }

  .tours-header {
    gap: 20px;
    margin-bottom: 26px;
  }

  .title-block h2 {
    font-size: clamp(2.6rem, 13vw, 3.8rem);
  }

  .tour-tabs {
    gap: 12px 16px;
  }

  .tour-tabs button {
    font-size: 0.78rem;
  }

  .dest-layout {
    grid-template-columns: 1fr;
    gap: 22px;
  }

  .film-column {
    align-items: center;
  }

  .film-frame {
    width: min(100%, 330px);
    max-width: 330px;
  }

  .tour-cards {
    display: flex;
    justify-content: flex-start;
    gap: 18px;
    overflow-x: auto;
    padding: 10px 24px 20px;
    scrollbar-width: none;
  }

  .tour-cards::-webkit-scrollbar {
    display: none;
  }

  .tour-card {
    width: 185px;
    min-width: 185px;
    border-radius: 22px;
  }

  .tour-card:hover {
    transform: translateY(-10px);
  }

  .card-overlay {
    padding: 20px 16px;
  }

  .card-overlay h3 {
    font-size: 1.15rem;
  }

  .card-overlay p {
    font-size: 0.82rem;
  }
}
</style>
