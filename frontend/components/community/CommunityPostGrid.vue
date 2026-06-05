<template>
  <section class="feed-area">
    <div class="section-head">
      <h2>{{ t('community.latest.title') }}</h2>
      <span>{{ t('community.latest.count', { count: posts.length }) }}</span>
    </div>
    <div class="section-divider"></div>

    <p v-if="loading" class="state">{{ t('community.latest.loading') }}</p>
    <p v-else-if="!posts.length" class="state">{{ t('community.latest.empty') }}</p>

    <div v-else class="post-grid">
      <CommunityPostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
        @save="$emit('save', post)"
        @use-plan="$emit('use-plan', post)"
      />
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import CommunityPostCard from './CommunityPostCard.vue'

defineProps({
  posts: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

defineEmits(['save', 'use-plan'])

const { t } = useI18n()
</script>

<style scoped>
.feed-area {
  width: 100%;
  margin: 0;
}
.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.section-head h2 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 900;
}

.section-head span {
  color: rgba(var(--v-theme-text), 0.58);
  font-weight: 800;
}

.section-divider{
  width: 120px;
  height: 2.5px;
  margin: -6px 0 18px;
  background: rgba(var(--v-theme-primary));
}

.post-grid {
  columns: 2 230px;
  column-gap: 18px;
}

.state {
  margin: 0;
  padding: 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  color: rgba(var(--v-theme-text), 0.66);
  background: rgba(var(--v-theme-surface), 0.94);
}
</style>
