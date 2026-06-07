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
  min-height: 320px;
  max-height: calc(100vh - 220px);
  overflow: auto;
  background: rgba(var(--v-theme-surface), 0.92);
  border: none;
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
  gap: 10px;
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

@media (max-width: 1250px) {
  .triplist-panel {
    position: relative;
    top: auto;
    min-height: auto;
    max-height: none;
    overflow: visible;
  }

  .triplist-content {
    padding: 40px 38px;
  }
}

@media (max-width: 900px) {
  .triplist-panel {
    box-shadow:
      0 0 24px rgba(44, 246, 179, 0.05),
      0 10px 28px rgba(0, 0, 0, 0.06);
  }

  .triplist-content {
    padding: 36px 34px;
  }

  .triplist {
    max-height: 260px;
    overflow-y: auto;
    padding-right: 4px;
  }

  .triplist li {
    padding: 16px 12px;
  }
}

@media (max-width: 600px) {
  .triplist-panel {
    background: rgba(var(--v-theme-surface), 0.96);
    box-shadow: none;
  }

  .panel-border {
    opacity: 0.75;
  }

  .triplist-content {
    padding: 34px 28px;
  }

  .triplist-head {
    align-items: center;
  }

  .add-trip-btn {
    width: 108px;
    height: 56px;
  }

  .add-trip-stamp {
    width: 108px;
    height: 50px;
  }

  .add-trip-btn span {
    font-size: 0.82rem;
  }

  .triplist {
    max-height: 210px;
    margin: 18px 0 8px;
  }

  .triplist li {
    padding: 14px 10px;
  }
}
</style>
