import axios from 'axios'

const API_KEY = import.meta.env.VITE_OPENWEATHER_API_KEY

export const getTripWeather = async (lat, lon, startDate, endDate, country, season) => {
  const today = new Date()
  const tripStart = new Date(`${startDate}T00:00:00`)
  const daysUntilTrip = Math.ceil((tripStart - today) / (1000 * 60 * 60 * 24))

  if (Number.isFinite(daysUntilTrip) && daysUntilTrip >= 0 && daysUntilTrip <= 14) {
    return await getWeatherForecast(lat, lon, startDate, endDate)
  }

  return getSeasonalWeatherEstimateForTrip(country, season, startDate, endDate)
}

export const getWeatherForecast = async (lat, lon, startDate, endDate) => {
  const response = await axios.get('https://api.open-meteo.com/v1/forecast', {
    params: {
      latitude: lat,
      longitude: lon,
      daily: 'temperature_2m_max,temperature_2m_min,weather_code',
      timezone: 'auto',
      start_date: startDate,
      end_date: endDate,
    },
  })

  const daily = response.data.daily

  return daily.time.map((date, index) => ({
    date,
    temp: Math.round(
      (daily.temperature_2m_max[index] + daily.temperature_2m_min[index]) / 2,
    ),
    condition: mapWeatherCodeToText(daily.weather_code[index]),
    type: 'forecast',
  }))
}

export const getCurrentWeather = async (lat, lon) => {
  if (!API_KEY) {
    const fallback = await axios.get('https://api.open-meteo.com/v1/forecast', {
      params: {
        latitude: lat,
        longitude: lon,
        current: 'temperature_2m,weather_code,is_day',
      },
    })
    const temp = fallback?.data?.current?.temperature_2m
    const weatherCode = Number(fallback?.data?.current?.weather_code)
    const isDay = Number(fallback?.data?.current?.is_day) === 1
    const iconCode = mapOpenMeteoToOpenWeatherIcon(weatherCode, isDay)
    const data = {
      main: {
        temp,
      },
      weather: iconCode
        ? [{ icon: iconCode, bootstrapIcon: mapBootstrapWeatherIcon(iconCode) }]
        : [],
    }

    return data
  }

  const response = await axios.get(
    `https://api.openweathermap.org/data/2.5/weather`,
    {
      params: {
        lat,
        lon,
        units: 'metric',
        appid: API_KEY,
      },
    }
  )

  const data = response.data

  if (data?.weather?.[0]) {
    const iconCode = normalizeIconCodeBySunCycle(data)
    data.weather[0].icon = iconCode
    data.weather[0].bootstrapIcon = mapBootstrapWeatherIcon(iconCode)
  }

  return data
}

export const getAuroraPrediction = async (lat, lon) => {
  try {
    const response = await axios.get(
      'https://services.swpc.noaa.gov/json/planetary_k_index_1m.json'
    )

    const records = response.data || []
    const latest = records[records.length - 1]

    const kp = Number(latest?.kp_index || 0)
    const chance = estimateAuroraChance(lat, kp)

    return {
      kp,
      chance,
      window: '10:00 PM - 2:00 AM',
      condition: getAuroraCondition(chance),
      note: getAuroraNote(lat, chance),
    }
  } catch {
    return {
      kp: 0,
      chance: 0,
      window: 'Night time, under clear dark skies',
      condition: 'Unavailable',
      note: 'Aurora data is currently unavailable.',
    }
  }
}

