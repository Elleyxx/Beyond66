<template>
  <section class="panel">
    <h2>{{ t('community.detail.portfolio.title') }}</h2>

    <article v-for="portfolio in portfolios" :key="portfolio.id" class="portfolio">
      <h3>{{ portfolio.title || t('community.detail.portfolio.fallbackTitle') }}</h3>
      <p>{{ portfolio.content }}</p>

      <div v-if="portfolio.images?.length" class="image-grid">
        <img
          v-for="image in portfolio.images"
          :key="image.id || image.image_url || image"
          :src="resolveAssetUrl(image.image_url || image)"
          alt=""
        />
      </div>
    </article>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { resolveAssetUrl } from '@/services/apiBase'

defineProps({
  portfolios: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 34px;
  padding: 28px;
  background: rgba(var(--v-theme-surface), 0.72);
}

h2,
h3,
p {
  margin-top: 0;
}

h2 {
  font-size: 1.15rem;
  font-weight: 900;
}

.portfolio + .portfolio {
  margin-top: 18px;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
}

img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border-radius: 8px;
}

</style>
