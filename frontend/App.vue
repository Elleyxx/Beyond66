<template>
  <v-app>
    <Header v-if="!hideLayout" />

    <div class="layout-shell" :class="{ 'home-shell': isHomeRoute && !hideLayout }">
      <v-main class="layout-main">
        <RouterView />
      </v-main>
      <Footer v-if="!hideLayout" :is-home="isHomeRoute" />
    </div>
  </v-app>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Header from './components/layout/HeaderComp.vue'
import Footer from './components/layout/FooterComp.vue'

const route = useRoute()
const hideLayout = computed(() => route.meta.hideLayout === true)
const isHomeRoute = computed(() => route.name === 'home')
</script>

<style scoped>
.layout-shell {
  background: rgb(var(--v-theme-background));
}

.layout-main {
  background: transparent;
}

.layout-shell.home-shell {
  background:
    linear-gradient(
      to bottom,
      rgba(var(--v-theme-background), 0.9),
      rgba(var(--v-theme-background), 0.9)
    ),
    url('/assets/images/aurora_mountain.jpg') center/cover fixed;
}
</style>
