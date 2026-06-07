<template>
  <section class="panel">
    <h2>{{ t('community.detail.comments.title') }}</h2>
    <p v-if="!comments.length" class="empty">{{ t('community.detail.comments.empty') }}</p>

    <article v-for="comment in comments" :key="comment.id || comment.created_at" class="comment">
      <div class="comment-author">
        <img
          v-if="comment.avatar"
          :src="resolveAssetUrl(comment.avatar)"
          :alt="comment.author || comment.username"
          class="avatar"
        />
        <div v-else class="avatar avatar-fallback">
          {{ (comment.author || comment.username || '?').slice(0, 1).toUpperCase() }}
        </div>
        <strong>{{ comment.author || comment.username || t('community.detail.comments.authorFallback') }}</strong>
      </div>
      <p>{{ comment.comment || comment.body }}</p>
    </article>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { resolveAssetUrl } from '@/services/apiBase'

defineProps({
  comments: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<style scoped>
.panel {
  border: 0;
  border-radius: 0;
  padding: 0;
  background: transparent;
}

h2,
p {
  margin-top: 0;
}

h2 {
  font-size: 1.15rem;
  font-weight: 900;
}

.comment {
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(var(--v-theme-background), 0.36);
}

.comment + .comment {
  margin-top: 10px;
}

.comment-author {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.avatar {
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-fallback {
  display: grid;
  place-items: center;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
  font-size: 0.85rem;
  font-weight: 900;
}

.comment strong {
  font-size: 0.9rem;
}

.comment p {
  margin: 0;
  padding-left: 44px;
  color: rgba(var(--v-theme-text), 0.82);
  line-height: 1.5;
}

.empty {
  color: rgba(var(--v-theme-text), 0.62);
}
</style>
