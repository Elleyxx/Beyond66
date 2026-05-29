<template>
  <v-app>
    <Header v-if="!hideLayout" />
    <v-main>
      <RouterView />
    </v-main>
    <Footer v-if="!hideLayout" />
  </v-app>
</template>

<script setup>
  import { computed } from 'vue'
  import { useRoute } from 'vue-router'
  import Header from './components/layout/HeaderComp.vue'
  import Footer from './components/layout/FooterComp.vue'

  const route = useRoute()
  const hideLayout = computed(() => route.meta.hideLayout === true)
</script>
