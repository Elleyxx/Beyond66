<template>
  <section class="profile-panel achievements-panel">
    <div class="section-head">
      <p>{{ t('profilePage.achievements.eyebrow') }}</p>
      <h2>{{ t('profilePage.achievements.title') }}</h2>
    </div>

    <div class="achievement-grid">
      <article
        v-for="item in normalizedAchievements"
        :key="item.title"
        class="achievement-medal"
      >
        <div class="medal-top">
          <div class="medal-icon">
            <v-icon size="30">{{ item.icon }}</v-icon>
          </div>

          <span class="level-pill">
            LV {{ item.level }} / {{ item.maxLevel }}
          </span>
        </div>

        <h3>{{ item.title }}</h3>
        <p>{{ item.desc }}</p>

        <div class="progress-info">
          <span>{{ item.current }} / {{ item.target }}</span>
          <small>{{ item.unit }}</small>
        </div>

        <div class="level-bar">
          <span
            v-for="level in item.maxLevel"
            :key="level"
            :class="{ active: level <= item.level }"
          ></span>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  achievements: {
    type: Array,
    required: true,
  },
})

const levelRules = {
  nordicPassport:    [1, 2, 3, 4, 5],
  routeBuilder:      [1, 2, 4, 6, 10],
  dreamListCurator:  [1, 3, 5, 8, 12],
  storyteller:       [1, 2, 3, 5, 8],
}

function getLevel(current, rules) {
  let level = 0
  rules.forEach((target, index) => {
    if (current >= target) level = index + 1
  })
  return level
}

function getNextTarget(current, rules) {
  return rules.find((target) => current < target) || rules[rules.length - 1]
}

const normalizedAchievements = computed(() => {
  return props.achievements.map((item) => {
    const rules = item.levelRules || levelRules[item.badgeKey] || [1, 3, 5, 10, 20]
    const current = Number(item.current ?? item.value ?? 0)
    const level = getLevel(current, rules)
    const title = item.badgeKey
      ? t(`profilePage.badges.${item.badgeKey}.title`)
      : (item.title || '')
    const desc = item.badgeKey
      ? t(`profilePage.badges.${item.badgeKey}.desc`, { count: current })
      : (item.desc || '')

    return {
      ...item,
      title,
      desc,
      current,
      level,
      maxLevel: rules.length,
      target: getNextTarget(current, rules),
      unit: item.unit || 'progress',
    }
  })
})
</script>

<style scoped>
.profile-panel {
  padding: 30px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
}

.section-head p {
  margin-bottom: 8px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.section-head h2 {
  margin-bottom: 26px;
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
}

.achievement-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.achievement-medal {
  min-height: 220px;
  padding: 20px;
  border-radius: 22px;
  background:
    radial-gradient(circle at top left, rgba(var(--v-theme-primary), 0.18), transparent 36%),
    rgba(var(--v-theme-primary), 0.055);
  border: 1px solid rgba(var(--v-theme-primary), 0.18);
  transition:
    transform 0.28s ease,
    box-shadow 0.28s ease,
    border-color 0.28s ease;
}

.achievement-medal:hover {
  transform: translateY(-6px);
  border-color: rgba(var(--v-theme-primary), 0.42);
  box-shadow: 0 18px 34px rgba(var(--v-theme-background), 0.14);
}

.medal-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.medal-icon {
  width: 62px;
  height: 62px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: rgb(var(--v-theme-primary));
  background:
    linear-gradient(145deg, rgba(var(--v-theme-primary), 0.22), rgba(var(--v-theme-primary), 0.04));
  border: 2px solid rgba(var(--v-theme-primary), 0.36);
  box-shadow:
    inset 0 0 0 5px rgba(var(--v-theme-surface), 0.88),
    0 10px 24px rgba(var(--v-theme-background), 0.12);
}

.level-pill {
  padding: 6px 10px;
  border-radius: 999px;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.08em;
}

.achievement-medal h3 {
  margin: 0 0 8px;
  color: rgb(var(--v-theme-text));
  font-size: 1.15rem;
  font-weight: 900;
}

.achievement-medal p {
  min-height: 40px;
  margin: 0;
  color: rgba(var(--v-theme-text), 0.64);
  font-size: 0.9rem;
  line-height: 1.45;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 20px;
  color: rgba(var(--v-theme-text), 0.7);
  font-size: 0.78rem;
  font-weight: 800;
}

.progress-info small {
  text-align: right;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  opacity: 0.68;
}

.level-bar {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 6px;
  margin-top: 12px;
}

.level-bar span {
  height: 7px;
  border-radius: 999px;
  background: rgba(var(--v-theme-text), 0.14);
}

.level-bar span.active {
  background: rgb(var(--v-theme-primary));
}

@media (max-width: 900px) {
  .achievement-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .achievement-medal {
    min-height: 205px;
    padding: 18px;
  }
}

@media (max-width: 600px) {
  .profile-panel {
    padding: 24px 18px;
  }

  .achievement-grid {
    grid-template-columns: 1fr;
  }

  .achievement-medal {
    min-height: 190px;
  }
}
</style>
