<script setup lang="ts">
import Navbar from '@/components/Navbar.vue'
import CookieBanner from '@/components/CookieBanner.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Navbar />
    <main class="flex-1">
      <router-view v-slot="{ Component }">
        <Transition name="page" mode="out-in">
          <component :is="Component" />
        </Transition>
      </router-view>
    </main>
    <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
      © 2026 EventFlow —
      <router-link to="/privacy" class="hover:text-primary-600 underline">Politique de confidentialité</router-link>
    </footer>
    <CookieBanner />
  </div>
</template>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.15s ease;
}
.page-enter-from,
.page-leave-to {
  opacity: 0;
}
</style>
