<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import AppAlert from '@/components/AppAlert.vue'

const auth = useAuthStore()
const router = useRouter()

// ─── Edit form ─────────────────────────────────────────────────────────────
const form = ref({ firstName: '', lastName: '', phone: '' })
const editErrors = ref<Record<string, string>>({})
const editMessage = ref<{ type: 'success' | 'error'; text: string } | null>(null)
const saving = ref(false)
const editMode = ref(false)

// ─── Anonymisation ──────────────────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteConfirmInput = ref('')
const anonymising = ref(false)
const deleteError = ref('')

onMounted(async () => {
  await auth.fetchMe()
  if (auth.user) {
    form.value.firstName = auth.user.firstName
    form.value.lastName = auth.user.lastName
    form.value.phone = auth.user.phone ?? ''
  }
})

function validateEdit() {
  editErrors.value = {}
  if (!form.value.firstName.trim()) editErrors.value.firstName = 'Le prénom est obligatoire.'
  if (!form.value.lastName.trim()) editErrors.value.lastName = 'Le nom est obligatoire.'
  return Object.keys(editErrors.value).length === 0
}

async function handleSave() {
  editMessage.value = null
  if (!validateEdit()) return
  saving.value = true
  try {
    await auth.updateMe({
      firstName: form.value.firstName,
      lastName: form.value.lastName,
      phone: form.value.phone || null,
    } as any)
    editMessage.value = { type: 'success', text: 'Vos données ont été mises à jour.' }
    editMode.value = false
  } catch (e: any) {
    editMessage.value = { type: 'error', text: e?.response?.data?.message ?? 'Erreur lors de la mise à jour.' }
  } finally {
    saving.value = false
  }
}

function cancelEdit() {
  if (auth.user) {
    form.value.firstName = auth.user.firstName
    form.value.lastName = auth.user.lastName
    form.value.phone = auth.user.phone ?? ''
  }
  editErrors.value = {}
  editMode.value = false
}

async function handleAnonymise() {
  deleteError.value = ''
  if (deleteConfirmInput.value !== 'SUPPRIMER') {
    deleteError.value = 'Veuillez saisir SUPPRIMER pour confirmer.'
    return
  }
  anonymising.value = true
  try {
    await auth.deleteMe()
    router.push('/')
  } catch (e: any) {
    deleteError.value = e?.response?.data?.message ?? 'Une erreur est survenue.'
  } finally {
    anonymising.value = false
  }
}

