<template>
  <main class="search-page">
    <section class="search-hero">
      <p class="eyebrow">{{ t('search.eyebrow') }}</p>
      <h1>{{ t('search.title') }}</h1>
      <form class="search-form" @submit.prevent="submitSearch">
        <i class="bi bi-search"></i>
        <input v-model="queryInput" type="search" :placeholder="t('search.placeholder')" />
        <button type="submit">{{ t('search.submit') }}</button>
      </form>
    </section>

    <section class="results-section">
      <div class="section-head">
        <h2>{{ resultHeading }}</h2>
        <span>{{ t('search.resultCount', { count: results.length }) }}</span>
      </div>

      <div v-if="results.length" class="result-grid">
        <RouterLink
          v-for="result in results"
          :key="`${result.path}-${result.title}`"
          class="result-card"
          :to="result.path"
        >
          <span>{{ result.category }}</span>
          <h3>{{ result.title }}</h3>
          <p>{{ result.description }}</p>
          <strong>{{ t('search.open') }}</strong>
        </RouterLink>
      </div>

      <p v-else class="empty-state">{{ t('search.empty') }}</p>
    </section>
  </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { searchSite } from '@/services/siteSearch'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const queryInput = ref(String(route.query.q || ''))

const results = computed(() => searchSite(queryInput.value, t, { limit: 80, locale: locale.value }))
const resultHeading = computed(() => {
  return queryInput.value.trim()
    ? t('search.resultsFor', { query: queryInput.value.trim() })
    : t('search.popular')
})

watch(
  () => route.query.q,
  (query) => {
    queryInput.value = String(query || '')
  },
)

function submitSearch() {
  const query = queryInput.value.trim()
  router.push({
    path: '/search',
    query: query ? { q: query } : {},
  })
}
</script>

<style scoped>
.search-page {
  min-height: 100vh;
  padding: 120px var(--page-gutter) 100px;
  background: rgb(var(--v-theme-background));
}

.search-hero {
  width: min(100%, 980px);
  margin: 0 auto 42px;
}

.eyebrow {
  margin: 0 0 10px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 900;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  font-size: clamp(2.5rem, 6vw, 5rem);
  line-height: 0.95;
  font-weight: 900;
}

.search-form {
  margin-top: 28px;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.14);
  border-radius: 999px;
  padding: 10px 12px 10px 18px;
  background: rgba(var(--v-theme-surface), 0.95);
}

.search-form i {
  color: rgb(var(--v-theme-primary));
}

.search-form input {
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: rgb(var(--v-theme-text));
  font: inherit;
}

.search-form button {
  border: 0;
  border-radius: 999px;
  padding: 10px 18px;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-weight: 900;
  cursor: pointer;
}

.results-section {
  width: min(100%, 1100px);
  margin: 0 auto;
}

.section-head {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 18px;
}

.section-head h2 {
  margin: 0;
  font-size: 1.35rem;
}

.section-head span {
  color: rgba(var(--v-theme-text), 0.58);
  font-weight: 800;
}

.result-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.result-card {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  padding: 20px;
  color: inherit;
  background: rgba(var(--v-theme-surface), 0.94);
  text-decoration: none;
  transition:
    transform 0.22s ease,
    border-color 0.22s ease,
    box-shadow 0.22s ease;
}

.result-card:hover {
  transform: translateY(-4px);
  border-color: rgba(var(--v-theme-primary), 0.4);
  box-shadow: 0 16px 28px rgba(var(--v-theme-background), 0.12);
}

.result-card span {
  width: fit-content;
  border-radius: 999px;
  padding: 5px 9px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
  font-size: 0.72rem;
  font-weight: 900;
  text-transform: uppercase;
}

.result-card h3 {
  margin: 16px 0 8px;
  font-size: 1.3rem;
}

.result-card p {
  margin: 0;
  color: rgba(var(--v-theme-text), 0.68);
  line-height: 1.55;
}

.result-card strong {
  margin-top: auto;
  padding-top: 18px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.86rem;
}

.empty-state {
  margin: 0;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  padding: 22px;
  color: rgba(var(--v-theme-text), 0.68);
  background: rgba(var(--v-theme-surface), 0.94);
}

@media (max-width: 1250px) {
  .search-page {
    padding-top: 120px;
    padding-bottom: 90px;
  }
}

@media (max-width: 900px) {
  .search-page {
    padding-top: 116px;
    padding-bottom: 84px;
  }

  .section-head {
    align-items: start;
    flex-direction: column;
  }
}

@media (max-width: 600px) {
  .search-page {
    padding-top: 108px;
    padding-bottom: 72px;
  }

  .search-form {
    grid-template-columns: auto minmax(0, 1fr);
    border-radius: 24px;
  }

  .search-form button {
    grid-column: 1 / -1;
  }
}
</style>
