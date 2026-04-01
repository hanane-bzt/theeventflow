import { createApp } from 'vue'
import App from './App.vue'
import './style.css'

import { createPinia } from 'pinia'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Restore session from stored JWT before mounting
const auth = useAuthStore()
auth.fetchMe().finally(() => {
  app.mount('#app')
})
