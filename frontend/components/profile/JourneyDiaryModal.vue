<template>
  <teleport to="body">
    <div class="diary-backdrop" @click.self="$emit('close')">
      <section class="diary-modal">
        <div class="modal-head">
          <div>
            <p>Travel Diary</p>
            <h2>{{ journey?.title || 'Journey Diary' }}</h2>
          </div>

          <button type="button" class="icon-btn" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          <label>
            Diary title
            <input v-model="title" type="text" placeholder="My First Aurora Experience" />
          </label>

          <label>
            Story
            <textarea v-model="story" rows="8" placeholder="Tell the story behind this journey..."></textarea>
          </label>

          <section class="photo-upload-section">
            <div class="photo-upload-head">
              <span>Diary photos</span>
              <small>{{ photos.length }}/10 images selected</small>
            </div>

            <label class="file-drop" :class="{ disabled: photos.length >= 10 }">
              <input
                type="file"
                accept="image/*"
                multiple
                :disabled="photos.length >= 10 || isProcessingImages"
                @change="handlePhotoUpload"
              />
              <i class="bi bi-images"></i>
              <span>{{ isProcessingImages ? 'Processing images...' : 'Choose Images' }}</span>
            </label>

            <small class="upload-hint">You can upload up to 10 images. Large images are resized before saving.</small>
          </section>

          <div v-if="photos.length" class="photo-preview">
            <figure v-for="(photo, index) in photos" :key="`${photo.slice(0, 48)}-${index}`">
              <img :src="resolveAssetUrl(photo)" alt="" />
              <button type="button" aria-label="Remove image" @click="removePhoto(index)">
                <i class="bi bi-x-lg"></i>
              </button>
            </figure>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="secondary" @click="$emit('close')">Cancel</button>
          <button type="button" class="primary" @click="saveDiary">Save Diary</button>
        </div>
      </section>
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { resolveAssetUrl } from '@/services/apiBase'
import { uploadDiaryImages } from '@/services/plannerService'

const props = defineProps({
  journey: { type: Object, default: null },
})

const emit = defineEmits(['close', 'save'])

const title = ref('')
const story = ref('')
const photos = ref([])
const isProcessingImages = ref(false)

watch(
  () => props.journey,
  (journey) => {
    title.value = journey?.diary?.title || ''
    story.value = journey?.diary?.story || ''
    photos.value = Array.isArray(journey?.diary?.photos) ? journey.diary.photos.slice(0, 10) : []
  },
  { immediate: true },
)

function saveDiary() {
  emit('save', {
    title: title.value.trim(),
    story: story.value.trim(),
    country: props.journey?.country || '',
    photos: photos.value.slice(0, 10),
  })
}

async function handlePhotoUpload(event) {
  const files = Array.from(event.target.files || [])
  event.target.value = ''

  if (!files.length || photos.value.length >= 10) return

  isProcessingImages.value = true

  try {
    const remainingSlots = Math.max(0, 10 - photos.value.length)
    const selectedFiles = files.slice(0, remainingSlots)
    const resizedFiles = await Promise.all(selectedFiles.map((file) => resizeImageFile(file)))
    const uploadedUrls = await uploadDiaryImages(resizedFiles)
    photos.value = [...photos.value, ...uploadedUrls].slice(0, 10)
  } finally {
    isProcessingImages.value = false
  }
}

function removePhoto(index) {
  photos.value = photos.value.filter((_, photoIndex) => photoIndex !== index)
}

function resizeImageFile(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onload = () => {
      const image = new Image()

      image.onload = () => {
        const maxWidth = 1200
        const maxHeight = 900
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
        canvas.toBlob(
          (blob) => {
            if (!blob) {
              reject(new Error('Image resize failed'))
              return
            }

            resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }))
          },
          'image/jpeg',
          0.78,
        )
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
.diary-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: clamp(14px, 3vw, 28px);
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(8px);
}

.diary-modal {
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

.modal-head,
.modal-actions {
  padding: 22px 28px;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
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
  font-size: 1.55rem;
  font-weight: 900;
}

.icon-btn {
  width: 36px;
  height: 36px;
  padding: 0;
  border: 0;
  background: transparent;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-text));
  cursor: pointer;
}

.modal-body {
  min-height: 0;
  display: grid;
  gap: 18px;
  overflow-y: auto;
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

.photo-preview {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.photo-upload-section {
  display: grid;
  gap: 10px;
}

.photo-upload-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  color: rgba(var(--v-theme-text), 0.72);
}

.photo-upload-head span {
  font-size: 0.82rem;
  font-weight: 850;
}

.photo-upload-head small,
.upload-hint {
  color: rgba(var(--v-theme-text), 0.52);
  font-size: 0.78rem;
}

.file-drop {
  min-height: 84px;
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

.file-drop.disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.file-drop input {
  display: none;
}

.file-drop i {
  font-size: 1.5rem;
}

.photo-preview figure {
  position: relative;
  margin: 0;
  overflow: hidden;
  border-radius: 12px;
  background: rgba(var(--v-theme-background), 0.42);
}

.photo-preview img {
  width: 100%;
  aspect-ratio: 4 / 3;
  display: block;
  object-fit: cover;
}

.photo-preview button {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  border: 0;
  border-radius: 50%;
  padding: 0;
  color: white;
  background: rgba(0, 0, 0, 0.58);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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

@media (max-width: 640px) {
  .photo-preview {
    grid-template-columns: 1fr;
  }
}
</style>
