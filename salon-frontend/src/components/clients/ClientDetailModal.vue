<script setup lang="ts">
import { computed } from 'vue'
import type { ClientHistory, ClientProfile } from '../../composables/useClients'
import { formatCurrency, formatDate, formatDateLong, formatTime } from '../../utils/format'

const props = defineProps<{
  client: ClientProfile | null
  history: ClientHistory | null
  stats: {
    totalSales: number
    serviceCount: number
    avgTicket: number
  }
}>()

const emit = defineEmits<{
  (e: 'edit', id: number): void
  (e: 'whatsapp', client: ClientProfile): void
  (e: 'close'): void
}>()

const activities = computed(() => props.history?.services ?? [])

const displayName = computed(() => {
  if (!props.client) return ''
  const base = props.client.full_name || `${props.client.first_name ?? ''} ${props.client.last_name ?? ''}`
  return base.trim() || 'Cliente'
})

const initials = computed(() => {
  if (!props.client) return 'CL'
  const base = displayName.value
  const parts = base.trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return 'CL'
  const first = parts[0]?.charAt(0) ?? ''
  const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : ''
  return `${first}${last}`.toUpperCase()
})

const genderLabel = computed(() => {
  const value = props.client?.gender
  if (!value) return '-'
  return {
    female: 'Femenino',
    male: 'Masculino',
    other: 'Otro',
    unspecified: 'No especifica',
  }[value] ?? value
})

function dayLabel(value: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '--'
  return new Intl.DateTimeFormat('es-PE', { day: '2-digit' }).format(date)
}

function monthLabel(value: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return new Intl.DateTimeFormat('es-PE', { month: 'short' }).format(date)
}
</script>

<template>
  <div class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <div>
          <h3>Detalle del cliente</h3>
          <p class="panel-subtitle">Historial de atenciones, ventas y datos principales.</p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <section v-if="props.client" class="detail-panel">
        <div class="profile-card">
          <div class="profile-header">
            <span class="avatar">{{ initials }}</span>
            <div>
              <h3 class="profile-name">{{ displayName }}</h3>
              <p class="profile-phone">{{ props.client.phone ?? 'Sin celular' }}</p>
            </div>
          </div>
          <div class="profile-actions">
            <button class="btn-ghost" type="button" @click="emit('edit', props.client.id)">
              Editar cliente
            </button>
            <button
              class="btn-whatsapp"
              type="button"
              :disabled="!props.client.phone"
              @click="emit('whatsapp', props.client)"
            >
              WhatsApp
            </button>
          </div>

          <div class="info-list">
            <div class="info-row">
              <span>Correo</span>
              <strong>{{ props.client.email ?? '-' }}</strong>
            </div>
            <div class="info-row">
              <span>Documento</span>
              <strong>{{ props.client.dni ?? '-' }}</strong>
            </div>
            <div class="info-row">
              <span>Cumpleanos</span>
              <strong>{{ props.client.birth_date ? formatDate(props.client.birth_date) : '-' }}</strong>
            </div>
            <div class="info-row">
              <span>Genero</span>
              <strong>{{ genderLabel }}</strong>
            </div>
            <div class="info-row">
              <span>Cliente desde</span>
              <strong>{{ props.client.created_at ? formatDate(props.client.created_at) : '-' }}</strong>
            </div>
            <div class="info-row">
              <span>Direccion</span>
              <strong>{{ props.client.address ?? '-' }}</strong>
            </div>
          </div>

          <div class="notes-block">
            <span>Notas</span>
            <p>{{ props.client.notes ?? 'Agrega notas para personalizar la atencion.' }}</p>
          </div>
        </div>

        <div class="activity-card">
          <div class="stats-grid">
            <div class="stat-card">
              <span>Total de ventas</span>
              <strong>{{ formatCurrency(props.stats.totalSales) }}</strong>
            </div>
            <div class="stat-card">
              <span>Reservas</span>
              <strong>{{ props.stats.serviceCount }}</strong>
            </div>
            <div class="stat-card">
              <span>Ticket promedio</span>
              <strong>{{ formatCurrency(props.stats.avgTicket) }}</strong>
            </div>
          </div>

          <div class="activity-list">
            <h4>Actividad</h4>
            <div v-if="!activities.length" class="activity-empty">
              <p>Sin atenciones registradas aun.</p>
            </div>
            <div v-for="service in activities" :key="service.id" class="activity-row">
              <div class="activity-date">
                <span class="day">{{ dayLabel(service.performed_at) }}</span>
                <span class="month">{{ monthLabel(service.performed_at) }}</span>
              </div>
              <div class="activity-main">
                <span class="activity-title">{{ service.service?.name ?? 'Servicio' }}</span>
                <span class="activity-sub">
                  {{ formatDateLong(service.performed_at) }} · {{ formatTime(service.performed_at) }} ·
                  {{ service.stylist?.user?.name ?? 'Estilista' }}
                </span>
              </div>
              <span class="activity-price">{{ formatCurrency(Number(service.total_amount ?? 0)) }}</span>
            </div>
          </div>
        </div>
      </section>

      <div v-else class="empty-state">
        <p>No hay cliente seleccionado.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 20, 0.5);
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 40;
}

