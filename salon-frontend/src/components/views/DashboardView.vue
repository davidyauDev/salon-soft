<script setup lang="ts">
import { computed, onMounted } from 'vue'
import KpiCard from '../dashboard/KpiCard.vue'
import { useDashboard } from '../../composables/useDashboard'
import { formatCurrency } from '../../utils/format'

type MetricKind = 'count' | 'money'

const dashboard = useDashboard()

onMounted(() => {
  dashboard.load()
})

const operationalLabels = ['Servicios', 'Ventas', 'Insumos', 'Comisiones'] as const
const donutColors = ['#0f766e', '#111111', '#7ba79f', '#bfd9d4'] as const

const demoDashboard = {
  kpis: {
    services_today: 24,
    sales_today: 8450,
    consumption_cost_today: 1240,
    commissions_today: 1680,
  },
  alerts: [
    { id: 1, name: 'Tinte 8.8', stock_total: 2, stock_minimum: 6, base_unit: 'u' },
    { id: 2, name: 'Shampoo neutro', stock_total: 5, stock_minimum: 10, base_unit: 'u' },
    { id: 3, name: 'Guantes talla M', stock_total: 18, stock_minimum: 20, base_unit: 'u' },
    { id: 4, name: 'Oxidante 20 vol', stock_total: 7, stock_minimum: 8, base_unit: 'u' },
  ],
  recent_services: [
    {
      id: 1,
      performed_at: '2026-04-07T08:20:00',
      client: 'María Torres',
      service: 'Color y brushing',
      stylist: 'Ana',
    },
    {
      id: 2,
      performed_at: '2026-04-07T09:15:00',
      client: 'Lucía Rojas',
      service: 'Corte + hidratación',
      stylist: 'Karla',
    },
    {
      id: 3,
      performed_at: '2026-04-07T11:30:00',
      client: 'Valeria Paredes',
      service: 'Peinado social',
      stylist: 'Ana',
    },
    {
      id: 4,
      performed_at: '2026-04-07T14:05:00',
      client: 'Sofía Vega',
      service: 'Uñas y cejas',
      stylist: 'Diana',
    },
    {
      id: 5,
      performed_at: '2026-04-07T16:45:00',
      client: 'Camila Salas',
      service: 'Tratamiento capilar',
      stylist: 'Karla',
    },
  ],
  top_commissions: [
    { stylist: 'Ana', total: 980 },
    { stylist: 'Karla', total: 760 },
    { stylist: 'Diana', total: 640 },
    { stylist: 'Paola', total: 480 },
  ],
}

const dataSource = computed(() => dashboard.data.value ?? demoDashboard)

const kpis = computed(() => {
  const data = dataSource.value

  const activeStylists = new Set(
    (data.recent_services ?? [])
      .map((entry) => entry.stylist)
      .filter((value): value is string => Boolean(value)),
  ).size

  const criticalAlerts = data.alerts.filter(
    (alert) => alert.stock_total <= alert.stock_minimum,
  ).length

  return [
    {
      title: 'Servicios hoy',
      value: `${data.kpis.services_today}`,
      change: 'Ritmo del salon',
      trend: 'up' as const,
    },
    {
      title: 'Ventas en productos',
      value: formatCurrency(data.kpis.sales_today),
      change: 'Caja del dia',
      trend: 'up' as const,
    },
    {
      title: 'Consumo de insumos',
      value: formatCurrency(data.kpis.consumption_cost_today),
      change: 'Costo del dia',
      trend: 'down' as const,
    },
    {
      title: 'Comisiones',
      value: formatCurrency(data.kpis.commissions_today),
      change: 'Corte operativo',
      trend: 'neutral' as const,
    },
    {
      title: 'Alertas criticas',
      value: `${criticalAlerts}`,
      change: 'Inventario sensible',
      trend: criticalAlerts > 0 ? ('down' as const) : ('neutral' as const),
    },
    {
      title: 'Estilistas activos',
      value: `${activeStylists}`,
      change: 'Equipo visible',
      trend: 'neutral' as const,
    },
  ]
})

