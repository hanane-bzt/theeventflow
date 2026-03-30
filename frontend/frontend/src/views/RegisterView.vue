<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

const email = ref("");
const password = ref("");
const firstName = ref("");
const lastName = ref("");
const consent = ref(false);

const handleRegister = async () => {
  await auth.register({
    email: email.value,
    password: password.value,
    firstName: firstName.value,
    lastName: lastName.value,
    consent: consent.value
  });

  router.push("/login");
};
</script>

<template>
  <div>
    <h1>Register</h1>

    <input v-model="firstName" placeholder="First Name" />
    <input v-model="lastName" placeholder="Last Name" />
    <input v-model="email" placeholder="Email" />
    <input v-model="password" type="password" />

    <label>
      <input type="checkbox" v-model="consent" />
      J'accepte les conditions (RGPD)
    </label>

    <button @click="handleRegister">S'inscrire</button>
  </div>
</template>