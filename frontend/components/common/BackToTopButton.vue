<template>
  <v-btn
    class="back-to-top-btn"
    icon
    size="large"
    :class="{ visible: isVisible }"
    @click="scrollToTop"
  >
    <v-icon>mdi-arrow-up</v-icon>
  </v-btn>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  threshold: {
    type: Number,
    default: 400,
  },
  target: {
    type: String,
    default: '',
  },
})

const isVisible = ref(false)
let scrollTarget = null

const handleWindowScroll = () => {
  const top = scrollTarget === window ? window.scrollY : scrollTarget?.scrollTop ?? 0
  isVisible.value = top > props.threshold
}

const scrollToTop = () => {
  if (scrollTarget === window) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }
  scrollTarget?.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  scrollTarget = props.target ? document.querySelector(props.target) : window
  if (!scrollTarget) {
    scrollTarget = window
  }
  scrollTarget.addEventListener('scroll', handleWindowScroll)
  handleWindowScroll()
})

onBeforeUnmount(() => {
  scrollTarget?.removeEventListener('scroll', handleWindowScroll)
})
</script>

<style scoped>
.back-to-top-btn {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 20;
  color: rgb(var(--v-theme-on-surface)) !important;
  background: rgba(var(--v-theme-background), 0.75) !important;
  border: 1px solid rgba(var(--v-theme-primary), 0.7);
  border-radius: 50%;
  box-shadow: 0 10px 24px rgba(var(--v-theme-background), 0.3);
  opacity: 0;
  pointer-events: none;
  transform: translateY(8px);
  transition: opacity 0.25s ease, transform 0.25s ease, background 0.25s ease;
}

.back-to-top-btn.visible {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0);
}

.back-to-top-btn:hover {
  background: rgba(var(--v-theme-primary), 0.22) !important;
}

@media (max-width: 760px) {
  .back-to-top-btn {
    right: 16px;
    bottom: 16px;
  }
}
</style>




