<template>
  <aside class="community-sidebar">
    <section class="sidebar-card">
      <h3>Popular Destinations</h3>

      <ul>
        <li v-for="item in destinations" :key="item.name">
          <span class="country-code">{{ item.code }}</span>
          <strong>{{ item.name }}</strong>
          <small>{{ item.posts }} posts</small>
        </li>
      </ul>
    </section>

    <section class="sidebar-card">
      <h3>Popular Tags</h3>

      <div class="tag-list">
        <button v-for="tag in tags" :key="tag" type="button">
          #{{ tag }}
        </button>
      </div>
    </section>

    <section class="sidebar-card">
      <h3>Top Creators</h3>

      <div v-for="creator in creators" :key="creator.name" class="creator-row">
        <div class="avatar">{{ creator.name.slice(0, 1) }}</div>

        <div>
          <strong>{{ creator.name }}</strong>
          <span>{{ creator.posts }} posts</span>
        </div>
      </div>
    </section>
  </aside>
</template>

<script setup>
defineProps({
  destinations: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  creators: { type: Array, default: () => [] },
})
</script>

<style scoped>
.community-sidebar {
  display: grid;
  gap: 18px;
  position: sticky;
  top: 90px;
}

.sidebar-card {
  padding: 18px;
  border-radius: 18px;
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
}

.sidebar-card h3 {
  margin: 0 0 14px;
}

.sidebar-card ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 12px;
}

.sidebar-card li,
.creator-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.country-code,
.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-background));
  background: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 900;
}

.sidebar-card small,
.creator-row span {
  margin-left: auto;
  color: rgba(var(--v-theme-text), 0.55);
  font-size: 0.78rem;
}

.creator-row div:last-child {
  display: grid;
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
  font-weight: 800;
}

@media (max-width: 1000px) {
  .community-sidebar {
    position: static;
  }
}
</style>
