<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'

//  Etat

const activeTab = ref<'requests' | 'events' | 'users'>('requests')

const events      = ref<any[]>([])
const users       = ref<any[]>([])
const requests    = ref<any[]>([])
const pendingCount = ref(0)

const loadingEvents   = ref(false)
const loadingUsers    = ref(false)
const loadingRequests = ref(false)

const error   = ref('')
const success = ref('')

//Chargement

async function loadRequests() {
  loadingRequests.value = true
  try {
    const { data } = await api.get('/admin/organizer-requests')
    requests.value     = data
    pendingCount.value = data.filter((r: any) => r.status === 'pending').length
  } catch { error.value = 'Erreur lors du chargement des demandes.' }
  finally { loadingRequests.value = false }
}

async function loadEvents() {
  loadingEvents.value = true
  try {
    const { data } = await api.get('/admin/events')
    events.value = data
  } catch { error.value = 'Erreur lors du chargement des événements.' }
  finally { loadingEvents.value = false }
}

async function loadUsers() {
  loadingUsers.value = true
  try {
    const { data } = await api.get('/admin/users')
    users.value = data
  } catch { error.value = 'Erreur lors du chargement des utilisateurs.' }
  finally { loadingUsers.value = false }
}

onMounted(() => {
  loadRequests()
  loadEvents()
  loadUsers()
})

// Actions events

async function deleteEvent(id: number) {
  if (!confirm('Supprimer définitivement cet événement ?')) return
  try {
    await api.delete(`/admin/events/${id}`)
    events.value = events.value.filter(e => e.id !== id)
    success.value = 'Événement supprimé.'
    setTimeout(() => success.value = '', 3000)
  } catch { error.value = 'Impossible de supprimer cet événement.' }
}

//Actions users 

async function deleteUser(id: number) {
  if (!confirm('Anonymiser cet utilisateur ? (action RGPD irréversible)')) return
  try {
    await api.delete(`/admin/users/${id}`)
    await loadUsers()
    success.value = 'Utilisateur anonymisé.'
    setTimeout(() => success.value = '', 3000)
  } catch { error.value = 'Impossible d\'anonymiser cet utilisateur.' }
}

//  Actions role requests 

async function approveRequest(id: number) {
  try {
    await api.post(`/admin/organizer-requests/${id}/approve`)
    await loadRequests()
    await loadUsers()
    success.value = 'Demande approuvée — utilisateur promu Organisateur.'
    setTimeout(() => success.value = '', 4000)
  } catch { error.value = 'Impossible d\'approuver cette demande.' }
}

async function rejectRequest(id: number) {
  try {
    await api.post(`/admin/organizer-requests/${id}/reject`)
    await loadRequests()
    success.value = 'Demande rejetée.'
    setTimeout(() => success.value = '', 3000)
  } catch { error.value = 'Impossible de rejeter cette demande.' }
}

//  Filtres 

const searchEvents = ref('')
const searchUsers  = ref('')

const filteredEvents = computed(() =>
  events.value.filter(e =>
    e.title?.toLowerCase().includes(searchEvents.value.toLowerCase()) ||
    e.organizer?.email?.toLowerCase().includes(searchEvents.value.toLowerCase())
  )
)

const filteredUsers = computed(() =>
  users.value.filter(u =>
    u.email?.toLowerCase().includes(searchUsers.value.toLowerCase()) ||
    u.firstName?.toLowerCase().includes(searchUsers.value.toLowerCase()) ||
    u.lastName?.toLowerCase().includes(searchUsers.value.toLowerCase())
  )
)

const pendingRequests  = computed(() => requests.value.filter(r => r.status === 'pending'))
const processedRequests = computed(() => requests.value.filter(r => r.status !== 'pending'))

function formatDate(iso: string) {
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso))
}

function roleLabel(role: string) {
  return role === 'ORGANIZER' ? 'Organisateur' : role === 'ADMIN' ? 'Admin' : 'Participant'
}

