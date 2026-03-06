<script setup lang="ts">
import type { ServiceRecord } from '../../composables/useServiceRecords'
import { formatCurrency, formatDate } from '../../utils/format'

const props = defineProps<{
  rows: ServiceRecord[]
  loading: boolean
  emptyText: string
}>()
</script>

<template>
  <section class="table-card">
    <div v-if="props.loading" class="table-empty">Cargando servicios...</div>
    <div v-else-if="!props.rows.length" class="table-empty">{{ props.emptyText }}</div>
    <div v-else class="table">
      <div class="table-row table-head">
        <span>ID VENTA</span>
        <span>CLIENTE</span>
        <span>SERVICIOS</span>
        <span>FECHA DE VENTA</span>
        <span>MONTO</span>
        <span class="actions-col"> </span>
      </div>
      <div v-for="row in props.rows" :key="row.id" class="table-row">
        <span class="id-cell">{{ row.id }}</span>
        <span>{{ row.client?.full_name ?? 'Sin cliente' }}</span>
        <div class="chips">
          <span v-if="row.service?.name" class="chip">{{ row.service.name }}</span>
        </div>
        <span>{{ formatDate(row.performed_at) }}</span>
        <span class="amount">{{ formatCurrency(Number(row.total_amount)) }}</span>
        <button class="icon-btn" type="button" aria-label="Detalle">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M7 7h10v2H7V7Zm0 4h10v2H7v-2Zm0 4h6v2H7v-2Z"
            />
          </svg>
        </button>
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
  grid-template-columns: 0.7fr 1.4fr 1.6fr 1fr 0.8fr auto;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  border-top: 1px solid rgba(17, 15, 20, 0.06);
  font-size: 0.92rem;
}

.table-head {
  background: #f6f7f9;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 600;
  border-top: none;
}

.id-cell {
  color: #5a4bff;
  font-weight: 700;
}

.amount {
  font-weight: 700;
}

.chips {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.chip {
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(90, 75, 255, 0.08);
  color: #4f46e5;
  font-size: 0.8rem;
  font-weight: 600;
}

.icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: #6f6770;
}

.table-empty {
  padding: 18px;
  color: #6f6770;
  font-size: 0.9rem;
  text-align: center;
}

.actions-col {
  text-align: right;
}
</style>