const operationalMetrics = computed(() => {
  const data = dataSource.value

  return [
    {
      label: operationalLabels[0],
      value: data.kpis.services_today,
      kind: 'count' as MetricKind,
    },
    {
      label: operationalLabels[1],
      value: data.kpis.sales_today,
      kind: 'money' as MetricKind,
    },
    {
      label: operationalLabels[2],
      value: data.kpis.consumption_cost_today,
      kind: 'money' as MetricKind,
    },
    {
      label: operationalLabels[3],
      value: data.kpis.commissions_today,
      kind: 'money' as MetricKind,
    },
  ]
})

const operationalChart = computed(() => {
  const metrics = operationalMetrics.value
  const maxValue = Math.max(1, ...metrics.map((metric) => metric.value))
  const totalValue = metrics.reduce((sum, metric) => sum + metric.value, 0)
  const dominantIndex = metrics.length
    ? metrics.findIndex((metric) => metric.value === maxValue)
    : -1

  return {
    totalValue,
    dominantLabel: dominantIndex >= 0 ? metrics[dominantIndex]?.label ?? 'Operacion' : 'Operacion',
    bars: metrics.map((metric, index) => ({
      ...metric,
      percent: Math.max(18, Math.round((metric.value / maxValue) * 100)),
      color: donutColors[index],
      display: metric.kind === 'money' ? formatCurrency(metric.value) : `${metric.value}`,
    })),
  }
})

const mixChart = computed(() => {
  const metrics = operationalMetrics.value
  const rawValues = metrics.map((metric) => Math.max(metric.value, 0))
  const normalizedValues = metrics.map((metric) =>
    metric.kind === 'count' ? metric.value * 300 : metric.value,
  )
  const fallback = normalizedValues.every((value) => value === 0) ? [1, 1, 1, 1] : normalizedValues
  const fallbackTotal = fallback.reduce((sum, value) => sum + value, 0)
  const activeTotal = rawValues.reduce((sum, value) => sum + value, 0)

  let cursor = 0
  const slices = metrics.map((metric, index) => {
    const amount = fallback[index] ?? 0
    const portion = fallbackTotal ? (amount / fallbackTotal) * 100 : 0
    const start = cursor
    cursor += portion
    return {
      label: metric.label,
      kind: metric.kind,
      rawValue: metric.value,
      display:
        metric.kind === 'money'
          ? formatCurrency(metric.value)
          : `${metric.value} servicios`,
      percent: fallbackTotal ? Math.round((amount / fallbackTotal) * 100) : 0,
      start,
      end: cursor,
      color: donutColors[index],
    }
  })

  const dominant = slices.reduce((winner, slice) => {
    if (!winner) return slice
    return slice.rawValue > winner.rawValue ? slice : winner
  }, null as (typeof slices)[number] | null)

  const donutStyle = {
    background: `conic-gradient(${slices
      .map((slice) => `${slice.color} ${slice.start}% ${slice.end}%`)
      .join(', ')})`,
  }

  return {
    slices,
    donutStyle,
    total: activeTotal,
    segmentCount: slices.length,
    dominantLabel: dominant?.label ?? 'Operacion',
  }
})

const commissionBars = computed(() => {
  const items = dataSource.value.top_commissions ?? []
  const maxValue = Math.max(1, ...items.map((item) => item.total))

  return items.slice(0, 4).map((item) => ({
    name: item.stylist ?? 'Estilista',
    total: item.total,
    totalLabel: formatCurrency(item.total),
    percent: Math.max(12, Math.round((item.total / maxValue) * 100)),
  }))
})

const stockPressure = computed(() => {
  const items = dataSource.value.alerts ?? []

  return items
    .slice()
    .sort((a, b) => a.stock_total / Math.max(1, a.stock_minimum) - b.stock_total / Math.max(1, b.stock_minimum))
    .slice(0, 5)
    .map((alert) => {
      const ratio = (alert.stock_total / Math.max(1, alert.stock_minimum)) * 100
      const percent = Math.max(8, Math.min(160, Math.round(ratio)))
      const level =
        alert.stock_total <= alert.stock_minimum
          ? {
              label: 'Critico',
              className: 'critical',
            }
          : ratio < 130
            ? {
                label: 'Bajo',
                className: 'warning',
              }
            : {
                label: 'Estable',
                className: 'stable',
              }

      return {
        id: alert.id,
        name: alert.name,
        stockLabel: `${alert.stock_total.toFixed(1)} ${alert.base_unit}`,
        minimumLabel: `Min. ${alert.stock_minimum.toFixed(1)} ${alert.base_unit}`,
        percent,
        level,
      }
    })
})

