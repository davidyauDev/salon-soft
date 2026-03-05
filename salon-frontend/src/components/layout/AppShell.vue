<script setup lang="ts">
import SidebarNav from './SidebarNav.vue'
import TopBar from './TopBar.vue'
import { useAuth } from '../../composables/useAuth'

const auth = useAuth()
</script>

<template>
  <div class="app-shell">
    <SidebarNav :user="auth.user.value" />
    <main class="app-main">
      <TopBar :user="auth.user.value" @logout="auth.logout" />
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.app-shell {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  min-height: 100vh;
}

.app-main {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 32px 40px 40px;
}

@media (max-width: 1100px) {
  .app-shell {
    grid-template-columns: 92px minmax(0, 1fr);
  }
}

@media (max-width: 820px) {
  .app-shell {
    grid-template-columns: 1fr;
  }
}
</style>
