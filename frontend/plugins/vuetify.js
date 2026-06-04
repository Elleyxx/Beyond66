import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

export default createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  theme: {
    defaultTheme: localStorage.getItem('theme') || 'light',
    themes: {
      light: {
        dark: false,
        colors: {
          background: "#f8fcff",   // snowy white
          surface: "#ffffff",      // clean snow card
          "on-surface": "#102033",
          header: "#d9ecff",       // soft sky blue
          footer: "#f4fbff",
          primary: "#76c6fc",      // Nordic sky blue
          secondary: "#c7e4f7",    // icy blue
          accent: "#c7eaff",       // snow shadow blue
          text: "#102033",         // deep blue text
          muted: "#6f8499",
          border: "#d6ecfa",
          error: "#c84b5f",
          success: "#2f8f58",
          headerText: "#102033",
          headerTextScrolled: "#102033",
          searchBorder: "#d6ecfa",
          menuText: "#102033",
          countryIceland: "#a7e3ff",
          countryNorway: "#ffbac7",
          countrySweden: "#ffeca8",
          countryFinland: "#b8d1ff",
          countryDenmark: "#ffd6a8",
          subtleText: "#6b7280",
          communityHeader: "#f4fbff"
        },
      },

      dark: {
        dark: true,
        colors: {
          background: "#07111f",   // night sky
          surface: "#0d1b2e",      // dark blue card
          "on-surface": "#f4fbff",
          header: "#10233d",    // deep Nordic blue
          footer: "#f4fbff",
          primary: "#2cf6b3",      // aurora green
          secondary: "#17395c",    // dark blue accent
          accent: "#5fffd0",       // aurora glow
          text: "#f4fbff",
          muted: "#a8bed1",
          border: "#214568",
          error: "#ff8f9f",
          success: "#8de8b1",
          headerText: "#f4fbff",
          headerTextScrolled: "#f4fbff",
          searchBorder: "#214568",
          menuText: "#f4fbff",
          countryIceland: "#77b8d6",
          countryNorway: "#d08a97",
          countrySweden: "#d8c279",
          countryFinland: "#8ea8d4",
          countryDenmark: "#d7a974",
          subtleText: "#a8bed1",
          communityHeader: "#f4fbff"
        },
      },
    },
  },
})
