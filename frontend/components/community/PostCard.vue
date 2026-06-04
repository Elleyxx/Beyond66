<template>
  <article class="post-card">
    <RouterLink class="cover-link" :to="`/community/${post.id}`">
      <img v-if="post.cover_image || post.coverImage" :src="post.cover_image || post.coverImage" :alt="post.title" />
      <div v-else class="cover-placeholder">{{ countryLabel }}</div>
    </RouterLink>

    <div class="post-body">
      <div class="post-meta">
        <span>{{ post.status || 'public' }}</span>
        <span>{{ formattedDate }}</span>
      </div>

      <RouterLink class="title-link" :to="`/community/${post.id}`">
        <h2>{{ post.title || 'Untitled trip' }}</h2>
      </RouterLink>

      <p>{{ post.description || 'A Nordic route shared by the community.' }}</p>

      <div class="post-actions">
        <button type="button" @click="$emit('save', post)">
          <i class="bi bi-bookmark"></i>
          Save
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  post: { type: Object, required: true },
})

defineEmits(['save'])

const countryLabel = computed(() => {
  return props.post?.trip?.meta?.country || props.post?.country || 'Nordic Trip'
})

const formattedDate = computed(() => {
  if (!props.post?.created_at && !props.post?.createdAt) return 'Recently'
  return new Intl.DateTimeFormat('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(props.post.created_at || props.post.createdAt))
})
</script>

<style scoped>
.post-card {
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  background: rgba(var(--v-theme-surface), 0.96);
}

.cover-link,
.cover-placeholder,
img {
  display: block;
  width: 100%;
  aspect-ratio: 16 / 9;
}

img {
  object-fit: cover;
}

.cover-placeholder {
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  font-weight: 900;
}

.post-body {
  padding: 16px;
}

.post-meta,
.post-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.post-meta {
  color: rgba(var(--v-theme-text), 0.56);
  font-size: 0.78rem;
  font-weight: 800;
  text-transform: capitalize;
}

.title-link {
  color: inherit;
  text-decoration: none;
}

h2 {
  margin: 10px 0 8px;
  font-size: 1.12rem;
  line-height: 1.25;
}

p {
  margin: 0 0 14px;
  color: rgba(var(--v-theme-text), 0.68);
  line-height: 1.55;
}

button {
  border: 0;
  border-radius: 999px;
  padding: 8px 12px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 850;
  cursor: pointer;
}
</style>
