<script setup lang="ts">
import { computed } from 'vue'
import type { ClientProfile } from '../../composables/useClients'
import { formatCurrency, formatDate } from '../../utils/format'
import NotificationStack from '../ui/NotificationStack.vue'

const props = defineProps<{
  clients: ClientProfile[]
  selectedId: number | null
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'select', id: number): void
  (e: 'edit', id: number): void
  (e: 'create'): void
  (e: 'whatsapp', client: ClientProfile): void
}>()

const resultCount = computed(() => props.clients.length)

function initialsFor(client: ClientProfile): string {
  const base = client.full_name || `${client.first_name ?? ''} ${client.last_name ?? ''}`
  const parts = base.trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return 'CL'
  const first = parts[0]?.charAt(0) ?? ''
  const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : ''
  return `${first}${last}`.toUpperCase()
}

function displayName(client: ClientProfile): string {
  if (client.full_name) return client.full_name
  return `${client.first_name ?? ''} ${client.last_name ?? ''}`.trim() || 'Cliente'
}

function displayPhone(client: ClientProfile): string {
  return client.phone ?? 'Sin celular'
}

function displayCreated(client: ClientProfile): string {
  return client.created_at ? formatDate(client.created_at) : '-'
}
</script>

<template>
  <section class="panel list-panel">
    <div class="panel-header">
      <div>
        <p class="eyebrow">Clientes</p>
        <h3>Clientes y fidelizacion</h3>
        <p class="panel-subtitle">Gestiona datos, reservas y comunicaciones en un solo lugar.</p>
      </div>
      <div class="header-actions">
        <div class="notifications-slot">
          <NotificationStack variant="compact" title="Alertas de clientes" countLabel="3 alertas" />
        </div>
        <span class="pill">Resultados {{ resultCount }}</span>
        <button class="btn-primary" type="button" @click="emit('create')">Crear cliente</button>
      </div>
    </div>

    <div class="table">
      <div class="table-row table-head">
        <span>Cliente</span>
        <span>Celular</span>
        <span># Citas</span>
        <span>Total ventas</span>
        <span>Creado</span>
        <span class="table-actions">Acciones</span>
      </div>

      <div v-if="!props.clients.length" class="empty-state">
        <p>No hay clientes aun. Crea el primero para empezar.</p>
        <button class="btn-primary" type="button" @click="emit('create')">Registrar cliente</button>
      </div>

      <div
        v-for="client in props.clients"
        :key="client.id"
        class="table-row"
        :class="{ active: client.id === props.selectedId }"
      >
        <button class="link-button" type="button" @click="emit('select', client.id)">
          <span class="avatar">{{ initialsFor(client) }}</span>
          <span class="client-main">
            <span class="client-name">{{ displayName(client) }}</span>
            <span class="client-meta">{{ client.email ?? 'Sin correo' }}</span>
          </span>
        </button>
        <span class="client-meta">{{ displayPhone(client) }}</span>
        <span class="client-metric">{{ client.service_count ?? 0 }}</span>
        <span class="client-metric">{{ formatCurrency(client.total_sales ?? 0) }}</span>
        <span class="client-meta">{{ displayCreated(client) }}</span>
        <div class="row-actions">
          <button class="btn-ghost" type="button" @click="emit('edit', client.id)">Editar</button>
          <button
            class="btn-whatsapp"
            type="button"
            :disabled="!client.phone"
            @click="emit('whatsapp', client)"
          >
            WhatsApp
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.list-panel {
  gap: 20px;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  justify-content: flex-end;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
  color: var(--ink-muted);
  border: 1px solid rgba(23, 20, 26, 0.1);
  background: rgba(255, 255, 255, 0.8);
}

.search-field {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: var(--radius-md);
  padding: 6px 12px;
  min-width: 260px;
}

.search-icon {
  width: 18px;
  height: 18px;
  color: var(--ink-muted);
}

.search-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.9rem;
  width: 100%;
}

.table-row {
  grid-template-columns: 1.4fr 0.9fr 0.6fr 0.9fr 0.7fr auto;
}

.table-row > :nth-child(3),
.table-row > :nth-child(4),
.table-row > :nth-child(5) {
  justify-self: center;
  text-align: center;
}

.table-row > :nth-child(6) {
  justify-self: end;
}

.table-row.table-head > :nth-child(6) {
  text-align: right;
}

.table-row.active {
  border-color: rgba(214, 106, 86, 0.6);
  box-shadow: 0 0 0 2px rgba(214, 106, 86, 0.12);
}

.table-row.table-head {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.table-actions {
  text-align: right;
}

.link-button {
  border: none;
  background: transparent;
  padding: 0;
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: center;
  gap: 12px;
  text-align: left;
  cursor: pointer;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-weight: 600;
  color: #1b1133;
  background: radial-gradient(circle at top, rgba(214, 106, 86, 0.35), rgba(255, 255, 255, 0.9));
  border: 1px solid rgba(214, 106, 86, 0.3);
}

.client-main {
  display: grid;
  gap: 4px;
}

.client-name {
  font-weight: 600;
  color: var(--ink-strong);
}

.client-meta {
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.client-metric {
  font-weight: 600;
  color: var(--ink-strong);
}

.row-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
}

.row-actions > button {
  min-width: 110px;
  height: 36px;
  padding: 0 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.btn-whatsapp {
  border: 1px solid rgba(37, 211, 102, 0.45);
  background: rgba(37, 211, 102, 0.15);
  color: #0b6e3f;
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  cursor: pointer;
  font-weight: 600;
}

.btn-whatsapp:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  padding: 28px;
  border-radius: var(--radius-lg);
  border: 1px dashed rgba(23, 20, 26, 0.2);
  display: grid;
  gap: 12px;
  text-align: center;
}

@media (max-width: 1100px) {
  .table-row {
    grid-template-columns: 1.4fr 0.9fr 0.6fr 0.9fr 0.7fr;
  }

  .table-actions,
  .row-actions {
    grid-column: 1 / -1;
    justify-content: flex-start;
  }
}

@media (max-width: 760px) {
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .search-field {
    width: 100%;
  }

  .table {
    overflow-x: auto;
  }

  .table-row {
    grid-template-columns: 1fr;
  }

  .row-actions {
    justify-content: flex-start;
  }
}
</style>
