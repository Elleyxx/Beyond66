<template>
  <main class="home-page">
    <HomeHero
      :countries="countries"
      :active-country="activeCountry"
      @change-country="setActiveCountry"
    />

    <HomeTours :countries="countries" />

    <HomeAbout />
    <HomeInfo />
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

import HomeHero from '@/components/home/homeHero.vue'
import HomeTours from '@/components/home/homeTopDest.vue'
import HomeAbout from '@/components/home/HomeAbout.vue'
import HomeInfo from '@/components/home/homeInfo.vue'

const countries = [
  {
    name: 'Norway',
    slug: 'norway',
    hero: '/assets/images/Norway/Geirangerfjord.jpg',
    video: '/assets/videos/norway_vid.mp4',
    places: [
      {
        title: 'Geirangerfjord',
        desc: 'Sail between cliffs, waterfalls, and deep blue fjord water.',
        image: '/assets/images/Norway/geirangerfjord1.jpg',
      },
      {
        title: 'Lofoten Islands',
        desc: 'Arctic peaks, fishing villages, beaches, and midnight sun.',
        image: '/assets/images/Norway/lofoten1.jpg',
      },
      {
        title: 'Tromso',
        desc: 'Northern lights, winter adventures, and Arctic culture.',
        image: '/assets/images/Norway/tromso1.jpg',
      },
      {
        title: 'Bergen',
        desc: 'Historic harbor fronts, mountain funicular views, and fjord gateways.',
        image: '/assets/images/Norway/bergen1.jpeg',
      },
      {
        title: 'Preikestolen',
        desc: 'Hike to a dramatic cliff plateau above the Lysefjord.',
        image: '/assets/images/Norway/preikestolen1.jpeg',
      },
    ],
  },
  {
    name: 'Sweden',
    slug: 'sweden',
    hero: '/assets/images/Sweden/sweden.jpg',
    video: '/assets/videos/sweden_vid.mp4',
    places: [
      {
        title: 'Stockholm',
        desc: 'Waterfront architecture, old-town lanes, and island views.',
        image: '/assets/images/Sweden/stockholm1.jpg',
      },
      {
        title: 'Abisko',
        desc: 'A northern lights gateway surrounded by Arctic wilderness.',
        image: '/assets/images/Sweden/abisko1.jpg',
      },
      {
        title: 'Gotland',
        desc: 'Medieval streets, coastlines, and slow island summers.',
        image: '/assets/images/Sweden/gotland1.jpeg',
      },
      {
        title: 'Gothenburg',
        desc: 'Canals, seafood markets, and a creative west-coast city vibe.',
        image: '/assets/images/Sweden/gothenburg1.jpeg',
      },
      {
        title: 'Kiruna',
        desc: 'Arctic landscapes, snowy trails, and winter sky experiences.',
        image: '/assets/images/Sweden/kiruna1.jpg',
      },
    ],
  },
  {
    name: 'Finland',
    slug: 'finland',
    hero: '/assets/images/Finland/finland.jpg',
    video: '/assets/videos/finland_vid.mp4',
    places: [
      {
        title: 'Rovaniemi',
        desc: 'Snowy Lapland forests, reindeer, and winter magic.',
        image: '/assets/images/Finland/Rovaniemi.jpg',
      },
      {
        title: 'Helsinki',
        desc: 'Design districts, sea air, islands, and Nordic food.',
        image: '/assets/images/Finland/helsinki1.jpeg',
      },
      {
        title: 'Lake Saimaa',
        desc: 'Quiet lake routes, cabins, forests, and open water.',
        image: '/assets/images/Finland/saimaa1.jpg',
      },
      {
        title: 'Turku',
        desc: 'Riverside culture, medieval landmarks, and coastal archipelago routes.',
        image: '/assets/images/Finland/turku1.jpg',
      },
      {
        title: 'Porvoo',
        desc: 'Storybook old town lanes and colorful riverside warehouses.',
        image: '/assets/images/Finland/porvoo1.jpg',
      },
    ],
  },
  {
    name: 'Iceland',
    slug: 'iceland',
    hero: '/assets/images/Iceland/iceland_lagoon.jpg',
    video: '/assets/videos/iceland_vid.mp4',
    places: [
      {
        title: 'Jokulsarlon',
        desc: 'Blue icebergs drift through a glacial lagoon.',
        image: '/assets/images/Iceland/jokulsarlon1.jpeg',
      },
      {
        title: 'Vik',
        desc: 'Black sand beaches, basalt stacks, and Atlantic waves.',
        image: '/assets/images/Iceland/vik1.jpg',
      },
      {
        title: 'Blue Lagoon',
        desc: 'Geothermal water surrounded by volcanic lava fields.',
        image: '/assets/images/Iceland/bluelagoon1.jpeg',
      },
      {
        title: 'Golden Circle',
        desc: 'Geysers, waterfalls, and rift-valley landscapes in one route.',
        image: '/assets/images/Iceland/goldencircle1.jpeg',
      },
      {
        title: 'Reykjavik',
        desc: 'Colorful streets, modern Nordic culture, and seaside city walks.',
        image: '/assets/images/Iceland/Reykjavík1.jpeg',
      },
    ],
  },
  {
    name: 'Denmark',
    slug: 'denmark',
    hero: '/assets/images/Denmark/denmark.jpg',
    video: '/assets/videos/denmark_vid.mp4',
    places: [
      {
        title: 'Copenhagen',
        desc: 'Canals, design culture, cycling streets, and harbor life.',
        image: '/assets/images/Denmark/copenhagen1.jpeg',
      },
      {
        title: 'Nyhavn',
        desc: 'Colorful waterfront facades and classic Danish city charm.',
        image: '/assets/images/Denmark/Nyhavn.jpg',
      },
      {
        title: 'Mons Klint',
        desc: 'White chalk cliffs above the Baltic coast.',
        image: '/assets/images/Denmark/monsklint1.jpg',
      },
      {
        title: 'Aarhus',
        desc: 'Contemporary art, cozy quarters, and vibrant Danish city life.',
        image: '/assets/images/Denmark/aarhus1.jpg',
      },
      {
        title: 'Odense',
        desc: 'Fairytale history, walkable streets, and relaxed local culture.',
        image: '/assets/images/Denmark/odense1.jpeg',
      },
    ],
  },
]

const activeCountrySlug = ref(countries[0].slug)
const activeCountry = computed(
  () => countries.find((country) => country.slug === activeCountrySlug.value) || countries[0]
)
let heroRotationTimer = 0

function setActiveCountry(country) {
  const targetSlug = typeof country === 'string' ? country : country?.slug
  if (!targetSlug) return

  const matchedCountry = countries.find((item) => item.slug === targetSlug)
  if (matchedCountry) {
    activeCountrySlug.value = matchedCountry.slug
  }
}

function rotateHeroCountry() {
  const currentIndex = countries.findIndex((country) => country.slug === activeCountry.value.slug)
  const nextIndex = (currentIndex + 1) % countries.length
  activeCountrySlug.value = countries[nextIndex].slug
}

onMounted(() => {
  heroRotationTimer = window.setInterval(rotateHeroCountry, 6500)
})

onBeforeUnmount(() => {
  if (heroRotationTimer) window.clearInterval(heroRotationTimer)
})
</script>

<style scoped>
.home-page {
  background: transparent;
  color: rgb(var(--v-theme-text));
  overflow-x: hidden;
}
</style>
