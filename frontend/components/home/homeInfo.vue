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

@media (max-width: 1250px) {
  .home-about {
    grid-template-columns: 1fr;
    gap: 34px;
    padding: 70px 6vw 90px;
  }

  .about-copy {
    max-width: 900px;
  }

  .about-copy h2 {
    max-width: 850px;
    font-size: clamp(2.6rem, 6vw, 4.2rem);
  }

  .about-copy p:last-child {
    max-width: 760px;
  }

  .about-stats {
    max-width: 760px;
    width: 100%;
  }
}

@media (max-width: 900px) {
  .home-about {
    gap: 30px;
    padding: 60px 5vw 80px;
  }

  .eyebrow {
    font-size: 0.78rem;
    letter-spacing: 0.14em;
  }

  .about-copy h2 {
    font-size: clamp(2.2rem, 7vw, 3.4rem);
  }

  .about-copy p:last-child {
    margin-top: 18px;
    font-size: 0.98rem;
    line-height: 1.7;
  }

  .about-stats {
    max-width: 620px;
  }

  .about-stats article {
    padding: 18px 0;
  }

  .about-stats strong {
    font-size: 2.1rem;
  }

  .about-stats span {
    font-size: 1.25rem;
  }
}

@media (max-width: 600px) {
  .home-about {
    gap: 24px;
    padding: 50px 20px 70px;
  }

  .eyebrow {
    font-size: 0.72rem;
    letter-spacing: 0.1em;
  }

  .about-copy h2 {
    font-size: clamp(1.9rem, 9vw, 2.8rem);
  }

  .about-copy p:last-child {
    font-size: 0.92rem;
    line-height: 1.65;
  }

  .about-stats article {
    align-items: flex-start;
    flex-direction: column;
    gap: 8px;
    padding: 16px 0;
  }

  .about-stats strong {
    font-size: 2rem;
  }

  .about-stats span {
    text-align: left;
    font-size: 1rem;
  }
}
</style>
