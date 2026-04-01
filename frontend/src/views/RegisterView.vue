<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import AppAlert from '@/components/AppAlert.vue'

const auth = useAuthStore()
const router = useRouter()

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  password: '',
  passwordConfirm: '',
  consent: false,
})

const errors = ref<Record<string, string>>({})
const globalError = ref('')
const loading = ref(false)

function validate() {
  errors.value = {}
  if (!form.value.firstName.trim()) errors.value.firstName = 'Le prénom est obligatoire.'
  if (!form.value.lastName.trim()) errors.value.lastName = 'Le nom est obligatoire.'
  if (!form.value.email.trim()) errors.value.email = "L'email est obligatoire."
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) errors.value.email = 'Email invalide.'
  if (!form.value.password) errors.value.password = 'Le mot de passe est obligatoire.'
  else if (form.value.password.length < 6) errors.value.password = 'Minimum 6 caractères.'
  if (form.value.password !== form.value.passwordConfirm) errors.value.passwordConfirm = 'Les mots de passe ne correspondent pas.'
  if (!form.value.consent) errors.value.consent = 'Vous devez accepter la politique de confidentialité.'
  return Object.keys(errors.value).length === 0
}

async function handleRegister() {
  globalError.value = ''
  if (!validate()) return
  loading.value = true
  try {
    await auth.register({
      email: form.value.email,
      password: form.value.password,
      firstName: form.value.firstName,
      lastName: form.value.lastName,
      phone: form.value.phone || undefined,
      consent: form.value.consent,
    })
    router.push('/login')
  } catch (e: any) {
    globalError.value = e?.response?.data?.message ?? 'Une erreur est survenue. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 bg-gray-50">
    <div class="w-full max-w-lg">
      <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-primary-500 to-indigo-400" />

        <div class="px-8 py-10">
          <!-- Header -->
          <div class="text-center mb-8">
            <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">Créer un compte</h1>
            <p class="text-gray-500 text-sm mt-1">Rejoignez EventFlow gratuitement</p>
          </div>

          <AppAlert :message="globalError" type="error" class="mb-5" />

          <form @submit.prevent="handleRegister" class="space-y-4" novalidate>
            <!-- Nom / Prénom -->
            <div class="grid grid-cols-2 gap-4">
              <AppInput
                v-model="form.firstName"
                label="Prénom"
                placeholder="Jean"
                required
                :error="errors.firstName"
              />
              <AppInput
                v-model="form.lastName"
                label="Nom"
                placeholder="Martin"
                required
                :error="errors.lastName"
              />
            </div>

            <!-- Email -->
            <AppInput
              v-model="form.email"
              label="Adresse email"
              type="email"
              placeholder="vous@exemple.com"
              required
              autocomplete="email"
              :error="errors.email"
            />

            <!-- Téléphone (optionnel — RGPD minimisation) -->
            <AppInput
              v-model="form.phone"
              label="Téléphone"
              type="tel"
              placeholder="+33 6 xx xx xx xx"
              hint="Optionnel — nous collectons le minimum nécessaire (RGPD)"
            />

            <!-- Password -->
            <AppInput
              v-model="form.password"
              label="Mot de passe"
              type="password"
              placeholder="••••••••"
              required
              autocomplete="new-password"
              :error="errors.password"
              hint="Minimum 6 caractères"
            />
            <AppInput
              v-model="form.passwordConfirm"
              label="Confirmer le mot de passe"
              type="password"
              placeholder="••••••••"
              required
              autocomplete="new-password"
              :error="errors.passwordConfirm"
            />

            <!-- RGPD Consent (non pré-coché — exigence RGPD) -->
            <div class="p-4 bg-gray-50 rounded-xl border" :class="errors.consent ? 'border-red-300' : 'border-gray-200'">
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="form.consent"
                  class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 shrink-0"
                />
                <span class="text-sm text-gray-700 leading-relaxed">
                  J'accepte la
                  <router-link to="/privacy" target="_blank" class="text-primary-600 underline font-medium hover:text-primary-700">
                    politique de confidentialité
                  </router-link>
                  d'EventFlow (v1.0) et consens au traitement de mes données personnelles conformément au RGPD.
                  <span class="text-red-500 font-semibold"> *</span>
                </span>
              </label>
              <p v-if="errors.consent" class="mt-2 text-xs text-red-600 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ errors.consent }}
              </p>
            </div>

            <AppButton type="submit" :loading="loading" full-width size="lg">
              Créer mon compte
            </AppButton>
          </form>

          <p class="text-center text-sm text-gray-500 mt-6">
            Déjà inscrit ?
            <router-link to="/login" class="text-primary-600 font-semibold hover:underline">Se connecter</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
