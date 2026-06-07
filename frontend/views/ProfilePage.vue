<template>
  <main class="profile-page">
    <div class="profile-wrap">
      <div v-if="isLoading" class="state-panel">
        <v-progress-circular indeterminate color="primary" />
        <p>Loading your profile...</p>
      </div>

      <div v-else-if="error" class="state-panel">
        <v-icon size="36">mdi-alert-circle-outline</v-icon>
        <p>{{ error }}</p>
        <v-btn color="primary" variant="flat" @click="loadProfile">Try Again</v-btn>
      </div>

      <template v-else>
        <ProfileHero :user="profile.user" />

        <ProfileStats :stats="profile.stats" />

        <section class="profile-grid">
          <PassportSection :countries="profile.passportCountries" />
          <AchievementsSection :achievements="profile.achievements" />
        </section>

        <MyJourneys
          :journeys="profile.journeys"
          @view="openJourney"
          @edit="openJourney"
          @toggle-share="toggleJourneyShare"
          @add-diary="openDiaryModal"
        />

        <SavedDestinations :destinations="profile.savedDestinations" @unsave="unsaveDestination" />

        <SavedPosts :posts="profile.savedPosts" @unsave="unsavePost" />

        <JourneyDiaryModal
          v-if="editingDiaryJourney"
          :journey="editingDiaryJourney"
          @close="editingDiaryJourney = null"
          @save="saveDiary"
        />
      </template>
    </div>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import ProfileHero from '@/components/profile/ProfileHero.vue'
import ProfileStats from '@/components/profile/ProfileStats.vue'
import PassportSection from '@/components/profile/PassportSection.vue'
import AchievementsSection from '@/components/profile/AchievementsSection.vue'
import MyJourneys from '@/components/profile/MyJourneys.vue'
import JourneyDiaryModal from '@/components/profile/JourneyDiaryModal.vue'
import SavedDestinations from '@/components/profile/SavedDestinations.vue'
import SavedPosts from '@/components/profile/SavedPosts.vue'
import { loadProfileDashboard } from '@/services/profileService'
import { saveJourneyDiary, updateJourneyVisibility } from '@/services/plannerService'
import { toggleSavedDestination } from '@/services/savedItemService'
import { unsaveCommunityPost } from '@/services/communityService'

const router = useRouter()
const isLoading = ref(true)
const error = ref('')
const editingDiaryJourney = ref(null)
const profile = reactive({
  user: {},
  stats: [],
  passportCountries: [],
  achievements: [],
  journeys: [],
  savedDestinations: [],
  savedPosts: [],
  publicPosts: [],
})

async function loadProfile() {
  isLoading.value = true
  error.value = ''

  try {
    const data = await loadProfileDashboard()
    profile.user = data.user || {}
    profile.stats = Array.isArray(data.stats) ? data.stats : []
    profile.passportCountries = Array.isArray(data.passportCountries) ? data.passportCountries : []
    profile.achievements = Array.isArray(data.achievements) ? data.achievements : []
    profile.journeys = Array.isArray(data.journeys) ? data.journeys : []
    profile.savedDestinations = Array.isArray(data.savedDestinations) ? data.savedDestinations : []
    profile.savedPosts = Array.isArray(data.savedPosts) ? data.savedPosts : []
    profile.publicPosts = Array.isArray(data.publicPosts) ? data.publicPosts : []
  } catch (loadError) {
    error.value = loadError?.message || 'Unable to load your profile right now.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadProfile)

function openJourney(journey) {
  router.push({
    path: '/trip-planner',
    query: { journey: journey.id },
  })
}

function openDiaryModal(journey) {
  editingDiaryJourney.value = journey
}

async function saveDiary(payload) {
  if (!editingDiaryJourney.value) return

  try {
    await saveJourneyDiary(editingDiaryJourney.value.id, payload)
    editingDiaryJourney.value = null
    await loadProfile()
  } catch (saveError) {
    error.value = saveError?.message || 'Unable to save diary.'
    editingDiaryJourney.value = null
  }
}

async function unsaveDestination(slug) {
  if (!slug) return
  profile.savedDestinations = profile.savedDestinations.filter((d) => d.slug !== slug)
  try {
    await toggleSavedDestination(slug)
  } catch {
    await loadProfile()
  }
}

async function unsavePost(postId) {
  if (!postId) return
  profile.savedPosts = profile.savedPosts.filter((p) => p.id !== postId)
  try {
    await unsaveCommunityPost(postId)
  } catch {
    await loadProfile()
  }
}

async function toggleJourneyShare(journey) {
  try {
    await updateJourneyVisibility(journey.id, journey.isPublic ? 'private' : 'public')
    await loadProfile()
  } catch (shareError) {
    error.value = shareError?.message || 'Unable to update journey visibility.'
  }
}
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  padding-top: 50px;
  padding-bottom: 70px;
  color: rgb(var(--v-theme-text));
  background: rgb(var(--v-theme-background));
}

.profile-wrap {
  width: 90%;
  margin: 0 auto;
}

.profile-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 28px;
  margin-top: 28px;
}

.state-panel {
  min-height: 360px;
  display: grid;
  place-items: center;
  align-content: center;
  gap: 16px;
  padding: 32px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  text-align: center;
}

.state-panel .v-icon {
  color: rgb(var(--v-theme-primary));
}

.state-panel p {
  margin: 0;
  opacity: 0.76;
}

@media (max-width: 1250px) {
  .profile-page {
    padding-top: 108px;
  }

  .profile-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .profile-page {
    padding-top: 120px;
    padding-bottom: 56px;
  }

  .profile-wrap {
    width: 95%;
  }
}

@media (max-width: 600px) {
  .profile-page {
    padding-top: 108px;
    padding-bottom: 48px;
  }

  .profile-wrap {
    width: 100%;
    padding: 0 16px;
  }
}
</style>
