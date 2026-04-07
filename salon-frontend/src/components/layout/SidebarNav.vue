<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

interface UserProfile {
  name: string
  role: string
}

type NavIcon =
  | 'dashboard'
  | 'products'
  | 'services'
  | 'sales'
  | 'clients'
  | 'commissions'
  | 'reports'
  | 'audit'

interface NavItem {
  label: string
  hint: string
  to: string
  badge: string
  icon: NavIcon
}

const props = defineProps<{
  user: UserProfile | null
}>()

const route = useRoute()

const navItems = computed<NavItem[]>(() => [
  { label: 'Dashboard', hint: 'Resumen operativo', to: '/', badge: 'Hoy', icon: 'dashboard' },
  { label: 'Productos', hint: 'Stock y catalogo', to: '/inventario', badge: 'Stock', icon: 'products' },
  { label: 'Servicios', hint: 'Agenda y tarifas', to: '/servicios', badge: 'Agenda', icon: 'services' },
  { label: 'Ventas', hint: 'Caja y POS', to: '/ventas', badge: 'POS', icon: 'sales' },
  { label: 'Clientes', hint: 'Historial y contacto', to: '/clientes', badge: 'CRM', icon: 'clients' },
  { label: 'Comisiones', hint: 'Equipo y rendimiento', to: '/comisiones', badge: 'Staff', icon: 'commissions' },
  { label: 'Reportes', hint: 'Lecturas y cortes', to: '/reportes', badge: 'KPIs', icon: 'reports' },
  { label: 'Auditoria', hint: 'Movimientos y logs', to: '/auditoria', badge: 'Logs', icon: 'audit' },
])

const iconPaths: Record<NavIcon, string> = {
  dashboard:
    'M4 11.3 12 4l8 7.3v8.2a1.2 1.2 0 0 1-1.2 1.2h-4.8v-6.1H10V20.7H5.2A1.2 1.2 0 0 1 4 19.5z',
  products:
    'm12 3 7 3.5v11L12 21l-7-3.5v-11L12 3Zm0 2.4L7.2 7.8 12 10.2l4.8-2.4L12 5.4Zm-5 4.2v6.3L12 19.6l5-2.5V9.6L12 12 7 9.6Z',
  services:
    'M6 4.5h12v15H6zM8.2 2.8h7.6v3H8.2zM8.4 9h7.2M8.4 12.4h7.2M8.4 15.8h4.8',
  sales:
    'M4.5 6.5h13.8l-.9 8.7H7L5.7 4.8H3.4V3h3zM9 19.4A1.4 1.4 0 1 1 9 16.6a1.4 1.4 0 0 1 0 2.8Zm7.2 0a1.4 1.4 0 1 1 0-2.8 1.4 1.4 0 0 1 0 2.8Z',
  clients:
    'M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-6.8 7.2a6.8 6.8 0 0 1 13.6 0M6 17.5c1.1-2.3 3.2-3.5 6-3.5s4.9 1.2 6 3.5',
  commissions:
    'M8.5 19.5V10M12 19.5V5M15.5 19.5v-7.2M19 19.5H5',
  reports:
    'M5 4.5h14v15H5zM8 15h3M8 11.5h8M8 8h5',
  audit:
    'M12 4a8 8 0 1 0 8 8h-2a6 6 0 1 1-1.8-4.2L13 11h6V5l-2.3 2.3A7.98 7.98 0 0 0 12 4Z',
}

const todayLabel = computed(() =>
  new Intl.DateTimeFormat('es-PE', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  }).format(new Date()),
)

const profileInitials = computed(() =>
  (props.user?.name ?? 'SC')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('') || 'SC',
)
</script>

<template>
  <aside class="sidebar">
    <section class="brand-card">
      <div class="brand-top">
        <div class="brand-mark" aria-hidden="true">
          <span>TB</span>
        </div>

        <div class="brand-copy">
          <p class="brand-kicker">Trend Belleza</p>
          <h2>Salon Control</h2>
          <p>Panel operativo del salon</p>
        </div>
      </div>

      <div class="brand-meta">
        <span class="brand-chip">Turno activo</span>
        <span class="brand-date">{{ todayLabel }}</span>
      </div>
    </section>

    <nav class="nav-block" aria-label="Navegacion principal">
      <p class="section-label">Operacion</p>
      <router-link
        v-for="item in navItems"
        :key="item.label"
        class="nav-item"
        :class="{ active: route.path === item.to || route.path.startsWith(`${item.to}/`) }"
        :to="item.to"
      >
        <span class="nav-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24">
            <path :d="iconPaths[item.icon]" />
          </svg>
        </span>
        <span class="nav-copy">
          <span class="nav-label">{{ item.label }}</span>
          <span class="nav-hint">{{ item.hint }}</span>
        </span>
        <span class="nav-badge">{{ item.badge }}</span>
      </router-link>
    </nav>

    <footer class="sidebar-foot">
      <div class="profile-card">
        <div class="avatar">{{ profileInitials }}</div>
        <div class="profile-copy">
          <p class="profile-name">{{ props.user?.name ?? 'Equipo Salon' }}</p>
          <span class="profile-role">{{ props.user?.role ?? 'Administrador' }}</span>
        </div>
      </div>
      <button class="settings" type="button">Ajustes</button>
    </footer>
  </aside>
</template>

