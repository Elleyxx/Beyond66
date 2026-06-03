<template>
  <section class="planner-card checklist-builder">
    <aside class="style-panel">
      <h3>Decorate</h3>

      <label>
        Checklist Title
        <input v-model="checklistTitle" type="text" />
      </label>

      <label>
        Paper Size
        <select v-model="paperSize">
          <option value="a4">A4</option>
          <option value="letter">Letter</option>
        </select>
      </label>

      <label>
        Font Style
        <select v-model="fontStyle">
          <option value="clean">Clean</option>
          <option value="handwritten">Handwritten</option>
          <option value="serif">Serif</option>
        </select>
      </label>

      <label class="switch-row">
        <input v-model="hideCheckedOnExport" type="checkbox" />
        Hide checked items on export
      </label>

      <label>
        Background
        <select v-model="theme">
          <option value="cream">Cream Notebook</option>
          <option value="blue">Blue Travel Notes</option>
          <option value="green">Nature Green</option>
          <option value="pink">Soft Pink</option>
        </select>
      </label>

      <label>
        Accent Color
        <input v-model="accentColor" type="color" />
      </label>

      <p class="hint">Click the star to highlight important items.</p>
    </aside>

    <div
      ref="checklistRef"
      class="checklist-paper"
      :class="[themeClass, `font-${fontStyle}`]"
      :style="{ '--accent': accentColor }"
    >
      <div class="paper-head">
        <div>
          <p class="eyebrow">Beyond 66° Travel Notes</p>
          <h2>{{ checklistTitle }}</h2>
        </div>

        <button class="export-btn" @click="exportChecklist">
          Export PDF
        </button>
      </div>

      <ul>
        <li
          v-for="item in items"
          :key="item.id"
          :class="{ checked: item.checked, highlighted: highlightedItems.includes(item.id) }"
        >
          <label>
            <input
              type="checkbox"
              :checked="item.checked"
              @change="$emit('toggle', item.id)"
            />

            <span>{{ item.name }}</span>
          </label>

          <button
            class="highlight-btn"
            type="button"
            :aria-label="`Highlight ${item.name}`"
            @click="toggleHighlight(item.id)"
          >
            ★
          </button>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

defineProps({
  items: {
    type: Array,
    required: true,
    default: () => [],
  },
})

defineEmits(['toggle'])

const checklistRef = ref(null)
const checklistTitle = ref('Packing Checklist')
const paperSize = ref('a4')
const fontStyle = ref('clean')
const hideCheckedOnExport = ref(false)
const theme = ref('cream')
const accentColor = ref('#7aa7d9')
const highlightedItems = ref([])

const themeClass = computed(() => `theme-${theme.value}`)

function toggleHighlight(id) {
  highlightedItems.value = highlightedItems.value.includes(id)
    ? highlightedItems.value.filter((itemId) => itemId !== id)
    : [...highlightedItems.value, id]
}

function exportChecklist() {
  const clonedChecklist = checklistRef.value?.cloneNode(true)

  if (!clonedChecklist) return

  clonedChecklist.querySelectorAll('.export-btn, .highlight-btn').forEach((el) => {
    el.remove()
  })

  if (hideCheckedOnExport.value) {
    clonedChecklist.querySelectorAll('li.checked').forEach((el) => {
      el.remove()
    })
  }

  const pageSize = paperSize.value === 'letter' ? 'Letter' : 'A4'
  const printWindow = window.open('', '_blank')

  if (!printWindow) return

  printWindow.document.write(`
    <html>
      <head>
        <title>${checklistTitle.value}</title>
        <style>
          @page {
            size: ${pageSize};
            margin: 0;
          }

          * {
            box-sizing: border-box;
          }

          html,
          body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
          }

          body {
            background: #f5f0e8;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }

          .checklist-paper {
            --accent: ${accentColor.value};

            width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 48px 56px;
            border-radius: 0;
            box-shadow: none;
            border: none;
            overflow: hidden;
          }

          .theme-cream {
            background:
              linear-gradient(to right, rgba(239, 86, 86, 0.35) 70px, transparent 71px),
              repeating-linear-gradient(
                to bottom,
                #fff8e9 0,
                #fff8e9 42px,
                rgba(95, 130, 160, 0.22) 43px
              );
          }

          .theme-blue {
            background:
              linear-gradient(to right, rgba(75, 125, 190, 0.22) 70px, transparent 71px),
              repeating-linear-gradient(
                to bottom,
                #eef7ff 0,
                #eef7ff 42px,
                rgba(65, 120, 170, 0.2) 43px
              );
          }

          .theme-green {
            background:
              linear-gradient(to right, rgba(67, 160, 110, 0.22) 70px, transparent 71px),
              repeating-linear-gradient(
                to bottom,
                #f0fff4 0,
                #f0fff4 42px,
                rgba(80, 150, 110, 0.18) 43px
              );
          }

          .theme-pink {
            background:
              linear-gradient(to right, rgba(220, 110, 150, 0.22) 70px, transparent 71px),
              repeating-linear-gradient(
                to bottom,
                #fff1f6 0,
                #fff1f6 42px,
                rgba(190, 100, 140, 0.18) 43px
              );
          }

          .font-clean {
            font-family: Arial, sans-serif;
          }

          .font-handwritten {
            font-family: 'Comic Sans MS', 'Bradley Hand', cursive;
          }

          .font-serif {
            font-family: Georgia, serif;
          }

          .paper-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 16px;
            margin-bottom: 16px;
            margin-left: 25px;
          }

          .eyebrow {
            margin: 0 0 4px;
            text-transform: uppercase;
            letter-spacing: .12em;
            font-size: .72rem;
            color: var(--accent);
            font-weight: 900;
          }

          h2 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 900;
          }

          ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
          }

          li {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 10px;
            padding: 8px 10px 8px 26px;
            border-radius: 14px;
          }

          li.highlighted {
            background: rgba(255, 222, 89, 0.38);
          }

          li.checked span {
            text-decoration: line-through;
            opacity: 0.55;
          }

          li label {
            display: flex;
            gap: 12px;
            align-items: center;
          }

          li input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
          }

          li span {
            font-size: 0.95rem;
            font-weight: 650;
            color: #222;
          }

          .export-btn,
          .highlight-btn {
            display: none !important;
          }
        </style>
      </head>

      <body>${clonedChecklist.outerHTML}</body>
    </html>
  `)

  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}
