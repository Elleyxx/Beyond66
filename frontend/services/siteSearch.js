import enDenmark from '@/src/locales/en/countries/denmark.json'
import enFinland from '@/src/locales/en/countries/finland.json'
import enIceland from '@/src/locales/en/countries/iceland.json'
import enNorway from '@/src/locales/en/countries/norway.json'
import enSweden from '@/src/locales/en/countries/sweden.json'
import enCommon from '@/src/locales/en/common.json'
import zhDenmark from '@/src/locales/zh/countries/denmark.json'
import zhFinland from '@/src/locales/zh/countries/finland.json'
import zhIceland from '@/src/locales/zh/countries/iceland.json'
import zhNorway from '@/src/locales/zh/countries/norway.json'
import zhSweden from '@/src/locales/zh/countries/sweden.json'
import zhCommon from '@/src/locales/zh/common.json'

const countryModules = {
  en: [enNorway, enSweden, enFinland, enIceland, enDenmark],
  zh: [zhNorway, zhSweden, zhFinland, zhIceland, zhDenmark],
}

const commonModules = {
  en: enCommon,
  zh: zhCommon,
}

function normalizeSearchText(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
}

function getSearchTokens(value) {
  return normalizeSearchText(value)
    .split(/[^a-z0-9\u4e00-\u9fff]+/i)
    .filter(Boolean)
}

function getLocaleKey(locale) {
  return String(locale || '').startsWith('zh') ? 'zh' : 'en'
}

function compactText(...parts) {
  return parts
    .flat()
    .filter(Boolean)
    .map((part) => String(part).trim())
    .filter(Boolean)
    .join(' ')
}

function collectStringValues(value, bucket = []) {
  if (!value) return bucket
  if (typeof value === 'string' || typeof value === 'number') {
    bucket.push(String(value))
    return bucket
  }
  if (Array.isArray(value)) {
    value.forEach((item) => collectStringValues(item, bucket))
    return bucket
  }
  if (typeof value === 'object') {
    Object.entries(value).forEach(([key, item]) => {
      if (key !== 'image' && key !== 'images' && key !== 'heroImage') {
        bucket.push(key)
        collectStringValues(item, bucket)
      }
    })
  }
  return bucket
}

function collectLeafStrings(value, path = [], bucket = []) {
  if (!value) return bucket
  if (typeof value === 'string' || typeof value === 'number') {
    bucket.push({ path, value: String(value) })
    return bucket
  }
  if (Array.isArray(value)) {
    value.forEach((item, index) => collectLeafStrings(item, [...path, String(index)], bucket))
    return bucket
  }
  if (typeof value === 'object') {
    Object.entries(value).forEach(([key, item]) => collectLeafStrings(item, [...path, key], bucket))
  }
  return bucket
}

function routeForCommonPath(path) {
  const [section, subsection] = path
  if (section === 'landing') return '/'
  if (section === 'home' || section === 'brand') return '/home'
  if (section === 'community') return '/community'
  if (section === 'planner') return '/trip-planner'
  if (section === 'profile') return '/profile'
  if (section === 'auth' && subsection === 'register') return '/register'
  if (section === 'auth') return '/login'
  if (section === 'countryNames' && subsection) return `/country/${subsection}`
  if (section === 'footer' && subsection === 'community') return '/community'
  if (section === 'footer' && subsection === 'planner') return '/trip-planner'
  if (section === 'footer' && subsection === 'explore') return '/explore'
  if (section === 'nav' && subsection === 'explore') return '/explore'
  if (section === 'nav' && subsection === 'planner') return '/trip-planner'
  if (section === 'nav' && subsection === 'community') return '/community'
  if (section === 'nav' && subsection === 'home') return '/home'
  return '/home'
}

function describeCommonPath(path, t) {
  const [section, subsection] = path
  if (section === 'countryNames' && subsection) {
    return t('search.descriptions.explore')
  }
  if (section === 'home') return t('search.descriptions.home')
  if (section === 'landing') return t('landing.eyebrow')
  if (section === 'community') return t('search.descriptions.community')
  if (section === 'planner') return t('search.descriptions.planner')
  if (section === 'auth') return subsection === 'register' ? t('auth.register.subtitle') : t('auth.login.subtitle')
  if (section === 'footer') return t('footer.madeFor')
  if (section === 'nav') return t('brand.name')
  if (section === 'brand') return t('brand.tagline')
  return t('search.categories.page')
}