const serviceFeed = computed(() =>
  (dataSource.value.recent_services ?? []).slice(0, 6).map((entry) => ({
    id: entry.id,
    time: entry.performed_at ? entry.performed_at.slice(11, 16) : '--:--',
    client: entry.client ?? 'Sin cliente',
    service: entry.service ?? 'Servicio',
    stylist: entry.stylist ?? 'Estilista',
  })),
)

const topStylist = computed(() => dataSource.value.top_commissions?.[0]?.stylist ?? 'Estilista')

const modeLabel = computed(() => (dashboard.data.value ? 'Datos reales' : 'Vista demo'))
</script>

<template>
  <section class="dashboard">
    <header class="dashboard-hero">
      <div class="hero-copy">
        <p class="hero-eyebrow">Resumen operativo</p>
        <h1>Centro de control del salon</h1>
        <p class="hero-description">
          Una lectura clara de citas, caja, inventario y equipo para operar con ritmo real.
        </p>
        <div class="hero-pills">
          <span>Actualizado hoy</span>
          <span>{{ modeLabel }}</span>
          <span>{{ mixChart.dominantLabel }}</span>
        </div>
      </div>

      <div class="hero-rail">
        <div class="hero-stat">
          <span>Pico operativo</span>
          <strong>{{ mixChart.dominantLabel }}</strong>
          <small>{{ mixChart.total }} unidades visuales</small>
        </div>
        <div class="hero-stat">
          <span>Equipo lider</span>
          <strong>{{ topStylist }}</strong>
          <small>Comision mas alta</small>
        </div>
        <div class="hero-stat">
          <span>Actividad</span>
          <strong>{{ operationalChart.totalValue ? 'En curso' : 'Inactiva' }}</strong>
          <small>{{ stockPressure.length }} alertas de inventario</small>
        </div>
      </div>
    </header>

    <div class="kpi-grid">
      <KpiCard
        v-for="kpi in kpis"
        :key="kpi.title"
        :title="kpi.title"
        :value="kpi.value"
        :change="kpi.change"
        :trend="kpi.trend"
      />
    </div>

    <div class="dashboard-grid">
        <section class="panel panel-chart panel-span-8">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Balance operativo</p>
              <h2>Comparativo del dia</h2>
            </div>
            <button type="button">Hoy</button>
          </header>

          <div class="chart-meta">
            <div class="chart-stat">
              <span class="chart-stat-label">Volumen</span>
              <strong>{{ operationalChart.totalValue ? formatCurrency(operationalChart.totalValue) : 'S/ 0.00' }}</strong>
            </div>
            <div class="chart-stat">
              <span class="chart-stat-label">Categoria dominante</span>
              <strong>{{ operationalChart.dominantLabel }}</strong>
            </div>
            <div class="chart-stat">
              <span class="chart-stat-label">Mix</span>
              <strong>{{ mixChart.total }} puntos</strong>
            </div>
          </div>

          <div class="bar-chart">
            <div class="bar-chart-canvas">
              <div v-for="bar in operationalChart.bars" :key="bar.label" class="bar-column">
                <div class="bar-track">
                  <span class="bar-fill" :style="{ height: `${bar.percent}%`, background: `linear-gradient(180deg, ${bar.color}, #111111)` }" />
                </div>
                <div class="bar-caption">
                  <strong>{{ bar.label }}</strong>
                  <span>{{ bar.display }}</span>
                </div>
              </div>
            </div>
            <div class="bar-legend">
              <span
                v-for="bar in operationalChart.bars"
                :key="`${bar.label}-legend`"
                class="legend-line"
              >
                <i :style="{ background: bar.color }"></i>
                {{ bar.label }}
              </span>
            </div>
          </div>
        </section>

        <aside class="panel panel-donut panel-span-4">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Mix del dia</p>
              <h2>Distribucion por rubro</h2>
            </div>
            <button type="button">Leer mix</button>
          </header>

          <div class="donut-layout">
            <div class="donut-ring" :style="mixChart.donutStyle">
              <div class="donut-center">
                <strong>{{ mixChart.segmentCount }}</strong>
                <span>rubros</span>
              </div>
            </div>

            <div class="donut-legend">
              <div v-for="slice in mixChart.slices" :key="slice.label" class="legend-item">
                <span class="legend-swatch" :style="{ background: slice.color }"></span>
                <div class="legend-copy">
                  <p>{{ slice.label }}</p>
                  <small>{{ slice.display }}</small>
                </div>
                <strong>{{ slice.percent }}%</strong>
              </div>
            </div>
          </div>
        </aside>

        <section class="panel panel-bars panel-span-5">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Equipo</p>
              <h2>Comisiones por estilista</h2>
            </div>
            <button type="button">Periodo</button>
          </header>

          <div class="bar-list">
            <article v-for="item in commissionBars" :key="item.name" class="bar-row">
              <div class="bar-head">
                <span>{{ item.name }}</span>
                <strong>{{ item.totalLabel }}</strong>
              </div>
              <div class="bar-track bar-track-horizontal">
                <span class="bar-fill bar-fill-horizontal" :style="{ width: `${item.percent}%` }" />
              </div>
              <small>{{ item.percent }}% del lider</small>
            </article>
          </div>
        </section>

        <section class="panel panel-stock panel-span-7">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Inventario</p>
              <h2>Presion de stock</h2>
            </div>
            <button type="button">Ver alertas</button>
          </header>

          <div class="stock-list">
            <article v-for="item in stockPressure" :key="item.id" class="stock-row">
              <div class="stock-top">
                <div>
                  <p class="stock-name">{{ item.name }}</p>
                  <p class="stock-meta">
                    {{ item.stockLabel }} / {{ item.minimumLabel }}
                  </p>
                </div>
                <span class="stock-badge" :class="item.level.className">{{ item.level.label }}</span>
              </div>
              <div class="stock-track">
                <span class="stock-fill" :class="item.level.className" :style="{ width: `${item.percent}%` }" />
              </div>
            </article>
          </div>
        </section>

        <section class="panel panel-feed panel-span-6">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Flujo diario</p>
              <h2>Ultimos servicios</h2>
            </div>
            <button type="button">Abrir agenda</button>
          </header>

          <div class="feed-list">
            <article v-for="entry in serviceFeed" :key="entry.id" class="feed-item">
              <div class="feed-time">{{ entry.time }}</div>
              <div class="feed-main">
                <p class="feed-service">{{ entry.service }}</p>
                <p class="feed-client">{{ entry.client }}</p>
                <p class="feed-meta">{{ entry.stylist }}</p>
              </div>
              <span class="feed-chip">Hoy</span>
            </article>
            <p v-if="!serviceFeed.length" class="feed-empty">
              Sin servicios recientes para mostrar.
            </p>
          </div>
        </section>

        <section class="panel panel-insights panel-span-6">
          <header class="panel-header">
            <div>
              <p class="panel-eyebrow">Lectura rapida</p>
              <h2>Semaforo operativo</h2>
            </div>
            <button type="button">Detalles</button>
          </header>

          <div class="signal-grid">
            <article class="signal-card">
              <span class="signal-label">Servicios</span>
              <strong>{{ kpis[0]?.value ?? '0' }}</strong>
              <small>Actividad capturada hoy</small>
            </article>
            <article class="signal-card">
              <span class="signal-label">Alertas</span>
              <strong>{{ kpis[4]?.value ?? '0' }}</strong>
              <small>Inventario que necesita atencion</small>
            </article>
            <article class="signal-card">
              <span class="signal-label">Equipo</span>
              <strong>{{ kpis[5]?.value ?? '0' }}</strong>
              <small>Estilistas con movimiento</small>
            </article>
          </div>

          <div class="mini-flow">
            <div v-for="bar in operationalChart.bars" :key="`${bar.label}-mini`" class="mini-row">
              <span>{{ bar.label }}</span>
              <div class="mini-track">
                <i :style="{ width: `${bar.percent}%`, background: `linear-gradient(90deg, ${bar.color}, #111111)` }" />
              </div>
              <strong>{{ bar.display }}</strong>
            </div>
          </div>
        </section>
      </div>
  </section>
