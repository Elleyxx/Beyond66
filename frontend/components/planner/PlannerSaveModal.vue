<template>
  <teleport to="body">
    <div class="modal-backdrop" @click.self="$emit('close')">
      <section class="save-modal">
        <div class="modal-head">
          <div>
            <p class="eyebrow">{{ t('planner.saveModal.eyebrow') }}</p>
            <h2>{{ isEditing ? t('planner.actions.saveEdit') : t('planner.actions.saveTrip') }}</h2>
          </div>

          <button class="icon-btn" type="button" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          <label>
            {{ t('planner.saveModal.tripTitle') }}
            <input v-model="title" type="text" :placeholder="t('planner.saveModal.titlePlaceholder')" />
          </label>

          <label>
            {{ t('planner.saveModal.description') }}
            <textarea
              v-model="description"
              rows="4"
              :placeholder="t('planner.saveModal.descriptionPlaceholder')"
            ></textarea>
          </label>

          <label>
            {{ t('planner.saveModal.tags') }}
            <input
              v-model="tagsInput"
              type="text"
              :placeholder="t('planner.saveModal.tagsPlaceholder')"
            />
            <small>{{ t('planner.saveModal.tagsHint') }}</small>
          </label>

          <div v-if="tags.length" class="tag-preview">
            <span v-for="tag in tags" :key="tag">#{{ tag }}</span>
          </div>

          <div class="tag-suggestions" aria-label="Suggested tags">
            <span>{{ t('planner.saveModal.suggestedTags') }}</span>

            <button
              v-for="tag in displayedSuggestedTags"
              :key="tag"
              type="button"
              :class="{ selected: hasTag(tag) }"
              :disabled="hasTag(tag)"
              @click="addSuggestedTag(tag)"
            >
              #{{ tag }}
            </button>
          </div>

          <label>
            {{ t('planner.saveModal.coverImage') }}

            <div class="image-upload">
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
              />

              <div v-if="coverPreview" class="preview">
                <img :src="coverPreview" :alt="t('planner.saveModal.coverAlt')" />
              </div>

              <div v-else class="upload-placeholder">
                <i class="bi bi-image"></i>
                <span>{{ t('planner.saveModal.chooseCover') }}</span>
              </div>
            </div>
          </label>

          <div v-if="!isEditing" class="visibility-options">
            <span>{{ t('planner.saveModal.visibility') }}</span>

            <label>
              <input v-model="visibility" type="radio" value="private" />
              {{ t('planner.saveModal.private') }}
            </label>

            <label>
              <input v-model="visibility" type="radio" value="public" />
              {{ t('planner.saveModal.public') }}
            </label>
          </div>
        </div>

        <div class="modal-actions">
          <button class="secondary" type="button" @click="$emit('close')">
            {{ t('planner.actions.cancel') }}
          </button>

          <button class="primary" type="button" @click="emitSave">
            {{ isEditing ? t('planner.actions.saveEdit') : visibility === 'public' ? t('planner.actions.publishTrip') : t('planner.actions.saveDraft') }}
          </button>
        </div>
      </section>
    </div>
  </teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const coverImage = ref(null)
const coverPreview = ref('')

const props = defineProps({
  defaultTitle: { type: String, default: '' },
  defaultDescription: { type: String, default: '' },
  defaultTags: { type: Array, default: () => [] },
  suggestedTags: { type: Array, default: () => [] },
  defaultVisibility: { type: String, default: 'private' },
  isEditing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'save-trip'])
const { t } = useI18n()

const title = ref(props.defaultTitle || t('planner.saveModal.defaultTitle'))
const description = ref(props.defaultDescription)
const tagsInput = ref(props.defaultTags.join(', '))
const visibility = ref(props.defaultVisibility)
const commonSuggestedTags = [
  'NorthernLights',
  'RoadTrip',
  'Fjords',
  'Photography',
  'BudgetTravel',
  'WinterTrip',
  'Food',
  'Nature',
]

