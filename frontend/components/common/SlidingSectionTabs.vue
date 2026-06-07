<template>
  <div class="section-tabs">
    <button
      v-for="tab in tabs"
      :key="tab.key"
      :class="['tab-btn', activeKey === tab.key && 'active']"
      @click="$emit('select', tab.key)"
    >
      {{ tab.label }}
    </button>
    <div class="tab-indicator" :style="indicatorStyle"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
  },
  activeKey: {
    type: String,
    required: true,
  },
})

defineEmits(['select'])

const activeIndex = computed(() =>
  props.tabs.findIndex((tab) => tab.key === props.activeKey)
)

const indicatorStyle = computed(() => ({
  width: `calc((100% - 36px) / ${props.tabs.length || 1})`,
  transform: `translateX(${Math.max(activeIndex.value, 0) * 100}%)`,
}))
</script>

<style scoped>
.section-tabs {
  position: sticky;
  top: 72px;
  z-index: 20;

  width: fit-content;
  max-width: min(1100px, calc(100vw - 32px));

  margin: 0 auto 30px;
  padding: 14px 18px;

  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 4px;

  border-radius: 28px;
  background: rgba(var(--v-theme-surface), 0.72);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border: 1px solid rgba(var(--v-theme-text), 0.18);
  box-shadow: 0 8px 30px rgba(var(--v-theme-background), 0.08);
}

.tab-btn {
  flex: 0 0 auto;
  position: relative;
  z-index: 2;

  border: none;
  background: transparent;

  padding: 12px 22px;

  font-size: 0.92rem;
  font-weight: 500;

  color: rgba(var(--v-theme-text), 0.5);
  cursor: pointer;

  transition:
    color 0.25s ease,
    transform 0.25s ease;
}

.tab-btn:hover {
  color: rgba(var(--v-theme-text), 0.76);
  transform: translateY(-1px);
}

.tab-btn.active {
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

.tab-indicator {
  position: absolute;
  bottom: 10px;
  left: 18px;
  height: 3px;
  border-radius: 999px;
  background: rgb(var(--v-theme-primary));
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    width 0.35s ease;
}

@media (max-width: 1250px) {
  .section-tabs {
    top: 92px;
    max-width: calc(100vw - 40px);
    margin-left: auto;
    margin-right: auto;
    padding: 12px 14px;
  }

  .tab-btn {
    padding: 10px 18px;
    font-size: 0.86rem;
  }

  .tab-indicator {
    left: 14px;
    bottom: 8px;
  }
}

@media (max-width: 900px) {
  .section-tabs {
    top: 88px;

    width: 100%;
    max-width: calc(100% - 32px);
    box-sizing: border-box;

    margin-left: auto;
    margin-right: auto;
    padding: 10px;
    gap: 2px;
  }

  .tab-btn {
    padding: 9px 14px;
    font-size: 0.82rem;
  }

  .tab-indicator {
    display: none;
  }

  .tab-btn.active {
    border-bottom: 2px solid rgb(var(--v-theme-primary));
  }
}

@media (max-width: 600px) {
  .section-tabs {
    top: 82px;

    width: 100%;
    max-width: calc(100% - 24px);
    box-sizing: border-box;

    padding: 8px;
  }

  .tab-btn {
    padding: 8px 12px;
    font-size: 0.76rem;
  }
}
</style>