function isPageContentPath(path) {
  const [section, group, item] = path

  if (section === 'brand') return false

  if (section === 'landing' || section === 'auth') return false

  if (section === 'home') {
    if (group === 'hero' && ['subtitle'].includes(item)) return true
    if (group === 'hero' && item === 'countries' && ['name', 'subtitle'].includes(path[4])) return true
    if (group === 'about' && ['title', 'paragraphOne', 'paragraphTwo'].includes(item)) return true
    if (group === 'info' && ['eyebrow', 'title', 'description'].includes(item)) return true
    if (group === 'tours' && item === 'title') return true
    if (group === 'trending' && item === 'title') return true
    return false
  }

  if (section === 'community') {
    return group === 'hero' && ['eyebrow', 'title', 'description'].includes(item)
  }

  if (section === 'planner') {
    if (group === 'title') return true
    if (group === 'timeline' && item === 'intro') return true
    if (group === 'checklist' && ['eyebrow', 'hint'].includes(item)) return true
    if (group === 'detail' && item === 'selectTripHint') return true
    return false
  }

  return false
}

function getCommonContentEntries(t, locale) {
  const common = commonModules[getLocaleKey(locale)]
  const seen = new Set()

  return collectLeafStrings(common)
    .filter(({ path, value }) => isPageContentPath(path) && value.trim().length > 2)
    .map(({ path, value }) => {
      const keyPath = path.join('.')
      const entryKey = `${keyPath}:${value}`
      if (seen.has(entryKey)) return null
      seen.add(entryKey)

      return createEntry({
        title: value,
        description: describeCommonPath(path, t),
        category: t('search.categories.page'),
        path: routeForCommonPath(path),
        keywords: [keyPath, path],
        priority: path[0] === 'nav' ? 8 : 9,
      })
    })
    .filter(Boolean)
}

function createEntry({ title, description, category, path, keywords = [], priority = 1 }) {
  const searchText = compactText(title, description, category, keywords)
  return {
    title,
    description,
    category,
    path,
    keywords,
    priority,
    searchText,
  }
}

function scoreEntry(entry, query) {
  const terms = normalizeSearchText(query).split(/\s+/).filter(Boolean)
  if (!terms.length) return entry.priority

  const title = normalizeSearchText(entry.title)
  const description = normalizeSearchText(entry.description)
  const category = normalizeSearchText(entry.category)
  const haystack = normalizeSearchText(entry.searchText)
  const tokens = getSearchTokens(entry.searchText)

  let score = entry.priority
  const matchesEveryTerm = terms.every((term) => {
    return tokens.some((token) => token.startsWith(term)) || haystack.includes(term)
  })
  if (!matchesEveryTerm) return 0

  terms.forEach((term) => {
    const titleTokens = getSearchTokens(entry.title)
    const startsWithTerm = tokens.some((token) => token.startsWith(term))
    const titleStartsWithTerm = titleTokens.some((token) => token.startsWith(term))

    if (title === term) score += 80
    else if (title.startsWith(term) || titleStartsWithTerm) score += 52
    else if (title.includes(term)) score += 30

    if (category.includes(term)) score += 12
    if (startsWithTerm) score += 14
    if (description.includes(term)) score += 8
    if (haystack.includes(term)) score += 4
  })

  return score
}

function getCountryEntries(t, locale) {
  const modules = countryModules[getLocaleKey(locale)]
  const entries = []

  modules.forEach((country) => {
    const countryPath = `/country/${country.slug}`
    const countryName = country.country || t(`countryNames.${country.slug}`)

    entries.push(
      createEntry({
        title: countryName,
        description: compactText(country.title, country.description),
        category: t('search.categories.country'),
        path: countryPath,
        keywords: collectStringValues(country.quickFacts),
        priority: 24,
      }),
    )

    entries.push(
      createEntry({
        title: country.title,
        description: country.description,
        category: t('countryPage.tabs.overview'),
        path: `${countryPath}#country-overview`,
        keywords: [countryName, collectStringValues(country.quickFacts)],
        priority: 18,
      }),
    )

    ;(country.bestTime || []).forEach((season) => {
      entries.push(
        createEntry({
          title: compactText(countryName, season.season),
          description: compactText(season.months, season.description),
          category: t('countryPage.bestTime'),
          path: `${countryPath}#country-overview`,
          keywords: [countryName, country.title, season.months],
          priority: 13,
        }),
      )
    })

    ;(country.highlights || []).forEach((highlight) => {
      entries.push(
        createEntry({
          title: highlight.title,
          description: compactText(highlight.subtitle, highlight.description),
          category: t('countryPage.tabs.highlights'),
          path: `${countryPath}#country-highlights`,
          keywords: [countryName, country.title],
          priority: 20,
        }),
      )
    })

    ;(country.destinations || []).forEach((destination) => {
      entries.push(
        createEntry({
          title: destination.name,
          description: destination.description,
          category: t('countryPage.tabs.destination'),
          path: `${countryPath}#country-destination`,
          keywords: [countryName, country.title],
          priority: 22,
        }),
      )
    })

    ;(country.experiences || []).forEach((experience) => {
      entries.push(
        createEntry({
          title: experience.title,
          description: experience.description || compactText(countryName, country.title),
          category: t('countryPage.tabs.experience'),
          path: `${countryPath}#country-experiences`,
          keywords: [countryName, country.description],
          priority: 19,
        }),
      )
    })

    ;(country.travelTips || []).forEach((tip) => {
      entries.push(
        createEntry({
          title: tip.title || t('countryPage.tipFallback', { number: entries.length + 1 }),
          description: tip.description || tip.text,
          category: t('countryPage.tabs.travelTips'),
          path: `${countryPath}#country-travel-tips`,
          keywords: [countryName, collectStringValues(tip)],
          priority: 12,
        }),
      )
    })
  })

  return entries
}

