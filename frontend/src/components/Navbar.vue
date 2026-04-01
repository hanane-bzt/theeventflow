<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

function handleLogout() {
  auth.logout()
  menuOpen.value = false
  router.push('/')
}
</script>

<template>
  <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-2 font-bold text-xl text-primary-600 shrink-0">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>
        EventFlow
      </router-link>

      <!-- Desktop nav -->
      <div class="hidden md:flex items-center gap-1">
        <router-link
          to="/"
          class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors"
          active-class="text-primary-600 bg-primary-50"
          exact
        >
          Accueil
        </router-link>
        <router-link
          to="/events"
          class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors"
          active-class="text-primary-600 bg-primary-50"
        >
          Événements
        </router-link>

        <template v-if="auth.isAuthenticated">
          <router-link
            to="/dashboard"
            class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors"
            active-class="text-primary-600 bg-primary-50"
          >
            Dashboard
          </router-link>
          <router-link
            v-if="auth.isOrganizer"
            to="/events/create"
            class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors"
            active-class="text-primary-600 bg-primary-50"
          >
            + Créer un événement
          </router-link>
        </template>
      </div>

      <!-- Desktop actions -->
      <div class="hidden md:flex items-center gap-3">
        <template v-if="!auth.isAuthenticated">
          <router-link
            to="/login"
            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors"
          >
            Connexion
          </router-link>
          <router-link
            to="/register"
            class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
          >
            S'inscrire
          </router-link>
        </template>

        <template v-else>
          <router-link
            to="/profile"
            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
          >
            <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
              {{ auth.user?.firstName?.[0] }}{{ auth.user?.lastName?.[0] }}
            </div>
            {{ auth.fullName }}
          </router-link>
          <button
            @click="handleLogout"
            class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-red-600 transition-colors"
          >
            Déconnexion
          </button>
        </template>
      </div>

      <!-- Mobile hamburger -->
      <button
        @click="menuOpen = !menuOpen"
        class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path v-if="!menuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </nav>

    <!-- Mobile menu -->
    <Transition name="fade-down">
      <div v-if="menuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
        <router-link to="/" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Accueil</router-link>
        <router-link to="/events" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Événements</router-link>

        <template v-if="auth.isAuthenticated">
          <router-link to="/dashboard" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Dashboard</router-link>
          <router-link to="/profile" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Mon profil</router-link>
          <router-link v-if="auth.isOrganizer" to="/events/create" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-primary-600 hover:bg-primary-50">+ Créer un événement</router-link>
          <button @click="handleLogout" class="block w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">Déconnexion</button>
        </template>
        <template v-else>
          <router-link to="/login" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Connexion</router-link>
          <router-link to="/register" @click="menuOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-primary-600 hover:bg-primary-50">S'inscrire</router-link>
        </template>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.fade-down-enter-active,
.fade-down-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-down-enter-from,
.fade-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