function getSeasonalWeatherEstimateForTrip(country, season, startDate, endDate) {
  const seasonalData = {
    Norway: {
      Winter: { temp: '-8°C to 4°C', condition: 'Cold, snowy, aurora season' },
      Spring: { temp: '2°C to 12°C', condition: 'Cool, fresh, light rain' },
      Summer: { temp: '12°C to 22°C', condition: 'Mild, long daylight, occasional rain' },
      Autumn: { temp: '3°C to 12°C', condition: 'Cool, rainy, colourful landscapes' },
    },
    Sweden: {
      Winter: { temp: '-10°C to 2°C', condition: 'Cold, snowy, dark evenings' },
      Spring: { temp: '3°C to 13°C', condition: 'Cool, blooming season' },
      Summer: { temp: '15°C to 24°C', condition: 'Mild to warm, long daylight' },
      Autumn: { temp: '4°C to 13°C', condition: 'Cool, windy, autumn colours' },
    },
    Finland: {
      Winter: { temp: '-15°C to -2°C', condition: 'Very cold, snow, aurora season' },
      Spring: { temp: '0°C to 10°C', condition: 'Cold to mild, melting snow' },
      Summer: { temp: '14°C to 23°C', condition: 'Mild, bright, good for nature trips' },
      Autumn: { temp: '2°C to 11°C', condition: 'Cool, rainy, colourful forests' },
    },
    Iceland: {
      Winter: { temp: '-2°C to 4°C', condition: 'Cold, windy, aurora season' },
      Spring: { temp: '1°C to 8°C', condition: 'Cool, windy, changing weather' },
      Summer: { temp: '8°C to 15°C', condition: 'Cool, long daylight, light rain' },
      Autumn: { temp: '2°C to 9°C', condition: 'Cool, windy, rainy' },
    },
    Denmark: {
      Winter: { temp: '0°C to 6°C', condition: 'Cold, cloudy, occasional rain' },
      Spring: { temp: '5°C to 14°C', condition: 'Cool, pleasant, light showers' },
      Summer: { temp: '15°C to 23°C', condition: 'Mild, sunny, comfortable' },
      Autumn: { temp: '6°C to 14°C', condition: 'Cool, windy, rainy' },
    },
  }

  const selected = seasonalData[country]?.[season] || {
    temp: 'Varies',
    condition: 'Seasonal weather estimate unavailable',
  }

  return buildDateRange(startDate, endDate).map((date, index) => ({
    date,
    temp: selected.temp,
    condition: index === 0 ? selected.condition : 'Expected seasonal conditions',
    type: 'seasonal',
  }))
}

function buildDateRange(startDate, endDate) {
  if (!startDate || !endDate) return [startDate || endDate || '']

  const start = new Date(`${startDate}T00:00:00`)
  const end = new Date(`${endDate}T00:00:00`)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return [startDate]

  const dates = []
  const cursor = new Date(start)

  while (cursor <= end && dates.length < 30) {
    dates.push(cursor.toISOString().slice(0, 10))
    cursor.setDate(cursor.getDate() + 1)
  }

  return dates.length ? dates : [startDate]
}

function getSeasonalWeatherEstimate(country, season, startDate, endDate) {
  const seasonalData = {
    Norway: {
      Winter: { temp: '-8°C to 4°C', condition: 'Cold, snowy, aurora season' },
      Spring: { temp: '2°C to 12°C', condition: 'Cool, fresh, light rain' },
      Summer: { temp: '12°C to 22°C', condition: 'Mild, long daylight, occasional rain' },
      Autumn: { temp: '3°C to 12°C', condition: 'Cool, rainy, colourful landscapes' },
    },
    Sweden: {
      Winter: { temp: '-10°C to 2°C', condition: 'Cold, snowy, dark evenings' },
      Spring: { temp: '3°C to 13°C', condition: 'Cool, blooming season' },
      Summer: { temp: '15°C to 24°C', condition: 'Mild to warm, long daylight' },
      Autumn: { temp: '4°C to 13°C', condition: 'Cool, windy, autumn colours' },
    },
    Finland: {
      Winter: { temp: '-15°C to -2°C', condition: 'Very cold, snow, aurora season' },
      Spring: { temp: '0°C to 10°C', condition: 'Cold to mild, melting snow' },
      Summer: { temp: '14°C to 23°C', condition: 'Mild, bright, good for nature trips' },
      Autumn: { temp: '2°C to 11°C', condition: 'Cool, rainy, colourful forests' },
    },
    Iceland: {
      Winter: { temp: '-2°C to 4°C', condition: 'Cold, windy, aurora season' },
      Spring: { temp: '1°C to 8°C', condition: 'Cool, windy, changing weather' },
      Summer: { temp: '8°C to 15°C', condition: 'Cool, long daylight, light rain' },
      Autumn: { temp: '2°C to 9°C', condition: 'Cool, windy, rainy' },
    },
    Denmark: {
      Winter: { temp: '0°C to 6°C', condition: 'Cold, cloudy, occasional rain' },
      Spring: { temp: '5°C to 14°C', condition: 'Cool, pleasant, light showers' },
      Summer: { temp: '15°C to 23°C', condition: 'Mild, sunny, comfortable' },
      Autumn: { temp: '6°C to 14°C', condition: 'Cool, windy, rainy' },
    },
  }

  const selected = seasonalData[country]?.[season] || {
    temp: 'Varies',
    condition: 'Seasonal weather estimate unavailable',
  }

  return [
    {
      date: startDate,
      temp: selected.temp,
      condition: selected.condition,
      type: 'seasonal',
    },
    {
      date: endDate,
      temp: selected.temp,
      condition: 'Expected seasonal conditions, not live forecast',
      type: 'seasonal',
    },
  ]
}