</template>

<style scoped>
.dashboard {
  display: grid;
  gap: 24px;
}

.dashboard-hero {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
  gap: 20px;
  padding: 24px;
  border-radius: 28px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background:
    radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
    linear-gradient(180deg, rgba(255, 253, 251, 0.98), rgba(245, 252, 250, 0.92));
  box-shadow:
    0 1px 0 rgba(255, 255, 255, 0.8) inset,
    0 18px 40px rgba(25, 25, 25, 0.04);
}

.hero-copy {
  display: grid;
  gap: 14px;
  align-content: start;
}

.hero-eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.24em;
  font-size: 0.68rem;
  color: var(--accent);
  font-weight: 700;
}

.hero-copy h1 {
  margin: 0;
  font-family: var(--font-display);
  font-size: clamp(2.2rem, 4vw, 3.8rem);
  line-height: 0.96;
  letter-spacing: -0.05em;
  color: var(--ink-strong);
}

.hero-description {
  margin: 0;
  max-width: 56ch;
  font-size: 1rem;
  color: var(--ink-muted);
  line-height: 1.6;
}

.hero-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.hero-pills span {
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.1);
  border: 1px solid rgba(15, 118, 110, 0.14);
  color: #0b534b;
  font-size: 0.78rem;
  font-weight: 600;
}