function formatDate(d: string | null | undefined) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-10">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-900">Mon profil</h1>
      <p class="text-gray-500 mt-1">Gérez vos données personnelles conformément au RGPD</p>
    </div>

    <!-- RGPD badge -->
    <div class="mb-6 flex items-center gap-3 p-4 bg-blue-50 border border-blue-100 rounded-2xl text-sm text-blue-800">
      <svg class="w-5 h-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      <span>
        <strong>Droit d'accès RGPD :</strong> Cette page affiche toutes vos données personnelles stockées.
        Vous pouvez les modifier ou demander leur anonymisation à tout moment.
      </span>
    </div>

    <!-- ─── Personal data card ─── -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
      <div class="h-1.5 bg-gradient-to-r from-primary-500 to-indigo-400" />
      <div class="p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-gray-900">Données personnelles</h2>
          <button
            v-if="!editMode"
            @click="editMode = true"
            class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Modifier (droit de rectification)
          </button>
        </div>

        <AppAlert v-if="editMessage" :type="editMessage.type" :message="editMessage.text" class="mb-5" />

        <!-- View mode -->
        <div v-if="!editMode" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Prénom</p>
            <p class="text-gray-900 font-medium">{{ auth.user?.firstName }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nom</p>
            <p class="text-gray-900 font-medium">{{ auth.user?.lastName }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Email</p>
            <p class="text-gray-900 font-medium">{{ auth.user?.email }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Téléphone</p>
            <p class="text-gray-900 font-medium">{{ auth.user?.phone ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Rôle</p>
            <p class="text-gray-900 font-medium capitalize">{{ auth.user?.roles.join(', ').replace('ROLE_', '').toLowerCase() }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Compte créé le</p>
            <p class="text-gray-900 font-medium">{{ formatDate(auth.user?.createdAt) }}</p>
          </div>
        </div>

        <!-- Edit mode -->
        <form v-else @submit.prevent="handleSave" class="space-y-4" novalidate>
          <div class="grid grid-cols-2 gap-4">
            <AppInput
              v-model="form.firstName"
              label="Prénom"
              required
              :error="editErrors.firstName"
            />
            <AppInput
              v-model="form.lastName"
              label="Nom"
              required
              :error="editErrors.lastName"
            />
          </div>
          <AppInput
            v-model="form.phone"
            label="Téléphone"
            type="tel"
            placeholder="+33 6 xx xx xx xx"
            hint="Optionnel — vous pouvez laisser ce champ vide"
          />
          <div class="flex gap-3 pt-2">
            <AppButton type="submit" :loading="saving">Enregistrer</AppButton>
            <button
              type="button"
              @click="cancelEdit"
              class="px-4 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
            >
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── Consent card ─── -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
      <div class="p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Consentement RGPD</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Date de consentement</p>
            <p class="text-gray-900 font-medium">{{ formatDate(auth.user?.consentDate) }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Version de la politique</p>
            <p class="text-gray-900 font-medium">v{{ auth.user?.consentVersion }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Statut du compte</p>
            <span
              :class="['inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1 rounded-full',
                auth.user?.isAnonymized ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-700']"
            >
              <span class="w-2 h-2 rounded-full" :class="auth.user?.isAnonymized ? 'bg-gray-400' : 'bg-green-500'" />
              {{ auth.user?.isAnonymized ? 'Anonymisé' : 'Actif' }}
            </span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <router-link to="/privacy" class="text-sm text-primary-600 hover:underline font-medium">
            Consulter notre politique de confidentialité →
          </router-link>
        </div>
      </div>
    </div>

    <!-- ─── Danger zone ─── -->
    <div class="bg-white rounded-3xl border border-red-100 shadow-sm overflow-hidden">
      <div class="h-1.5 bg-red-400" />
      <div class="p-8">
        <h2 class="text-lg font-bold text-red-700 mb-2">Zone de danger</h2>
        <p class="text-sm text-gray-600 mb-5">
          <strong>Droit à l'effacement (RGPD) :</strong> Vous pouvez demander l'anonymisation de votre compte.
          Vos données personnelles seront remplacées par des valeurs neutres. Vos inscriptions et logs de consentement
          sont conservés pour des obligations légales.
        </p>
        <button
          @click="showDeleteConfirm = true"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 text-red-700 font-semibold rounded-xl border border-red-200 hover:bg-red-100 transition-colors text-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Anonymiser mon compte
        </button>
      </div>
    </div>

    <!-- ─── Delete confirm modal ─── -->
    <Transition name="fade">
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click.self="showDeleteConfirm = false"
      >
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
          <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-xl font-extrabold text-gray-900 text-center mb-2">Anonymiser le compte ?</h3>
          <p class="text-sm text-gray-500 text-center mb-1">
            Vos données personnelles seront remplacées et vous serez déconnecté.
            Cette action est <strong>irréversible</strong>.
          </p>
          <p class="text-sm text-gray-500 text-center mb-6">
            Tapez <strong class="text-red-600 font-mono">SUPPRIMER</strong> pour confirmer.
          </p>

          <input
            v-model="deleteConfirmInput"
            type="text"
            placeholder="SUPPRIMER"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm text-center font-mono focus:outline-none focus:ring-2 focus:ring-red-400 mb-3"
          />
          <p v-if="deleteError" class="text-xs text-red-600 text-center mb-3">{{ deleteError }}</p>

          <div class="flex gap-3">
            <button
              @click="showDeleteConfirm = false; deleteConfirmInput = ''; deleteError = ''"
              class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors text-sm"
            >
              Annuler
            </button>
            <AppButton @click="handleAnonymise" :loading="anonymising" variant="danger" class="flex-1">
              Confirmer l'anonymisation
            </AppButton>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