<style scoped>
.sidebar {
  position: sticky;
  top: 0;
  display: grid;
  align-content: start;
  gap: 18px;
  min-height: 100vh;
  padding: 22px;
  background:
    radial-gradient(circle at top, rgba(15, 118, 110, 0.1), transparent 26%),
    linear-gradient(180deg, #141414 0%, #0e0e0e 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden auto;
}

.brand-card,
.sidebar-foot {
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.04);
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05) inset;
}

.brand-card {
  padding: 18px;
  display: grid;
  gap: 14px;
}

.brand-top {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-mark {
  width: 52px;
  height: 52px;
  flex: none;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background:
    radial-gradient(circle at 32% 30%, rgba(255, 255, 255, 0.28), transparent 32%),
    linear-gradient(145deg, var(--accent), #0b534b);
  color: #fffdf8;
  font-weight: 800;
  letter-spacing: 0.08em;
  box-shadow: 0 14px 28px rgba(15, 118, 110, 0.28);
}

.brand-mark span {
  transform: translateY(1px);
}

.brand-copy {
  display: grid;
  gap: 2px;
}

.brand-kicker,
.section-label {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  font-size: 0.65rem;
  color: rgba(247, 244, 239, 0.62);
}

.brand-copy h2 {
  margin: 0;
  font-family: var(--font-display);
  font-size: 1.22rem;
  line-height: 1;
  color: #fffdf8;
}

.brand-copy p:last-child {
  margin: 0;
  font-size: 0.8rem;
  color: rgba(247, 244, 239, 0.72);
}

.brand-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  justify-content: space-between;
}

.brand-chip,
.status-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.brand-chip {
  background: rgba(15, 118, 110, 0.14);
  color: #d4f3ee;
  border: 1px solid rgba(15, 118, 110, 0.2);
}

.brand-date {
  font-size: 0.76rem;
  color: rgba(247, 244, 239, 0.7);
}

.nav-block {
  display: grid;
  gap: 10px;
}

.nav-item {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 12px 14px;
  border-radius: 18px;
  text-decoration: none;
  color: var(--sidebar-ink);
  border: 1px solid rgba(255, 255, 255, 0.06);
  background: rgba(255, 255, 255, 0.03);
  transition:
    transform 0.18s ease,
    border-color 0.18s ease,
    background 0.18s ease,
    box-shadow 0.18s ease;
}

.nav-item::before {
  content: '';
  position: absolute;
  inset: 8px auto 8px 8px;
  width: 3px;
  border-radius: 999px;
  background: transparent;
}

.nav-item:hover {
  transform: translateX(2px);
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.12);
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(15, 118, 110, 0.3);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
}

.nav-item.active::before {
  background: var(--accent);
}

.nav-icon {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  color: #d7f0ea;
  background: rgba(15, 118, 110, 0.12);
  border: 1px solid rgba(15, 118, 110, 0.18);
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.nav-copy {
  display: grid;
  gap: 2px;
  min-width: 0;
}

.nav-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #fffdf8;
}

.nav-hint {
  font-size: 0.75rem;
  color: rgba(247, 244, 239, 0.68);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-badge {
  padding: 5px 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.06);
  color: rgba(247, 244, 239, 0.78);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.sidebar-foot {
  margin-top: auto;
  padding: 16px;
  display: grid;
  gap: 12px;
}

.profile-card {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background:
    radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.22), transparent 32%),
    linear-gradient(145deg, #1b1b1b, #0f766e);
  color: #fffdf8;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.profile-copy {
  display: grid;
  gap: 2px;
}

.profile-name {
  margin: 0;
  color: #fffdf8;
  font-size: 0.92rem;
  font-weight: 700;
}

.profile-role {
  font-size: 0.76rem;
  color: rgba(247, 244, 239, 0.72);
}

.settings {
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #fffdf8;
  border-radius: 14px;
  padding: 11px 14px;
  font-weight: 700;
  cursor: pointer;
  transition:
    transform 0.18s ease,
    background 0.18s ease,
    border-color 0.18s ease;
}

.settings:hover {
  transform: translateY(-1px);
  background: rgba(15, 118, 110, 0.16);
  border-color: rgba(15, 118, 110, 0.28);
}

@media (max-width: 1100px) {
  .sidebar {
    padding: 18px 14px;
  }

  .brand-copy,
  .nav-copy,
  .nav-badge,
  .brand-meta,
  .profile-copy,
  .settings {
    display: none;
  }

  .nav-item {
    grid-template-columns: 1fr;
    justify-items: center;
  }

  .nav-icon {
    width: 44px;
    height: 44px;
  }
}

@media (max-width: 820px) {
  .sidebar {
    position: relative;
    min-height: auto;
    height: auto;
    gap: 12px;
    padding: 12px 12px 10px;
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  .brand-card {
    padding: 12px;
  }

  .brand-copy,
  .nav-copy,
  .nav-badge,
  .brand-meta,
  .profile-copy,
  .settings {
    display: none;
  }

  .nav-block {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
    margin: 0 -2px;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
  }

  .nav-block .section-label {
    display: none;
  }

  .nav-item {
    flex: 0 0 auto;
    width: 62px;
    min-width: 62px;
    height: 62px;
    padding: 0;
    grid-template-columns: 1fr;
    justify-items: center;
  }

  .nav-item .nav-icon {
    width: 40px;
    height: 40px;
  }

  .nav-item.active::before {
    inset: auto auto 6px 50%;
    width: 18px;
    height: 3px;
    transform: translateX(-50%);
  }

  .sidebar-foot {
    display: none;
  }
}
</style>
