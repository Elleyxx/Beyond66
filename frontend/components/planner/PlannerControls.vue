<template>
  <section class="planner-card controls-card">
    <div class="controls-grid">
      <div class="left-box column-box field-section">
        <label>
          <h3>{{ t('planner.controls.countriesRoute') }}</h3>
          <p class="field-note">{{ t('planner.controls.countriesRouteNote') }}</p>

          <div
            class="country-slot"
            v-for="(slot, index) in countrySlots"
            :key="`slot-${index}`"
            draggable="true"
            @dragstart="onDragStart(index)"
            @dragover.prevent
            @drop="onDrop(index)"
          >
            <i class="bi bi-grip-vertical drag-icon"></i>

            <select :value="slot" @change="updateCountrySlot(index, $event.target.value)">
              <option v-for="item in countries" :key="item.value" :value="item.value">
                {{ item.label }}
              </option>
            </select>

            <button
              class="remove-country-btn"
              type="button"
              @click="removeCountrySlot(index)"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <button v-if="countrySlots.length < 5" class="add-country-btn" @click="addCountrySlot">+</button>
        </label>
      </div>

      <div class="middle-box column-box field-section">
        <div class="date-picker-box">
          <label>
            <div class="date-header">
              <h3>{{ t('planner.controls.travelDate') }}</h3>
              <p class="date-meta-pill">
                <span>{{ derivedSeason }}</span>
                <span> | </span>
                <span>{{ t('planner.controls.days', { count: tripDuration }) }}</span>
              </p>
            </div>

            <p class="field-note">{{ t('planner.controls.travelDateNote') }}</p>

            <VueDatePicker
              :model-value="modelValue.dateRange"
              :range="{ partialRange: false }"
              auto-apply
              model-type="yyyy-MM-dd"
              :enable-time-picker="false"
              :clearable="false"
              :placeholder="t('planner.controls.selectTravelDates')"
              @update:model-value="updateDateRange"
            >
              <template #trigger>
                <button class="date-trigger" type="button">
                  <span>{{ dateRangeLabel }}</span>
                  <i class="bi bi-calendar3"></i>
                </button>
              </template>
            </VueDatePicker>
          </label>
        </div>

        <div class="budget-selector">
          <label>
            <div class="budget-header">
              <h3>{{ t('planner.controls.budget') }}</h3>
              <span class="budget-pill">
                {{ getBudgetLabel(modelValue.budgetLevel || 3) }}
              </span>
            </div>

            <p class="field-note">
              {{ t('planner.controls.budgetNote') }}
            </p>

            <input
              class="budget-slider"
              type="range"
              min="1"
              max="5"
              step="1"
              :value="modelValue.budgetLevel || 3"
              @input="updateBudgetLevel(Number($event.target.value))"
            />
            <div class="budget-scale">
              <span>$ {{ budgetRange.min }}</span>
              <span>$ {{ budgetRange.max }}</span>
            </div>
          </label>
        </div>

        <label>
          <h3>{{ t('planner.controls.accommodation') }}</h3>
          <p class="field-note">{{ t('planner.controls.accommodationNote') }}</p>
          <select :value="modelValue.accommodation || accommodations[0]" @change="emitPatch({ accommodation: $event.target.value })">
            <option v-for="item in accommodations" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>

        <label>
          <h3>{{ t('planner.controls.transport') }}</h3>
          <p class="field-note">{{ t('planner.controls.transportNote') }}</p>
          <select :value="modelValue.transport || transports[0]" @change="emitPatch({ transport: $event.target.value })">
            <option v-for="item in transports" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>
      </div>

      <div class="right-box column-box field-section">
        <label>
          <h3>{{ t('planner.controls.pax') }}</h3>
          <p class="field-note">{{ t('planner.controls.paxNote') }}</p>
          <select :value="modelValue.pax || 1" @change="emitPatch({ pax: Number($event.target.value) })">
            <option v-for="n in 20" :key="n" :value="n">{{ n }}</option>
          </select>
        </label>
        <label>
          <h3>{{ t('planner.controls.travelStyle') }}</h3>
          <p class="field-note">{{ t('planner.controls.travelStyleNote') }}</p>
          <select :value="modelValue.style" @change="emitPatch({ style: $event.target.value })">
            <option v-for="item in styles" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>

        <label>
          <h3>{{ t('planner.controls.tripType') }}</h3>
          <p class="field-note">{{ t('planner.controls.tripTypeNote') }}</p>
          <select :value="modelValue.tripType || tripTypes[0]" @change="emitPatch({ tripType: $event.target.value })">
            <option v-for="item in tripTypes" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>

        <label>
          <h3>{{ t('planner.controls.activityLevel') }}</h3>
          <p class="field-note">{{ t('planner.controls.activityLevelNote') }}</p>
          <select :value="modelValue.activityLevel || activityLevels[1]" @change="emitPatch({ activityLevel: $event.target.value })">
            <option v-for="item in activityLevels" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>

      </div>

      <div class="interests-box field-section">
        <h3>{{ t('planner.controls.interests') }}</h3>
        <p class="field-note">{{ t('planner.controls.interestsNote') }}</p>

        <div class="interests-grid">
          <label v-for="item in interests" :key="item.value" class="check-option">
            <input
              type="checkbox"
              :checked="selectedInterests.includes(item.value)"
              @change="toggleInterest(item.value)"
            />
            <i :class="['bi', item.icon]" aria-hidden="true"></i>
            <span>{{ item.label }}</span>
          </label>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps({
  modelValue: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue'])
const { t } = useI18n()

const countries = computed(() => [
  { value: 'Norway', label: t('countryNames.norway') },
  { value: 'Sweden', label: t('countryNames.sweden') },
  { value: 'Finland', label: t('countryNames.finland') },
  { value: 'Iceland', label: t('countryNames.iceland') },
  { value: 'Denmark', label: t('countryNames.denmark') },
])
const styles = ['Adventure', 'Relax', 'Culture', 'Nature', 'Luxury', 'Balanced']

const budgetRange = computed(() => {
  const level = props.modelValue.budgetLevel || 3

  const ranges = {
    1: { min: 500, max: 1000 },
    2: { min: 1000, max: 2000 },
    3: { min: 2000, max: 3500 },
    4: { min: 3500, max: 5500 },
    5: { min: 5500, max: 9000 },
  }

  return ranges[level]
})

function getBudgetLabel(level) {
  const labels = {
    1: t('planner.options.budget'),
    2: t('planner.options.economy'),
    3: t('planner.options.midRange'),
    4: t('planner.options.premium'),
    5: t('planner.options.luxury'),
  }

  return labels[level] || t('planner.options.midRange')
}

function updateBudgetLevel(level) {
  emitPatch({
    budgetLevel: level,
    budget: getBudgetLabel(level),
  })
}

const accommodations = ['Hotel', 'Cabin', 'Apartment', 'Hostel', 'Resort']
const transports = ['Train', 'Rental Car', 'Bus', 'Flight', 'Ferry']
const tripTypes = ['Couple', 'Family', 'Solo', 'Friends']
const activityLevels = ['Easy', 'Moderate', 'Active', 'Extreme']
const interests = [
  { value: 'Northern Lights', label: 'Northern Lights', icon: 'bi-stars' },
  { value: 'Nature', label: 'Nature', icon: 'bi-tree' },
  { value: 'Photography', label: 'Photography', icon: 'bi-camera' },
  { value: 'Food', label: 'Food', icon: 'bi-fork-knife' },
  { value: 'Culture', label: 'Culture', icon: 'bi-bank' },
  { value: 'Wildlife', label: 'Wildlife', icon: 'bi-binoculars' },
  { value: 'Winter Sports', label: 'Winter Sports', icon: 'bi-snow' },
  { value: 'Fjords', label: 'Fjords', icon: 'bi-water' },
  { value: 'Christmas Markets', label: 'Christmas Markets', icon: 'bi-gift' },
]

const countrySlots = ref([])
const dragIndex = ref(-1)
const selectedInterests = computed(() =>
  Array.isArray(props.modelValue.interests) ? props.modelValue.interests : [],
)

const tripDuration = computed(() => {
  return calculateDuration(props.modelValue.startDate, props.modelValue.endDate)
})

const dateRangeLabel = computed(() => {
  if (!props.modelValue.startDate || !props.modelValue.endDate) {
    return t('planner.controls.selectTravelDates')
  }

  return `${formatDate(props.modelValue.startDate)} → ${formatDate(props.modelValue.endDate)}`
})

const derivedSeason = computed(() => {
  return getSeasonFromDate(props.modelValue.startDate)
})

watch(
  () => props.modelValue,
  (next) => {
    if (Array.isArray(next.countryRoute) && next.countryRoute.length) {
      countrySlots.value = [...next.countryRoute].slice(0, 5)
      return
    }
    countrySlots.value = [next.country || countries.value[0].value]
  },
  { immediate: true, deep: true },
)

function emitPatch(patch) {
  emit('update:modelValue', { ...props.modelValue, ...patch })
}

function updateDateRange(range) {
  const normalizedRange = Array.isArray(range) ? range : []
  const startDate = normalizedRange[0] || ''
  const endDate = normalizedRange[1] || ''

  emitPatch({
    dateRange: normalizedRange,
    startDate,
    endDate,
    duration: calculateDuration(startDate, endDate),
    season: getSeasonFromDate(startDate),
  })
}

function calculateDuration(startDate, endDate) {
  if (!startDate || !endDate) return 0

  const start = new Date(startDate)
  const end = new Date(endDate)
  const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1

  return diff > 0 ? diff : 0
}

function getSeasonFromDate(dateValue) {
  if (!dateValue) return '—'

  const month = new Date(dateValue).getMonth() + 1

  if ([12, 1, 2].includes(month)) return 'Winter'
  if ([3, 4, 5].includes(month)) return 'Spring'
  if ([6, 7, 8].includes(month)) return 'Summer'
  return 'Autumn'
}

function formatDate(dateValue) {
  if (!dateValue) return ''

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(dateValue))
}

function toggleInterest(interest) {
  const current = selectedInterests.value
  const next = current.includes(interest)
    ? current.filter((item) => item !== interest)
    : [...current, interest]

  emitPatch({ interests: next })
}

function syncCountries(nextSlots) {
  const cleaned = nextSlots.slice(0, 5)
  countrySlots.value = cleaned
  emitPatch({
    countryRoute: cleaned,
    country: cleaned[0],
  })
}

function addCountrySlot() {
  if (countrySlots.value.length >= 5) return
  syncCountries([...countrySlots.value, countries.value[0].value])
}

function updateCountrySlot(index, value) {
  const next = [...countrySlots.value]
  next[index] = value
  syncCountries(next)
}

function removeCountrySlot(index) {
  const next = [...countrySlots.value]

  next.splice(index, 1)

  // Always keep at least one country
  if (next.length === 0) {
    next.push(countries.value[0].value)
  }

  syncCountries(next)
}

function onDragStart(index) {
  dragIndex.value = index
}

function onDrop(dropIndex) {
  const from = dragIndex.value
  if (from < 0 || dropIndex < 0 || from === dropIndex) return

  const next = [...countrySlots.value]
  const [moved] = next.splice(from, 1)
  next.splice(dropIndex, 0, moved)
  syncCountries(next)
  dragIndex.value = -1
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 22px;
}

.controls-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 24px;
}

