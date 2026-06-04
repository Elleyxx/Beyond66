<template>
  <section class="planning-column planner-container">
    <div class="stepper-header" :style="{ '--step-count': steps.length }">
      <div class="progress-timeline">
        <div
          v-for="(step, index) in steps"
          :key="step.title"
          class="timeline-step"
          :class="{
            completed: index < currentStep,
            active: index === currentStep,
            pending: index > currentStep,
            locked: index > maxUnlockedStep,
          }"
          role="button"
          :tabindex="index <= maxUnlockedStep ? 0 : -1"
          :aria-disabled="index > maxUnlockedStep"
          :aria-label="`Go to step ${index + 1}: ${step.title}`"
          @click="index <= maxUnlockedStep && $emit('go-step', index)"
          @keydown.enter="index <= maxUnlockedStep && $emit('go-step', index)"
          @keydown.space.prevent="index <= maxUnlockedStep && $emit('go-step', index)"
        >
          <span class="timeline-icon-ring" aria-hidden="true">
            <i :class="['bi', step.iconClass, 'timeline-icon-bi']"></i>
          </span>
          <div class="timeline-dot"></div>
          <p>{{ step.title }}</p>
        </div>
      </div>

      <div class="timeline-line-track">
        <div class="timeline-line-fill" :style="{ width: `${progressPercent}%` }"></div>
      </div>
    </div>

    <div class="planning-grid">
      <PlannerControls v-if="currentStep === 0" :model-value="tripMeta" @update:model-value="$emit('update:tripMeta', $event)" />

      <PlannerTimeline
        v-if="currentStep === 1"
        :timeline="timelineDays"
        :country-route="tripMeta.countryRoute || [tripMeta.country]"
        :interests="tripMeta.interests || []"
        @update:timeline="$emit('update:timelineDays', $event)"
      />

      <PlannerBudget
        v-if="currentStep === 2"
        :estimate="budgetEstimate"
        @update:estimate="$emit('update:budgetEstimate', $event)"
      />

      <PlannerWeatherAurora
        v-if="currentStep === 3"
        :weather="weatherForecast"
        :aurora="auroraForecast"
      />

      <PlannerChecklist
        v-if="currentStep === 4"
        :items="packingList"
        @toggle="$emit('toggle-packing', $event)"
        @print="$emit('print-checklist')"
      />

      <PlannerSummary
        v-if="currentStep === 5"
        :meta="tripMeta"
        :summary="aiSummary"
        :timeline="timelineDays"
        :budget="budgetEstimate"
        :weather="weatherForecast"
        :aurora="auroraForecast"
        :total-activities="totalActivities"
        :checked-count="checkedPackingCount"
        :checklist-count="packingList.length"
        @back="$emit('prev-step')"
        @open-save-modal="$emit('open-save-modal')"
      />
    </div>

    <div class="stepper-actions">
      <button class="step-btn secondary" :disabled="currentStep === 0" @click="$emit('prev-step')">Back</button>
      <button v-if="currentStep < steps.length - 1" class="step-btn primary" :disabled="isGeneratingAi" @click="$emit('next-step')">
        {{ currentStep === 0 && isGeneratingAi ? 'Generating Trip Plan...' : 'Next' }}
      </button>
      <button v-else class="step-btn primary" @click="$emit('open-save-modal')">
        {{ isEditing ? 'Save Edit' : 'Save Trip' }}
      </button>
    </div>
    <p v-if="aiError" class="ai-error">{{ aiError }}</p>
  </section>
</template>

<script setup>
import PlannerControls from '@/components/planner/PlannerControls.vue'
import PlannerTimeline from '@/components/planner/PlannerTimeline.vue'
import PlannerBudget from '@/components/planner/PlannerBudget.vue'
import PlannerWeatherAurora from '@/components/planner/PlannerWeatherAurora.vue'
import PlannerChecklist from '@/components/planner/PlannerChecklist.vue'
import PlannerSummary from '@/components/planner/PlannerSummary.vue'

