<template>
  <section class="panel">
    <p class="eyebrow">{{ t('community.detail.summary.eyebrow') }}</p>
    <h2>{{ t('community.detail.summary.journey', { place: meta.country || routeLabel }) }}</h2>
    <p v-if="summary" class="summary-text">{{ summary }}</p>

    <div class="summary-grid">
      <div>
        <span>{{ t('community.detail.summary.country') }}</span>
        <strong>{{ meta.country || routeLabel }}</strong>
      </div>

      <div>
        <span>{{ t('community.detail.summary.dates') }}</span>
        <strong>{{ dateRange }}</strong>
      </div>

      <div>
        <span>{{ t('community.detail.summary.duration') }}</span>
        <strong>{{ t('community.detail.summary.days', { count: meta.duration || 0 }) }}</strong>
      </div>

      <div>
        <span>{{ t('community.detail.summary.budget') }}</span>
        <strong>{{ meta.budget || t('community.detail.summary.notSet') }}</strong>
      </div>

      <div>
        <span>{{ t('community.detail.summary.style') }}</span>
        <strong>{{ meta.style || t('community.detail.summary.notSet') }}</strong>
      </div>

      <div>
        <span>{{ t('community.detail.summary.season') }}</span>
        <strong>{{ meta.season || t('community.detail.summary.anySeason') }}</strong>
      </div>
    </div>

    <section v-if="hasDiary" class="diary-block">
      <p class="diary-eyebrow">Travel Diary</p>
      <h3>{{ diary.title || 'Journey Diary' }}</h3>
      <p v-if="diary.story" class="diary-story">{{ diary.story }}</p>

      <div v-if="diaryPhotos.length" class="diary-photos">
        <img v-for="photo in diaryPhotos" :key="photo" :src="resolveAssetUrl(photo)" alt="" />
      </div>
    </section>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveAssetUrl } from '@/services/apiBase'

const props = defineProps({
  trip: { type: Object, default: null },
})

const { t } = useI18n()
const meta = computed(() => props.trip?.meta || props.trip?.tripMeta || {})
const summary = computed(() => props.trip?.summary || '')
const diary = computed(() => props.trip?.diary || {})
const diaryPhotos = computed(() => Array.isArray(diary.value.photos) ? diary.value.photos : [])
const hasDiary = computed(() =>
  Boolean(diary.value.title || diary.value.story || diaryPhotos.value.length),
)
const routeLabel = computed(() => Array.isArray(meta.value.countryRoute) ? meta.value.countryRoute.join(' -> ') : t('countryNames.nordic'))
const dateRange = computed(() => {
  if (!meta.value.startDate || !meta.value.endDate) return t('community.detail.summary.datesNotSet')
  return `${meta.value.startDate} - ${meta.value.endDate}`
})
</script>

<style scoped>
.panel {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 28px;
  padding: clamp(20px, 3vw, 28px);
  background:
    linear-gradient(135deg, rgba(var(--v-theme-primary), 0.12), transparent 58%),
    rgba(var(--v-theme-background), 0.36);
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

h2 {
  margin: 0;
  font-size: clamp(1.5rem, 3vw, 2.35rem);
  line-height: 1.12;
  font-weight: 900;
}

.summary-text {
  margin: 14px 0 0;
  max-width: 680px;
  color: rgba(var(--v-theme-text), 0.76);
  line-height: 1.65;
}

.summary-grid {
  margin-top: 22px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.summary-grid div {
  min-height: 86px;
  padding: 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.74);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

span {
  display: block;
  margin-bottom: 6px;
  color: rgba(var(--v-theme-text), 0.58);
  font-size: 0.82rem;
  font-weight: 800;
}

strong {
  color: rgb(var(--v-theme-text));
  font-size: 1rem;
  font-weight: 900;
}

.diary-block {
  margin-top: 22px;
  padding: 18px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.74);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.diary-eyebrow {
  margin: 0 0 6px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.diary-block h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 900;
}

.diary-story {
  margin: 10px 0 0;
  color: rgba(var(--v-theme-text), 0.76);
  line-height: 1.65;
}

.diary-photos {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 14px;
}

.diary-photos img {
  width: 100%;
  aspect-ratio: 4 / 3;
  border-radius: 12px;
  object-fit: cover;
}

@media (max-width: 720px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }

  .diary-photos {
    grid-template-columns: 1fr;
  }
}
</style>