.field-section {
  min-height: 250px;
}

.column-box {
  display: grid;
  align-content: start;
  gap: 18px;
  padding: 6px 24px 6px 0;
  border-right: 2px solid rgba(var(--v-theme-on-surface), 0.18);
}

.right-box {
  border-right: 0;
  padding-right: 0;
}

h3 {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 1rem;
  font-weight: 750;
  color: rgb(var(--v-theme-text));
}

.field-note {
  margin: -2px 0 10px;
  font-size: 0.78rem;
  line-height: 1.35;
  letter-spacing: 0.01em;
  color: rgba(var(--v-theme-text), 0.74);
  font-weight: 500;
}

.country-slot {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  padding: 8px 12px;

  border: 1px solid rgba(var(--v-theme-on-surface), 0.15);
  border-radius: 14px;
  background: rgba(var(--v-theme-background), 0.35);
}

.country-slot select {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 14px;
  padding: 10px 14px;
  background: rgba(var(--v-theme-background), 0.45);
  color: rgb(var(--v-theme-text));
  font-weight: 700;
}

.drag-icon {
  color: rgb(var(--v-theme-primary));
  cursor: grab;
  font-size: 1.15rem;
}

.country-slot select,
.add-country-btn,
select,
input[type='date']
{
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 14px;
  padding: 10px 14px;
  background: rgba(var(--v-theme-background), 0.45);
  color: rgb(var(--v-theme-text));
  font-weight: 700;
  cursor: pointer;
}

