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
  grid-template-columns: 304px minmax(0, 1fr);
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 26%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.58), rgba(249, 244, 239, 0.76));
}

.app-main {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 30px 36px 40px;
  border-left: 1px solid rgba(25, 25, 25, 0.08);
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

  .app-main {
    padding: 16px 14px 22px;
    border-left: none;
    gap: 16px;
  }
}
</style>
