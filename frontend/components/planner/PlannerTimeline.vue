<template>
  <section class="planner-card timeline-card">
    <div class="timeline-head">
      <h2>{{ t('planner.timeline.title') }}</h2>
    </div>

    <p class="timeline-intro">
      {{ t('planner.timeline.intro') }}
    </p>

    <div class="activity-toolbar-head">
      <h3>{{ t('planner.timeline.activityPool') }}</h3>
      <span>{{ t('planner.timeline.dragHint') }}</span>
    </div>

    <div class="activity-pool">
      <button
        v-for="activity in activityPool"
        :key="activity"
        draggable="true"
        @dragstart="onDragStart(activity)"
      >
        {{ activity }}
      </button>
    </div>

    <div class="trip-timeline">
      <article
        v-for="(day, dayIndex) in timeline"
        :key="day.day"
        class="timeline-day"
        @dragover.prevent
        @drop="onDrop(dayIndex)"
      >
        <div class="day-label">
          <h3>{{ t('planner.timeline.day', { day: day.day }) }}</h3>
          <p v-if="day.date" class="day-date">{{ formatDate(day.date) }}</p>
          <select
            class="day-country-select"
            :value="dayCountry(day, dayIndex)"
            @change="setDayCountry(dayIndex, $event.target.value)"
          >
            <option
              v-for="country in countryOptionsList()"
              :key="`${dayIndex}-${country}`"
              :value="country"
            >
              {{ country }}
            </option>
          </select>
        </div>

        <div class="day-line">
          <span></span>
        </div>

        <div class="day-content">
          <div class="activity-panel">
            <h4>{{ t('planner.timeline.activities') }}</h4>

            <ul>
              <li v-for="(item, itemIndex) in day.items" :key="`${item}-${itemIndex}`">
                <span>{{ item }}</span>
                <button class="remove" @click="removeItem(dayIndex, itemIndex)">×</button>
              </li>

              <li v-if="!day.items.length" class="empty">
                {{ t('planner.timeline.dropActivity') }}
              </li>
            </ul>
          </div>

        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  timeline: { type: Array, required: true },
  countryRoute: { type: Array, default: () => [] },
  interests: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:timeline'])
const { t } = useI18n()

const dragged = ref('')

const fallbackActivities = [
  'Guided old town walk with local storytelling',
  'Scenic train or coastal route transfer',
  'Nordic café stop with regional pastries',
  'Evening viewpoint walk for sunset photos',
]

const activitiesByInterest = {
  'Northern Lights': [
    'Join a guided northern lights chase with warm drinks',
    'Visit a dark-sky viewpoint for aurora photography',
    'Book a glass igloo or wilderness camp aurora night',
  ],
  Nature: [
    'Hike an easy fjord or forest viewpoint trail',
    'Explore a national park with a local nature guide',
    'Take a slow scenic drive through lakes and mountains',
  ],
  Photography: [
    'Golden-hour photo walk around iconic viewpoints',
    'Capture waterfront architecture and Nordic street scenes',
    'Plan a landscape photography stop at sunrise',
  ],
  Food: [
    'Try a Nordic tasting menu or local seafood dinner',
    'Visit a market hall for regional snacks and produce',
    'Join a guided food walk with traditional desserts',
  ],
  Culture: [
    'Tour a heritage museum or historic royal district',
    'Explore old town streets, churches, and design shops',
    'Attend a local cultural performance or folk exhibit',
  ],
  Wildlife: [
    'Join a whale, puffin, reindeer, or moose watching tour',
    'Visit a wildlife reserve with ethical local guides',
    'Take a quiet nature walk focused on Arctic birdlife',
  ],
  'Winter Sports': [
    'Try beginner-friendly skiing or snowshoeing',
    'Book a husky sledding or snowmobile experience',
    'Spend an afternoon at a Nordic winter activity park',
  ],
  Fjords: [
    'Take a fjord cruise with waterfall viewpoints',
    'Ride a scenic ferry through mountain-lined waterways',
    'Visit a fjord village and nearby panoramic lookout',
  ],
  'Christmas Markets': [
    'Browse a Christmas market for crafts and warm drinks',
    'Visit festive old town lights and seasonal stalls',
    'Try Nordic holiday snacks at an evening market',
  ],
}

const activityPool = computed(() => {
  const selectedActivities = props.interests.flatMap((interest) => activitiesByInterest[interest] || [])
  return [...new Set(selectedActivities.length ? selectedActivities : fallbackActivities)]
})

const fallbackCountries = computed(() => [t('planner.timeline.fallbackCountry')])

function countryOptionsList() {
  return props.countryRoute.length ? props.countryRoute : fallbackCountries.value
}

function dayCountry(day, dayIndex) {
  const options = countryOptionsList()
  return day.country || options[dayIndex] || options[0]
}

