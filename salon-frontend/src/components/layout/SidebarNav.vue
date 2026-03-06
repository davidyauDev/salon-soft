<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

interface UserProfile {
  name: string
  role: string
}

const props = defineProps<{
  user: UserProfile | null
}>()

const route = useRoute()

const navItems = computed(() => [
  { label: 'Dashboard', to: '/', badge: 'Hoy' },
  { label: 'Productos', to: '/inventario', badge: 'Stock' },
  { label: 'Servicios', to: '/servicios', badge: 'Agenda' },
  { label: 'Ventas', to: '/ventas', badge: 'POS' },
  { label: 'Clientes', to: '/clientes', badge: 'Historial' },
  { label: 'Comisiones', to: '/comisiones', badge: 'Staff' },
  { label: 'Reportes', to: '/reportes', badge: 'KPIs' },
  { label: 'Auditoria', to: '/auditoria', badge: 'Logs' },
])
</script>

<template>
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">SC</div>
      <div class="brand-text">
        <p class="brand-title">Salon Control</p>
        <p class="brand-subtitle">Central de operaciones</p>
      </div>
    </div>

    <nav class="nav">
      <router-link
        v-for="item in navItems"
        :key="item.label"
        class="nav-item"
        :class="{ active: route.path === item.to }"
        :to="item.to"
      >
        <span class="nav-dot" />
        <span class="nav-label">{{ item.label }}</span>
        <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
      </router-link>
    </nav>

    <div class="sidebar-foot">
      <div class="profile">
        <div class="avatar">{{ props.user?.name?.slice(0, 2)?.toUpperCase() ?? 'SC' }}</div>
        <div>
          <p class="profile-name">{{ props.user?.name ?? 'Equipo Salon' }}</p>
          <p class="profile-role">{{ props.user?.role ?? 'Administrador' }}</p>
        </div>
      </div>
      <button class="settings" type="button">Ajustes</button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  display: flex;
  flex-direction: column;
  padding: 32px 24px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--border-subtle);
  gap: 32px;
  position: sticky;
  top: 0;
  height: 100vh;
}

.brand {
  display: flex;
  gap: 16px;
  align-items: center;
}

.brand-mark {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: var(--ink-strong);
  background: linear-gradient(140deg, #fbe2d1, #f6c7d1);
  border: 1px solid rgba(17, 15, 20, 0.08);
}

.brand-title {
  font-family: var(--font-display);
  font-size: 1.1rem;
  margin: 0;
  color: var(--ink-strong);
}

.brand-subtitle {
  margin: 0;
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid transparent;
  background: transparent;
  padding: 12px 14px;
  border-radius: 16px;
  color: var(--ink-strong);
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
  text-decoration: none;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.7);
  border-color: rgba(17, 15, 20, 0.08);
}

.nav-item.active {
  background: var(--card);
  box-shadow: var(--shadow-soft);
  border-color: rgba(17, 15, 20, 0.08);
}

.nav-dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: var(--accent);
  box-shadow: 0 0 0 4px rgba(214, 106, 86, 0.18);
}

.nav-label {
  flex: 1;
  text-align: left;
}

.nav-badge {
  font-size: 0.7rem;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.8);
  color: var(--ink-muted);
}

.sidebar-foot {
  margin-top: auto;
  display: grid;
  gap: 16px;
}

.profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: #1f1d29;
  color: #f7f1ea;
  font-weight: 600;
  display: grid;
  place-items: center;
}

.profile-name {
  margin: 0;
  font-size: 0.9rem;
  color: var(--ink-strong);
}

.profile-role {
  margin: 0;
  font-size: 0.75rem;
  color: var(--ink-muted);
}

.settings {
  border: 1px solid rgba(17, 15, 20, 0.1);
  background: rgba(255, 255, 255, 0.8);
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

@media (max-width: 1100px) {
  .brand-text,
  .nav-label,
  .nav-badge,
  .profile {
    display: none;
  }

  .nav-item {
    justify-content: center;
  }
}

@media (max-width: 820px) {
  .sidebar {
    position: relative;
    height: auto;
    flex-direction: row;
    align-items: center;
    overflow-x: auto;
  }

  .nav {
    flex-direction: row;
    flex-wrap: nowrap;
  }
}
</style>
