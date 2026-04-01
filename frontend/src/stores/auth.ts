import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface User {
  id: number
  email: string
  firstName: string
  lastName: string
  phone: string | null
  role: string
  roles: string[]
  consentDate: string
  consentVersion: string
  isAnonymized: boolean
  createdAt: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value)
  const isOrganizer = computed(() => user.value?.roles.includes('ROLE_ORGANIZER') ?? false)
  const isUser = computed(() => !isOrganizer.value)
  const fullName = computed(() =>
    user.value ? `${user.value.firstName} ${user.value.lastName}` : '',
  )

  async function login(credentials: { email: string; password: string }) {
    // LexikJWT firewall returns { token: "eyJ..." }
    const { data } = await api.post<{ token: string }>('/auth/login', credentials)
    token.value = data.token
    localStorage.setItem('token', data.token)
    await fetchMe()
  }

  async function register(data: {
    email: string
    password: string
    firstName: string
    lastName: string
    phone?: string
    consent: boolean
  }) {
    await api.post('/auth/register', data)
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const { data } = await api.get<User>('/me')
      user.value = data
    } catch {
      logout()
    }
  }

  async function updateMe(data: Partial<Pick<User, 'firstName' | 'lastName' | 'phone'>>) {
    const { data: updated } = await api.put<User>('/me', data)
    user.value = updated
    return updated
  }

  async function deleteMe() {
    await api.delete('/me')
    logout()
  }

  async function updateConsent(granted: boolean) {
    const { data } = await api.put<{ message: string }>('/me/consent', { granted })
    return data
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return {
    user,
    token,
    isAuthenticated,
    isOrganizer,
    isUser,
    fullName,
    login,
    register,
    fetchMe,
    updateMe,
    deleteMe,
    updateConsent,
    logout,
  }
})