.hero-rail {
  display: grid;
  gap: 12px;
}

.hero-stat {
  border-radius: 18px;
  padding: 14px 16px;
  background: #fffdfa;
  border: 1px solid rgba(25, 25, 25, 0.08);
  display: grid;
  gap: 4px;
}

.hero-stat span {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--ink-muted);
}

.hero-stat strong {
  font-family: var(--font-display);
  font-size: 1.35rem;
  color: var(--ink-strong);
}

.hero-stat small {
  color: var(--ink-muted);
}

.loading-shell {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.loading-block {
  min-height: 170px;
  border-radius: 24px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background:
    linear-gradient(90deg, rgba(255, 255, 255, 0.55), rgba(15, 118, 110, 0.08), rgba(255, 255, 255, 0.55)),
    #fffdfb;
  background-size: 240% 100%;
  animation: shimmer 1.6s linear infinite;
}

.loading-large {
  grid-column: span 2;
  min-height: 260px;
}

.loading-medium {
  min-height: 260px;
}

.loading-small {
  min-height: 260px;
}

.kpi-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  gap: 20px;
}

.panel {
  border-radius: 24px;
  padding: 20px 22px;
  background: #fffdfb;
  border: 1px solid rgba(25, 25, 25, 0.08);
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.82) inset;
  display: grid;
  gap: 18px;
}

