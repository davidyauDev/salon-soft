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
        <span class="id-cell">{{ row.id }}</span>
        <span class="status-chip">{{ row.status ?? 'completed' }}</span>
        <span>{{ row.client?.full_name ?? 'Sin cliente' }}</span>
        <span>{{ row.service?.name ?? 'Sin servicio' }}</span>
        <span class="amount">{{ formatCurrency(Number(row.total_amount)) }}</span>
        <span>{{ formatDate(row.performed_at) }}</span>
      </div>
    </div>
  </section>
</template>

<style scoped>
.table-card {
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 14px;
  background: #fff;
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
  border-top: 1px solid rgba(17, 15, 20, 0.06);
  font-size: 0.92rem;
}

.table-head {
  background: #f6f8fc;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  color: #6a7387;
  border-top: none;
}

.id-cell,
.amount {
  font-weight: 700;
  color: #20304a;
}

.status-chip {
  width: fit-content;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(90, 75, 255, 0.08);
  color: #4f46e5;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.table-empty {
  padding: 48px 18px;
  color: #97a1b2;
  font-size: 0.95rem;
  text-align: center;
}
</style>
