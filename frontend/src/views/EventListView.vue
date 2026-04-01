<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import EventCard from '@/components/EventCard.vue'

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

const events = ref<Event[]>([])
const loading = ref(true)
const search = ref('')

onMounted(async () => {
  try {
    const { data } = await api.get<Event[]>('/events')
    events.value = data
  } finally {
    loading.value = false
  }
})

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return events.value
  return events.value.filter(
    e =>
      e.title.toLowerCase().includes(q) ||
      e.location.toLowerCase().includes(q) ||
      e.description.toLowerCase().includes(q),
  )
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-900">Tous les événements</h1>
      <p class="text-gray-500 mt-1">Découvrez et inscrivez-vous aux prochains événements</p>
    </div>

    <!-- Search -->
    <div class="relative mb-8">
      <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="Rechercher un événement, une ville..."
        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 h-60 animate-pulse">
        <div class="h-2 bg-gray-200 rounded-t-2xl" />
        <div class="p-5 space-y-3">
          <div class="h-4 bg-gray-200 rounded w-3/4" />
          <div class="h-3 bg-gray-100 rounded w-1/2" />
          <div class="h-3 bg-gray-100 rounded w-2/3" />
        </div>
      </div>
    </div>

    <!-- Events grid -->
    <template v-else>
      <div v-if="filtered.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <EventCard v-for="event in filtered" :key="event.id" :event="event" />
      </div>
      <div v-else class="text-center py-20 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="font-semibold text-lg">Aucun événement trouvé</p>
        <p class="text-sm mt-1">Essayez un autre terme de recherche</p>
      </div>
    </template>

    <!-- Count -->
    <p v-if="!loading && filtered.length" class="mt-6 text-sm text-gray-400 text-center">
      {{ filtered.length }} événement{{ filtered.length > 1 ? 's' : '' }} affiché{{ filtered.length > 1 ? 's' : '' }}
    </p>
  </div>
</template>
