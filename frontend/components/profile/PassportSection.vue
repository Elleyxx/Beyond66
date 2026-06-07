<template>
  <section class="profile-panel passport-panel">
    <div class="section-head">
      <p>{{ t('profilePage.passport.eyebrow') }}</p>
      <h2>{{ t('profilePage.passport.title') }}</h2>
    </div>

    <div class="stamp-grid">
      <div
        v-for="country in countries"
        :key="country.code"
        class="passport-stamp"
        :class="{ stamped: country.stamped, locked: !country.stamped }"
        :style="{ '--stamp-color': country.color || 'rgb(var(--v-theme-primary))' }"
      >
        <div class="stamp-inner">
          <div class="stamp-country">
            <span></span>
            <h3>{{ country.name }}</h3>
            <span></span>
          </div>

          <i :class="country.icon || 'bi bi-compass'" class="stamp-symbol"></i>

          <strong>
            {{ country.stamped ? t('profilePage.passport.discovered') : t('profilePage.passport.undiscovered') }}
          </strong>

          <p>
            {{ country.stamped ? country.activity || t('profilePage.passport.journeyCreated') : t('profilePage.passport.startJourney') }}
          </p>

          <small>
            {{ country.stamped && country.date ? country.date : t('profilePage.passport.brand') }}
          </small>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  countries: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.profile-panel {
  padding: 30px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 18px;
  background: rgb(var(--v-theme-surface));
}

.section-head p {
  margin-bottom: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.section-head h2 {
  margin-bottom: 55px;
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
}

.stamp-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 22px;
}

.passport-stamp {
  width: 210px;
  flex-shrink: 0;
  min-height: 165px;
  padding: 10px;
  color: var(--stamp-color);
  border: 2px solid currentColor;
  border-radius: 18px;
  background:
    radial-gradient(circle at 20% 15%, rgba(255, 255, 255, 0.9), transparent 25%),
    linear-gradient(145deg, #fffdf6, #fff3d8);
  box-shadow: 0 12px 26px rgba(var(--v-theme-background), 0.14);
  transform: rotate(-1.5deg);
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease,
    opacity 0.25s ease;
}

.passport-stamp:nth-child(even) {
  transform: rotate(1.5deg);
}

.passport-stamp:hover {
  transform: rotate(0deg) translateY(-4px);
  box-shadow: 0 18px 34px rgba(var(--v-theme-background), 0.18);
}

.passport-stamp.locked {
  opacity: 0.42;
  filter: grayscale(0.35);
}

.stamp-inner {
  height: 100%;
  min-height: 145px;
  padding: 14px 12px;
  display: grid;
  grid-template-rows: auto 1fr auto auto auto;
  align-items: center;
  justify-items: center;
  gap: 7px;
  border: 2px dashed currentColor;
  border-radius: 12px;
  text-align: center;
  text-transform: uppercase;
}

.stamp-country {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 8px;
}

.stamp-country span {
  width: 100%;
  height: 2px;
  background: currentColor;
  opacity: 0.75;
}

.stamp-country h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 900;
  line-height: 1;
  letter-spacing: 0.12em;
  white-space: nowrap;
}

.stamp-symbol {
  font-size: 2.4rem;
  line-height: 1;
  opacity: 0.95;
}

.stamp-inner strong {
  width: 100%;
  padding: 5px 8px;
  border-top: 2px solid currentColor;
  border-bottom: 2px solid currentColor;
  font-size: 0.78rem;
  font-weight: 900;
  line-height: 1.1;
  letter-spacing: 0.08em;
}

.stamp-inner p,
.stamp-inner small {
  margin: 0;
  font-weight: 900;
  line-height: 1;
}

.stamp-inner p {
  font-size: 0.78rem;
  letter-spacing: 0.08em;
}

.stamp-inner small {
  font-size: 0.68rem;
  letter-spacing: 0.08em;
  opacity: 0.78;
}

@media (max-width: 900px) {
  .stamp-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 520px) {
  .stamp-grid {
    grid-template-columns: 1fr;
  }
}
</style>
