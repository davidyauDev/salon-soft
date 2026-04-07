<script setup lang="ts">
import type { ServiceRecord } from '../../composables/useServiceRecords'
import { formatCurrency, formatDate } from '../../utils/format'

const emit = defineEmits<{
  (e: 'detail', row: ServiceRecord): void
}>()

defineProps<{
  rows: ServiceRecord[]
  loading: boolean
  emptyText: string
}>()

function productCount(row: ServiceRecord): number {
  return row.sale?.items?.length ?? 0
}
</script>

<template>
  <section class="table-card">
    <div v-if="loading" class="table-empty">Cargando ventas express...</div>
    <div v-else-if="!rows.length" class="table-empty">{{ emptyText }}</div>
    <div v-else class="table">
      <div class="table-row table-head">
        <span>ID VENTA</span>
        <span>ESTADO</span>
        <span>CLIENTE</span>
        <span>SERVICIO</span>
        <span>STAFF</span>
        <span>FECHA</span>
        <span>MONTO</span>
        <span> </span>
      </div>
      <div v-for="row in rows" :key="row.id" class="table-row">
        <span class="id-cell">{{ row.id }}</span>
        <span class="status-chip">{{ row.status ?? 'paid' }}</span>
        <span>{{ row.client?.full_name ?? 'Sin cliente' }}</span>
        <div class="service-cell">
          <span>{{ row.service?.name ?? 'Sin servicio' }}</span>
          <small v-if="productCount(row)">+ {{ productCount(row) }} producto(s)</small>
        </div>
        <span>{{ row.stylist?.user?.name ?? 'Sin staff' }}</span>
        <span>{{ formatDate(row.performed_at) }}</span>
        <span class="amount">{{ formatCurrency(Number(row.grand_total ?? row.total_amount)) }}</span>
        <button class="icon-btn" type="button" aria-label="Detalle" @click="emit('detail', row)">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 7h10v2H7V7Zm0 4h10v2H7v-2Zm0 4h6v2H7v-2Z" />
          </svg>
        </button>
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
  grid-template-columns: 0.72fr 0.95fr 1.4fr 1.45fr 1.1fr 1fr 0.85fr auto;
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

.service-cell {
  display: grid;
  gap: 2px;
}

.service-cell small {
  color: var(--ink-muted);
  font-size: 0.76rem;
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

.icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fffdfa;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: var(--ink-muted);
}

.table-empty {
  padding: 48px 18px;
  color: var(--ink-muted);
  font-size: 0.95rem;
  text-align: center;
}
</style>