.modal-card {
  width: min(980px, 100%);
  max-height: 92vh;
  overflow: auto;
  background: var(--panel-bg);
  border-radius: var(--radius-xl);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 20px 45px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 18px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.close-button {
  border: none;
  background: rgba(17, 15, 20, 0.08);
  width: 36px;
  height: 36px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1rem;
}

.detail-panel {
  display: grid;
  grid-template-columns: minmax(220px, 0.45fr) minmax(320px, 1fr);
  gap: 18px;
}

.profile-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: var(--radius-lg);
  padding: 18px;
  border: 1px solid rgba(17, 15, 20, 0.1);
  display: grid;
  gap: 16px;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: #1b1133;
  background: radial-gradient(circle at top, rgba(214, 106, 86, 0.35), rgba(255, 255, 255, 0.95));
  border: 1px solid rgba(214, 106, 86, 0.35);
}

.profile-name {
  margin: 0;
  font-size: 1.1rem;
}

.profile-phone {
  margin: 4px 0 0;
  color: var(--ink-muted);
  font-size: 0.85rem;
}

.profile-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.profile-actions > button {
  height: 36px;
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

.info-list {
  display: grid;
  gap: 10px;
}

.info-row {
  display: grid;
  grid-template-columns: 110px 1fr;
  gap: 10px;
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.info-row strong {
  color: var(--ink-strong);
  font-weight: 600;
  text-align: left;
  word-break: break-word;
}

.notes-block {
  background: rgba(17, 15, 20, 0.04);
  border-radius: var(--radius-md);
  padding: 12px;
  display: grid;
  gap: 6px;
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.notes-block span {
  font-weight: 600;
  color: var(--ink-strong);
}

.activity-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: var(--radius-lg);
  padding: 18px;
  border: 1px solid rgba(17, 15, 20, 0.1);
  display: grid;
  gap: 18px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.stat-card {
  border-radius: var(--radius-md);
  padding: 12px 14px;
  background: rgba(248, 243, 238, 0.9);
  border: 1px solid rgba(17, 15, 20, 0.08);
  display: grid;
  gap: 4px;
}

.stat-card span {
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.stat-card strong {
  font-size: 1.1rem;
}

.activity-list {
  display: grid;
  gap: 12px;
  max-height: 420px;
  overflow-y: auto;
  padding-right: 4px;
}

.activity-list h4 {
  margin: 0;
}

.activity-row {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(17, 15, 20, 0.06);
}

.activity-date {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: rgba(17, 15, 20, 0.06);
  display: grid;
  place-items: center;
  font-weight: 600;
  color: var(--ink-strong);
  text-transform: capitalize;
}

.activity-date .day {
  font-size: 1rem;
}

.activity-date .month {
  font-size: 0.7rem;
  color: var(--ink-muted);
}

.activity-main {
  display: grid;
  gap: 4px;
}

.activity-title {
  font-weight: 600;
}

.activity-sub {
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.activity-price {
  font-weight: 600;
  color: var(--accent);
}

.activity-empty {
  padding: 18px;
  border-radius: var(--radius-md);
  border: 1px dashed rgba(23, 20, 26, 0.2);
  text-align: center;
  color: var(--ink-muted);
}

.empty-state {
  padding: 32px;
  border-radius: var(--radius-lg);
  border: 1px dashed rgba(23, 20, 26, 0.2);
  text-align: center;
  color: var(--ink-muted);
}

@media (max-width: 980px) {
  .detail-panel {
    grid-template-columns: 1fr;
  }
}
</style>
