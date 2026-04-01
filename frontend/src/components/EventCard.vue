<script setup lang="ts">
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

defineProps<{ event: Event }>()

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatTime(dateStr: string) {
  return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

function spotsLeft(event: Event) {
  return event.maxParticipants - event.registrationsCount
}

function fillPercent(event: Event) {
  return Math.min(100, Math.round((event.registrationsCount / event.maxParticipants) * 100))
}
</script>

<template>
  <router-link
    :to="`/events/${event.id}`"
    class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-primary-100 transition-all duration-200"
  >
    <!-- Colored top band based on category -->
    <div class="h-2 rounded-t-2xl bg-gradient-to-r from-primary-500 to-indigo-400" />

    <div class="p-5">
      <!-- Title -->
      <h3 class="font-bold text-gray-900 text-base leading-snug group-hover:text-primary-600 transition-colors line-clamp-2">
        {{ event.title }}
      </h3>

      <!-- Date & Location -->
      <div class="mt-3 space-y-1.5">
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <svg class="w-4 h-4 shrink-0 text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span class="capitalize">{{ formatDate(event.eventDate) }} · {{ formatTime(event.eventDate) }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <svg class="w-4 h-4 shrink-0 text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span class="truncate">{{ event.location }}</span>
        </div>
      </div>

      <!-- Fill bar -->
      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>{{ event.registrationsCount }} inscrits</span>
          <span :class="spotsLeft(event) <= 5 ? 'text-red-500 font-semibold' : ''">
            {{ spotsLeft(event) <= 0 ? 'Complet' : `${spotsLeft(event)} places restantes` }}
          </span>
        </div>
        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="fillPercent(event) >= 90 ? 'bg-red-400' : fillPercent(event) >= 70 ? 'bg-yellow-400' : 'bg-primary-500'"
            :style="`width: ${fillPercent(event)}%`"
          />
        </div>
      </div>

      <!-- Organizer + CTA -->
      <div class="mt-4 flex items-center justify-between">
        <span class="text-xs text-gray-400">
          Par {{ event.organizer.firstName }} {{ event.organizer.lastName }}
        </span>
        <span class="text-xs font-semibold text-primary-600 group-hover:underline">Voir →</span>
      </div>
    </div>
  </router-link>
</template>
