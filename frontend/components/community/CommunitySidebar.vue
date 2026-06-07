<template>
  <aside class="community-sidebar">
    <section v-if="destinations.length" class="sidebar-card">
      <h3>{{ t('community.sidebar.destinations') }}</h3>

      <ul>
        <li v-for="item in destinations" :key="item.name">
          <RouterLink class="destination-link" :to="countryPath(item)">
            <span class="country-code">{{ item.code }}</span>

            <strong>{{ item.name }}</strong>

            <small>{{ t('community.sidebar.posts', { count: item.posts }) }}</small>
          </RouterLink>
        </li>
      </ul>
    </section>

    <section v-if="tags.length" class="sidebar-card">
      <h3>{{ t('community.sidebar.tags') }}</h3>

      <div class="tag-list">
        <button v-for="tag in tags" :key="tag" type="button">
          #{{ tag }}
        </button>
      </div>
    </section>
  </aside>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

defineProps({
  destinations: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
})

const { t } = useI18n()

function slugifyCountry(name) {
  return String(name || '')
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '-')
}

function countryPath(item) {
  return `/country/${item.slug || slugifyCountry(item.name)}`
}
</script>

<style scoped>
.community-sidebar {
  display: grid;
  gap: 28px;
  position: sticky;
  top: 90px;
}

.sidebar-card {
  min-height: 220px;
  padding: 28px 24px;
  border-radius: 28px;
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.sidebar-card h3 {
  margin: 0 0 32px;
}

.sidebar-card ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 12px;
}

.sidebar-card li {
  margin: 0;
}

.destination-link {
  display: flex;
  align-items: center;
  gap: 10px;
  color: inherit;
  text-decoration: none;
  padding: 6px 8px;
  border-radius: 14px;
  transition:
    background 0.25s ease,
    transform 0.25s ease;
}

.destination-link:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: translateX(4px);
}

.country-code {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 900;
  flex: 0 0 auto;
}

.destination-link strong {
  color: rgb(var(--v-theme-text));
}

.sidebar-card small {
  margin-left: auto;
  color: rgba(var(--v-theme-text), 0.55);
  font-size: 0.78rem;
}

.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag-list button {
  border: 0;
  border-radius: 999px;
  padding: 7px 10px;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
  font-weight: 650;
}

@media (max-width: 1250px) {
  .community-sidebar {
    position: static;
  }
}
</style>
