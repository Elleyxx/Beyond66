<template>
  <section class="community-trending">
    <div class="section-head">
      <div class="title-block">
        <h2>{{ t('home.trending.title') }}</h2>
      </div>

      <RouterLink class="explore-link explore-link-top" to="/community">
        {{ t('home.trending.explore') }}
        <i class="bi bi-arrow-right"></i>
      </RouterLink>
    </div>

    <p v-if="isLoading" class="state">{{ t('home.trending.loading') }}</p>
    <p v-else-if="!topPosts.length" class="state">{{ t('home.trending.empty') }}</p>

    <div v-else class="post-grid">
      <RouterLink
        v-for="post in topPosts"
        :key="post.id"
        class="trend-card"
        :to="`/community/${post.id}`"
      >
        <div class="image-wrap">
          <img v-if="coverImage(post)" :src="coverImage(post)" :alt="post.title" />
          <div v-else class="image-placeholder">
            {{ post.country || t('home.trending.fallbackCountry') }}
          </div>

          <span class="likes-pill">
            <i class="bi bi-heart-fill"></i>
            {{ post.likes || 0 }}
          </span>
        </div>

        <div class="card-body">
          <div class="author-row">
            <span>{{ post.authorName || post.author_name || t('home.trending.fallbackAuthor') }}</span>
            <strong>{{ post.country || post.trip?.meta?.country || t('home.trending.fallbackCountry') }}</strong>
          </div>

          <h3>{{ post.title || t('home.trending.fallbackTitle') }}</h3>
          <p>{{ post.description || t('home.trending.fallbackDescription') }}</p>

          <div class="meta-row">
            <span>{{ durationLabel(post) }}</span>
            <span>{{ post.season || post.trip?.meta?.season || t('home.trending.anySeason') }}</span>
          </div>
        </div>
      </RouterLink>
    </div>

    <RouterLink
      v-if="topPosts.length"
      class="explore-link explore-link-bottom"
      to="/community"
    >
      {{ t('home.trending.explore') }}
      <i class="bi bi-arrow-right"></i>
    </RouterLink>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import { resolveAssetUrl } from '@/services/apiBase'
import { getCommunityPosts } from '@/services/communityService'

const posts = ref([])
const isLoading = ref(false)
const { t } = useI18n()

const topPosts = computed(() => {
  return [...posts.value]
    .sort((a, b) => Number(b.likes || 0) - Number(a.likes || 0))
    .slice(0, 3)
})

onMounted(loadPosts)

async function loadPosts() {
  isLoading.value = true

  try {
    posts.value = await getCommunityPosts()
  } catch {
    posts.value = []
  } finally {
    isLoading.value = false
  }
}

function coverImage(post) {
  return resolveAssetUrl(post.coverImage || post.cover_image || '')
}

function durationLabel(post) {
  const duration = post.duration || post.trip?.meta?.duration
  return duration ? t('home.trending.days', { count: duration }) : t('home.trending.flexible')
}
</script>

<style scoped>
.community-trending {
  padding: 0 8vw 110px;
  background: transparent;
}

.section-head {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 28px;
  margin-bottom: 30px;
}

.title-block h2 {
  margin-bottom: 34px;
  color: rgba(var(--v-theme-on-surface), 0.35);
  font-size: clamp(4.5rem, 9vw, 8rem);
  line-height: 0.8;
  font-weight: 900;
}

.explore-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.92rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-decoration: none;
  text-transform: uppercase;
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}

.explore-link:hover {
  opacity: 0.78;
  transform: translateX(4px);
}

.explore-link-top {
  margin-bottom: 38px;
}

.explore-link-bottom {
  display: none;
}

.post-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.trend-card {
  min-width: 0;
  overflow: hidden;
  border-radius: 24px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  color: inherit;
  background: rgba(var(--v-theme-surface), 0.94);
  text-decoration: none;
  transition:
    transform 0.28s ease,
    box-shadow 0.28s ease,
    border-color 0.28s ease;
}

.trend-card:hover {
  transform: translateY(-10px);
  border-color: rgba(var(--v-theme-primary), 0.28);
  box-shadow: 0 22px 44px rgba(var(--v-theme-background), 0.16);
}

.image-wrap {
  position: relative;
  height: 230px;
  overflow: hidden;
  background: rgba(var(--v-theme-primary), 0.12);
}

.image-wrap img,
.image-placeholder {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-primary));
  font-weight: 900;
}

.image-wrap img {
  transition: transform 0.45s ease;
}

.trend-card:hover img {
  transform: scale(1.06);
}

.likes-pill {
  position: absolute;
  top: 14px;
  right: 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 999px;
  padding: 7px 11px;
  color: white;
  background: rgba(0, 0, 0, 0.48);
  font-size: 0.78rem;
  font-weight: 900;
}

.card-body {
  padding: 18px;
}

.author-row,
.meta-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

.author-row {
  justify-content: space-between;
  color: rgba(var(--v-theme-text), 0.62);
  font-size: 0.82rem;
  font-weight: 800;
}

.author-row strong {
  color: rgb(var(--v-theme-primary));
}

h3 {
  margin: 12px 0 8px;
  font-size: 1.25rem;
  line-height: 1.18;
  font-weight: 900;
}

.card-body p {
  margin: 0;
  color: rgba(var(--v-theme-text), 0.68);
  line-height: 1.55;
}

.meta-row {
  margin-top: 16px;
}

.meta-row span {
  border-radius: 999px;
  padding: 6px 10px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
  font-size: 0.76rem;
  font-weight: 900;
}

.state {
  margin: 0;
  padding: 20px;
  border-radius: 18px;
  color: rgba(var(--v-theme-text), 0.64);
  background: rgba(var(--v-theme-surface), 0.94);
}

/* Tablet */
@media (max-width: 1250px) {
  .community-trending {
    padding: 0 6vw 95px;
  }

  .section-head {
    align-items: flex-end;
    gap: 24px;
    margin-bottom: 26px;
  }

  .title-block h2 {
    margin-bottom: 24px;
    font-size: clamp(3.8rem, 8vw, 6.2rem);
  }

  .explore-link-top {
    margin-bottom: 30px;
    white-space: nowrap;
  }

  .image-wrap {
    height: 210px;
  }
}

/* Small tablet / large mobile */
@media (max-width: 900px) {
  .community-trending {
    padding: 0 5vw 85px;
  }

  .section-head {
    align-items: flex-start;
    margin-bottom: 28px;
  }

  .title-block h2 {
    margin-bottom: 0;
    font-size: clamp(3.2rem, 10vw, 5rem);
  }

  .explore-link-top {
    display: none;
  }

  .post-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .trend-card {
    width: 100%;
    max-width: 680px;
    margin: 0 auto;
  }

  .image-wrap {
    height: 260px;
  }

  .explore-link-bottom {
    display: flex;
    justify-content: center;
    width: fit-content;
    margin: 30px auto 0;
  }
}

/* Mobile */
@media (max-width: 600px) {
  .community-trending {
    padding: 0 20px 75px;
  }

  .section-head {
    margin-bottom: 22px;
  }

  .title-block h2 {
    font-size: clamp(2.7rem, 13vw, 4rem);
  }

  .image-wrap {
    height: 210px;
  }

  .card-body {
    padding: 16px;
  }

  h3 {
    font-size: 1.12rem;
  }

  .card-body p {
    font-size: 0.9rem;
  }

  .explore-link-bottom {
    margin-top: 24px;
    font-size: 0.78rem;
    letter-spacing: 0.06em;
  }
}
</style>
