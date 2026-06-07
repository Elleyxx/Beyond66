<template>
  <teleport to="body">
    <div class="backdrop" @click.self="$emit('close')">
      <section class="community-edit-modal">
        <div class="modal-head">
          <div>
            <p>Edit Post</p>
            <h2>{{ post?.title || 'Untitled trip' }}</h2>
          </div>

          <button class="icon-btn" type="button" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          <label>
            Title
            <input v-model="title" type="text" placeholder="Post title" />
          </label>

          <label>
            Description
            <textarea v-model="description" rows="5" placeholder="Describe the journey"></textarea>
          </label>

          <label>
            Tags
            <input v-model="tagsInput" type="text" placeholder="NorthernLights, RoadTrip, Fjords" />
            <small>Separate tags with commas. Maximum 8 tags.</small>
          </label>

          <div v-if="tags.length" class="tag-preview">
            <span v-for="tag in tags" :key="tag">#{{ tag }}</span>
          </div>

          <section class="cover-section">
            <div class="cover-label">
              <span>Cover image</span>
              <small>Preview the current cover or choose a new image.</small>
            </div>

            <div class="cover-preview">
              <img v-if="coverImagePreview" :src="coverImagePreview" :alt="title" />
              <div v-else class="cover-placeholder">
                <i class="bi bi-image"></i>
                <span>No cover image selected</span>
              </div>
            </div>

            <label class="file-drop">
              <input type="file" accept="image/*" @change="handleCoverUpload" />
              <i class="bi bi-cloud-arrow-up"></i>
              <span>Choose New Image</span>
            </label>

            <label>
              Cover image URL
              <input v-model="coverImage" type="text" placeholder="/assets/images/Norway/Geirangerfjord.jpg" />
            </label>
          </section>

          <div class="visibility-options">
            <span>Visibility</span>

            <label>
              <input v-model="status" type="radio" value="private" />
              Private
            </label>

            <label>
              <input v-model="status" type="radio" value="public" />
              Public
            </label>
          </div>
        </div>

        <div class="actions">
          <button type="button" class="secondary" @click="$emit('close')">Cancel</button>
          <button type="button" class="primary" @click="emitSave">Save Changes</button>
        </div>
      </section>
    </div>
  </teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { resolveAssetUrl } from '@/services/apiBase'

const props = defineProps({
  post: { type: Object, default: null },
})

const emit = defineEmits(['close', 'save'])

const title = ref('')
const description = ref('')
const tagsInput = ref('')
const coverImage = ref('')
const status = ref('public')
const coverImagePreview = computed(() => resolveAssetUrl(coverImage.value))

const tags = computed(() => {
  const seen = new Set()

  return tagsInput.value
    .split(',')
    .map((tag) => tag.trim().replace(/^#/, ''))
    .filter(Boolean)
    .filter((tag) => {
      const key = tag.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return true
    })
    .slice(0, 8)
})

watch(
  () => props.post,
  (post) => {
    title.value = post?.title || ''
    description.value = post?.description || ''
    coverImage.value = post?.coverImage || post?.cover_image || ''
    tagsInput.value = Array.isArray(post?.tags) ? post.tags.join(', ') : ''
    status.value = post?.status || 'public'
  },
  { immediate: true },
)

function emitSave() {
  emit('save', {
    title: title.value.trim(),
    description: description.value.trim(),
    tags: tags.value,
    coverImage: coverImage.value.trim(),
    status: status.value,
  })
}

function handleCoverUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return

  resizeImageFile(file)
    .then((dataUrl) => {
      coverImage.value = dataUrl
    })
    .catch(() => {
      coverImage.value = ''
    })
}

function resizeImageFile(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onload = () => {
      const image = new Image()

      image.onload = () => {
        const maxWidth = 1200
        const maxHeight = 675
        const ratio = Math.min(maxWidth / image.width, maxHeight / image.height, 1)
        const width = Math.max(1, Math.round(image.width * ratio))
        const height = Math.max(1, Math.round(image.height * ratio))
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')

        if (!ctx) {
          reject(new Error('Canvas unavailable'))
          return
        }

        canvas.width = width
        canvas.height = height
        ctx.drawImage(image, 0, 0, width, height)
        resolve(canvas.toDataURL('image/jpeg', 0.82))
      }

      image.onerror = reject
      image.src = String(reader.result || '')
    }

    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}
</script>

<style scoped>
.backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: clamp(14px, 3vw, 28px);
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(8px);
}

