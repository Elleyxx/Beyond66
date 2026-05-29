import { createI18n } from 'vue-i18n'

import enCommon from '../locales/en/common.json'
import zhCommon from '../locales/zh/common.json'

import enMap from '../locales/en/countries/map.json'
import enIceland from '../locales/en/countries/iceland.json'
import enNorway from '../locales/en/countries/norway.json'
import enSweden from '../locales/en/countries/sweden.json'
import enFinland from '../locales/en/countries/finland.json'
import enDenmark from '../locales/en/countries/denmark.json'

import zhMap from '../locales/zh/countries/map.json'
import zhIceland from '../locales/zh/countries/iceland.json'
import zhNorway from '../locales/zh/countries/norway.json'
import zhSweden from '../locales/zh/countries/sweden.json'
import zhFinland from '../locales/zh/countries/finland.json'
import zhDenmark from '../locales/zh/countries/denmark.json'

function mergeCountries(...modules) {
  return modules.reduce((acc, mod) => {
    let segment = {}
    if (mod && typeof mod === 'object') {
      if (mod.countries && typeof mod.countries === 'object') {
        segment = mod.countries
      } else if (typeof mod.slug === 'string' && mod.slug) {
        segment = { [mod.slug]: mod }
      }
    }
    return { ...acc, ...segment }
  }, {})
}

const messages = {
  en: {
    ...enCommon,
    countries: mergeCountries(enMap, enIceland, enNorway, enSweden, enFinland, enDenmark),
  },
  zh: {
    ...zhCommon,
    countries: mergeCountries(zhMap, zhIceland, zhNorway, zhSweden, zhFinland, zhDenmark),
  },
}

export default createI18n({
  legacy: false,
  locale: localStorage.getItem('locale') || 'en',
  fallbackLocale: 'en',
  messages,
})
