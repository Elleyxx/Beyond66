import axios from 'axios'

const API_KEY = import.meta.env.VITE_OPENWEATHER_API_KEY

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
