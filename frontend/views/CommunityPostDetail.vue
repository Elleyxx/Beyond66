<template>
  <main class="post-detail-page">
    <PostDetailHeader :post="post" @save="isSaveModalOpen = true" @edit="isEditModalOpen = true" @like="toggleLike" />

    <div class="detail-content">
      <div class="left-column">
        <section class="summary-panel">
          <PostTripSummary :trip="post?.trip" />
        </section>
        <section class="itinerary-panel">
          <PostItineraryPreview :timeline="post?.trip?.timeline || []" />
        </section>
        <section class="comments-panel">
          <PostCommentList :comments="comments" />
          <PostCommentBox @submit="addComment" />
        </section>
      </div>

      <PostPortfolio v-if="hasPortfolios" class="portfolio-panel" :portfolios="post?.portfolios || []" />
    </div>

    <PostSaveModal
      v-if="isSaveModalOpen"
      :post="post"
      @close="isSaveModalOpen = false"
      @confirm="savePost"
    />

    <PostEditModal
      v-if="isEditModalOpen"
      :post="post"
      @close="isEditModalOpen = false"
      @save="updatePost"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import PostCommentBox from '@/components/community/PostCommentBox.vue'
import PostCommentList from '@/components/community/PostCommentList.vue'
import PostDetailHeader from '@/components/community/PostDetailHeader.vue'
import PostEditModal from '@/components/community/PostEditModal.vue'
import PostItineraryPreview from '@/components/community/PostItineraryPreview.vue'
import PostPortfolio from '@/components/community/PostPortfolio.vue'
import PostSaveModal from '@/components/community/PostSaveModal.vue'
import PostTripSummary from '@/components/community/PostTripSummary.vue'
import {
  addPostComment,
  getCommunityPost,
  saveCommunityPost,
  togglePostLike,
  updateCommunityPost,
} from '@/services/communityService'

const route = useRoute()
const post = ref(null)
const comments = ref([])
const isSaveModalOpen = ref(false)
const isEditModalOpen = ref(false)

const hasPortfolios = computed(() => Boolean(post.value?.portfolios?.length))

onMounted(loadPost)

async function loadPost() {
  const data = await getCommunityPost(route.params.id)
  post.value = data.post || data
  comments.value = data.comments || []
}

async function toggleLike() {
  if (!post.value) return
  try {
    const result = await togglePostLike(post.value.id)
    post.value = { ...post.value, liked: result.liked, likes: result.likes }
  } catch {
    // unauthenticated or network error — silently ignore
  }
}

async function addComment(comment) {
  const nextComment = await addPostComment(route.params.id, comment)
  comments.value.push(nextComment)
}

async function savePost() {
  await saveCommunityPost(post.value.id)
  isSaveModalOpen.value = false
}

async function updatePost(payload) {
  post.value = await updateCommunityPost(post.value.id, payload)
  isEditModalOpen.value = false
}

</script>

<style scoped>
.post-detail-page {
  min-height: calc(100vh - 24px);
  padding: 78px var(--page-gutter) 100px;
  background: rgb(var(--v-theme-background));
}

.detail-content {
  width: 90%;
  margin: 26px auto 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
  align-items: start;
}

.left-column {
  display: grid;
  gap: 28px;
  min-width: 0;
}

.summary-panel,
.comments-panel,
.itinerary-panel {
  padding: 28px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 34px;
  background: rgba(var(--v-theme-surface), 0.72);
}

.comments-panel {
  display: grid;
  gap: 16px;
}

@media (max-width: 1250px) {
  .post-detail-page {
    padding-top: 50px;
    padding-left: 40px;
    padding-right: 40px;
  }

  .detail-content {
    width: 100%;
  }
}

@media (max-width: 900px) {
  .post-detail-page {
    padding-top: 50px;
    padding-left: 28px;
    padding-right: 28px;
    padding-bottom: 84px;
  }
}

@media (max-width: 600px) {
  .post-detail-page {
    padding-top: 50px;
    padding-left: 16px;
    padding-right: 16px;
    padding-bottom: 72px;
  }

  .summary-panel,
  .itinerary-panel,
  .comments-panel {
    border-radius: 24px;
    padding: 18px;
  }
}
</style>
