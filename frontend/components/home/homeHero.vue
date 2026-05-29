<template>
  <section class="home-hero">
    <img
      class="hero-bg"
      :src="displayedHeroSrc"
      :alt="displayedHeroAlt"
    />
    <img
      v-if="incomingHeroSrc"
      class="hero-bg hero-bg-incoming"
      :class="{ 'is-visible': showIncomingHero }"
      :src="incomingHeroSrc"
      :alt="incomingHeroAlt"
    />

    <div class="hero-overlay"></div>

    <div class="hero-content">
      <div class="country-nav">
        <button
          v-for="(country, index) in countries"
          :key="country.slug"
          :class="{ active: activeCountry.slug === country.slug }"
          @click="$emit('change-country', country)"
        >
          <span>{{ country.name }}</span>
          <small>0{{ index + 1 }}/05</small>
        </button>
      </div>

      <Transition name="copy-fade" mode="out-in">
        <div :key="activeCountry.slug" class="hero-copy">
          <h1>{{ activeCountry.name }}</h1>
          <p>Experience Nordic landscapes, culture and winter adventures.</p>
          <router-link class="view-btn" :to="`/country/${activeCountry.slug}`">View More</router-link>
        </div>
      </Transition>
    </div>
  </section>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  countries: Array,
  activeCountry: Object,
})

defineEmits(['change-country'])

const displayedHeroSrc = ref(props.activeCountry?.hero || '')
const displayedHeroAlt = ref(props.activeCountry?.name || 'Nordic destination')
const incomingHeroSrc = ref('')
const incomingHeroAlt = ref('')
const showIncomingHero = ref(false)
let swapTimer = null

watch(
  () => props.activeCountry,
  (nextCountry) => {
    if (!nextCountry?.hero || nextCountry.hero === displayedHeroSrc.value) {
      return
    }

    const preloadImage = new Image()
    preloadImage.src = nextCountry.hero
    preloadImage.onload = () => {
      incomingHeroSrc.value = nextCountry.hero
      incomingHeroAlt.value = nextCountry.name || 'Nordic destination'

      requestAnimationFrame(() => {
        showIncomingHero.value = true
      })

      if (swapTimer) {
        clearTimeout(swapTimer)
      }

      swapTimer = setTimeout(() => {
        displayedHeroSrc.value = nextCountry.hero
        displayedHeroAlt.value = nextCountry.name || 'Nordic destination'
        incomingHeroSrc.value = ''
        incomingHeroAlt.value = ''
        showIncomingHero.value = false
      }, 900)
    }
  },
  { deep: true }
)

onBeforeUnmount(() => {
  if (swapTimer) {
    clearTimeout(swapTimer)
  }
})
</script>

<style scoped>
.home-hero {
  position: relative;
  width: 100vw;
  height: 82vh;
  left: 50%;
  transform: translateX(-50%);
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-bg-incoming {
  opacity: 0;
  transform: scale(1.03);
  transition: opacity 0.9s ease, transform 0.9s ease;
}

.hero-bg-incoming.is-visible {
  opacity: 1;
  transform: scale(1);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(to bottom, rgba(5, 15, 30, 0.2), rgba(5, 15, 30, 0.58)),
    linear-gradient(to right, rgba(5, 15, 30, 0.35), transparent);
}

.hero-content {
  position: relative;
  z-index: 2;
  height: 100%;
  padding: 0 8vw;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: rgb(var(--v-theme-primary));
}

.country-nav {
  display: flex;
  gap: 40px;
}

.country-nav button {
  background: transparent;
  border: none;
  color: rgba(var(--v-theme-muted), 0.62);
  text-transform: uppercase;
  font-weight: 800;
  cursor: pointer;
  text-align: left;
}

.country-nav button.active {
  color: rgb(var(--v-theme-primary));
}

.country-nav span {
  display: block;
  font-size: 0.9rem;
}

.country-nav small {
  font-size: 0.65rem;
}

.hero-copy {
  max-width: 720px;
}

.copy-fade-enter-active,
.copy-fade-leave-active {
  transition: opacity 0.7s ease, transform 0.7s ease;
}

.copy-fade-enter-from,
.copy-fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

.hero-content h1 {
  font-size: clamp(4rem, 10vw, 9rem);
  line-height: 0.9;
  margin: 80px 0 12px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 4px;
}

.hero-content p {
  max-width: 520px;
  font-size: 1rem;
  opacity: 0.9;
}

.view-btn {
  width: fit-content;
  margin-top: 18px;
  padding: 10px 24px;
  border: 1px solid rgb(var(--v-theme-on-surface));
  background: transparent;
  color: rgb(var(--v-theme-on-surface));
  border-radius: 999px;
  cursor: pointer;
  text-decoration: none;
}

@media (max-width: 768px) {
  .home-hero {
    height: 76vh;
  }

  .hero-content {
    padding: 0 24px;
  }

  .country-nav {
    gap: 18px;
    overflow-x: auto;
  }

  .hero-content h1 {
    font-size: 4rem;
  }
}
</style>
