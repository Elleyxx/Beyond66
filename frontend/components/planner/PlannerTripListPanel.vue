<template>
  <aside class="triplist-panel">
    <img class="panel-border" src="/assets/images/trip_border.svg" alt="" />

    <div class="triplist-content">
      <div class="triplist-head">
        <h2>{{ t('planner.list.title') }}</h2>
        <button class="add-trip-btn" @click="$emit('add')">
          <img class="add-trip-stamp" src="/assets/images/stamp.svg" alt="" aria-hidden="true" />
          <span>{{ t('planner.list.add') }}</span>
        </button>
      </div>

      <ul class="triplist">
        <li
          v-for="trip in trips"
          :key="trip.id"
          :class="{ active: selectedTripId === trip.id }"
          @click="$emit('select', trip.id)"
        >
          <strong>{{ trip.title }}</strong>
          <span>{{ trip.country }} · {{ t('planner.list.days', { count: trip.duration }) }}</span>
        </li>
        <li v-if="!trips.length" class="empty-trip">
          {{ t('planner.list.empty') }}
        </li>
      </ul>
    </div>
  </aside>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  trips: { type: Array, default: () => [] },
  selectedTripId: { type: [Number, String, null], default: null },
})
defineEmits(['select', 'add'])
const { t } = useI18n()
</script>

<style scoped>
.triplist-panel {
  position: sticky;
  top: 100px;
  align-self: start;
  width: 100%;
  background: rgba(var(--v-theme-surface), 0.92);
  border: none;
  min-height: 320px;
  max-height: calc(100vh - 220px);
  overflow: auto;
  box-shadow:
    0 0 30px rgba(44, 246, 179, 0.06),
    0 12px 40px rgba(0, 0, 0, 0.08);
}

.panel-border {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: fill;
  object-position: center;
  pointer-events: none;
  z-index: 0;
}

.triplist-content {
  position: relative;
  z-index: 1;
  padding: 44px 42px;
}

.triplist-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 10px;
}

.triplist-head h2 {
  margin: 0;
  font-size: clamp(1.25rem, 1.7vw, 1.55rem);
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
}

.add-trip-btn {
  border: none;
  background: transparent;
  color: rgb(var(--v-theme-primary));
  padding: 0;
  font-weight: 700;
  cursor: pointer;
  position: relative;
  width: 120px;
  height: 64px;
  display: inline-grid;
  place-items: center;
  overflow: hidden;
}

.add-trip-stamp {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 120px;
  height: 55px;
  object-fit: fill;
  pointer-events: none;
}

.add-trip-btn span {
  position: relative;
  z-index: 1;
  width: 100%;
  text-align: center;
  font-size: 0.92rem;
}

.add-trip-btn:hover {
  transform: translateY(-1px);
}

.triplist {
  list-style: none;
  margin: 22px 0;
  padding: 0;
  display: grid;
  gap: 8px;
}

.triplist li {
  border: 1px dashed rgba(var(--v-theme-primary), 0.45);
  border-radius: 12px;
  padding: 20px 10px;
  cursor: pointer;
  background: rgba(var(--v-theme-background), 0.3);
}

.triplist li.active {
  background: rgba(var(--v-theme-primary), 0.14);
}

.triplist li span {
  display: block;
  margin-top: 4px;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-text), 0.7);
}

.triplist li.empty-trip {
  cursor: default;
  color: rgba(var(--v-theme-text), 0.7);
  text-align: center;
}
</style>
