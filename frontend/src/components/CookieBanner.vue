<script setup lang="ts">
import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'eventflow_cookie_consent'

const visible = ref(false)
const showDetails = ref(false)

const preferences = ref({
  necessary: true,   // toujours activé
  analytics: false,
  marketing: false,
})

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (!saved) {
    visible.value = true
  }
})

function acceptAll() {
  preferences.value.analytics = true
  preferences.value.marketing = true
  save()
}

function rejectAll() {
  preferences.value.analytics = false
  preferences.value.marketing = false
  save()
}

function saveCustom() {
  save()
}

function save() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({
    ...preferences.value,
    timestamp: new Date().toISOString(),
    version: '1.0',
  }))
  visible.value = false
}
</script>

<template>
  <Transition name="slide-up">
    <div
      v-if="visible"
      class="fixed bottom-0 left-0 right-0 z-50 p-4 md:p-6"
    >
      <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-600 to-indigo-500 px-6 py-4 flex items-center gap-3">
          <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <h2 class="text-white font-bold text-base">Gestion des cookies — RGPD</h2>
        </div>

        <div class="px-6 py-4">
          <p class="text-sm text-gray-600 mb-4">
            EventFlow utilise des cookies pour assurer le bon fonctionnement du site et améliorer votre expérience.
            Vous pouvez choisir les catégories que vous acceptez.
            <router-link to="/privacy" class="text-primary-600 underline hover:text-primary-700">Politique de confidentialité</router-link>
          </p>

          <!-- Cookie categories -->
          <div v-if="showDetails" class="space-y-3 mb-4">
            <!-- Nécessaires -->
            <div class="flex items-start justify-between gap-4 p-3 bg-gray-50 rounded-xl">
              <div>
                <p class="text-sm font-semibold text-gray-800">Cookies nécessaires</p>
                <p class="text-xs text-gray-500 mt-0.5">Authentification, session, sécurité. Obligatoires au fonctionnement.</p>
              </div>
              <div class="shrink-0">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                  Toujours actif
                </span>
              </div>
            </div>

            <!-- Analytiques -->
            <div class="flex items-start justify-between gap-4 p-3 bg-gray-50 rounded-xl">
              <div>
                <p class="text-sm font-semibold text-gray-800">Cookies analytiques</p>
                <p class="text-xs text-gray-500 mt-0.5">Statistiques de navigation anonymisées pour améliorer le service.</p>
              </div>
              <button
                @click="preferences.analytics = !preferences.analytics"
                :class="[
                  'relative shrink-0 w-11 h-6 rounded-full transition-colors duration-200',
                  preferences.analytics ? 'bg-primary-500' : 'bg-gray-300',
                ]"
              >
                <span
                  :class="[
                    'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200',
                    preferences.analytics ? 'translate-x-5' : '',
                  ]"
                />
              </button>
            </div>

            <!-- Marketing -->
            <div class="flex items-start justify-between gap-4 p-3 bg-gray-50 rounded-xl">
              <div>
                <p class="text-sm font-semibold text-gray-800">Cookies marketing</p>
                <p class="text-xs text-gray-500 mt-0.5">Personnalisation des contenus et des publicités éventuelles.</p>
              </div>
              <button
                @click="preferences.marketing = !preferences.marketing"
                :class="[
                  'relative shrink-0 w-11 h-6 rounded-full transition-colors duration-200',
                  preferences.marketing ? 'bg-primary-500' : 'bg-gray-300',
                ]"
              >
                <span
                  :class="[
                    'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200',
                    preferences.marketing ? 'translate-x-5' : '',
                  ]"
                />
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap items-center gap-3">
            <button
              @click="acceptAll"
              class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors"
            >
              Tout accepter
            </button>
            <button
              @click="rejectAll"
              class="px-5 py-2.5 bg-white text-gray-700 border border-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors"
            >
              Tout refuser
            </button>
            <button
              v-if="!showDetails"
              @click="showDetails = true"
              class="px-5 py-2.5 text-primary-600 text-sm font-semibold hover:underline transition-colors"
            >
              Personnaliser
            </button>
            <button
              v-else
              @click="saveCustom"
              class="px-5 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded-xl hover:bg-gray-900 transition-colors"
            >
              Enregistrer mes choix
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