.remove-country-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  color: rgba(var(--v-theme-text), 0.7);
  padding: 0;
}

.remove-country-btn:hover {
  color: #ef4444;
}

.add-country-btn {
  font-size: 1.25rem;
}

.add-country-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.middle-box,
.right-box {
  display: grid;
  gap: 18px;
}

.column-box label {
  display: grid;
  gap: 10px;
}

input[type='range'] {
  width: 100%;
}

.range-value {
  color: rgb(var(--v-theme-primary));
  font-weight: 800;
}

select {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 14px;
  padding: 12px 14px;
  background: rgba(var(--v-theme-background), 0.5);
  color: rgb(var(--v-theme-text));
}

input[type='date'] {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 14px;
  padding: 12px 14px;
  background: rgba(var(--v-theme-background), 0.5);
  color: rgb(var(--v-theme-text));
}

.date-picker-box {
  border-radius: 22px;
  background: rgba(var(--v-theme-background), 0.35);
}

.date-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.date-header h3 {
  margin: 0;
}

.date-trigger {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 18px;
  padding: 10px 16px;
  background: rgba(var(--v-theme-surface), 0.88);
  color: rgb(var(--v-theme-text));
  font-weight: 750;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
  text-align: left;
}

.date-trigger span {
  min-width: 0;
}

