<script setup lang="ts">
import { shallowRef } from 'vue'

interface UserProfile {
  name: string
  email: string
  role: string
}

const props = defineProps<{
  user: UserProfile | null
}>()

const emit = defineEmits<{
  logout: []
  openMenu: []
}>()

const dateLabel = shallowRef(
  new Intl.DateTimeFormat('es-PE', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  }).format(new Date()),
)
</script>

<template>
  <header class="topbar">
    <button class="menu-button" type="button" aria-label="Abrir menu" @click="emit('openMenu')">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path
          d="M4 6h16M4 12h16M4 18h16"
          fill="none"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linecap="round"
        />
      </svg>
    </button>
    <div class="header-copy">
      <p class="eyebrow">Resumen operativo</p>
      <h1 class="headline">Todo listo para hoy</h1>
      <p class="date">{{ dateLabel }}</p>
    </div>
    <div class="topbar-actions">
      <div class="alerts-pill" aria-label="Alertas del sistema">
        <span class="alerts-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24">
            <path
              d="M12 2a5 5 0 0 0-5 5v2.1c0 .89-.24 1.77-.69 2.55L5.1 13.1A2 2 0 0 0 6.8 16h10.4a2 2 0 0 0 1.7-2.9l-1.21-1.45a4.97 4.97 0 0 1-.69-2.55V7a5 5 0 0 0-5-5Zm0 20a3 3 0 0 0 2.83-2h-5.66A3 3 0 0 0 12 22Z"
            />
          </svg>
        </span>
        <span class="alerts-label">Alertas</span>
        <span class="alerts-count">3</span>
      </div>
      <div class="user-chip">
        <div>
          <p class="user-name">{{ props.user?.name ?? 'Usuario' }}</p>
          <p class="user-role">{{ props.user?.role ?? 'admin' }}</p>
        </div>
        <button class="logout" type="button" @click="emit('logout')">Salir</button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: center;
  padding: 4px 2px 14px;
  border-bottom: 1px solid rgba(25, 25, 25, 0.08);
  position: sticky;
  top: 0;
  z-index: 30;
  background: linear-gradient(180deg, rgba(250, 248, 244, 0.96), rgba(250, 248, 244, 0.84));
  backdrop-filter: blur(10px);
}

.menu-button {
  display: none;
  width: 42px;
  height: 42px;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  border: 1px solid rgba(25, 25, 25, 0.12);
  background: rgba(255, 253, 251, 0.92);
  color: var(--ink-strong);
  cursor: pointer;
}

.menu-button svg {
  width: 20px;
  height: 20px;
}

.header-copy {
  display: grid;
  gap: 4px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.65rem;
  color: var(--accent);
}

.headline {
  font-family: var(--font-display);
  font-size: 2rem;
  line-height: 1;
  margin: 0;
  color: #261722;
}

.date {
  margin: 0;
  color: #7e6672;
  font-size: 0.9rem;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.alerts-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid rgba(25, 25, 25, 0.1);
  background: rgba(255, 253, 251, 0.96);
  color: #261722;
  user-select: none;
}

.alerts-icon {
  width: 16px;
  height: 16px;
  color: var(--accent);
}

.alerts-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.alerts-label {
  font-size: 0.8rem;
  font-weight: 600;
}

.alerts-count {
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
}


.user-chip {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 14px;
  border: 1px solid rgba(25, 25, 25, 0.12);
  background: rgba(255, 253, 251, 0.94);
}

.user-name {
  margin: 0;
  font-weight: 600;
  font-size: 0.85rem;
}

.user-role {
  margin: 0;
  font-size: 0.7rem;
  color: var(--ink-muted);
}

.logout {
  border: none;
  background: #111111;
  color: #fff;
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 0.75rem;
  cursor: pointer;
}

.logout:hover {
  background: var(--accent);
}

@media (max-width: 820px) {
  .topbar {
    flex-wrap: wrap;
    align-items: center;
    gap: 10px 12px;
    padding: 8px 0 12px;
  }

  .menu-button {
    display: inline-flex;
    flex: 0 0 auto;
  }

  .header-copy {
    flex: 1 1 180px;
    min-width: 0;
  }

  .topbar-actions {
    width: 100%;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .headline {
    font-size: 1.45rem;
  }

  .date {
    font-size: 0.82rem;
  }

  .alerts-pill {
    padding: 8px 10px;
    gap: 6px;
  }

  .alerts-label {
    display: none;
  }

  .alerts-count {
    min-width: 18px;
    height: 18px;
  }

  .user-chip {
    width: auto;
    flex: 1 1 auto;
    justify-content: space-between;
    min-width: 0;
    padding: 8px 10px;
  }

  .user-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
  }

  .logout {
    padding: 6px 9px;
  }
}
</style>
