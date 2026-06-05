function normalizeSearchText(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
}

function entryMatches(entry, query) {
  const haystack = normalizeSearchText(
    [
      entry.title,
      entry.description,
      entry.category,
      ...(entry.keywords || []),
    ].join(' '),
  )

  return normalizeSearchText(query)
    .split(/\s+/)
    .filter(Boolean)
    .every((term) => haystack.includes(term))
}

export function buildSiteSearchEntries(t) {
  return [
    {
      title: t('nav.home'),
      description: t('search.descriptions.home'),
      category: t('search.categories.page'),
      path: '/home',
      keywords: ['home', 'journey', 'about', 'beyond 66', 'homepage', '首页', '旅程'],
    },
    {
      title: t('nav.explore'),
      description: t('search.descriptions.explore'),
      category: t('search.categories.page'),
      path: '/explore',
      keywords: ['explore', 'map', 'countries', 'destinations', '探索', '国家', '目的地'],
    },
    {
      title: t('nav.planner'),
      description: t('search.descriptions.planner'),
      category: t('search.categories.tool'),
      path: '/trip-planner',
      keywords: ['planner', 'itinerary', 'budget', 'weather', 'checklist', 'ai', '规划', '行程', '预算'],
    },
    {
      title: t('nav.community'),
      description: t('search.descriptions.community'),
      category: t('search.categories.page'),
      path: '/community',
      keywords: ['community', 'posts', 'stories', 'saved posts', '社区', '帖子', '故事'],
    },
    {
      title: t('countryNames.norway'),
      description: t('home.hero.countries.norway.subtitle'),
      category: t('search.categories.country'),
      path: '/country/norway',
      keywords: ['norway', 'fjords', 'geirangerfjord', 'lofoten', 'tromso', 'bergen', 'preikestolen', '挪威', '峡湾'],
    },
    {
      title: t('countryNames.sweden'),
      description: t('home.hero.countries.sweden.subtitle'),
      category: t('search.categories.country'),
      path: '/country/sweden',
      keywords: ['sweden', 'stockholm', 'abisko', 'gotland', 'gothenburg', 'kiruna', '瑞典'],
    },
    {
      title: t('countryNames.finland'),
      description: t('home.hero.countries.finland.subtitle'),
      category: t('search.categories.country'),
      path: '/country/finland',
      keywords: ['finland', 'rovaniemi', 'helsinki', 'saimaa', 'turku', 'porvoo', 'aurora', '芬兰', '极光'],
    },
    {
      title: t('countryNames.iceland'),
      description: t('home.hero.countries.iceland.subtitle'),
      category: t('search.categories.country'),
      path: '/country/iceland',
      keywords: ['iceland', 'jokulsarlon', 'vik', 'blue lagoon', 'golden circle', 'reykjavik', 'glacier', 'volcano', '冰岛', '冰川', '火山'],
    },
    {
      title: t('countryNames.denmark'),
      description: t('home.hero.countries.denmark.subtitle'),
      category: t('search.categories.country'),
      path: '/country/denmark',
      keywords: ['denmark', 'copenhagen', 'nyhavn', 'mons klint', 'aarhus', 'odense', 'castles', '丹麦', '城堡'],
    },
    {
      title: t('search.destinations.fjords'),
      description: t('search.descriptions.fjords'),
      category: t('search.categories.destination'),
      path: '/country/norway',
      keywords: ['fjord', 'fjords', 'geirangerfjord', 'naeroyfjord', 'norway', '峡湾', '挪威'],
    },
    {
      title: t('search.destinations.aurora'),
      description: t('search.descriptions.aurora'),
      category: t('search.categories.experience'),
      path: '/country/finland',
      keywords: ['aurora', 'northern lights', 'lapland', 'tromso', 'abisko', 'finland', 'norway', 'sweden', '极光', '拉普兰'],
    },
    {
      title: t('search.destinations.winter'),
      description: t('search.descriptions.winter'),
      category: t('search.categories.experience'),
      path: '/trip-planner',
      keywords: ['winter', 'snow', 'arctic', 'packing', 'weather', '冬季', '雪', '北极'],
    },
  ]
}

export function searchSite(query, t, options = {}) {
  const limit = options.limit || 20
  const entries = buildSiteSearchEntries(t)
  const trimmedQuery = String(query || '').trim()

  if (!trimmedQuery) return entries.slice(0, limit)

  return entries.filter((entry) => entryMatches(entry, trimmedQuery)).slice(0, limit)
}
