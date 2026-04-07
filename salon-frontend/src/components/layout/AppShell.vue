<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SidebarNav from './SidebarNav.vue'
import TopBar from './TopBar.vue'
import { useAuth } from '../../composables/useAuth'

const auth = useAuth()
const route = useRoute()
const mobileNavOpen = ref(false)

function closeMobileNav(): void {
  mobileNavOpen.value = false
}

function toggleMobileNav(): void {
  mobileNavOpen.value = !mobileNavOpen.value
}

watch(
  () => route.path,
  () => {
    closeMobileNav()
  },
)

watch(mobileNavOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

onBeforeUnmount(() => {
  document.body.style.overflow = ''
})
</script>

<template>
  <div class="app-shell">
    <div v-if="mobileNavOpen" class="sidebar-backdrop" @click="closeMobileNav" />
    <SidebarNav
      :user="auth.user.value"
      :mobile-open="mobileNavOpen"
      @close="closeMobileNav"
      @navigate="closeMobileNav"
    />
    <main class="app-main">
      <TopBar :user="auth.user.value" @logout="auth.logout" @open-menu="toggleMobileNav" />
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

.sidebar-backdrop {
  display: none;
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

  .sidebar-backdrop {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(17, 15, 20, 0.45);
    backdrop-filter: blur(4px);
    z-index: 40;
  }

  .app-main {
    padding: 16px 14px 22px;
    border-left: none;
    gap: 16px;
  }
}
</style>
