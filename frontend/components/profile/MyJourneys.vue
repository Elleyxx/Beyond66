<template>
  <section class="section-block">
    <div class="section-title">
      <p>{{ t('profilePage.journeys.eyebrow') }}</p>
      <h2>{{ t('profilePage.journeys.title') }}</h2>
    </div>

    <div v-if="journeys.length" class="journey-grid">
      <article
        v-for="journey in journeys"
        :key="journey.id || journey.title"
        class="journey-card"
        :class="{ 'is-clickable': journey.postId }"
        :role="journey.postId ? 'link' : undefined"
        :tabindex="journey.postId ? 0 : undefined"
        @click="openCommunityPost(journey)"
        @keydown.enter="openCommunityPost(journey)"
      >
        <img :src="resolveAssetUrl(journey.image)" :alt="journey.title" />

        <div class="journey-content">
          <div>
            <p class="country">{{ journey.country }}</p>
            <h3>{{ journey.title }}</h3>
            <small>
              {{ journey.days ? `${journey.days} Days · ` : '' }}{{ journey.date }}
            </small>
          </div>

          <div class="journey-tags">
            <span :class="{ active: journey.hasPlanner }">{{ t('profilePage.journeys.tagPlanner') }}</span>
            <span :class="{ active: journey.hasDiary }">{{ t('profilePage.journeys.tagDiary') }}</span>
            <span :class="{ active: journey.isPublic }">
              {{ journey.isPublic ? t('profilePage.journeys.tagPublic') : t('profilePage.journeys.tagPrivate') }}
            </span>
          </div>

          <div class="journey-actions">
            <button type="button" @click.stop="$emit('view', journey)">{{ t('profilePage.journeys.actionView') }}</button>
            <button type="button" @click.stop="$emit('edit', journey)">{{ t('profilePage.journeys.actionEdit') }}</button>
            <button type="button" @click.stop="$emit('toggle-share', journey)">
              {{ journey.isPublic ? t('profilePage.journeys.actionUnshare') : t('profilePage.journeys.actionShare') }}
            </button>
            <button type="button" @click.stop="$emit('add-diary', journey)">
              {{ journey.hasDiary ? t('profilePage.journeys.actionEditDiary') : t('profilePage.journeys.actionAddDiary') }}
            </button>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="empty-panel">
      <div class="empty-icon-wrap">
        <i class="bi bi-map"></i>
      </div>
      <div class="empty-text">
        <h3>{{ t('profilePage.journeys.emptyTitle') }}</h3>
        <p>{{ t('profilePage.journeys.emptyText') }}</p>
      </div>
      <RouterLink to="/trip-planner" class="empty-btn">
        {{ t('profilePage.journeys.emptyButton') }}
        <i class="bi bi-arrow-right"></i>
      </RouterLink>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { resolveAssetUrl } from '@/services/apiBase'

const { t } = useI18n()

defineProps({
  journeys: {
    type: Array,
    required: true,
  },
})

defineEmits(['view', 'edit', 'toggle-share', 'add-diary'])

const router = useRouter()

function openCommunityPost(journey) {
  if (journey.postId) {
    router.push(`/community/${journey.postId}`)
  }
}
</script>

<style scoped>
.section-block {
  margin-top: 36px;
}

.section-title p {
  margin-bottom: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.section-title h2 {
  margin-bottom: 22px;
  font-family: 'Playfair Display', serif;
  font-size: 2.3rem;
}

.journey-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

.journey-card {
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  outline: none;
}

.journey-card.is-clickable {
  cursor: pointer;
}

.journey-card.is-clickable:hover,
.journey-card.is-clickable:focus-visible {
  transform: translateY(-6px);
  box-shadow: 0 18px 36px rgba(var(--v-theme-background), 0.18);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.journey-card img {
  width: 100%;
  height: 210px;
  object-fit: cover;
}

.journey-content {
  padding: 22px;
}

.country {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.journey-content h3 {
  margin: 4px 0 6px;
  font-size: 1.25rem;
}

.journey-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.journey-tags span {
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.08);
  font-size: 0.78rem;
  opacity: 0.65;
}

.journey-tags span.active {
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.14);
  opacity: 1;
}

.journey-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.journey-actions button {
  border: 1px solid rgba(var(--v-theme-primary), 0.28);
  border-radius: 999px;
  padding: 7px 10px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
  font-size: 0.78rem;
  font-weight: 850;
  cursor: pointer;
}

.journey-actions button:hover {
  background: rgba(var(--v-theme-primary), 0.16);
}

.empty-panel {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
  padding: 64px 32px;
  border: 2px dashed rgba(var(--v-theme-primary), 0.22);
  border-radius: 28px;
  background: rgba(var(--v-theme-primary), 0.03);
  text-align: center;
}

.empty-icon-wrap {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
  font-size: 2rem;
}

.empty-text h3 {
  margin: 0 0 8px;
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
}

.empty-text p {
  margin: 0;
  opacity: 0.62;
  max-width: 360px;
  line-height: 1.6;
}

.empty-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 0;
  border-radius: 999px;
  padding: 12px 26px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-size: 0.88rem;
  font-weight: 850;
  text-decoration: none;
  cursor: pointer;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.empty-btn:hover {
  opacity: 0.88;
  transform: translateY(-2px);
}

@media (max-width: 900px) {
  .journey-grid {
    grid-template-columns: 1fr;
  }
}
</style>
