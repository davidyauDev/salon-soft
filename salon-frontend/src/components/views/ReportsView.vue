<script setup lang="ts">
import { onMounted } from 'vue'
import { useReports } from '../../composables/useReports'
import { formatCurrency } from '../../utils/format'

const reports = useReports()

onMounted(() => {
  reports.load()
})
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Reportes</p>
        <h2>Indicadores clave</h2>
      </div>
    </header>

    <div class="summary-grid">
      <article class="summary-card">
        <p>Ventas</p>
        <h3>{{ formatCurrency(reports.salesTotal.value) }}</h3>
      </article>
      <article class="summary-card">
        <p>Consumo de insumos</p>
        <h3>{{ formatCurrency(reports.consumptionTotal.value) }}</h3>
      </article>
    </div>

    <section class="panel">
      <h3>Stock bajo</h3>
      <div class="table">
        <div class="table-row table-head">
          <span>Item</span>
          <span>Stock</span>
          <span>Minimo</span>
        </div>
        <div v-for="item in reports.stockLow.value" :key="item.id" class="table-row">
          <span>{{ item.name }}</span>
          <span>{{ item.stock_total.toFixed(2) }} {{ item.base_unit }}</span>
          <span>{{ item.stock_minimum }} {{ item.base_unit }}</span>
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.summary-card {
  background: var(--card);
  border-radius: 20px;
  padding: 18px;
  border: 1px solid rgba(17, 15, 20, 0.08);
  box-shadow: var(--shadow-soft);
}

.summary-card p {
  margin: 0;
  color: var(--ink-muted);
  font-size: 0.85rem;
}

.summary-card h3 {
  margin: 6px 0 0;
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 12px;
}
</style>