defineProps({
  steps: { type: Array, required: true },
  currentStep: { type: Number, required: true },
  maxUnlockedStep: { type: Number, default: 0 },
  progressPercent: { type: Number, required: true },
  tripMeta: { type: Object, required: true },
  timelineDays: { type: Array, required: true },
  budgetEstimate: { type: Object, required: true },
  weatherForecast: { type: Array, required: true },
  auroraForecast: { type: [Object, Array], required: true },
  packingList: { type: Array, required: true },
  totalActivities: { type: Number, required: true },
  checkedPackingCount: { type: Number, required: true },
  aiSummary: { type: String, default: '' },
  aiError: { type: String, default: '' },
  isGeneratingAi: { type: Boolean, default: false },
  isEditing: { type: Boolean, default: false },
})

defineEmits([
  'go-step',
  'next-step',
  'prev-step',
  'save-trip',
  'open-save-modal',
  'toggle-packing',
  'print-checklist',
  'generate-ai-plan',
  'update:tripMeta',
  'update:timelineDays',
  'update:budgetEstimate',
])
</script>

<style scoped>
.planner-container {
  position: relative;
  min-width: 0;
  border: 4px solid rgba(var(--v-theme-primary), 0.9);
  background: rgba(var(--v-theme-secondary), 0.5);
  padding: clamp(20px, 2.4vw, 32px);
}

.planner-container::before {
  content: '';
  position: absolute;
  inset: 8px;
  border: 4px dashed rgba(var(--v-theme-primary), 0.9);
  pointer-events: none;
}

.planning-grid {
  display: grid;
  gap: 12px;
  min-width: 0;
}

.stepper-header {
  margin-bottom: 28px;
  position: relative;
}

.progress-timeline {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 6px;
  align-items: end;
  margin-bottom: 8px;
  position: relative;
  z-index: 2;
}

.timeline-step {
  text-align: center;
  cursor: pointer;
  outline: none;
}

.timeline-step.locked {
  cursor: not-allowed;
  opacity: 0.62;
}

.timeline-step:focus-visible {
  border-radius: 10px;
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary), 0.35);
}

.timeline-icon-ring {
  width: 46px;
  height: 46px;
  margin: 0 auto;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-primary), 0.5);
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-primary), 0.08);
}

.timeline-icon-bi {
  font-size: 1.35rem;
  line-height: 1;
  color: rgb(var(--v-theme-primary));
}

.timeline-dot {
  width: 14px;
  height: 14px;
  margin: 8px auto 6px;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
  background: rgba(var(--v-theme-surface), 1);
  position: relative;
  z-index: 3;
}

.timeline-step p {
  margin: 0;
  font-size: 0.78rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.42);
}

.timeline-step.pending .timeline-icon-ring {
  border-color: rgba(var(--v-theme-on-surface), 0.24);
  background: rgba(var(--v-theme-on-surface), 0.04);
}

.timeline-step.pending .timeline-icon-bi {
  color: rgba(var(--v-theme-on-surface), 0.42);
}

.timeline-step.completed .timeline-dot,
.timeline-step.active .timeline-dot {
  border-color: rgba(var(--v-theme-primary), 0.95);
  background: rgba(var(--v-theme-primary), 0.95);
}

.timeline-step.completed p,
.timeline-step.active p {
  color: rgb(var(--v-theme-text));
}

.timeline-step.active p {
  color: rgb(var(--v-theme-primary));
}

.timeline-line-track {
  position: absolute;
  left: calc(100% / (var(--step-count) * 2));
  right: calc(100% / (var(--step-count) * 2));
  top: 61px;
  height: 4px;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.18);
  overflow: hidden;
  z-index: 1;
}

.timeline-line-fill {
  height: 100%;
  background: linear-gradient(90deg, rgba(var(--v-theme-primary), 0.55), rgba(var(--v-theme-primary), 1));
  transition: width 0.3s ease;
}

.stepper-count {
  margin: 8px 0 0;
  font-size: 0.82rem;
  color: rgba(var(--v-theme-text), 0.75);
}

.stepper-actions {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.step-btn {
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
}

.step-btn.primary {
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
}

.step-btn.secondary {
  color: rgb(var(--v-theme-text));
  background: rgba(var(--v-theme-surface), 0.9);
  border-color: rgba(var(--v-theme-on-surface), 0.2);
}

.step-btn.ai {
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  border-color: rgba(var(--v-theme-primary), 0.35);
}

.step-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.ai-error {
  margin: 8px 0 0;
  color: #ef4444;
  font-size: 0.86rem;
  font-weight: 700;
}
</style>