</script>

<style scoped>
.planner-card {
  background: rgba(var(--v-theme-surface), 0.95);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 24px;
  padding: 22px;
}

.checklist-builder {
  display: grid;
  grid-template-columns: 230px minmax(0, 1fr);
  gap: 24px;
}

.style-panel {
  border-radius: 24px;
  padding: 18px;
  background: rgba(var(--v-theme-background), 0.42);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  display: grid;
  align-content: start;
  gap: 16px;
}

.style-panel h3 {
  margin: 0;
  font-size: 1rem;
}

.style-panel label {
  display: grid;
  gap: 8px;
  font-size: 0.82rem;
  font-weight: 800;
}

.style-panel select,
.style-panel input[type='text'],
.style-panel input[type='color'] {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.18);
  border-radius: 12px;
  padding: 8px 10px;
  background: rgba(var(--v-theme-surface), 0.85);
  color: rgb(var(--v-theme-text));
}

.switch-row {
  display: flex !important;
  grid-template-columns: none !important;
  align-items: center;
  gap: 8px;
}

.switch-row input {
  accent-color: var(--accent);
}

.hint {
  margin: 0;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-text), 0.65);
}

.checklist-paper {
  --accent: #7aa7d9;
  position: relative;
  border-radius: 30px;
  padding: 30px 34px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  box-shadow: 0 18px 38px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.font-clean {
  font-family: Arial, sans-serif;
}

.font-handwritten {
  font-family: 'Comic Sans MS', 'Bradley Hand', cursive;
}

.font-serif {
  font-family: Georgia, serif;
}

.theme-cream {
  background:
    linear-gradient(to right, rgba(239, 86, 86, 0.35) 54px, transparent 55px),
    repeating-linear-gradient(
      to bottom,
      #fff8e9 0,
      #fff8e9 34px,
      rgba(95, 130, 160, 0.22) 35px
    );
}

.theme-blue {
  background:
    linear-gradient(to right, rgba(75, 125, 190, 0.22) 54px, transparent 55px),
    repeating-linear-gradient(
      to bottom,
      #eef7ff 0,
      #eef7ff 34px,
      rgba(65, 120, 170, 0.2) 35px
    );
}

.theme-green {
  background:
    linear-gradient(to right, rgba(67, 160, 110, 0.22) 54px, transparent 55px),
    repeating-linear-gradient(
      to bottom,
      #f0fff4 0,
      #f0fff4 34px,
      rgba(80, 150, 110, 0.18) 35px
    );
}

.theme-pink {
  background:
    linear-gradient(to right, rgba(220, 110, 150, 0.22) 54px, transparent 55px),
    repeating-linear-gradient(
      to bottom,
      #fff1f6 0,
      #fff1f6 34px,
      rgba(190, 100, 140, 0.18) 35px
    );
}

.paper-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding-bottom: 16px;
  margin-bottom: 16px;
  margin-left: 25px;
  border-bottom: 2px solid var(--accent);
}

.eyebrow {
  margin: 0 0 4px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.72rem;
  font-weight: 900;
  color: var(--accent);
}

h2 {
  margin: 0;
  font-size: 1.7rem;
  font-weight: 900;
}

.export-btn {
  border: 1px solid var(--accent);
  background: color-mix(in srgb, var(--accent) 14%, transparent);
  color: var(--accent);
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 800;
  cursor: pointer;
}

ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

li {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 8px 10px 8px 26px;
  border-radius: 14px;
  transition: 0.2s ease;
}

li.highlighted {
  background: rgba(255, 222, 89, 0.38);
}

li.checked span {
  text-decoration: line-through;
  opacity: 0.55;
}

li label {
  display: flex;
  gap: 12px;
  align-items: center;
}

li input {
  width: 18px;
  height: 18px;
  accent-color: var(--accent);
}

li span {
  font-size: 0.95rem;
  font-weight: 650;
  color: rgb(var(--v-theme-text));
}

.highlight-btn {
  border: 0;
  background: transparent;
  color: rgba(var(--v-theme-text), 0.3);
  cursor: pointer;
  font-size: 1rem;
}

li.highlighted .highlight-btn {
  color: #f59e0b;
}

@media (max-width: 900px) {
  .checklist-builder {
    grid-template-columns: 1fr;
  }
}
</style>
