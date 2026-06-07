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
          <span>{{ heroCountryName(country) }}</span>
          <small>0{{ index + 1 }}/05</small>
        </button>
      </div>

      <Transition name="copy-fade" mode="out-in">
        <div :key="activeCountry.slug" class="hero-copy">
          <h1>{{ heroCountryName(activeCountry) }}</h1>
          <p>{{ heroCountrySubtitle(activeCountry) }}</p>
          <button class="view-btn" type="button" @click="openActiveCountry">
            {{ t('home.hero.viewMore') }}
          </button>
        </div>
      </Transition>
    </div>
  </section>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const props = defineProps({
  countries: Array,
  activeCountry: Object,
})

defineEmits(['change-country'])

const router = useRouter()
const { t } = useI18n()
const displayedHeroSrc = ref(props.activeCountry?.hero || '')
const displayedHeroAlt = ref(props.activeCountry?.name || t('home.hero.destinationAlt'))
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
      incomingHeroAlt.value = nextCountry.name || t('home.hero.destinationAlt')

      requestAnimationFrame(() => {
        showIncomingHero.value = true
      })

      if (swapTimer) {
        clearTimeout(swapTimer)
      }

      swapTimer = setTimeout(() => {
        displayedHeroSrc.value = nextCountry.hero
        displayedHeroAlt.value = nextCountry.name || t('home.hero.destinationAlt')
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

function openActiveCountry() {
  if (!props.activeCountry?.slug) return
  router.push(`/country/${props.activeCountry.slug}`)
}

function heroCountryName(country) {
  if (!country?.slug) return country?.name || ''
  const key = `home.hero.countries.${country.slug}.name`
  const translated = t(key)
  return translated === key ? country.name : translated
}

function heroCountrySubtitle(country) {
  if (!country?.slug) return t('home.hero.subtitle')
  const key = `home.hero.countries.${country.slug}.subtitle`
  const translated = t(key)
  return translated === key ? t('home.hero.subtitle') : translated
}
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
  color: rgba(var(--v-theme-muted), 0.80);
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
  margin: 80px 0 50px;
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
  border: 1.8px solid rgb(var(--v-theme-primary));
  background: transparent;
  color: rgb(var(--v-theme-primary));
  border-radius: 999px;
  cursor: pointer;
  text-decoration: none;
}

@media (max-width: 1250px) {
  .home-hero {
    height: 72vh;
    min-height: 520px;
    margin-top: 22px; /* space for mobile header */
  }

  .hero-content {
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 0 48px 48px;
  }

  .country-nav {
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
  }

  .hero-copy {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .hero-content h1 {
    margin: 30px 0 18px;
  }
}

@media (max-width: 900px) {
  .home-hero {
    height: 66vh;
    min-height: 460px;
    margin-top: 22px;
  }

  .hero-content {
    padding: 0 24px 36px;
  }

  .country-nav {
    justify-content: center;
    gap: 14px;
  }

  .hero-content h1 {
    font-size: 3.6rem;
    margin: 22px 0 12px;
  }

  .hero-content p {
    max-width: 420px;
    font-size: 0.9rem;
  }
}

@media (max-width: 600px) {
  .home-hero {
    height: 58vh;
    min-height: 390px;
    margin-top: 22px;
  }

  .hero-content {
    padding: 0 18px 28px;
  }

  .country-nav {
    gap: 10px;
  }

  .country-nav span {
    font-size: 0.68rem;
  }

  .country-nav small {
    font-size: 0.5rem;
  }

  .hero-content h1 {
    font-size: 2.7rem;
    margin: 18px 0 10px;
  }

  .hero-content p {
    max-width: 300px;
    font-size: 0.8rem;
    line-height: 1.4;
  }

  .view-btn {
    margin-top: 8px;
    padding: 7px 18px;
    font-size: 0.75rem;
  }
}
</style>
