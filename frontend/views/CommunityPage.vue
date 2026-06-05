<template>
  <main class="community-page">
    <CommunityHero v-model:filters="filters" />

    <section class="community-layout">
      <div class="community-main">
        <TrendingPosts :posts="trendingPosts" />

        <CommunityPostGrid
          :posts="filteredPosts"
          :loading="isLoading"
          @save="openSaveModal"
          @use-plan="usePlan"
        />
      </div>

      <CommunitySidebar
        :destinations="destinations"
        :tags="tags"
      />
    </section>

    <PostSaveModal
      v-if="savingPost"
      :post="savingPost"
      @close="savingPost = null"
      @confirm="savePost"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import CommunityHero from '@/components/community/CommunityHero.vue'
import CommunityPostGrid from '@/components/community/CommunityPostGrid.vue'
import CommunitySidebar from '@/components/community/CommunitySidebar.vue'
import PostSaveModal from '@/components/community/PostSaveModal.vue'
import TrendingPosts from '@/components/community/TrendingPosts.vue'
import { getCommunityPosts, saveCommunityPost } from '@/services/communityService'

const router = useRouter()
const { t } = useI18n()
const posts = ref([])
const isLoading = ref(false)
const savingPost = ref(null)

const filters = ref({
  search: '',
  category: 'all',
})

const filteredPosts = computed(() => {
  const query = filters.value.search.trim().toLowerCase()

  return posts.value.filter((post) => {
    const matchesSearch =
      !query ||
      [post.title, post.description, post.country, post.authorName]
        .some((value) => String(value || '').toLowerCase().includes(query))

    const matchesCategory =
      filters.value.category === 'all' ||
      post.type === filters.value.category ||
      post.category === filters.value.category

    return matchesSearch && matchesCategory
  })
})

const trendingPosts = computed(() => posts.value.slice(0, 3))

const destinations = computed(() => [
  { code: 'NO', name: t('countryNames.norway'), posts: 32 },
  { code: 'IS', name: t('countryNames.iceland'), posts: 28 },
  { code: 'FI', name: t('countryNames.finland'), posts: 19 },
])

const tags = ['NorthernLights', 'RoadTrip', 'Fjords', 'Photography', 'BudgetTravel']

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

function openSaveModal(post) {
  savingPost.value = post
}

async function savePost(post) {
  await saveCommunityPost(post.id)
  savingPost.value = null
}

function usePlan(post) {
  router.push({
    path: '/trip-planner',
    query: { usePost: post.id },
  })
}
</script>

<style scoped>
.community-page {
  min-height: 100vh;
  padding: 78px clamp(24px, 5vw, 72px) 100px;
  background: rgb(var(--v-theme-background));
}

.community-layout {
  width: 90%;
  margin: 0 auto 34px;
  display: grid;
  grid-template-columns: minmax(0, 7fr) minmax(240px, 3fr);
  gap: 32px;
  align-items: start;
}

.community-main {
  display: grid;
  gap: 30px;
  min-width: 0;
}

@media (max-width: 1000px) {
  .community-layout {
    width: 90%;
    grid-template-columns: 1fr;
  }
}
</style>
