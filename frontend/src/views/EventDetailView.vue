<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import AppButton from '@/components/AppButton.vue'
import AppAlert from '@/components/AppAlert.vue'

interface Event {
  id: number
  title: string
  description: string
  eventDate: string
  location: string
  maxParticipants: number
  organizer: { id: number; firstName: string; lastName: string }
  isPublished: boolean
  createdAt: string
  registrationsCount: number
}

interface Registration {
  id: number
  userId: number
  eventId: number
  registeredAt: string
  status: 'pending' | 'confirmed' | 'cancelled'
}

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const event = ref<Event | null>(null)
const registration = ref<Registration | null>(null)
const loading = ref(true)
const registering = ref(false)
const message = ref<{ type: 'success' | 'error'; text: string } | null>(null)

const id = Number(route.params.id)

const spotsLeft = computed(() => {
  if (!event.value) return 0
  return event.value.maxParticipants - event.value.registrationsCount
})

const fillPercent = computed(() => {
  if (!event.value) return 0
  return Math.min(100, Math.round((event.value.registrationsCount / event.value.maxParticipants) * 100))
})

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

function formatTime(dateStr: string) {
  return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  try {
    const { data } = await api.get<Event>(`/events/${id}`)
    event.value = data

    if (auth.isAuthenticated) {
      const { data: reg } = await api.get<Registration | null>(`/events/${id}/my-registration`)
      registration.value = reg
    }
  } catch {
    router.push('/events')
  } finally {
    loading.value = false
  }
})

async function handleRegister() {
  if (!auth.isAuthenticated) return router.push('/login')
  registering.value = true
  message.value = null
  try {
    const { data } = await api.post<Registration>(`/events/${id}/register`)
    registration.value = data
    if (event.value) event.value.registrationsCount++
    message.value = { type: 'success', text: 'Inscription confirmée ! À bientôt.' }
  } catch (e: any) {
    message.value = { type: 'error', text: e?.response?.data?.message ?? "Une erreur est survenue." }
  } finally {
    registering.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-10">
    <!-- Back -->
    <router-link to="/events" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600 transition-colors mb-6">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      Retour aux événements
    </router-link>

    <!-- Skeleton -->
    <div v-if="loading" class="bg-white rounded-3xl border border-gray-100 shadow-sm animate-pulse">
      <div class="h-3 bg-gray-200 rounded-t-3xl" />
      <div class="p-8 space-y-4">
        <div class="h-7 bg-gray-200 rounded w-2/3" />
        <div class="h-4 bg-gray-100 rounded w-1/3" />
        <div class="h-32 bg-gray-100 rounded mt-6" />
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="event" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="h-2 bg-gradient-to-r from-primary-500 to-indigo-400" />

      <div class="p-8 md:p-10">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
          <!-- Left: info -->
          <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">{{ event.title }}</h1>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="capitalize">{{ formatDate(event.eventDate) }}</span>
              </span>
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ formatTime(event.eventDate) }}
              </span>
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ event.location }}
              </span>
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Organisé par {{ event.organizer.firstName }} {{ event.organizer.lastName }}
              </span>
            </div>

            <!-- Jauge -->
            <div class="mt-6 p-4 bg-gray-50 rounded-2xl">
              <div class="flex justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">{{ event.registrationsCount }} inscrits sur {{ event.maxParticipants }}</span>
                <span :class="spotsLeft <= 5 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                  {{ spotsLeft <= 0 ? 'Complet' : `${spotsLeft} places restantes` }}
                </span>
              </div>
              <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-700"
                  :class="fillPercent >= 90 ? 'bg-red-400' : fillPercent >= 70 ? 'bg-yellow-400' : 'bg-primary-500'"
                  :style="`width: ${fillPercent}%`"
                />
              </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
              <h2 class="font-bold text-gray-900 mb-2">À propos de cet événement</h2>
              <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ event.description }}</p>
            </div>
          </div>

          <!-- Right: action card -->
          <div class="md:w-72 shrink-0">
            <div class="sticky top-24 bg-gray-50 rounded-2xl border border-gray-200 p-6 space-y-4">
              <div class="text-center">
                <p class="text-2xl font-extrabold text-gray-900">Gratuit</p>
                <p class="text-sm text-gray-500 mt-0.5">Inscription ouverte</p>
              </div>

              <AppAlert v-if="message" :type="message.type" :message="message.text" />

              <!-- Already registered -->
              <div v-if="registration && registration.status !== 'cancelled'" class="text-center p-3 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm font-semibold text-green-700">✓ Vous êtes inscrit !</p>
                <p class="text-xs text-green-600 mt-0.5">Votre place est confirmée</p>
              </div>

              <!-- Register button -->
              <template v-else-if="spotsLeft > 0">
                <AppButton
                  v-if="auth.isAuthenticated"
                  @click="handleRegister"
                  :loading="registering"
                  full-width
                  size="lg"
                >
                  S'inscrire à cet événement
                </AppButton>
                <div v-else class="space-y-2">
                  <router-link
                    to="/login"
                    class="flex justify-center items-center w-full px-5 py-3 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-colors text-sm"
                  >
                    Se connecter pour s'inscrire
                  </router-link>
                  <p class="text-center text-xs text-gray-400">
                    Pas encore de compte ?
                    <router-link to="/register" class="text-primary-600 underline">S'inscrire</router-link>
                  </p>
                </div>
              </template>

              <!-- Full -->
              <div v-else class="text-center p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm font-semibold text-red-700">Événement complet</p>
              </div>

              <!-- Organizer edit -->
              <router-link
                v-if="auth.isOrganizer && auth.user?.id === event.organizer.id"
                :to="`/events/${event.id}/edit`"
                class="flex justify-center items-center gap-2 w-full px-4 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors text-sm"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Modifier l'événement
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
