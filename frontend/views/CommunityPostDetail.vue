<template>
  <main class="post-detail-page">
    <PostDetailHeader :post="post" @save="isSaveModalOpen = true" />

    <div class="detail-layout">
      <section class="main-column">
        <PostTripSummary :trip="post?.trip" />
        <PostItineraryPreview :timeline="post?.trip?.timeline || []" />
        <PostPortfolio :portfolios="post?.portfolios || []" />
        <PostCommentList :comments="comments" />
        <PostCommentBox @submit="addComment" />
      </section>

      <aside class="side-column">
        <PostVisibilityControl
          v-if="isOwner"
          :status="post?.status || 'private'"
          @update:status="updateVisibility"
        />
      </aside>
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

.detail-layout {
  max-width: 1180px;
  margin: 24px auto 0;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 24px;
  align-items: start;
}

.main-column {
  display: grid;
  gap: 18px;
}

@media (max-width: 980px) {
  .detail-layout {
    grid-template-columns: 1fr;
  }
}
</style>
