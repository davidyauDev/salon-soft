<script setup lang="ts">
import type { ServiceRecord } from '../../composables/useServiceRecords'
import { formatCurrency, formatDate } from '../../utils/format'

defineProps<{
  rows: ServiceRecord[]
  loading: boolean
  emptyText: string
}>()
</script>

<template>
  <section class="table-card">
    <div v-if="loading" class="table-empty">Cargando citas...</div>
    <div v-else-if="!rows.length" class="table-empty">{{ emptyText }}</div>
    <div v-else class="table">
      <div class="table-row table-head">
        <span>ID DE CITA</span>
        <span>ESTADO</span>
        <span>CLIENTE</span>
        <span>SERVICIO</span>
        <span>COSTO</span>
        <span>FECHA DE CITA</span>
      </div>
      <div v-for="row in rows" :key="row.id" class="table-row">
        <span class="id-cell" data-label="ID de cita">{{ row.id }}</span>
        <span class="status-chip" data-label="Estado">{{ row.status ?? 'completed' }}</span>
        <span data-label="Cliente">{{ row.client?.full_name ?? 'Sin cliente' }}</span>
        <span data-label="Servicio">{{ row.service?.name ?? 'Sin servicio' }}</span>
        <span class="amount" data-label="Costo">{{ formatCurrency(Number(row.total_amount)) }}</span>
        <span data-label="Fecha de cita">{{ formatDate(row.performed_at) }}</span>
      </div>
    </div>
  </section>
</template>

<style scoped>
.table-card {
  border: 1px solid rgba(25, 25, 25, 0.08);
  border-radius: 18px;
  background: rgba(255, 253, 251, 0.92);
  overflow: hidden;
}

.table {
  display: grid;
}

.table-row {
  display: grid;
  grid-template-columns: 0.75fr 1fr 1.45fr 1.5fr 0.9fr 1fr;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  border-top: 1px solid rgba(25, 25, 25, 0.06);
  font-size: 0.92rem;
}

.table-head {
  background: #f4efe7;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  color: var(--ink-muted);
  border-top: none;
}

.id-cell,
.amount {
  font-weight: 700;
  color: var(--ink-strong);
}

.status-chip {
  width: fit-content;
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--accent-soft);
  color: #0b534b;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.table-empty {
  padding: 48px 18px;
  color: var(--ink-muted);
  font-size: 0.95rem;
  text-align: center;
}

@media (max-width: 760px) {
  .table-head {
    display: none;
  }

  .table-row {
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    padding: 14px;
    align-items: start;
  }

  .table-row > * {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
  }

  .table-row > *::before {
    content: attr(data-label);
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--ink-muted);
    font-weight: 600;
  }

  .table-row > :first-child,
  .table-row > :nth-child(2) {
    grid-column: span 1;
  }
}
</style>
