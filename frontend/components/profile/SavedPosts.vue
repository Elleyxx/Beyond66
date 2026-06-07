<template>
  <section class="section-block">
    <div class="section-title">
      <p>{{ t('profilePage.savedPosts.eyebrow') }}</p>
      <h2>{{ t('profilePage.savedPosts.title') }}</h2>
    </div>

    <div v-if="posts.length" class="posts-grid">
      <article
        v-for="post in posts"
        :key="post.id"
        class="post-card"
        role="link"
        tabindex="0"
        @click="router.push(`/community/${post.id}`)"
        @keydown.enter="router.push(`/community/${post.id}`)"
      >
        <button
          type="button"
          class="unsave-btn"
          :aria-label="`Remove ${post.title} from saved`"
          title="Remove from saved"
          @click.stop="$emit('unsave', post.id)"
        >
          <i class="bi bi-bookmark-fill"></i>
        </button>

        <div class="card-image">
          <img v-if="post.coverImage" :src="resolveAssetUrl(post.coverImage)" :alt="post.title" />
          <div v-else class="image-placeholder">{{ post.country }}</div>
        </div>

        <div class="card-body">
          <div class="author-row">
            <div class="avatar">{{ authorInitial(post) }}</div>
            <span class="author-name">{{ post.authorName }}</span>
          </div>

          <p class="card-country">{{ post.country }}</p>
          <h3>{{ post.title }}</h3>
          <p class="card-desc">{{ post.description }}</p>

          <div class="card-stats">
            <span><i class="bi bi-heart"></i> {{ post.likes }}</span>
            <span><i class="bi bi-chat"></i> {{ post.comments }}</span>
          </div>

          <small v-if="post.savedAt" class="card-date">
            <i class="bi bi-clock"></i>
            {{ t('profilePage.savedPosts.savedAt', { date: formatDate(post.savedAt) }) }}
          </small>
        </div>

        <div class="card-hover-cta">
          <span>{{ t('profilePage.savedPosts.viewCta') }} <i class="bi bi-arrow-right"></i></span>
        </div>
      </article>
    </div>

    <div v-else class="empty-panel">
      <div class="empty-icon-wrap">
        <i class="bi bi-bookmark-heart"></i>
      </div>
      <div class="empty-text">
        <h3>{{ t('profilePage.savedPosts.emptyTitle') }}</h3>
        <p>{{ t('profilePage.savedPosts.emptyText') }}</p>
      </div>
      <RouterLink to="/community" class="empty-btn">
        {{ t('profilePage.savedPosts.emptyButton') }}
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
const router = useRouter()

defineProps({
  posts: { type: Array, required: true },
})

defineEmits(['unsave'])

function authorInitial(post) {
  return String(post.authorName || 'T').slice(0, 1).toUpperCase()
}

function formatDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.section-block {
  margin: 50px 0 100px;
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

/* Grid — same 3-column as saved destinations */
.posts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

/* Card */
.post-card {
  position: relative;
  border-radius: 28px;
  overflow: hidden;
  background: rgba(var(--v-theme-surface), 0.96);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  cursor: pointer;
  outline: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.post-card:hover,
.post-card:focus-visible {
  transform: translateY(-6px);
  box-shadow: 0 18px 36px rgba(0, 0, 0, 0.14);
}

/* Unsave button */
.unsave-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 4;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  backdrop-filter: blur(10px);
  cursor: pointer;
  transition: transform 0.2s ease, background 0.2s ease;
}

.unsave-btn:hover {
  transform: translateY(-2px) scale(1.08);
  background: rgba(var(--v-theme-primary), 0.8);
}

/* Image */
.card-image {
  height: 210px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.45s ease;
}

.post-card:hover .card-image img {
  transform: scale(1.06);
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
}

/* Body */
.card-body {
  padding: 18px 22px 14px;
}

.author-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 0.75rem;
  font-weight: 900;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  flex-shrink: 0;
}

.author-name {
  font-size: 0.82rem;
  opacity: 0.7;
}

.card-country {
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  margin: 0 0 4px;
}

.card-body h3 {
  margin: 0 0 6px;
  font-size: 1.15rem;
  line-height: 1.25;
}

.card-desc {
  margin: 0 0 12px;
  font-size: 0.85rem;
  line-height: 1.5;
  opacity: 0.65;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-stats {
  display: flex;
  gap: 12px;
  margin-bottom: 10px;
}

.card-stats span {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  font-weight: 800;
  color: rgb(var(--v-theme-primary));
}

.card-stats i {
  font-size: 0.82rem;
}

.card-date {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.76rem;
  opacity: 0.5;
}

/* Hover CTA */
.card-hover-cta {
  padding: 0 22px 18px;
}

.card-hover-cta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  opacity: 0;
  transform: translateY(6px);
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.post-card:hover .card-hover-cta span,
.post-card:focus-visible .card-hover-cta span {
  opacity: 1;
  transform: translateY(0);
}

/* Empty state */
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

@media (max-width: 1100px) {
  .posts-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 700px) {
  .posts-grid {
    grid-template-columns: 1fr;
  }
}
</style>