const displayedSuggestedTags = computed(() => {
  const seen = new Set()

  return [...props.suggestedTags, ...commonSuggestedTags]
    .map((tag) => String(tag || '').trim().replace(/^#/, ''))
    .filter(Boolean)
    .filter((tag) => {
      const key = tag.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return true
    })
    .slice(0, 12)
})

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
  () => props.defaultTitle,
  (nextTitle) => {
    title.value = nextTitle || t('planner.saveModal.defaultTitle')
  },
)

watch(
  () => props.defaultDescription,
  (nextDescription) => {
    description.value = nextDescription
  },
)

watch(
  () => props.defaultTags,
  (nextTags) => {
    tagsInput.value = Array.isArray(nextTags) ? nextTags.join(', ') : ''
  },
)

watch(
  () => props.defaultVisibility,
  (nextVisibility) => {
    visibility.value = nextVisibility
  },
)

function emitSave() {
  emit('save-trip', {
    title: title.value.trim() || props.defaultTitle || t('planner.saveModal.defaultTitle'),
    description: description.value.trim(),
    tags: tags.value,
    visibility: props.isEditing ? props.defaultVisibility : visibility.value,
    coverImage: coverImage.value,
  })
}

function hasTag(tag) {
  return tags.value.some((currentTag) => currentTag.toLowerCase() === tag.toLowerCase())
}

function addSuggestedTag(tag) {
  if (hasTag(tag) || tags.value.length >= 8) return

  tagsInput.value = [...tags.value, tag].join(', ')
}

function handleImageUpload(event) {
  const file = event.target.files?.[0]

  if (!file) return

  coverImage.value = file

  coverPreview.value = URL.createObjectURL(file)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: clamp(14px, 3vw, 28px);
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(8px);
}

.save-modal {
  width: min(94vw, 780px);
  max-height: min(86vh, 760px);
  border-radius: 24px;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.14);
  box-shadow: 0 28px 80px rgba(0, 0, 0, 0.24);
  display: grid;
  grid-template-rows: auto minmax(0, 1fr) auto;
  overflow: hidden;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 24px 28px 18px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.modal-body {
  display: grid;
  gap: 18px;
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 22px 28px;
}

.modal-body::-webkit-scrollbar {
  width: 10px;
}

.modal-body::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-on-surface), 0.06);
}

.modal-body::-webkit-scrollbar-thumb {
  border: 3px solid rgb(var(--v-theme-surface));
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.65);
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  font-weight: 900;
  color: rgb(var(--v-theme-primary));
}

h2 {
  margin: 0;
  font-size: 1.7rem;
  font-weight: 900;
  color: rgb(var(--v-theme-text));
}

.icon-btn {
  width: 36px;
  height: 36px;
  border: 0;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-text));
  cursor: pointer;
}

label {
  display: grid;
  gap: 8px;
  font-size: 0.82rem;
  font-weight: 850;
  color: rgba(var(--v-theme-text), 0.72);
}

label small {
  color: rgba(var(--v-theme-text), 0.52);
  font-weight: 700;
}

input,
textarea {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 16px;
  padding: 12px 14px;
  background: rgba(var(--v-theme-background), 0.42);
  color: rgb(var(--v-theme-text));
  font: inherit;
  outline: none;
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

.tag-suggestions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-top: -6px;
}

.tag-suggestions > span {
  width: 100%;
  font-size: 0.76rem;
  font-weight: 850;
  color: rgba(var(--v-theme-text), 0.58);
}

.tag-suggestions button {
  border: 1px solid rgba(var(--v-theme-primary), 0.28);
  border-radius: 999px;
  padding: 7px 11px;
  background: rgba(var(--v-theme-primary), 0.06);
  color: rgba(var(--v-theme-text), 0.78);
  font-size: 0.76rem;
  font-weight: 850;
}

.tag-suggestions button:hover:not(:disabled) {
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
}

.tag-suggestions button.selected,
.tag-suggestions button:disabled {
  border-color: rgba(var(--v-theme-primary), 0.18);
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
  cursor: default;
  opacity: 0.78;
}

textarea {
  resize: vertical;
}

.visibility-options {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
  padding: 14px;
  border-radius: 18px;
  background: rgba(var(--v-theme-background), 0.34);
}

.visibility-options > span {
  width: 100%;
  font-size: 0.75rem;
  font-weight: 900;
  text-transform: uppercase;
  color: rgba(var(--v-theme-text), 0.55);
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

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 18px 28px 24px;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgb(var(--v-theme-surface));
}

button {
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 850;
  cursor: pointer;
}

button.primary {
  border: 0;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-background));
}

button.secondary {
  border: 1px solid rgba(var(--v-theme-primary), 0.42);
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.image-upload {
  position: relative;
}

.image-upload input[type='file'] {
  width: 100%;
  padding: 12px;
  border: 2px dashed rgba(var(--v-theme-primary), 0.25);
  border-radius: 18px;
  cursor: pointer;
}

.upload-placeholder {
  display: grid;
  place-items: center;
  gap: 8px;
  padding: 24px;
  border-radius: 18px;
  background: rgba(var(--v-theme-background), 0.3);
  color: rgba(var(--v-theme-text), 0.6);
}

.upload-placeholder i {
  font-size: 2rem;
}

.preview {
  margin-top: 12px;
  overflow: hidden;
  border-radius: 18px;
}

.preview img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  display: block;
}

@media (max-width: 640px) {
  .save-modal {
    width: min(100%, 560px);
    max-height: 90vh;
    border-radius: 18px;
  }

  .modal-head,
  .modal-body,
  .modal-actions {
    padding-left: 18px;
    padding-right: 18px;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .modal-actions button {
    width: 100%;
  }
}
</style>
