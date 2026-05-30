import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css'
import vuetify from './plugins/vuetify';
import i18n from './src/locales/i18n'
import 'vue-svg-map/style.css'
import { initializeInactivitySessionGuard } from './utils/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)
app.use(i18n)

initializeInactivitySessionGuard()

app.mount('#app')
