<template>
  <header class="detail-header">
    <img v-if="post?.cover_image || post?.coverImage" :src="post.cover_image || post.coverImage" :alt="post.title" />
    <button class="back-button" type="button" @click="goBack">
      <i class="bi bi-arrow-left"></i>
      <span>Back</span>
    </button>

    <div class="header-content">
      <div class="header-copy">
        <p class="status-pill">{{ post?.status || 'public' }}</p>
        <h1>{{ post?.title || 'Community trip' }}</h1>
        <span class="description">{{ post?.description || 'A shared Nordic journey.' }}</span>
      </div>

      <button class="save-button" type="button" @click="$emit('save')">
        <i class="bi bi-bookmark"></i>
        Save Post
      </button>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'

defineProps({
  post: { type: Object, default: null },
})

defineEmits(['save'])

const router = useRouter()

function goBack() {
  router.push('/community')
}
</script>

<style scoped>
.detail-header {
  width: 90%;
  min-height: 340px;
  margin: 0 auto;
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
  padding: clamp(22px, 4vw, 44px);
  color: #fff;
  background: linear-gradient(0deg, rgba(0, 0, 0, 0.68), rgba(0, 0, 0, 0.08));
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 24px;
  height: 100%;
}

.header-copy {
  min-width: 0;
}

.back-button {
  position: absolute;
  top: 20px;
  left: 22px;
  z-index: 2;
  margin: 0;
  padding: 0;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 0;
  color: rgba(255, 255, 255, 0.9);
  background: transparent;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: none;
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

@media (max-width: 720px) {
  .header-content {
    align-items: start;
    flex-direction: column;
  }
}
</style>