.panel-chart {
  background: linear-gradient(180deg, #fffdfb, #f6fcfa);
}

.panel-donut {
  background: linear-gradient(180deg, #fffdfb, #f8fbfa);
}

.panel-bars {
  background: linear-gradient(180deg, #fffdfb, #f7faf8);
}

.panel-stock {
  background: linear-gradient(180deg, #fffdfb, #f8fbfb);
}

.panel-feed {
  background: linear-gradient(180deg, #fffdfb, #fdfcf9);
}

.panel-insights {
  background: linear-gradient(180deg, #fffdfb, #f7fcfb);
}

.panel-span-8 {
  grid-column: span 8;
}

.panel-span-7 {
  grid-column: span 7;
}

.panel-span-6 {
  grid-column: span 6;
}

.panel-span-5 {
  grid-column: span 5;
}

.panel-span-4 {
  grid-column: span 4;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.panel-header h2 {
  margin: 6px 0 0;
  font-size: 1.28rem;
  color: var(--ink-strong);
}

.panel-header button {
  border: 1px solid rgba(25, 25, 25, 0.12);
  background: rgba(255, 253, 251, 0.96);
  padding: 8px 12px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  color: var(--ink-strong);
}

.panel-eyebrow {
  margin: 0;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: var(--ink-muted);
}

.chart-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.chart-stat {
  border-radius: 18px;
  padding: 14px 16px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: #fffdfa;
  display: grid;
  gap: 4px;
}

.chart-stat-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--ink-muted);
}

.chart-stat strong {
  color: var(--ink-strong);
  font-size: 1.15rem;
  font-family: var(--font-display);
}

.bar-chart {
  display: grid;
  gap: 14px;
}

.bar-chart-canvas {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  align-items: end;
  min-height: 280px;
  padding: 18px 10px 6px;
  border-radius: 22px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.82), rgba(245, 252, 250, 0.94)),
    #fffdfb;
}

.bar-column {
  display: grid;
  gap: 12px;
  align-items: end;
  justify-items: center;
}

.bar-track {
  height: 220px;
  width: 100%;
  max-width: 72px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  border-radius: 999px 999px 14px 14px;
  background: rgba(25, 25, 25, 0.05);
  overflow: hidden;
  position: relative;
}

.bar-track::before {
  content: '';
  position: absolute;
  inset: 12px 50% 12px 50%;
  width: 1px;
  background: rgba(25, 25, 25, 0.04);
}

.bar-fill {
  width: 100%;
  min-height: 18px;
  border-radius: 999px 999px 12px 12px;
  box-shadow: 0 14px 22px rgba(15, 118, 110, 0.14);
}

.bar-caption {
  display: grid;
  gap: 2px;
  justify-items: center;
  text-align: center;
}

.bar-caption strong {
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--ink-muted);
}

.bar-caption span {
  font-family: var(--font-display);
  font-size: 1.05rem;
  color: var(--ink-strong);
}

.bar-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.legend-line {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 11px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.08);
  border: 1px solid rgba(25, 25, 25, 0.08);
  color: var(--ink-strong);
  font-size: 0.75rem;
  font-weight: 600;
}

.legend-line i {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  display: inline-block;
}

.donut-layout {
  display: grid;
  gap: 18px;
  justify-items: center;
}

.donut-ring {
  width: 220px;
  height: 220px;
  border-radius: 50%;
  position: relative;
  box-shadow: inset 0 0 0 1px rgba(25, 25, 25, 0.06);
}

.donut-ring::after {
  content: '';
  position: absolute;
  inset: 18px;
  border-radius: 50%;
  background: #fffdfa;
  border: 1px solid rgba(25, 25, 25, 0.08);
}

.donut-center {
  position: absolute;
  inset: 0;
  z-index: 1;
  display: grid;
  place-items: center;
  text-align: center;
  gap: 2px;
}

.donut-center strong {
  font-family: var(--font-display);
  font-size: 2rem;
  color: var(--ink-strong);
}

.donut-center span {
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: var(--ink-muted);
}

.donut-legend {
  display: grid;
  gap: 10px;
  width: 100%;
}

.legend-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
  padding: 11px 12px;
  border-radius: 16px;
  background: #fffdfa;
  border: 1px solid rgba(25, 25, 25, 0.08);
}

.legend-swatch {
  width: 12px;
  height: 12px;
  border-radius: 999px;
}

.legend-copy {
  display: grid;
  gap: 2px;
}

.legend-copy p {
  margin: 0;
  font-weight: 700;
  color: var(--ink-strong);
}

.legend-copy small {
  color: var(--ink-muted);
}

.legend-item strong {
  color: var(--ink-strong);
}

.bar-list,
.stock-list,
.feed-list,
.signal-grid,
.mini-flow {
  display: grid;
  gap: 12px;
}

.bar-row {
  border-radius: 18px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: #fffdfa;
  padding: 14px 16px;
  display: grid;
  gap: 10px;
}

.bar-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.bar-head span {
  font-weight: 700;
  color: var(--ink-strong);
}

.bar-head strong {
  font-family: var(--font-display);
  color: var(--ink-strong);
}

.bar-track-horizontal {
  height: 10px;
}

.bar-fill-horizontal {
  border-radius: 999px;
  display: block;
  min-width: 8px;
  box-shadow: none;
}

.bar-row small {
  color: var(--ink-muted);
  font-size: 0.8rem;
}

.stock-row {
  border-radius: 18px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: #fffdfa;
  padding: 14px 16px;
  display: grid;
  gap: 10px;
}

