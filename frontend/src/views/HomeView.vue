<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import EventCard from '@/components/EventCard.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const events = ref<any[]>([])
const totalEvents = ref<number | null>(null)

onMounted(async () => {
  try {
    const { data } = await api.get<any[]>('/events')
    events.value = data.slice(0, 3)
    totalEvents.value = data.length
  } catch {
    // homepage works without events
  }
})
</script>

<template>
  <!-- Hero -->
  <section class="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-600 to-indigo-500 text-white">
    <div class="absolute inset-0 opacity-10">
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-white rounded-full" />
      <div class="absolute -bottom-32 -left-16 w-80 h-80 bg-white rounded-full" />
    </div>
    <div class="relative max-w-6xl mx-auto px-4 py-20 md:py-28 text-center">
      <span class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse" />
        Plateforme certifiée RGPD
      </span>
      <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
        Gérez vos événements<br class="hidden md:block" />
        <span class="text-indigo-200">professionnels</span> simplement
      </h1>
      <p class="text-lg md:text-xl text-indigo-100 max-w-2xl mx-auto mb-10 leading-relaxed">
        EventFlow est la plateforme moderne pour créer, publier et gérer vos conférences,
        meetups et formations. Conformité RGPD native.
      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <router-link
          to="/events"
          class="px-8 py-3.5 bg-white text-primary-700 font-bold rounded-2xl hover:bg-indigo-50 transition-colors shadow-lg text-base"
        >
          Voir les événements
        </router-link>
        <router-link
          v-if="!auth.isAuthenticated"
          to="/register"
          class="px-8 py-3.5 bg-primary-800/50 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-primary-800/70 transition-colors border border-white/30 text-base"
        >
          Créer un compte
        </router-link>
        <router-link
          v-else-if="auth.isOrganizer"
          to="/events/create"
          class="px-8 py-3.5 bg-primary-800/50 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-primary-800/70 transition-colors border border-white/30 text-base"
        >
          + Créer un événement
        </router-link>
      </div>
    </div>
  </section>

  <!-- Stats -->
  <section class="bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div>
        <p class="text-3xl font-extrabold text-primary-600">
          <span v-if="totalEvents !== null">{{ totalEvents }}</span>
          <span v-else class="inline-block w-8 h-8 bg-gray-100 rounded animate-pulse" />
        </p>
        <p class="text-sm text-gray-500 mt-1">Événements à venir</p>
      </div>
      <div>
        <p class="text-3xl font-extrabold text-primary-600">3</p>
        <p class="text-sm text-gray-500 mt-1">Rôles disponibles</p>
      </div>
      <div>
        <p class="text-3xl font-extrabold text-primary-600">100%</p>
        <p class="text-sm text-gray-500 mt-1">Conforme RGPD</p>
      </div>
      <div>
        <p class="text-3xl font-extrabold text-primary-600">JWT</p>
        <p class="text-sm text-gray-500 mt-1">Sécurisé & moderne</p>
      </div>
    </div>
  </section>

  <!-- Recent events -->
  <section class="max-w-6xl mx-auto px-4 py-16">
    <div class="flex items-end justify-between mb-8">
      <div>
        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Prochains événements</h2>
        <p class="text-gray-500 mt-1">Ne ratez pas ces rendez-vous incontournables</p>
      </div>
      <router-link to="/events" class="text-sm font-semibold text-primary-600 hover:underline hidden md:block">
        Tous les événements →
      </router-link>
    </div>

    <div v-if="events.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <EventCard v-for="event in events" :key="event.id" :event="event" />
    </div>

    <div class="mt-8 text-center md:hidden">
      <router-link to="/events" class="text-sm font-semibold text-primary-600 hover:underline">
        Voir tous les événements →
      </router-link>
    </div>
  </section>

  <!-- Features / RGPD -->
  <section class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Pourquoi EventFlow ?</h2>
        <p class="text-gray-500 mt-2">Une plateforme moderne, sécurisée et respectueuse de vos données</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <div v-for="feature in features" :key="feature.title" class="flex flex-col items-center text-center p-6 rounded-2xl border border-gray-100 hover:border-primary-100 hover:shadow-md transition-all">
          <div class="w-14 h-14 bg-primary-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
            </svg>
          </div>
          <h3 class="font-bold text-gray-900 mb-2">{{ feature.title }}</h3>
          <p class="text-sm text-gray-500 leading-relaxed">{{ feature.description }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section v-if="!auth.isAuthenticated" class="bg-gradient-to-r from-primary-600 to-indigo-500 py-16 text-white text-center">
    <div class="max-w-2xl mx-auto px-4">
      <h2 class="text-2xl md:text-3xl font-extrabold mb-4">Prêt à vous lancer ?</h2>
      <p class="text-indigo-100 mb-8">Créez votre compte gratuitement et rejoignez la communauté EventFlow.</p>
      <router-link
        to="/register"
        class="inline-block px-8 py-3.5 bg-white text-primary-700 font-bold rounded-2xl hover:bg-indigo-50 transition-colors shadow-lg"
      >
        Créer mon compte
      </router-link>
    </div>
  </section>
</template>

<script lang="ts">
const features = [
  {
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    title: 'Conformité RGPD native',
    description: 'Consentement explicite, droit à l\'oubli, anonymisation automatique et registre des traitements intégré.',
  },
  {
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    title: 'SPA Vue.js 3 rapide',
    description: 'Interface réactive avec Composition API, Pinia et Vue Router. Navigation fluide et instantanée.',
  },
  {
    icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
    title: 'Gestion complète',
    description: 'Créez, publiez et gérez vos événements. Suivez les inscriptions en temps réel avec des jauges dynamiques.',
  },
]
</script>
