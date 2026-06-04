<template>
  <main class="community-page">
    <CommunityHero v-model:filters="filters" />

    <TrendingPosts :posts="trendingPosts" />

    <section class="community-layout">
      <CommunityPostGrid
        :posts="filteredPosts"
        :loading="isLoading"
        @save="openSaveModal"
        @use-plan="usePlan"
      />

      <CommunitySidebar
        :destinations="destinations"
        :tags="tags"
        :creators="creators"
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
import { useRouter } from 'vue-router'
import CommunityHero from '@/components/community/CommunityHero.vue'
import CommunityPostGrid from '@/components/community/CommunityPostGrid.vue'
import CommunitySidebar from '@/components/community/CommunitySidebar.vue'
import PostSaveModal from '@/components/community/PostSaveModal.vue'
import TrendingPosts from '@/components/community/TrendingPosts.vue'
import { getCommunityPosts, saveCommunityPost } from '@/services/communityService'

const router = useRouter()
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

const destinations = [
  { code: 'NO', name: 'Norway', posts: 32 },
  { code: 'IS', name: 'Iceland', posts: 28 },
  { code: 'FI', name: 'Finland', posts: 19 },
]

const tags = ['NorthernLights', 'RoadTrip', 'Fjords', 'Photography', 'BudgetTravel']

const creators = [
  { name: 'Ellie', posts: 8 },
  { name: 'Sarah', posts: 6 },
]

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
  max-width: 1180px;
  margin: 0 auto 34px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 28px;
  align-items: start;
}

@media (max-width: 1000px) {
  .community-layout {
    grid-template-columns: 1fr;
  }
}
</style>