.stock-top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.stock-name {
  margin: 0;
  font-weight: 700;
  color: var(--ink-strong);
}

.stock-meta {
  margin: 2px 0 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.stock-badge {
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.stock-badge.critical {
  background: rgba(138, 59, 55, 0.12);
  color: #8a3b37;
}

.stock-badge.warning {
  background: rgba(15, 118, 110, 0.12);
  color: #0b534b;
}

.stock-badge.stable {
  background: rgba(191, 217, 212, 0.55);
  color: #355b57;
}

.stock-track {
  height: 10px;
  border-radius: 999px;
  background: rgba(25, 25, 25, 0.08);
  overflow: hidden;
}

.stock-fill {
  height: 100%;
  display: block;
  border-radius: inherit;
}

.stock-fill.critical {
  background: linear-gradient(90deg, #8a3b37, #d17a71);
}

.stock-fill.warning {
  background: linear-gradient(90deg, var(--accent), #0b534b);
}

.stock-fill.stable {
  background: linear-gradient(90deg, #6f9d97, #bfd9d4);
}

.feed-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: start;
  padding: 14px 16px;
  border-radius: 18px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: #fffdfa;
}

.feed-time {
  width: 54px;
  font-weight: 700;
  color: var(--ink-strong);
}

.feed-main {
  display: grid;
  gap: 2px;
}

.feed-service {
  margin: 0;
  font-weight: 700;
  color: var(--ink-strong);
}

.feed-client {
  margin: 0;
  font-size: 0.92rem;
  color: var(--ink-muted);
}

.feed-meta {
  margin: 0;
  font-size: 0.76rem;
  color: var(--ink-muted);
}

.feed-chip {
  padding: 5px 10px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.1);
  color: #0b534b;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.feed-empty {
  margin: 0;
  padding: 18px 0 2px;
  color: var(--ink-muted);
  font-size: 0.9rem;
}

.signal-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.signal-card {
  border-radius: 18px;
  padding: 14px 16px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: #fffdfa;
  display: grid;
  gap: 6px;
}

.signal-label {
  font-size: 0.72rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--ink-muted);
}

.signal-card strong {
  font-family: var(--font-display);
  font-size: 1.3rem;
  color: var(--ink-strong);
}

.signal-card small {
  color: var(--ink-muted);
  line-height: 1.45;
}

.mini-row {
  display: grid;
  grid-template-columns: 72px 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 14px;
  background: rgba(255, 253, 251, 0.92);
  border: 1px solid rgba(25, 25, 25, 0.08);
}

.mini-row span {
  color: var(--ink-strong);
  font-size: 0.8rem;
  font-weight: 600;
}

.mini-track {
  height: 8px;
  border-radius: 999px;
  background: rgba(25, 25, 25, 0.08);
  overflow: hidden;
}

.mini-track i {
  display: block;
  height: 100%;
  border-radius: inherit;
}

.mini-row strong {
  font-family: var(--font-display);
  font-size: 0.95rem;
  color: var(--ink-strong);
}

@media (max-width: 1200px) {
  .dashboard-hero {
    grid-template-columns: 1fr;
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .panel-span-8,
  .panel-span-7,
  .panel-span-6,
  .panel-span-5,
  .panel-span-4 {
    grid-column: span 1;
  }

  .loading-shell {
    grid-template-columns: 1fr 1fr;
  }

  .loading-large,
  .loading-small,
  .loading-medium {
    grid-column: span 1;
  }
}

@media (max-width: 760px) {
  .hero-copy h1 {
    font-size: 2rem;
  }

  .chart-meta,
  .signal-grid {
    grid-template-columns: 1fr;
  }

  .bar-chart-canvas {
    gap: 12px;
    min-height: 240px;
  }

  .bar-track {
    height: 180px;
  }

  .donut-ring {
    width: 190px;
    height: 190px;
  }

  .feed-item,
  .mini-row,
  .stock-top {
    grid-template-columns: 1fr;
  }

  .feed-chip {
    justify-self: start;
  }

  .loading-shell {
    grid-template-columns: 1fr;
  }
}

@keyframes shimmer {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 240% 50%;
  }
}
</style>
