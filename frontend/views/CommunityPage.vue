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
          @edit="openEditModal"
          @use-plan="usePlan"
          @like="handleLike"
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

    <PostEditModal
      v-if="editingPost"
      :post="editingPost"
      @close="editingPost = null"
      @save="updatePost"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
// import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import CommunityHero from '@/components/community/CommunityHero.vue'
import CommunityPostGrid from '@/components/community/CommunityPostGrid.vue'
import CommunitySidebar from '@/components/community/CommunitySidebar.vue'
import PostEditModal from '@/components/community/PostEditModal.vue'
import PostSaveModal from '@/components/community/PostSaveModal.vue'
import TrendingPosts from '@/components/community/TrendingPosts.vue'
import { getCommunityPosts, saveCommunityPost, togglePostLike, updateCommunityPost } from '@/services/communityService'

const router = useRouter()
// const { t } = useI18n()
const posts = ref([])
const isLoading = ref(false)
const savingPost = ref(null)
const editingPost = ref(null)

const filters = ref({
  category: 'all',
})

const filteredPosts = computed(() => {
  return posts.value.filter((post) => {
    if (filters.value.category === 'my_posts') return post.isOwner === true
    if (filters.value.category !== 'all') return post.postCategory === filters.value.category
    return true
  })
})

const trendingPosts = computed(() => posts.value.slice(0, 3))

const countryCodeMap = {
  Norway: 'NO',
  Iceland: 'IS',
  Finland: 'FI',
  Sweden: 'SE',
  Denmark: 'DK',
}

const destinations = computed(() => {
  const map = {}

  posts.value.forEach((post) => {
    const country = post.country || post.trip?.country
    if (!country) return

    if (!map[country]) {
      map[country] = {
        code: countryCodeMap[country] || country.slice(0, 2).toUpperCase(),
        name: country,
        posts: 0,
      }
    }

    map[country].posts += 1
  })

  return Object.values(map).sort((a, b) => b.posts - a.posts)
})

const tags = computed(() => {
  const tagMap = new Map()

  posts.value.forEach((post) => {
    const postTags = [
      ...(post.tags || []),
      ...(post.trip?.tags || []),
    ]

    postTags.forEach((tag) => {
      const cleanTag = String(tag).trim()
      if (!cleanTag) return

      tagMap.set(cleanTag, (tagMap.get(cleanTag) || 0) + 1)
    })
  })

  return Array.from(tagMap.entries())
    .sort((a, b) => b[1] - a[1])
    .map(([tag]) => tag)
    .slice(0, 10)
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

function openSaveModal(post) {
  savingPost.value = post
}

function openEditModal(post) {
  editingPost.value = post
}

async function savePost(post) {
  await saveCommunityPost(post.id)
  savingPost.value = null
}

async function updatePost(payload) {
  if (!editingPost.value) return

  const updatedPost = await updateCommunityPost(editingPost.value.id, payload)
  posts.value = posts.value.map((post) => (post.id === updatedPost.id ? updatedPost : post))
  editingPost.value = null
}

async function handleLike(post) {
  try {
    const result = await togglePostLike(post.id)
    posts.value = posts.value.map((p) =>
      p.id === post.id ? { ...p, liked: result.liked, likes: result.likes } : p,
    )
  } catch {
    // unauthenticated or network error — silently ignore
  }
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
  padding: 78px var(--page-gutter) 100px;
  background: rgb(var(--v-theme-background));
  margin-top: 50px;
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

@media (max-width: 1250px) {
  .community-page {
    padding-left: 40px;
    padding-right: 40px;
  }

  .community-layout {
    width: 100%;
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .community-page {
    padding-top: 50px;
    padding-bottom: 84px;
    margin-top: 0;
  }

  .community-layout {
    gap: 24px;
  }
}

@media (max-width: 600px) {
  .community-page {
    padding-top: 104px;
    padding-bottom: 72px;
  }
}
</style>
