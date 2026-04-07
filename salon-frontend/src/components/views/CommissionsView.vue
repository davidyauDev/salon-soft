<script setup lang="ts">
import { onMounted } from 'vue'
import { useCommissions } from '../../composables/useCommissions'
import { formatCurrency, formatDateTime } from '../../utils/format'

const commissions = useCommissions()

onMounted(() => {
  commissions.load()
})
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Comisiones</p>
        <h2>Resumen por estilista</h2>
      </div>
    </header>

    <section class="panel">
      <h3>Totales del periodo</h3>
      <div class="summary-grid">
        <div
          v-for="(row, index) in commissions.summary.value"
          :key="row.stylist ?? index"
          class="summary-card"
        >
          <p class="summary-name">{{ row.stylist ?? 'Estilista' }}</p>
          <p class="summary-total">{{ formatCurrency(row.total) }}</p>
        </div>
      </div>
    </section>

    <section class="panel">
      <h3>Detalle de comisiones</h3>
      <div class="table">
        <div class="table-row table-head">
          <span>Fecha</span>
          <span>Estilista</span>
          <span>Servicio</span>
          <span>Monto</span>
        </div>
        <div v-for="entry in commissions.commissions.value" :key="entry.id" class="table-row">
          <span>{{ formatDateTime(entry.calculated_at) }}</span>
          <span>{{ entry.stylist?.user?.name ?? 'Estilista' }}</span>
          <span>{{ entry.record?.service?.name ?? 'Servicio' }}</span>
          <span>{{ formatCurrency(entry.amount) }}</span>
        </div>
      </div>
    </section>
  </section>
</template>

<style scoped>
.view {
  display: grid;
  gap: 24px;
}

.summary-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.summary-card {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 16px;
  padding: 14px;
  border: 1px solid rgba(17, 15, 20, 0.05);
}

.summary-name {
  margin: 0;
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.summary-total {
  margin: 6px 0 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1.2fr 0.8fr;
  gap: 12px;
}
</style>
