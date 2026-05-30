import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import './style.css'

import { createPinia } from 'pinia'

import VueApexCharts from 'vue3-apexcharts'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.component('VueApexCharts', VueApexCharts)

app.mount('#app')