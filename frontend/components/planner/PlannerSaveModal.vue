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
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const coverImage = ref(null)
const coverPreview = ref('')

const props = defineProps({
  defaultTitle: { type: String, default: '' },
  defaultDescription: { type: String, default: '' },
  defaultVisibility: { type: String, default: 'private' },
  isEditing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'save-trip'])
const { t } = useI18n()

const title = ref(props.defaultTitle || t('planner.saveModal.defaultTitle'))
const description = ref(props.defaultDescription)
const visibility = ref(props.defaultVisibility)

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
  () => props.defaultVisibility,
  (nextVisibility) => {
    visibility.value = nextVisibility
  },
)

function emitSave() {
  emit('save-trip', {
    title: title.value.trim() || props.defaultTitle || t('planner.saveModal.defaultTitle'),
    description: description.value.trim(),
    visibility: props.isEditing ? props.defaultVisibility : visibility.value,
    coverImage: coverImage.value,
  })
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
  padding: 24px;
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(8px);
}

.save-modal {
  width: min(100%, 520px);
  border-radius: 28px;
  padding: 24px;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.14);
  box-shadow: 0 28px 80px rgba(0, 0, 0, 0.24);
  display: grid;
  gap: 18px;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
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
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-on-surface), 0.08);
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
  height: 180px;
  object-fit: cover;
  display: block;
}
</style>
