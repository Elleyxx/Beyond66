<template>
  <main class="post-detail-page">
    <PostDetailHeader :post="post" @save="isSaveModalOpen = true" @edit="isEditModalOpen = true" />

    <div class="detail-content">
      <div class="left-column">
        <section class="summary-panel">
          <PostTripSummary :trip="post?.trip" />
        </section>
        <section class="comments-panel">
          <PostCommentList :comments="comments" />
          <PostCommentBox @submit="addComment" />
        </section>
      </div>

      <section class="itinerary-panel">
        <PostItineraryPreview :timeline="post?.trip?.timeline || []" />
      </section>

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
  padding: 78px clamp(24px, 5vw, 72px) 100px;
  background: rgb(var(--v-theme-background));
}

.detail-content {
  width: 90%;
  margin: 26px auto 0;
  display: grid;
  grid-template-columns: minmax(0, 6fr) minmax(320px, 4fr);
  grid-template-areas:
    "left itinerary"
    "portfolio portfolio";
  gap: 28px;
  align-items: start;
}

.left-column {
  grid-area: left;
  display: grid;
  gap: 28px;
}

.summary-panel {
  min-width: 0;
}

.itinerary-panel {
  grid-area: itinerary;
}

.comments-panel {
  min-width: 0;
}

.portfolio-panel {
  grid-area: portfolio;
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

@media (max-width: 980px) {
  .detail-content {
    grid-template-columns: 1fr;
    grid-template-areas:
      "left"
      "itinerary"
      "portfolio";
  }

  .summary-panel,
  .itinerary-panel,
  .comments-panel {
    border-radius: 24px;
    padding: 18px;
  }
}
</style>
