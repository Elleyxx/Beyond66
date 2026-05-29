<template>
  <main class="home-page">
    <HomeHero
      :countries="countries"
      :active-country="activeCountry"
      @change-country="setActiveCountry"
    />

    <HomeTours
      :countries="countries"
      :active-country="activeCountry"
      @change-country="setActiveCountry"
    />

    <HomeAbout />
  </main>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

import HomeHero from '@/components/home/homeHero.vue'
import HomeTours from '@/components/home/homeTopDest.vue'
import HomeAbout from '@/components/home/HomeAbout.vue'

const countries = [
  {
    name: 'Norway',
    slug: 'norway',
    hero: '/assets/images/Norway/Geirangerfjord.jpg',
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
    ],
  },
  {
    name: 'Sweden',
    slug: 'sweden',
    hero: '/assets/images/Sweden/sweden.jpg',
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
    ],
  },
  {
    name: 'Finland',
    slug: 'finland',
    hero: '/assets/images/Finland/finland.jpg',
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
    ],
  },
  {
    name: 'Iceland',
    slug: 'iceland',
    hero: '/assets/images/Iceland/iceland_lagoon.jpg',
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
    ],
  },
  {
    name: 'Denmark',
    slug: 'denmark',
    hero: '/assets/images/Denmark/denmark.jpg',
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
    ],
  },
]

const activeCountry = ref(countries[0])
let heroRotationTimer = 0

function setActiveCountry(country) {
  activeCountry.value = country
}

function rotateHeroCountry() {
  const currentIndex = countries.findIndex((country) => country.slug === activeCountry.value.slug)
  const nextIndex = (currentIndex + 1) % countries.length
  activeCountry.value = countries[nextIndex]
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
  background: rgb(var(--v-theme-background));
  color: rgb(var(--v-theme-text));
  overflow-x: hidden;
}
</style>
