<template>
  <main class="post-detail-page">
    <PostDetailHeader :post="post" @save="isSaveModalOpen = true" />

    <div class="detail-content">
      <section class="trip-overview-panel">
        <PostTripSummary :trip="post?.trip" />
        <PostItineraryPreview :timeline="post?.trip?.timeline || []" />
      </section>

      <PostPortfolio v-if="hasPortfolios" :portfolios="post?.portfolios || []" />

      <section class="comments-panel">
        <PostCommentList :comments="comments" />
        <PostCommentBox @submit="addComment" />
      </section>

      <PostVisibilityControl
        v-if="isOwner"
        :status="post?.status || 'private'"
        @update:status="updateVisibility"
      />
    </div>

    <PostSaveModal
      v-if="isSaveModalOpen"
      :post="post"
      @close="isSaveModalOpen = false"
      @confirm="savePost"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import PostCommentBox from '@/components/community/PostCommentBox.vue'
import PostCommentList from '@/components/community/PostCommentList.vue'
import PostDetailHeader from '@/components/community/PostDetailHeader.vue'
import PostItineraryPreview from '@/components/community/PostItineraryPreview.vue'
import PostPortfolio from '@/components/community/PostPortfolio.vue'
import PostSaveModal from '@/components/community/PostSaveModal.vue'
import PostTripSummary from '@/components/community/PostTripSummary.vue'
import PostVisibilityControl from '@/components/community/PostVisibilityControl.vue'
import {
  addPostComment,
  getCommunityPost,
  saveCommunityPost,
  updatePostVisibility,
} from '@/services/communityService'

const route = useRoute()
const post = ref(null)
const comments = ref([])
const isSaveModalOpen = ref(false)

const isOwner = computed(() => Boolean(post.value?.isOwner))
const hasPortfolios = computed(() => Boolean(post.value?.portfolios?.length))

onMounted(loadPost)

async function loadPost() {
  const data = await getCommunityPost(route.params.id)
  post.value = data.post || data
  comments.value = data.comments || []
}

async function addComment(comment) {
  const nextComment = await addPostComment(route.params.id, comment)
  comments.value.push(nextComment)
}

async function savePost() {
  await saveCommunityPost(post.value.id)
  isSaveModalOpen.value = false
}

async function updateVisibility(status) {
  post.value = {
    ...post.value,
    status: await updatePostVisibility(post.value.id, status),
  }
}
</script>

<style scoped>
.post-detail-page {
  min-height: calc(100vh - 24px);
  padding: 78px clamp(24px, 5vw, 72px) 100px;
  background: rgb(var(--v-theme-background));
}

.detail-content {
  width: 90%;
  margin: 26px auto 0;
  display: grid;
  gap: 28px;
}

.trip-overview-panel {
  display: grid;
  grid-template-columns: minmax(0, 7fr) minmax(260px, 3fr);
  gap: 18px;
  padding: 28px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 42px;
  background: rgba(var(--v-theme-surface), 0.72);
}

.comments-panel {
  display: grid;
  gap: 16px;
  padding: 28px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 34px;
  background: rgba(var(--v-theme-surface), 0.72);
}

@media (max-width: 980px) {
  .trip-overview-panel {
    grid-template-columns: 1fr;
    border-radius: 28px;
    padding: 18px;
  }

  .comments-panel {
    border-radius: 24px;
    padding: 18px;
  }
}
</style>
