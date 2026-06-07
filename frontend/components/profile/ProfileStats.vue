<template>
  <section class="stats-grid">
    <article v-for="stat in stats" :key="stat.key || stat.label" class="stat-card">
      <i :class="stat.icon" class="stat-icon"></i>
      <h3>{{ stat.value }}</h3>
      <p>{{ stat.key ? t(stat.key) : stat.label }}</p>
    </article>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  stats: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 18px;
  margin-top: 28px;
}

.stat-card {
  display: grid;
  justify-items: center;
  padding: 24px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 12px 30px rgba(var(--v-theme-background), 0.12);
  text-align: center;
}

.stat-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgb(var(--v-theme-primary));
  font-size: 1.9rem;
  line-height: 1;
}

.stat-card h3 {
  margin: 12px 0 4px;
  font-size: 2rem;
}

.stat-card p {
  margin: 0;
  opacity: 0.72;
}
</style>
