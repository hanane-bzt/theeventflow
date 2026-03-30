import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { mockLogin, mockRegister } from "../api/mock";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<any>(null);
  const token = ref(localStorage.getItem("token"));

  const isAuthenticated = computed(() => !!token.value);

  async function login(credentials: any) {
    const res = await mockLogin(credentials);

    token.value = res.token;
    user.value = res.user;

    localStorage.setItem("token", res.token);
  }

  async function register(data: any) {
    await mockRegister(data);
  }

  function logout() {
    token.value = null;
    user.value = null;
    localStorage.removeItem("token");
  }

  return { user, token, isAuthenticated, login, register, logout };
});