.date-trigger i {
  color: rgb(var(--v-theme-primary));
}

.date-picker-box :deep(.dp__main) {
  font-family: inherit;
}

.date-picker-box :deep(.dp__menu) {
  border: 0;
  border-radius: 24px;
  box-shadow: 0 22px 48px rgba(0, 0, 0, 0.18);
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.date-picker-box :deep(.dp__calendar_header_item),
.date-picker-box :deep(.dp__cell_inner) {
  border-radius: 999px;
}

.date-picker-box :deep(.dp__range_between) {
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
}

.date-picker-box :deep(.dp__range_start),
.date-picker-box :deep(.dp__range_end) {
  background: rgb(var(--v-theme-primary));
  color: white;
}

.date-meta-pill {
  width: fit-content;
  margin: 0;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 620;
  display: flex;
  align-items: center;
  gap: 12px;
}

.interests-box {
  grid-column: 1 / -1;
  border: 2px solid rgba(var(--v-theme-primary), 0.22);
  border-radius: 28px;
  padding: 20px;
  background: rgba(var(--v-theme-primary), 0.06);
}

.interests-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.check-option {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  padding: 12px 14px;
  font-weight: 700;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.14);
  border-radius: 16px;
  background: rgba(var(--v-theme-surface), 0.72);
}

.check-option input {
  width: 20px;
  height: 20px;
  accent-color: rgb(var(--v-theme-primary));
  cursor: pointer;
}

.check-option i {
  color: rgb(var(--v-theme-on-surface));
  font-size: 1.1rem;
  line-height: 1;
}

.budget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.budget-pill {
  width: fit-content;
  margin: 0;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 620;
  display: flex;
  align-items: center;
  gap: 12px;
}

.budget-slider {
  width: 100%;
  appearance: none;
  height: 6px;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.12);
}

.budget-slider::-webkit-slider-thumb {
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
  border: 3px solid rgb(var(--v-theme-surface));
  box-shadow: 0 2px 10px rgba(0,0,0,0.15);
  cursor: pointer;
}

.budget-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
  border: 3px solid rgb(var(--v-theme-surface));
  cursor: pointer;
}

.budget-level-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  margin-top: 4px;
  text-align: center;
}

.budget-level-row span {
  font-size: 0.72rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.55);
}

.budget-scale {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  font-weight: 700;
  color: rgba(var(--v-theme-text), 0.7);
}

@media (max-width: 900px) {
  .controls-grid {
    grid-template-columns: 1fr;
  }

  .column-box,
  .interests-box {
    grid-column: auto;
    grid-row: auto;
  }

  .field-section {
    min-height: auto;
  }

  .interests-grid {
    grid-template-columns: 1fr;
  }
}
</style>
