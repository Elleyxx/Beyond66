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
  top: 65px;
  z-index: 20;
  width: fit-content;
  max-width: min(96vw, 1100px);
  margin: 0 auto 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 14px 18px;
  border-radius: 28px;
  background: rgba(var(--v-theme-surface), 0.72);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border: 1px solid rgba(var(--v-theme-text), 0.18);
  box-shadow: 0 8px 30px rgba(var(--v-theme-background), 0.08);
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: none;
}

.section-tabs::-webkit-scrollbar {
  display: none;
}

.tab-btn {
  position: relative;
  z-index: 2;
  border: none;
  background: transparent;
  padding: 12px 22px;
  font-size: 0.92rem;
  font-weight: 500;
  color: rgba(var(--v-theme-text), 0.5);
  cursor: pointer;
  transition: color 0.25s ease, transform 0.25s ease;
}

.tab-btn:hover {
  color: rgba(var(--v-theme-text), 0.76);
}

.tab-btn.active {
  color: rgb(var(--v-theme-text));
  font-weight: 700;
}

.tab-indicator {
  position: absolute;
  bottom: 10px;
  left: 18px;
  height: 3px;
  border-radius: 999px;
  background: rgb(var(--v-theme-text));
  transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
</style>