function formatDate(value) {
  return new Intl.DateTimeFormat('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(`${value}T00:00:00`))
}

function setDayCountry(dayIndex, country) {
  const next = props.timeline.map((d) => ({ ...d, items: [...d.items] }))
  next[dayIndex] = { ...next[dayIndex], country, destination: next[dayIndex].destination || country }
  emit('update:timeline', next)
}

function onDragStart(activity) {
  dragged.value = activity
}

function onDrop(dayIndex) {
  if (!dragged.value) return

  const next = props.timeline.map((d) => ({
    ...d,
    items: [...d.items],
  }))

  next[dayIndex].items.push(dragged.value)
  emit('update:timeline', next)
  dragged.value = ''
}

function removeItem(dayIndex, itemIndex) {
  const next = props.timeline.map((d) => ({
    ...d,
    items: [...d.items],
  }))

  next[dayIndex].items.splice(itemIndex, 1)
  emit('update:timeline', next)
}
</script>

<style scoped>
.planner-card {
  min-width: 0;
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 20px;
  padding: 20px;
}

.timeline-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

h2 {
  margin: 0;
  font-size: 1.1rem;
}

.timeline-intro {
  margin: 10px 0 0;
  width: 100%;
  color: rgba(var(--v-theme-text), 0.72);
  font-size: 0.88rem;
  line-height: 1.55;
}

.pill {
  font-size: 0.75rem;
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
  border-radius: 999px;
  padding: 4px 10px;
}

.activity-toolbar-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.1);
}

.activity-toolbar-head h3 {
  margin: 0;
  font-size: 0.95rem;
}

.activity-toolbar-head span {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-primary));
}

.activity-pool {
  max-width: 100%;
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding: 8px 2px 12px;
  margin: 8px 0 18px;
  position: sticky;
  top: 80px;
  z-index: 5;
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(10px);
  scrollbar-width: thin;
}

.activity-pool button {
  flex: 0 0 auto;
  width: clamp(150px, 22vw, 220px);
  white-space: normal;
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.25);
  background: rgba(var(--v-theme-background), 0.45);
  color: rgb(var(--v-theme-text));
  border-radius: 14px;
  padding: 8px 11px;
  cursor: grab;
  font-weight: 700;
  font-size: 0.78rem;
  line-height: 1.3;
  text-align: left;
}

.trip-timeline {
  display: grid;
  gap: 12px;
  min-width: 0;
}

.timeline-day {
  display: grid;
  grid-template-columns: minmax(82px, 108px) 28px minmax(0, 1fr);
  min-height: 132px;
  padding: 14px 0;
  border-bottom: 0;
}

.timeline-day:last-child {
  border-bottom: 0;
}

.day-label {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.day-label h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 900;
  color: rgb(var(--v-theme-text));
}

.day-date {
  margin: 4px 0 0;
  font-size: 0.72rem;
  font-weight: 800;
  color: rgb(var(--v-theme-primary));
}

.day-country-select {
  margin-top: 8px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.22);
  border-radius: 10px;
  padding: 6px 8px;
  font-size: 0.78rem;
  font-weight: 700;
  background: rgba(var(--v-theme-background), 0.5);
  color: rgba(var(--v-theme-text), 0.85);
}

.day-line {
  position: relative;
  display: flex;
  justify-content: center;
}

.day-line::before {
  content: '';
  width: 2px;
  height: 100%;
  background: rgba(var(--v-theme-on-surface), 0.22);
}

.day-line span {
  position: absolute;
  top: 50%;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: rgb(var(--v-theme-surface));
  border: 2px solid rgba(var(--v-theme-on-surface), 0.28);
  transform: translateY(-50%);
}

.day-content {
  padding-left: 10px;
  min-width: 0;
}

.activity-panel {
  border-radius: 16px;
  padding: 14px 16px;
  background: rgba(var(--v-theme-background), 0.32);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
}

.day-content h4 {
  margin: 0 0 12px;
  font-size: 1rem;
  font-weight: 900;
  color: rgb(var(--v-theme-text));
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

li {
  position: relative;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: start;
  padding-left: 18px;
  font-size: 0.86rem;
  line-height: 1.5;
  color: rgba(var(--v-theme-text), 0.82);
}

li::before {
  content: '';
  position: absolute;
  left: 2px;
  top: 0.72em;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
}

.empty {
  opacity: 0.55;
  font-style: italic;
}

.remove {
  width: 24px;
  height: 24px;
  border: 0;
  border-radius: 50%;
  background: rgba(var(--v-theme-on-surface), 0.08);
  color: rgba(var(--v-theme-text), 0.65);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.remove:hover {
  color: white;
  background: #ef4444;
}

@media (max-width: 700px) {
  .timeline-day {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .day-label {
    align-items: flex-start;
  }

  .day-line {
    display: none;
  }

  .day-content {
    padding-left: 0;
  }

  .day-label h3 {
    font-size: 1.15rem;
  }

  .day-country-select {
    font-size: 0.65rem;
  }
}
</style>
