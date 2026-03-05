<script setup lang="ts">
import { computed, onMounted } from 'vue'
import KpiCard from '../dashboard/KpiCard.vue'
import { useDashboard } from '../../composables/useDashboard'

const dashboard = useDashboard()

onMounted(() => {
  dashboard.load()
})

const kpis = computed(() => {
  const data = dashboard.data.value
  if (!data) {
    return []
  }

  return [
    {
      title: 'Servicios hoy',
      value: `${data.kpis.services_today}`,
      change: 'Total del dia',
      trend: 'up' as const,
    },
    {
      title: 'Ventas en productos',
      value: `S/ ${data.kpis.sales_today.toFixed(2)}`,
      change: 'Total del dia',
      trend: 'up' as const,
    },
    {
      title: 'Consumo de insumos',
      value: `S/ ${data.kpis.consumption_cost_today.toFixed(2)}`,
      change: 'Costo del dia',
      trend: 'down' as const,
    },
    {
      title: 'Comisiones',
      value: `S/ ${data.kpis.commissions_today.toFixed(2)}`,
      change: 'Corte del dia',
      trend: 'neutral' as const,
    },
  ]
})

const alerts = computed(() => dashboard.data.value?.alerts ?? [])
const services = computed(() => dashboard.data.value?.recent_services ?? [])
const commissions = computed(() => {
  return (dashboard.data.value?.top_commissions ?? []).map((item) => {
    const progress = Math.min(100, Math.round((item.total / 400) * 100))
    return {
      stylist: item.stylist ?? 'Estilista',
      total: `S/ ${item.total.toFixed(2)}`,
      progress,
    }
  })
})
</script>

<template>
  <section class="dashboard">
    <p v-if="dashboard.loading.value" class="loading-state">Cargando datos...</p>
    <div v-else class="kpi-grid">
      <KpiCard
        v-for="kpi in kpis"
        :key="kpi.title"
        :title="kpi.title"
        :value="kpi.value"
        :change="kpi.change"
        :trend="kpi.trend"
      />
    </div>

    <div class="dashboard-columns">
      <section class="panel panel-primary">
        <header class="panel-header">
          <div>
            <p class="panel-eyebrow">Flujo diario</p>
            <h2>Servicios programados</h2>
          </div>
          <button type="button">Abrir agenda</button>
        </header>
        <div class="panel-body">
          <div v-for="entry in services" :key="entry.id" class="service-row">
            <div class="service-time">
              {{ entry.performed_at ? entry.performed_at.slice(11, 16) : '--:--' }}
            </div>
            <div class="service-main">
              <p class="service-client">{{ entry.client ?? 'Sin cliente' }}</p>
              <p class="service-detail">{{ entry.service ?? 'Servicio' }}</p>
            </div>
            <span class="service-stylist">{{ entry.stylist ?? 'Estilista' }}</span>
          </div>
        </div>
      </section>

      <section class="panel panel-secondary">
        <header class="panel-header">
          <div>
            <p class="panel-eyebrow">Inventario</p>
            <h2>Alertas de stock</h2>
          </div>
          <button type="button">Ver kardex</button>
        </header>
        <div class="panel-body">
          <div v-for="alert in alerts" :key="alert.id" class="alert-row">
            <div>
              <p class="alert-item">{{ alert.name }}</p>
              <p class="alert-stock">
                {{ alert.stock_total.toFixed(2) }} {{ alert.base_unit }} disponibles
              </p>
            </div>
            <span class="alert-status">
              {{ alert.stock_total <= alert.stock_minimum ? 'Critico' : 'Bajo' }}
            </span>
          </div>
        </div>
      </section>
    </div>

    <section class="panel panel-wide">
      <header class="panel-header">
        <div>
          <p class="panel-eyebrow">Equipo</p>
          <h2>Comisiones en curso</h2>
        </div>
        <button type="button">Cerrar periodo</button>
      </header>
      <div class="commission-grid">
        <article v-for="item in commissions" :key="item.stylist" class="commission-card">
          <div class="commission-head">
            <div>
              <p class="commission-name">{{ item.stylist }}</p>
              <p class="commission-total">{{ item.total }}</p>
            </div>
            <span class="commission-chip">Meta 90%</span>
          </div>
          <div class="commission-bar">
            <span :style="{ width: `${item.progress}%` }" />
          </div>
          <p class="commission-foot">Avance {{ item.progress }}%</p>
        </article>
      </div>
    </section>
  </section>
</template>

<style scoped>
.dashboard {
  display: grid;
  gap: 24px;
}

.loading-state {
  margin: 0;
  font-size: 0.9rem;
  color: var(--ink-muted);
}

.kpi-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.dashboard-columns {
  display: grid;
  gap: 24px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.panel {
  border-radius: 24px;
  padding: 20px 22px;
  background: var(--card);
  border: 1px solid rgba(17, 15, 20, 0.08);
  box-shadow: var(--shadow-soft);
  display: grid;
  gap: 18px;
}

.panel-primary {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(255, 245, 236, 0.8));
}

.panel-secondary {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(247, 238, 244, 0.9));
}

.panel-wide {
  background: linear-gradient(120deg, rgba(255, 255, 255, 0.98), rgba(240, 246, 248, 0.92));
}

.panel-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.panel-header h2 {
  margin: 6px 0 0;
  font-size: 1.3rem;
  color: var(--ink-strong);
}

.panel-header button {
  border: none;
  background: rgba(17, 15, 20, 0.07);
  padding: 8px 12px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
}

.panel-eyebrow {
  margin: 0;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: var(--ink-muted);
}

.service-row,
.alert-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(17, 15, 20, 0.05);
}

.service-row + .service-row,
.alert-row + .alert-row {
  margin-top: 10px;
}

.service-time {
  font-weight: 700;
  color: var(--ink-strong);
  width: 56px;
}

.service-client {
  margin: 0;
  font-weight: 600;
}

.service-detail {
  margin: 2px 0 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.service-stylist {
  margin-left: auto;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.alert-item {
  margin: 0;
  font-weight: 600;
}

.alert-stock {
  margin: 2px 0 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.alert-status {
  margin-left: auto;
  font-size: 0.75rem;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(214, 106, 86, 0.15);
  color: var(--accent);
}

.commission-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.commission-card {
  border-radius: 18px;
  padding: 16px;
  background: white;
  border: 1px solid rgba(17, 15, 20, 0.08);
  display: grid;
  gap: 12px;
}

.commission-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.commission-name {
  margin: 0;
  font-weight: 600;
}

.commission-total {
  margin: 4px 0 0;
  font-family: var(--font-display);
  font-size: 1.1rem;
}

.commission-chip {
  font-size: 0.7rem;
  background: rgba(17, 15, 20, 0.05);
  padding: 4px 8px;
  border-radius: 999px;
}

.commission-bar {
  height: 8px;
  background: rgba(17, 15, 20, 0.08);
  border-radius: 999px;
  overflow: hidden;
}

.commission-bar span {
  display: block;
  height: 100%;
  background: var(--accent);
  border-radius: 999px;
}

.commission-foot {
  margin: 0;
  font-size: 0.75rem;
  color: var(--ink-muted);
}
</style>