function roleBadgeClass(role: string) {
  if (role === 'ADMIN')     return 'bg-red-100 text-red-700'
  if (role === 'ORGANIZER') return 'bg-purple-100 text-purple-700'
  return 'bg-blue-100 text-blue-700'
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-10 h-10 rounded-2xl bg-red-100 flex items-center justify-center">
          <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-extrabold text-gray-900">Administration</h1>
          <p class="text-sm text-gray-500">Vue d'ensemble et modération de la plateforme</p>
        </div>
      </div>
    </div>

    <!-- Alertes -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex justify-between">
      {{ error }}
      <button @click="error = ''" class="font-bold">✕</button>
    </div>
    <div v-if="success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
      {{ success }}
    </div>

    <!-- Stats rapides -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Événements</p>
        <p class="text-3xl font-extrabold text-gray-900">{{ events.length }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Utilisateurs</p>
        <p class="text-3xl font-extrabold text-gray-900">{{ users.length }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Demandes en attente</p>
        <p class="text-3xl font-extrabold text-gray-900">{{ pendingCount }}</p>
        <span v-if="pendingCount > 0" class="absolute top-4 right-4 w-3 h-3 bg-red-500 rounded-full animate-pulse" />
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 rounded-xl p-1 w-fit">
      <button
        v-for="tab in [
          { key: 'requests', label: 'Demandes de rôle' },
          { key: 'events',   label: 'Événements' },
          { key: 'users',    label: 'Utilisateurs' },
        ]"
        :key="tab.key"
        @click="activeTab = tab.key as any"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-semibold transition-all relative',
          activeTab === tab.key ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700',
        ]"
      >
        {{ tab.label }}
        <span
          v-if="tab.key === 'requests' && pendingCount > 0"
          class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
        >{{ pendingCount }}</span>
      </button>
    </div>

    <!-- ─── Tab: Demandes de rôle ──────────────────────────────────────────── -->
    <div v-if="activeTab === 'requests'">

      <!-- En attente -->
      <div class="mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-3">
          En attente
          <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">{{ pendingRequests.length }}</span>
        </h2>

        <div v-if="loadingRequests" class="space-y-3">
          <div v-for="i in 2" :key="i" class="h-20 bg-gray-100 rounded-2xl animate-pulse" />
        </div>

        <div v-else-if="pendingRequests.length === 0" class="text-center py-10 text-gray-400 text-sm bg-white rounded-2xl border border-gray-100">
          Aucune demande en attente.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="req in pendingRequests"
            :key="req.id"
            class="bg-white rounded-2xl border border-amber-200 shadow-sm p-5"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                    {{ req.user?.firstName?.[0] }}{{ req.user?.lastName?.[0] }}
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-900">{{ req.user?.firstName }} {{ req.user?.lastName }}</p>
                    <p class="text-xs text-gray-500">{{ req.user?.email }}</p>
                  </div>
                  <span class="text-xs text-gray-400">— {{ formatDate(req.createdAt) }}</span>
                </div>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-xl px-4 py-2 mt-2">
                  "{{ req.message }}"
                </p>
              </div>
              <div class="flex flex-col gap-2 shrink-0">
                <button
                  @click="approveRequest(req.id)"
                  class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors"
                >
                  Approuver
                </button>
                <button
                  @click="rejectRequest(req.id)"
                  class="px-4 py-2 bg-white border border-red-300 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-50 transition-colors"
                >
                  Rejeter
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Traitées -->
      <div v-if="processedRequests.length > 0">
        <h2 class="text-base font-bold text-gray-800 mb-3">Traitées</h2>
        <div class="space-y-2">
          <div
            v-for="req in processedRequests"
            :key="req.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4"
          >
            <span :class="[
              'shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold',
              req.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700',
            ]">
              {{ req.status === 'approved' ? 'Approuvée' : 'Rejetée' }}
            </span>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-800">{{ req.user?.firstName }} {{ req.user?.lastName }}</p>
              <p class="text-xs text-gray-500">{{ req.user?.email }}</p>
            </div>
            <p class="text-xs text-gray-400">{{ req.processedAt ? formatDate(req.processedAt) : '' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Tab: Événements ────────────────────────────────────────────────── -->
    <div v-if="activeTab === 'events'">
      <div class="mb-4">
        <input
          v-model="searchEvents"
          type="text"
          placeholder="Rechercher par titre ou organisateur..."
          class="w-full max-w-sm px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
      </div>

      <div v-if="loadingEvents" class="space-y-3">
        <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 rounded-2xl animate-pulse" />
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Titre</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Organisateur</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Date</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Inscrits</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Statut</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="filteredEvents.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400">Aucun événement.</td>
            </tr>
            <tr v-for="event in filteredEvents" :key="event.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 font-medium text-gray-900">{{ event.title }}</td>
              <td class="px-5 py-3 text-gray-600">
                {{ event.organizer?.firstName }} {{ event.organizer?.lastName }}
                <span class="block text-xs text-gray-400">{{ event.organizer?.email }}</span>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ event.eventDate ? formatDate(event.eventDate) : '—' }}</td>
              <td class="px-5 py-3 text-gray-600">{{ event.registrationsCount }} / {{ event.maxParticipants }}</td>
              <td class="px-5 py-3">
                <span :class="event.isPublished ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                  class="px-2 py-0.5 rounded-full text-xs font-semibold">
                  {{ event.isPublished ? 'Publié' : 'Brouillon' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <button
                  @click="deleteEvent(event.id)"
                  class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                >
                  Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─── Tab: Utilisateurs ─────────────────────────────────────────────── -->
    <div v-if="activeTab === 'users'">
      <div class="mb-4">
        <input
          v-model="searchUsers"
          type="text"
          placeholder="Rechercher par nom ou email..."
          class="w-full max-w-sm px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
      </div>

      <div v-if="loadingUsers" class="space-y-3">
        <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 rounded-2xl animate-pulse" />
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Utilisateur</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Rôle</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Inscriptions</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Événements créés</th>
              <th class="text-left px-5 py-3 font-semibold text-gray-600">Inscrit le</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400">Aucun utilisateur.</td>
            </tr>
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900">
                  {{ user.isAnonymized ? 'Utilisateur supprimé' : `${user.firstName} ${user.lastName}` }}
                </p>
                <p class="text-xs text-gray-400">{{ user.email }}</p>
              </td>
              <td class="px-5 py-3">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', roleBadgeClass(user.role)]">
                  {{ roleLabel(user.role) }}
                </span>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ user.registrationsCount }}</td>
              <td class="px-5 py-3 text-gray-600">{{ user.eventsCount }}</td>
              <td class="px-5 py-3 text-gray-500 text-xs">{{ formatDate(user.createdAt) }}</td>
              <td class="px-5 py-3 text-right">
                <button
                  v-if="!user.isAnonymized"
                  @click="deleteUser(user.id)"
                  class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                >
                  Anonymiser
                </button>
                <span v-else class="text-xs text-gray-400 italic">Anonymisé</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>
