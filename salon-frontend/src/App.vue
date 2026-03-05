<script setup lang="ts">
import { computed, onMounted } from 'vue'
import AppShell from './components/layout/AppShell.vue'
import LoginView from './components/views/LoginView.vue'
import { useAuth } from './composables/useAuth'

const auth = useAuth()

const showApp = computed(() => auth.ready.value && auth.isAuthenticated.value)
const showLogin = computed(() => auth.ready.value && !auth.isAuthenticated.value)

onMounted(() => {
  auth.initialize()
})
</script>

<template>
  <div v-if="!auth.ready.value" class="app-loading">Cargando...</div>
  <LoginView v-else-if="showLogin" />
  <AppShell v-else-if="showApp" />
</template>

<style scoped>
.app-loading {
  min-height: 100vh;
  display: grid;
  place-items: center;
  font-weight: 600;
  color: var(--ink-muted);
}
</style>
