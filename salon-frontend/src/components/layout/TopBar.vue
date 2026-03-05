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
    <div>
      <p class="eyebrow">Resumen operativo</p>
      <h1 class="headline">Todo listo para hoy</h1>
      <p class="date">{{ dateLabel }}</p>
    </div>
    <div class="topbar-actions">
      <label class="search">
        <span>Buscar</span>
        <input type="search" placeholder="Clientes, servicios, productos" />
      </label>
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
  gap: 24px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.65rem;
  color: var(--ink-muted);
}

.headline {
  font-family: var(--font-display);
  font-size: 2.4rem;
  margin: 6px 0;
  color: var(--ink-strong);
}

.date {
  margin: 0;
  color: var(--ink-muted);
}

.topbar-actions {
  display: grid;
  gap: 12px;
  justify-items: end;
}

.search {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 14px;
  border: 1px solid var(--border-subtle);
  background: rgba(255, 255, 255, 0.7);
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.search input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.85rem;
  width: 220px;
  color: var(--ink-strong);
}


.user-chip {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 14px;
  border: 1px solid var(--border-subtle);
  background: rgba(255, 255, 255, 0.75);
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
  background: rgba(17, 15, 20, 0.08);
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 0.75rem;
  cursor: pointer;
}

@media (max-width: 820px) {
  .topbar {
    flex-direction: column;
  }

  .topbar-actions {
    width: 100%;
    justify-items: start;
  }

  .search {
    width: 100%;
  }
}
</style>
