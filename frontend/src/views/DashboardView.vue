<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
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
  event?: {
    id: number
    title: string
    eventDate: string
    location: string
    maxParticipants: number
    isPublished: boolean
  }
}

const auth = useAuthStore()

const registrations = ref<Registration[]>([])
const myEvents = ref<Event[]>([])

const loading = ref(true)
const message = ref<{ type: 'success' | 'error'; text: string } | null>(null)
const cancellingId = ref<number | null>(null)
const deletingId = ref<number | null>(null)

onMounted(async () => {
  try {
    if (auth.isOrganizer) {
      const { data } = await api.get<Event[]>('/me/events')
      myEvents.value = data
    } else {
      const { data } = await api.get<Registration[]>('/me/registrations')
      registrations.value = data
    }
  } finally {
    loading.value = false
  }
})

async function cancelRegistration(reg: Registration) {
  cancellingId.value = reg.id
  message.value = null
  try {
    await api.delete(`/registrations/${reg.id}`)
    reg.status = 'cancelled'
    message.value = { type: 'success', text: 'Inscription annulée.' }
  } catch (e: any) {
    message.value = { type: 'error', text: e?.response?.data?.message ?? 'Erreur.' }
  } finally {
    cancellingId.value = null
  }
}

async function deleteEvent(event: Event) {
  deletingId.value = event.id
  message.value = null
  try {
    await api.delete(`/events/${event.id}`)
    myEvents.value = myEvents.value.filter(e => e.id !== event.id)
    message.value = { type: 'success', text: 'Événement supprimé.' }
  } catch (e: any) {
    message.value = { type: 'error', text: e?.response?.data?.message ?? 'Erreur.' }
  } finally {
    deletingId.value = null
  }
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function statusLabel(s: string) {
  return { confirmed: 'Confirmée', pending: 'En attente', cancelled: 'Annulée' }[s] ?? s
}
function statusClass(s: string) {
  return {
    confirmed: 'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    cancelled: 'bg-gray-100 text-gray-500',
  }[s] ?? 'bg-gray-100 text-gray-500'
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">
          Bonjour, {{ auth.user?.firstName }}
        </h1>
        <p class="text-gray-500 mt-1">
          <span v-if="auth.isOrganizer">Gérez vos événements et suivez les inscriptions.</span>
          <span v-else>Retrouvez vos inscriptions aux événements.</span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <router-link
          v-if="auth.isOrganizer"
          to="/events/create"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-colors shadow-sm text-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Créer un événement
        </router-link>
        <router-link
          to="/profile"
          class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors text-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Mon profil
        </router-link>
      </div>
    </div>

    <!-- Alert -->
    <AppAlert v-if="message" :type="message.type" :message="message.text" class="mb-6" />

    <!-- Stat cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <template v-if="auth.isOrganizer">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Événements</p>
          <p class="text-3xl font-extrabold text-gray-900">{{ myEvents.length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Inscrits total</p>
          <p class="text-3xl font-extrabold text-primary-600">
            {{ myEvents.reduce((s, e) => s + e.registrationsCount, 0) }}
          </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Publiés</p>
          <p class="text-3xl font-extrabold text-green-600">{{ myEvents.filter(e => e.isPublished).length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Brouillons</p>
          <p class="text-3xl font-extrabold text-gray-400">{{ myEvents.filter(e => !e.isPublished).length }}</p>
        </div>
      </template>
      <template v-else>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Inscriptions</p>
          <p class="text-3xl font-extrabold text-gray-900">{{ registrations.length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Confirmées</p>
          <p class="text-3xl font-extrabold text-green-600">{{ registrations.filter(r => r.status === 'confirmed').length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Annulées</p>
          <p class="text-3xl font-extrabold text-gray-400">{{ registrations.filter(r => r.status === 'cancelled').length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Rôle</p>
          <p class="text-lg font-extrabold text-primary-600">Participant</p>
        </div>
      </template>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 h-20 animate-pulse" />
    </div>

    <!-- ─── ORGANIZER: My events ─── -->
    <template v-else-if="auth.isOrganizer">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Mes événements</h2>

      <div v-if="myEvents.length === 0" class="text-center py-16 bg-white rounded-2xl border border-gray-100">
        <svg class="w-14 h-14 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="font-semibold text-gray-500">Vous n'avez pas encore créé d'événement</p>
        <router-link to="/events/create" class="inline-block mt-4 text-sm font-bold text-primary-600 hover:underline">
          Créer mon premier événement →
        </router-link>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="event in myEvents"
          :key="event.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center gap-4"
        >
          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="font-bold text-gray-900 truncate">{{ event.title }}</h3>
              <span
                :class="['text-xs px-2 py-0.5 rounded-full font-semibold shrink-0', event.isPublished ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']"
              >
                {{ event.isPublished ? 'Publié' : 'Brouillon' }}
              </span>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ formatDate(event.eventDate) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                {{ event.location }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                {{ event.registrationsCount }} / {{ event.maxParticipants }} inscrits
              </span>
            </div>
            <!-- Fill bar -->
            <div class="mt-2 h-1 bg-gray-100 rounded-full w-48">
              <div
                class="h-full rounded-full bg-primary-400"
                :style="`width: ${Math.min(100, (event.registrationsCount / event.maxParticipants) * 100)}%`"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 shrink-0">
            <router-link
              :to="`/events/${event.id}`"
              class="px-3 py-2 text-xs font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Voir
            </router-link>
            <router-link
              :to="`/events/${event.id}/edit`"
              class="px-3 py-2 text-xs font-semibold text-primary-600 border border-primary-200 rounded-lg hover:bg-primary-50 transition-colors"
            >
              Modifier
            </router-link>
            <AppButton
              @click="deleteEvent(event)"
              :loading="deletingId === event.id"
              variant="danger"
              size="sm"
            >
              Supprimer
            </AppButton>
          </div>
        </div>
      </div>
    </template>

    <!-- ─── USER: My registrations ─── -->
    <template v-else>
      <h2 class="text-xl font-bold text-gray-900 mb-4">Mes inscriptions</h2>

      <div v-if="registrations.length === 0" class="text-center py-16 bg-white rounded-2xl border border-gray-100">
        <svg class="w-14 h-14 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>
        <p class="font-semibold text-gray-500">Vous n'êtes inscrit à aucun événement</p>
        <router-link to="/events" class="inline-block mt-4 text-sm font-bold text-primary-600 hover:underline">
          Découvrir les événements →
        </router-link>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="reg in registrations"
          :key="reg.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center gap-4"
          :class="reg.status === 'cancelled' ? 'opacity-60' : ''"
        >
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="font-bold text-gray-900 truncate">{{ reg.event?.title ?? 'Événement #' + reg.eventId }}</h3>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-semibold shrink-0', statusClass(reg.status)]">
                {{ statusLabel(reg.status) }}
              </span>
            </div>
            <div v-if="reg.event" class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ formatDate(reg.event.eventDate) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                {{ reg.event.location }}
              </span>
              <span>Inscrit le {{ formatDate(reg.registeredAt) }}</span>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <router-link
              :to="`/events/${reg.eventId}`"
              class="px-3 py-2 text-xs font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Voir
            </router-link>
            <AppButton
              v-if="reg.status !== 'cancelled'"
              @click="cancelRegistration(reg)"
              :loading="cancellingId === reg.id"
              variant="danger"
              size="sm"
            >
              Annuler
            </AppButton>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
