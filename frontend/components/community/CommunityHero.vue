<template>
  <section class="community-hero">
    <div class="hero-content">
      <p class="eyebrow">{{ t('community.hero.eyebrow') }}</p>
      <h1>{{ t('community.hero.title') }}</h1>

      <p>
        {{ t('community.hero.description') }}
      </p>

      <div class="hero-search">
        <i class="bi bi-search"></i>
        <input
          :value="filters.search"
          type="search"
          :placeholder="t('community.hero.searchPlaceholder')"
          @input="patch({ search: $event.target.value })"
        />
      </div>

      <div class="category-pills">
        <button
          v-for="pill in pills"
          :key="pill.value"
          :class="{ active: filters.category === pill.value }"
          type="button"
          @click="patch({ category: pill.value })"
        >
          {{ pill.label }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  filters: { type: Object, required: true },
})

const emit = defineEmits(['update:filters'])
const { t } = useI18n()

const pills = computed(() => [
  {
    label: t('community.filters.all'),
    value: 'all',
  },
  {
    label: t('community.filters.journey'),
    value: 'journey',
  },
  {
    label: t('community.filters.diary'),
    value: 'diary',
  },
  {
    label: t('community.filters.completeJourney'),
    value: 'complete_journey',
  },
  {
    label: t('community.filters.myPosts'),
    value: 'my_posts',
  },
])

function patch(update) {
  emit('update:filters', {
    ...props.filters,
    ...update,
  })
}
</script>

<style scoped>
.community-hero {
  max-width: 90%;
  min-height: 340px;
  margin: 0 auto 34px;
  border-radius: 28px;
  overflow: hidden;
  background:
    linear-gradient(90deg, rgba(9, 15, 21, 0.8), rgba(9, 15, 21, 0.45), rgba(9, 15, 21, 0.12)),
    url('../../assets/images/community.jpg');
  background-size: cover;
  background-position: center;
}

.hero-content {
  min-height: 340px;
  padding: 42px 48px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.eyebrow {
  margin: 0 0 8px;
  color: rgb(var(--v-theme-primary)) !important;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  color: rgb(var(--v-theme-communityHeader));
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.5rem, 5vw, 4.5rem);
}

.hero-content p {
  max-width: 560px;
  color: rgb(var(--v-theme-communityHeader), 0.82);
  line-height: 1.6;
}

.hero-search {
  width: min(100%, 580px);
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 13px 16px;
  border-radius: 999px;
  background: rgb(var(--v-theme-surface));
}

.hero-search i {
  color: rgb(var(--v-theme-on-surface), 0.66);
}
.hero-search input {
  width: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: rgb(var(--v-theme-surface), 0.66);
}

.category-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.category-pills button {
  border: 1px solid rgba(255, 255, 255, 0.24);
  background: rgba(255, 255, 255, 0.14);
  color: white;
  border-radius: 999px;
  padding: 9px 18px;
  font-weight: 600;
  cursor: pointer;
}

.category-pills button.active {
  background: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}

@media (max-width: 720px) {
  .hero-content {
    padding: 30px 24px;
  }
}
</style>
