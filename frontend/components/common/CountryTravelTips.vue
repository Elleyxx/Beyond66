<template>
  <section :id="sectionId" class="section-card tab-section">
    <div class="section-title-row">
      <h2>{{ title }}</h2>
      <div class="section-title-line"></div>
    </div>
    <div class="tips-grid">
      <article v-for="(tip, index) in tips" :key="`${tip.title}-${index}`" class="tip-item">
        <i :class="['bi', tip.icon, 'tip-icon']" aria-hidden="true"></i>
        <h3>{{ tip.title }}</h3>
        <p>{{ tip.text }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  sectionId: { type: String, required: true },
  title: { type: String, required: true },
  tips: { type: Array, default: () => [] },
})
</script>

<style scoped>
.tab-section {
  scroll-margin-top: 120px;
  margin-bottom: 80px;
}

.section-card {
  border-radius: 0;
  padding: 0;
  background: transparent;
  border: none;
  box-shadow: none;
}

.section-title-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 50px;
}

.section-title-row h2 {
  margin: 0;
  white-space: nowrap;
}

.section-title-line {
  flex: 1;
  height: 2px;
  background: rgba(var(--v-theme-text), 1);
}

.tips-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 38px;
  margin-top: 28px;
}

.tip-item {
  display: grid;
  justify-items: center;
  align-content: start;
  text-align: center;
  gap: 12px;
  padding: 10px 8px;
}

.tip-icon {
  width: 54px;
  height: 54px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgb(var(--v-theme-primary));
  font-size: 2rem;
}

.tip-item h3 {
  margin: 0;
  max-width: 22ch;
  font-size: 1.08rem;
  line-height: 1.25;
}

.tip-item p {
  margin: 0;
  max-width: 34ch;
  line-height: 1.6;
  opacity: 0.75;
}

@media (max-width: 900px) {
  .section-card {
    padding: 28px;
  }

  .tips-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .section-card {
    padding: 20px;
  }
}
</style>
