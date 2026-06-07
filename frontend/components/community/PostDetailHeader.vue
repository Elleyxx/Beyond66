<template>
  <header class="detail-header">
    <img v-if="coverImage" :src="coverImage" :alt="post?.title" />
    <button class="back-button" type="button" @click="goBack">
      <i class="bi bi-arrow-left"></i>
      <span>{{ t('community.detail.back') }}</span>
    </button>

    <div class="header-content">
      <div class="header-copy">
        <p class="status-pill">{{ post?.status || t('community.detail.statusFallback') }}</p>
        <h1>{{ post?.title || t('community.detail.titleFallback') }}</h1>
        <span class="description">{{ post?.description || t('community.detail.descriptionFallback') }}</span>
      </div>

      <div class="header-actions">
        <button class="like-button" :class="{ liked: post?.liked }" type="button" @click="$emit('like')">
          <i :class="post?.liked ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
          {{ post?.likes || 0 }}
        </button>
        <button class="save-button" type="button" @click="$emit(post?.isOwner ? 'edit' : 'save')">
          <i :class="post?.isOwner ? 'bi bi-pencil-square' : 'bi bi-bookmark'"></i>
          {{ post?.isOwner ? 'Edit Post' : t('community.detail.savePost') }}
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { resolveAssetUrl } from '@/services/apiBase'

const props = defineProps({
  post: { type: Object, default: null },
})

defineEmits(['save', 'edit', 'like'])

const router = useRouter()
const { t } = useI18n()
const coverImage = computed(() => resolveAssetUrl(props.post?.cover_image || props.post?.coverImage || ''))

function goBack() {
  router.push('/community')
}
</script>

<style scoped>
.detail-header {
  width: 90%;
  min-height: 340px;
  margin: 50px auto;
  display: grid;
  align-items: end;
  overflow: hidden;
  border-radius: 8px;
  background: rgba(var(--v-theme-primary), 0.12);
  position: relative;
}

img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.header-content {
  position: relative;
  z-index: 1;

  padding:
    clamp(22px, 4vw, 44px)
    clamp(22px, 4vw, 44px)
    clamp(22px, 4vw, 44px);

  padding-top: 75px; /* reserve space for back button */

  color: #fff;

  background:
    linear-gradient(
      0deg,
      rgba(0, 0, 0, 0.68),
      rgba(0, 0, 0, 0.08)
    );

  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 24px;

  min-height: 100%;
}

.header-copy {
  min-width: 0;
  margin-top: 15px;
}

.back-button {
  position: absolute;
  top: 20px;
  left: 22px;
  z-index: 10;

  display: inline-flex;
  align-items: center;
  gap: 6px;

  border: 0;
  background: transparent;

  color: rgba(255, 255, 255, 0.95);
  font-size: 0.9rem;
  font-weight: 700;

  cursor: pointer;

  transition:
    color 0.2s ease,
    transform 0.2s ease;
}

.back-button:hover {
  color: rgb(var(--v-theme-primary));
  transform: translateX(-3px);
}

.status-pill {
  width: fit-content;
  margin: 0 0 12px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.28);
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 850;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

h1 {
  max-width: 760px;
  margin: 0;
  font-size: clamp(2rem, 5vw, 4rem);
  line-height: 1.05;
}

.description {
  display: block;
  max-width: 660px;
  margin-top: 12px;
  line-height: 1.6;
}

.header-actions {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  gap: 10px;
}

.like-button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 999px;
  padding: 9px 14px;
  color: #fff;
  background: transparent;
  font-weight: 850;
  cursor: pointer;
  transition:
    border-color 0.2s,
    color 0.2s;
}

.like-button:hover,
.like-button.liked {
  border-color: #e05252;
  color: #e05252;
}

.save-button {
  flex: 0 0 auto;
  border: 0;
  border-radius: 999px;
  padding: 10px 14px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 850;
  cursor: pointer;
}

@media (max-width: 1250px) {
  .detail-header {
    width: 100%;
    margin: 22px auto;
  }
}

@media (max-width: 900px) {
  .detail-header {
    min-height: 300px;
  }

  .header-content {
    padding: clamp(18px, 3.5vw, 32px);
    gap: 16px;
  }

  h1 {
    font-size: clamp(1.8rem, 5vw, 3.2rem);
  }

  .description {
    font-size: 0.9rem;
  }
}

@media (max-width: 600px) {
  .back-button {
    top: 18px;
    left: 18px;
  }

  .header-content {
    padding-top: 72px;
    align-items: flex-start;
    flex-direction: column;
  }

  .header-copy {
    margin-top: 15px;
  }
}
</style>