.community-edit-modal {
  width: min(94vw, 760px);
  max-height: min(86vh, 720px);
  display: grid;
  grid-template-rows: auto minmax(0, 1fr) auto;
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.14);
  border-radius: 24px;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 28px 80px rgba(0, 0, 0, 0.24);
}

.modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 24px 28px 18px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.modal-head p {
  margin: 0 0 6px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.modal-head h2 {
  margin: 0;
  color: rgb(var(--v-theme-text));
  font-size: 1.55rem;
  font-weight: 900;
}

.icon-btn {
  width: 36px;
  height: 36px;
  padding: 0;
  display: grid;
  place-items: center;
  border: 0;
  background: transparent;
  color: rgb(var(--v-theme-text));
  cursor: pointer;
}

.modal-body {
  min-height: 0;
  display: grid;
  gap: 18px;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 22px 28px;
}

label {
  display: grid;
  gap: 8px;
  color: rgba(var(--v-theme-text), 0.72);
  font-size: 0.82rem;
  font-weight: 850;
}

label small {
  color: rgba(var(--v-theme-text), 0.52);
}

input,
textarea {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 16px;
  padding: 12px 14px;
  outline: none;
  background: rgba(var(--v-theme-background), 0.42);
  color: rgb(var(--v-theme-text));
  font: inherit;
}

textarea {
  resize: vertical;
}

.tag-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag-preview span {
  border-radius: 999px;
  padding: 6px 10px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
  font-size: 0.78rem;
  font-weight: 900;
}

.cover-preview {
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 18px;
}

.cover-preview img,
.cover-placeholder {
  width: 100%;
  height: 220px;
  display: block;
}

.cover-preview img {
  object-fit: cover;
}

.cover-section {
  display: grid;
  gap: 12px;
}

.cover-label {
  display: grid;
  gap: 4px;
}

.cover-label span {
  color: rgba(var(--v-theme-text), 0.72);
  font-size: 0.82rem;
  font-weight: 850;
}

.cover-label small {
  color: rgba(var(--v-theme-text), 0.52);
  font-size: 0.78rem;
}

.cover-placeholder {
  display: grid;
  place-items: center;
  align-content: center;
  gap: 8px;
  color: rgba(var(--v-theme-text), 0.58);
  background: rgba(var(--v-theme-background), 0.42);
}

.cover-placeholder i {
  font-size: 2rem;
}

.file-drop {
  min-height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 2px dashed rgba(var(--v-theme-primary), 0.34);
  border-radius: 16px;
  background: rgba(var(--v-theme-primary), 0.06);
  color: rgb(var(--v-theme-primary));
  cursor: pointer;
}

.file-drop input {
  display: none;
}

.file-drop i {
  font-size: 1.35rem;
}

.visibility-options {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
  padding: 14px;
  border-radius: 16px;
  background: rgba(var(--v-theme-background), 0.34);
}

.visibility-options > span {
  width: 100%;
  color: rgba(var(--v-theme-text), 0.55);
  font-size: 0.75rem;
  font-weight: 900;
  text-transform: uppercase;
}

.visibility-options label {
  display: flex;
  align-items: center;
  gap: 8px;
}

.visibility-options input {
  width: auto;
  accent-color: rgb(var(--v-theme-primary));
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 18px 28px 24px;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

button {
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 850;
  cursor: pointer;
}

.secondary {
  border: 1px solid rgba(var(--v-theme-primary), 0.42);
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
}

.primary {
  border: 0;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
}

@media (max-width: 600px) {
  .community-edit-modal {
    max-height: 90vh;
    border-radius: 18px;
  }

  .modal-head,
  .modal-body,
  .actions {
    padding-left: 18px;
    padding-right: 18px;
  }

  .actions {
    flex-direction: column-reverse;
  }
}
</style>
