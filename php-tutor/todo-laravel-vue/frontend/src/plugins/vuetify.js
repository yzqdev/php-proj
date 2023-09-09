// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

const myCustomLightTheme = {
  dark: false,
  colors: {
    // background: "#011C27",
    background: "#FBFEFB",
    // surface: "#0E1428",
    // surface: "#FBFEFB",
    primary: "#03254E",
    secondary: "#D95D39",
    error: "#f44336",
    info: "#2196F3",
    success: "#4caf50",
    warning: "#fb8c00",
  },
}

// Vuetify
import { createVuetify } from 'vuetify'

export default createVuetify({
  theme: {
    defaultTheme: 'myCustomLightTheme',
    themes: {
      myCustomLightTheme,
    },
  },
})
