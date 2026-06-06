<template>
  <article class="post-card">
    <RouterLink class="post-image" :to="`/community/${post.id}`">
      <img v-if="coverImage" :src="coverImage" :alt="post.title" />
      <div v-else class="image-placeholder">{{ post.country || post.trip?.meta?.country || t('countryNames.nordic') }}</div>
      <span class="post-type">{{ post.typeLabel || t('community.card.tripPlan') }}</span>
    </RouterLink>

    <div class="post-body">
      <div class="author-row">
        <div class="avatar">{{ authorInitial }}</div>
        <span>{{ post.authorName || post.author_name || t('community.card.traveller') }}</span>
      </div>

      <RouterLink class="title-link" :to="`/community/${post.id}`">
        <h3>{{ post.title || t('community.card.untitled') }}</h3>
      </RouterLink>
      <p>{{ post.description || t('community.card.fallbackDescription') }}</p>

      <div v-if="tags.length" class="post-tags">
        <span v-for="tag in tags" :key="tag">#{{ tag }}</span>
      </div>

      <div class="post-meta">
        <span>{{ durationLabel }}</span>
        <span>{{ post.country || post.trip?.meta?.country || t('countryNames.nordic') }}</span>
        <span>{{ post.season || post.trip?.meta?.season || t('community.card.anySeason') }}</span>
      </div>

      <div class="post-actions">
        <button type="button"><i class="bi bi-heart"></i> {{ post.likes || 0 }}</button>
        <button type="button"><i class="bi bi-chat"></i> {{ post.comments || 0 }}</button>
        <button type="button" @click="$emit('save')"><i class="bi bi-bookmark"></i> {{ t('community.card.save') }}</button>
        <button type="button" @click="$emit('use-plan')">{{ t('community.card.usePlan') }}</button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  post: { type: Object, required: true },
})

defineEmits(['save', 'use-plan'])
const { t } = useI18n()

const coverImage = computed(() => props.post.coverImage || props.post.cover_image || '')
const authorInitial = computed(() => String(props.post.authorName || props.post.author_name || 'T').slice(0, 1).toUpperCase())
const tags = computed(() => {
  const rawTags = Array.isArray(props.post.tags)
    ? props.post.tags
    : Array.isArray(props.post.trip?.tags)
      ? props.post.trip.tags
      : []

  return rawTags.map((tag) => String(tag || '').trim().replace(/^#/, '')).filter(Boolean).slice(0, 5)
})
const durationLabel = computed(() => {
  const duration = props.post.duration || props.post.trip?.meta?.duration
  return duration ? t('community.card.days', { count: duration }) : t('community.card.flexible')
})
</script>

<style scoped>
.post-card {
  break-inside: avoid;
  margin-bottom: 18px;
  border-radius: 18px;
  overflow: hidden;
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
}

.post-image {
  position: relative;
  display: block;
  min-height: 220px;
  color: inherit;
  text-decoration: none;
}

.post-image img,
.image-placeholder {
  width: 100%;
  height: 220px;
  object-fit: cover;
  display: grid;
  place-items: center;
}

.image-placeholder {
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  font-weight: 900;
}

.post-type {
  position: absolute;
  left: 14px;
  top: 14px;
  border-radius: 999px;
  padding: 6px 10px;
  background: rgba(0, 0, 0, 0.45);
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
}

.post-body {
  padding: 16px;
}

.author-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 900;
}

.title-link {
  color: inherit;
  text-decoration: none;
}

.post-body h3 {
  margin: 0 0 8px;
}

.post-body p {
  margin: 0 0 12px;
  color: rgba(var(--v-theme-text), 0.68);
  line-height: 1.5;
}

.post-meta,
.post-actions,
.post-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.post-tags {
  margin-bottom: 12px;
}

.post-meta span {
  font-size: 0.76rem;
  font-weight: 800;
  padding: 5px 9px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.post-tags span {
  font-size: 0.72rem;
  font-weight: 850;
  color: rgba(var(--v-theme-text), 0.72);
}

.post-actions {
  margin-top: 14px;
}

.post-actions button {
  border: 0;
  background: rgba(var(--v-theme-background), 0.6);
  border-radius: 999px;
  padding: 7px 10px;
  cursor: pointer;
}
</style>
