<template>
  <header class="detail-header">
    <img v-if="post?.cover_image || post?.coverImage" :src="post.cover_image || post.coverImage" :alt="post.title" />
    <div class="header-content">
      <p>{{ post?.status || 'public' }}</p>
      <h1>{{ post?.title || 'Community trip' }}</h1>
      <span>{{ post?.description || 'A shared Nordic journey.' }}</span>
      <button type="button" @click="$emit('save')">
        <i class="bi bi-bookmark"></i>
        Save Post
      </button>
    </div>
  </header>
</template>

<script setup>
defineProps({
  post: { type: Object, default: null },
})

defineEmits(['save'])
</script>

<style scoped>
.detail-header {
  max-width: 1180px;
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
}

p {
  margin: 0 0 8px;
  font-weight: 900;
  text-transform: uppercase;
}

h1 {
  max-width: 760px;
  margin: 0;
  font-size: clamp(2rem, 5vw, 4rem);
  line-height: 1.05;
}

span {
  display: block;
  max-width: 660px;
  margin-top: 12px;
  line-height: 1.6;
}

button {
  margin-top: 18px;
  border: 0;
  border-radius: 999px;
  padding: 10px 14px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 850;
  cursor: pointer;
}
</style>