function getCommunityStoredEntries(t) {
  if (typeof localStorage === 'undefined') return []

  const entries = []
  const seen = new Set()
  const likelyKeys = ['community', 'post', 'posts', 'trip']

  Object.keys(localStorage)
    .filter((key) => likelyKeys.some((token) => key.toLowerCase().includes(token)))
    .forEach((key) => {
      try {
        const value = JSON.parse(localStorage.getItem(key))
        const posts = Array.isArray(value) ? value : Array.isArray(value?.posts) ? value.posts : []

        posts.forEach((post) => {
          const title = post?.title || post?.tripTitle
          if (!title) return

          const id = post.id || post.post_id || post.slug || title
          const entryKey = `community-${id}`
          if (seen.has(entryKey)) return
          seen.add(entryKey)

          entries.push(
            createEntry({
              title,
              description: post.description || post.summary || t('community.card.fallbackDescription'),
              category: t('nav.community'),
              path: post.id ? `/community/${post.id}` : '/community',
              keywords: collectStringValues(post),
              priority: 26,
            }),
          )
        })
      } catch {
        // Ignore unrelated localStorage values.
      }
    })

  return entries
}

export function buildSiteSearchEntries(t, options = {}) {
  return [
    createEntry({
      title: t('nav.home'),
      description: t('search.descriptions.home'),
      category: t('search.categories.page'),
      path: '/home',
      keywords: [t('home.about.title'), t('home.info.title'), t('home.trending.title')],
      priority: 10,
    }),
    createEntry({
      title: t('nav.explore'),
      description: t('search.descriptions.explore'),
      category: t('search.categories.page'),
      path: '/explore',
      keywords: [t('countryPage.tabs.destination'), t('countryPage.tabs.experience')],
      priority: 11,
    }),
    createEntry({
      title: t('nav.planner'),
      description: t('search.descriptions.planner'),
      category: t('search.categories.tool'),
      path: '/trip-planner',
      keywords: [
        t('planner.controls.budget'),
        t('planner.weather.weather'),
        t('planner.weather.aurora'),
        t('planner.checklist.defaultTitle'),
        t('planner.timeline.title'),
      ],
      priority: 11,
    }),
    createEntry({
      title: t('nav.community'),
      description: t('search.descriptions.community'),
      category: t('search.categories.page'),
      path: '/community',
      keywords: [
        t('community.hero.title'),
        t('community.trending.title'),
        t('community.latest.title'),
        t('community.sidebar.tags'),
      ],
      priority: 11,
    }),
    ...getCountryEntries(t, options.locale),
    ...getCommonContentEntries(t, options.locale),
    ...getCommunityStoredEntries(t),
  ]
}

export function searchSite(query, t, options = {}) {
  const limit = options.limit || 20
  const entries = buildSiteSearchEntries(t, options)
  const trimmedQuery = String(query || '').trim()

  if (!trimmedQuery) return entries.slice(0, limit)

  return entries
    .map((entry) => ({ entry, score: scoreEntry(entry, trimmedQuery) }))
    .filter((result) => result.score > 0)
    .sort((a, b) => b.score - a.score || b.entry.priority - a.entry.priority)
    .slice(0, limit)
    .map((result) => result.entry)
}
