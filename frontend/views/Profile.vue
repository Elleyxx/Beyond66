<template>
  <v-container class="py-10">
    <h1>{{ t('profile.title') }}</h1>
    <p>{{ t('profile.loginRequired') }}</p>
  </v-container>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>
