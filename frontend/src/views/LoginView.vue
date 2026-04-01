<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import AppAlert from '@/components/AppAlert.vue'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  error.value = ''
  if (!email.value || !password.value) {
    error.value = 'Veuillez remplir tous les champs.'
    return
  }
  loading.value = true
  try {
    await auth.login({ email: email.value, password: password.value })
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'Email ou mot de passe incorrect.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 bg-gray-50">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Top gradient bar -->
        <div class="h-1.5 bg-gradient-to-r from-primary-500 to-indigo-400" />

        <div class="px-8 py-10">
          <!-- Header -->
          <div class="text-center mb-8">
            <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">Connexion</h1>
            <p class="text-gray-500 text-sm mt-1">Bon retour sur EventFlow !</p>
          </div>

          <!-- Demo hint -->
          <div class="mb-6 p-3.5 bg-indigo-50 rounded-xl border border-indigo-100 text-xs text-indigo-700 space-y-0.5">
            <p class="font-semibold mb-1">Comptes de démonstration :</p>
            <p>👤 Utilisateur : <span class="font-mono">user@test.com</span> / <span class="font-mono">user123</span></p>
            <p>🎤 Organisateur : <span class="font-mono">orga@test.com</span> / <span class="font-mono">orga123</span></p>
          </div>

          <AppAlert :message="error" type="error" class="mb-5" />

          <form @submit.prevent="handleLogin" class="space-y-4">
            <AppInput
              v-model="email"
              label="Adresse email"
              type="email"
              placeholder="vous@exemple.com"
              required
              autocomplete="email"
            />
            <AppInput
              v-model="password"
              label="Mot de passe"
              type="password"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />

            <AppButton type="submit" :loading="loading" full-width size="lg" class="mt-2">
              Se connecter
            </AppButton>
          </form>

          <p class="text-center text-sm text-gray-500 mt-6">
            Pas encore de compte ?
            <router-link to="/register" class="text-primary-600 font-semibold hover:underline">S'inscrire</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