function mapWeatherCodeToText(code) {
  if ([0].includes(code)) return 'Clear'
  if ([1, 2].includes(code)) return 'Partly Cloudy'
  if ([3].includes(code)) return 'Cloudy'
  if ([45, 48].includes(code)) return 'Fog'
  if ([51, 53, 55, 56, 57].includes(code)) return 'Drizzle'
  if ([61, 63, 65, 66, 67, 80, 81, 82].includes(code)) return 'Rain'
  if ([71, 73, 75, 77, 85, 86].includes(code)) return 'Snow'
  if ([95, 96, 99].includes(code)) return 'Thunderstorm'

  return 'Cloudy'
}

function estimateAuroraChance(lat, kp) {
  const absLat = Math.abs(Number(lat))

  if (!Number.isFinite(absLat) || !Number.isFinite(kp)) return 0

  let base = 0

  if (absLat >= 68) base = 60
  else if (absLat >= 64) base = 45
  else if (absLat >= 60) base = 28
  else if (absLat >= 55) base = 12
  else base = 3

  const kpBoost = kp * 8
  return Math.min(95, Math.round(base + kpBoost))
}

function getAuroraCondition(chance) {
  if (chance >= 70) return 'High'
  if (chance >= 40) return 'Moderate'
  if (chance >= 15) return 'Low'
  return 'Very Low'
}

function getAuroraNote(lat, chance) {
  const absLat = Math.abs(Number(lat))

  if (absLat < 55) {
    return 'Aurora viewing is unlikely at this latitude. Northern Norway, Finland, Iceland, or Swedish Lapland usually have better chances.'
  }

  if (chance >= 70) {
    return 'Good aurora potential. Look for dark skies away from city lights.'
  }

  if (chance >= 40) {
    return 'Possible aurora activity. Clear skies and low light pollution will improve visibility.'
  }

  return 'Aurora chance is limited, but visibility may improve during stronger geomagnetic activity.'
}

function mapBootstrapWeatherIcon(iconCode) {
  if (!iconCode) return 'bi-cloud'

  const isNight = iconCode.endsWith('n')
  const weatherType = iconCode.substring(0, 2)

  switch (weatherType) {
    case '01':
      return isNight ? 'bi-moon-stars-fill' : 'bi-sun-fill'
    case '02':
      return isNight ? 'bi-cloud-moon-fill' : 'bi-cloud-sun-fill'
    case '03':
    case '04':
      return 'bi-cloud-fill'
    case '09':
    case '10':
      return 'bi-cloud-rain-fill'
    case '11':
      return 'bi-cloud-lightning-rain-fill'
    case '13':
      return 'bi-snow'
    case '50':
      return 'bi-cloud-fog-fill'
    default:
      return isNight ? 'bi-moon-stars-fill' : 'bi-sun-fill'
  }
}

function normalizeIconCodeBySunCycle(data) {
  const rawIconCode = data?.weather?.[0]?.icon
  if (!rawIconCode || rawIconCode.length < 2) return rawIconCode || ''

  const weatherType = rawIconCode.substring(0, 2)
  const current = Number(data?.dt)
  const sunrise = Number(data?.sys?.sunrise)
  const sunset = Number(data?.sys?.sunset)

  if (Number.isFinite(current) && Number.isFinite(sunrise) && Number.isFinite(sunset)) {
    const isNight = current < sunrise || current >= sunset
    return `${weatherType}${isNight ? 'n' : 'd'}`
  }

  return rawIconCode
}

function mapOpenMeteoToOpenWeatherIcon(code, isDay) {
  const day = isDay ? 'd' : 'n'
  if ([0].includes(code)) return `01${day}` // clear
  if ([1].includes(code)) return `02${day}` // mainly clear
  if ([2].includes(code)) return `03${day}` // partly cloudy
  if ([3].includes(code)) return `04${day}` // overcast
  if ([45, 48].includes(code)) return `50${day}` // fog
  if ([51, 53, 55, 56, 57].includes(code)) return `09${day}` // drizzle
  if ([61, 63, 65, 66, 67, 80, 81, 82].includes(code)) return `10${day}` // rain
  if ([71, 73, 75, 77, 85, 86].includes(code)) return `13${day}` // snow
  if ([95, 96, 99].includes(code)) return `11${day}` // thunderstorm
  return `03${day}`
}
