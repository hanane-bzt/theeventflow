<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import AppAlert from '@/components/AppAlert.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const isEdit = computed(() => !!route.params.id)
const eventId = computed(() => Number(route.params.id))

const form = ref({
  title: '',
  description: '',
  eventDate: '',
  location: '',
  maxParticipants: 50,
  isPublished: true,
})

const errors = ref<Record<string, string>>({})
const globalError = ref('')
const success = ref('')
const loading = ref(false)
const initialLoading = ref(false)
const showDeleteConfirm = ref(false)
const deleting = ref(false)

onMounted(async () => {
  if (!isEdit.value) return
  initialLoading.value = true
  try {
    const { data } = await api.get<any>(`/events/${eventId.value}`)
    if (data.organizer?.id !== auth.user?.id) {
      router.push('/dashboard')
      return
    }
    // Format date for datetime-local input
    const d = new Date(data.eventDate)
    const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16)
    form.value = {
      title: data.title,
      description: data.description,
      eventDate: local,
      location: data.location,
      maxParticipants: data.maxParticipants,
      isPublished: data.isPublished,
    }
  } catch {
    router.push('/dashboard')
  } finally {
    initialLoading.value = false
  }
})

function validate() {
  errors.value = {}
  if (!form.value.title.trim()) errors.value.title = 'Le titre est obligatoire.'
  if (!form.value.description.trim()) errors.value.description = 'La description est obligatoire.'
  if (!form.value.eventDate) errors.value.eventDate = 'La date est obligatoire.'
  if (!form.value.location.trim()) errors.value.location = 'Le lieu est obligatoire.'
  if (form.value.maxParticipants < 1) errors.value.maxParticipants = 'Minimum 1 participant.'
  return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
  globalError.value = ''
  success.value = ''
  if (!validate()) return
  loading.value = true
  try {
    if (isEdit.value) {
      await api.put(`/events/${eventId.value}`, form.value)
      success.value = 'Événement modifié avec succès !'
    } else {
      const { data } = await api.post<{ id: number }>('/events', form.value)
      router.push(`/events/${data.id}`)
    }
  } catch (e: any) {
    globalError.value = e?.response?.data?.message ?? 'Une erreur est survenue.'
  } finally {
    loading.value = false
  }
}

async function handleDelete() {
  deleting.value = true
  try {
    await api.delete(`/events/${eventId.value}`)
    router.push('/dashboard')
  } catch (e: any) {
    globalError.value = e?.response?.data?.message ?? 'Impossible de supprimer cet événement.'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-10">
    <!-- Back -->
    <router-link to="/dashboard" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600 transition-colors mb-6">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      Retour au dashboard
    </router-link>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="h-1.5 bg-gradient-to-r from-primary-500 to-indigo-400" />

      <div class="p-8">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-2">
          {{ isEdit ? 'Modifier l\'événement' : 'Créer un événement' }}
        </h1>
        <p class="text-sm text-gray-500 mb-8">
          {{ isEdit ? 'Mettez à jour les informations de votre événement.' : 'Remplissez les informations pour publier votre événement.' }}
        </p>

        <!-- Loading state -->
        <div v-if="initialLoading" class="space-y-4 animate-pulse">
          <div class="h-10 bg-gray-100 rounded-xl" v-for="i in 5" :key="i" />
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-5" novalidate>
          <AppAlert :message="globalError" type="error" />
          <AppAlert :message="success" type="success" />

          <AppInput
            v-model="form.title"
            label="Titre de l'événement"
            placeholder="Vue.js Summit 2026"
            required
            :error="errors.title"
          />

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">
              Description <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              placeholder="Décrivez votre événement en détail..."
              :class="[
                'w-full px-4 py-2.5 rounded-xl border bg-white text-gray-900 text-sm resize-none transition-colors',
                'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent placeholder:text-gray-400',
                errors.description ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400',
              ]"
            />
            <p v-if="errors.description" class="text-xs text-red-600">{{ errors.description }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-gray-700">
                Date et heure <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.eventDate"
                type="datetime-local"
                :class="[
                  'w-full px-4 py-2.5 rounded-xl border text-sm transition-colors',
                  'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
                  errors.eventDate ? 'border-red-400 bg-red-50' : 'border-gray-300',
                ]"
              />
              <p v-if="errors.eventDate" class="text-xs text-red-600">{{ errors.eventDate }}</p>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-gray-700">Participants max <span class="text-red-500">*</span></label>
              <input
                v-model.number="form.maxParticipants"
                type="number"
                min="1"
                max="10000"
                :class="[
                  'w-full px-4 py-2.5 rounded-xl border text-sm transition-colors',
                  'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
                  errors.maxParticipants ? 'border-red-400 bg-red-50' : 'border-gray-300',
                ]"
              />
              <p v-if="errors.maxParticipants" class="text-xs text-red-600">{{ errors.maxParticipants }}</p>
            </div>
          </div>

          <AppInput
            v-model="form.location"
            label="Lieu"
            placeholder="Paris – La Défense, CNIT"
            required
            :error="errors.location"
          />

          <!-- Published toggle -->
          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div>
              <p class="text-sm font-semibold text-gray-800">Publier l'événement</p>
              <p class="text-xs text-gray-500 mt-0.5">L'événement sera visible par tous les utilisateurs</p>
            </div>
            <button
              type="button"
              @click="form.isPublished = !form.isPublished"
              :class="[
                'relative w-11 h-6 rounded-full transition-colors duration-200',
                form.isPublished ? 'bg-primary-500' : 'bg-gray-300',
              ]"
            >
              <span
                :class="[
                  'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200',
                  form.isPublished ? 'translate-x-5' : '',
                ]"
              />
            </button>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-2">
            <AppButton type="submit" :loading="loading" size="lg">
              {{ isEdit ? 'Enregistrer les modifications' : 'Créer l\'événement' }}
            </AppButton>
            <router-link
              to="/dashboard"
              class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
            >
              Annuler
            </router-link>
            <div class="flex-1" />
            <button
              v-if="isEdit"
              type="button"
              @click="showDeleteConfirm = true"
              class="px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-xl transition-colors"
            >
              Supprimer
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete confirmation modal -->
    <Transition name="fade">
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click.self="showDeleteConfirm = false"
      >
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
          <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Supprimer l'événement ?</h3>
          <p class="text-sm text-gray-500 text-center mb-6">Cette action est irréversible. Toutes les inscriptions associées seront perdues.</p>
          <div class="flex gap-3">
            <button
              @click="showDeleteConfirm = false"
              class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors text-sm"
            >
              Annuler
            </button>
            <AppButton @click="handleDelete" :loading="deleting" variant="danger" class="flex-1">
              Supprimer
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
