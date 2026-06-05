<template>
  <section class="home-about">
    <div class="about-copy">
      <p class="eyebrow"><i class="bi bi-snow2"></i> {{ t('home.info.eyebrow') }}</p>
      <h2>{{ t('home.info.title') }}</h2>
      <p>
        {{ t('home.info.description') }}
      </p>
    </div>

    <div class="about-stats" :aria-label="t('home.info.statsLabel')">
      <article>
        <strong>5</strong>
        <span>{{ t('home.info.countries') }}</span>
      </article>
      <article>
        <strong>60+</strong>
        <span>{{ t('home.info.places') }}</span>
      </article>
      <article>
        <strong>66°</strong>
        <span>{{ t('home.info.inspiration') }}</span>
      </article>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
.home-about {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
  gap: 48px;
  align-items: center;
  padding: 0 8vw 110px;
  background: transparent;
}

.about-copy {
  max-width: 760px;
}

.eyebrow {
  margin: 0 0 14px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.85rem;
  font-weight: 850;
  letter-spacing: 0.18em;
}

.about-copy h2 {
  margin: 0;
  max-width: 720px;
  color: rgb(var(--v-theme-text));
  font-size: clamp(2rem, 4rem, 4rem);
  line-height: 1.1;
  font-weight: 800;
}

.about-copy p:last-child {
  max-width: 620px;
  margin: 24px 0 0;
  color: rgb(var(--v-theme-muted));
  line-height: 1.75;
}

.about-stats {
  display: grid;
  gap: 14px;
}

.about-stats article {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 24px;
  padding: 22px 0;
  border-bottom: 1px solid rgba(var(--v-theme-text), 0.16);
}

.about-stats strong {
  color: rgb(var(--v-theme-primary));
  font-size: 2.4rem;
  line-height: 1;
}

.about-stats span {
  color: rgb(var(--v-theme-text));
  font-weight: 800;
  text-align: right;
  font-size: 1.5rem;
}

@media (max-width: 820px) {
  .home-about {
    grid-template-columns: 1fr;
    padding: 64px 24px 80px;
  }

  .about-copy h2 {
    font-size: 2.4rem;
  }
}
</style>
