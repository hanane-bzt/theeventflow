import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  //  Public 
  {
    path: '/',
    component: () => import('@/views/HomeView.vue'),
  },
  {
    path: '/events',
    component: () => import('@/views/EventListView.vue'),
  },
  {
    path: '/events/:id',
    component: () => import('@/views/EventDetailView.vue'),
  },
  {
    path: '/login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    component: () => import('@/views/RegisterView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/privacy',
    component: () => import('@/views/PrivacyView.vue'),
  },

  //  Authentifié (USER / ORGANIZER) 
  {
    path: '/dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { requiresAuth: true },
  },

  //  Organizer seulement 
  {
    path: '/events/create',
    component: () => import('@/views/EventFormView.vue'),
    meta: { requiresAuth: true, requiresOrganizer: true },
  },
  {
    path: '/events/:id/edit',
    component: () => import('@/views/EventFormView.vue'),
    meta: { requiresAuth: true, requiresOrganizer: true },
  },

  //  Fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

//  Guards de navigation 

router.beforeEach((to, _from) => {
  const auth = useAuthStore()

  // Redirige les utilisateurs déjà connectés vers le dashboard
  if (to.meta.guestOnly && auth.isAuthenticated) return '/dashboard'

  // Route nécessitant une authentification
  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'

  // Route réservée aux organisateurs
  if (to.meta.requiresOrganizer && !auth.isOrganizer) return '/dashboard'
})

export default router
