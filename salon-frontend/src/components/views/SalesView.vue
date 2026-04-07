<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue'
import { useSales } from '../../composables/useSales'
import type { SaleRecord } from '../../composables/useSales'
import { useServiceRecords } from '../../composables/useServiceRecords'
import type { ServiceRecord } from '../../composables/useServiceRecords'
import { useExpressSales } from '../../composables/useExpressSales'
import { useCatalogs } from '../../composables/useCatalogs'
import { useClients } from '../../composables/useClients'
import { notifyError, notifySuccess } from '../../lib/notify'
import SalesSummaryCards from '../sales/SalesSummaryCards.vue'
import SalesAppointmentsTable from '../sales/SalesAppointmentsTable.vue'
import SalesExpressTable from '../sales/SalesExpressTable.vue'
import SalesProductsTable from '../sales/SalesProductsTable.vue'
import SalesDetailModal from '../sales/SalesDetailModal.vue'
import SalesDrawer from '../sales/SalesDrawer.vue'
import SalesPaymentModal from '../sales/SalesPaymentModal.vue'
import ClientQuickModal from '../sales/ClientQuickModal.vue'
import ExpressSaleDrawer from '../sales/ExpressSaleDrawer.vue'
import ExpressSuccessModal from '../sales/ExpressSuccessModal.vue'

const catalogs = useCatalogs()
const sales = useSales()
const records = useServiceRecords()
const expressSales = useExpressSales()
const clientsStore = useClients()

const activeTab = shallowRef<'appointments' | 'express' | 'products'>('express')
const search = shallowRef('')
const fromDate = shallowRef('2026-03-06')
const toDate = shallowRef('2026-04-05')
const itemsPerPage = shallowRef(20)

const menuOpen = shallowRef(false)

const productDrawerOpen = shallowRef(false)
const paymentOpen = shallowRef(false)
const savingSale = shallowRef(false)

const expressDrawerOpen = shallowRef(false)
const savingExpress = shallowRef(false)
const expressSuccessOpen = shallowRef(false)
const expressSuccessTotal = shallowRef(0)

const detailOpen = shallowRef(false)
const detailMode = shallowRef<'services' | 'products'>('products')
const detailSale = shallowRef<SaleRecord | null>(null)
const detailRecord = shallowRef<ServiceRecord | null>(null)

const productClientQuery = shallowRef('')
const productClientId = shallowRef<number | null>(null)
const expressClientQuery = shallowRef('')
const expressClientId = shallowRef<number | null>(null)

const clientModalOpen = shallowRef(false)
const clientModalTarget = shallowRef<'express' | 'product' | null>(null)
const savingClient = shallowRef(false)

const pendingSale = shallowRef<
  | {
      client_id: number | null
      lines: Array<{ id: number; name: string; unit_price: number; quantity: number; type: 'product' | 'service' }>
    }
  | null
>(null)

onMounted(() => {
  catalogs.load()
  sales.load()
  records.load()
  expressSales.load()
})

const searchPlaceholder = computed(() => {
  if (activeTab.value === 'appointments') return 'Buscar por cliente o servicio'
  if (activeTab.value === 'express') return 'Buscar por cliente o servicio'
  return 'Buscar por cliente o producto'
})

function inRange(value: string): boolean {
  if (!value) return true
  const current = new Date(value)
  if (Number.isNaN(current.getTime())) return true

  const from = fromDate.value ? new Date(`${fromDate.value}T00:00:00`) : null
  const to = toDate.value ? new Date(`${toDate.value}T23:59:59`) : null

  if (from && current < from) return false
  if (to && current > to) return false
  return true
}

const appointmentRows = computed(() =>
  records.records.value.filter((record) => record.source !== 'express' && inRange(record.performed_at)),
)

const expressRows = computed(() =>
  expressSales.records.value.filter((record) => inRange(record.performed_at)),
)

const productRows = computed(() =>
  sales.sales.value.filter((sale) => inRange(sale.sold_at)),
)

const filteredAppointments = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return appointmentRows.value

  return appointmentRows.value.filter((record) => {
    const client = record.client?.full_name?.toLowerCase() ?? ''
    const service = record.service?.name?.toLowerCase() ?? ''
    return client.includes(query) || service.includes(query)
  })
})

const filteredExpress = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return expressRows.value

  return expressRows.value.filter((record) => {
    const client = record.client?.full_name?.toLowerCase() ?? ''
    const service = record.service?.name?.toLowerCase() ?? ''
    const products = (record.sale?.items ?? [])
      .map((line) => line.item?.name?.toLowerCase() ?? '')
      .join(' ')
    return client.includes(query) || service.includes(query) || products.includes(query)
  })
})

const filteredProducts = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return productRows.value

  return productRows.value.filter((sale) => {
    const client = sale.client?.full_name?.toLowerCase() ?? ''
    const products = (sale.items ?? [])
      .map((line) => line.item?.name?.toLowerCase() ?? '')
      .join(' ')
    return client.includes(query) || products.includes(query)
  })
})

const pagedAppointments = computed(() => filteredAppointments.value.slice(0, itemsPerPage.value))
const pagedExpress = computed(() => filteredExpress.value.slice(0, itemsPerPage.value))
const pagedProducts = computed(() => filteredProducts.value.slice(0, itemsPerPage.value))

const paymentClient = computed(() => {
  if (!pendingSale.value?.client_id) return null
  return catalogs.clients.value.find((client) => client.id === pendingSale.value?.client_id) ?? null
})

const paymentTotal = computed(() => {
  if (!pendingSale.value) return 0
  return pendingSale.value.lines.reduce((sum, line) => sum + line.unit_price * line.quantity, 0)
})

const activeCount = computed(() => {
  if (activeTab.value === 'appointments') return filteredAppointments.value.length
  if (activeTab.value === 'express') return filteredExpress.value.length
  return filteredProducts.value.length
})

const summaryCards = computed(() => {
  const appointmentsTotal = appointmentRows.value.reduce((sum, record) => sum + Number(record.total_amount), 0)
  const expressTotal = expressRows.value.reduce((sum, record) => sum + Number(record.grand_total ?? record.total_amount), 0)
  const productsTotal = productRows.value.reduce((sum, sale) => sum + Number(sale.total_amount), 0)

  return [
    { id: 'total', label: 'Ventas totales', value: appointmentsTotal + expressTotal + productsTotal, kind: 'money' as const },
    { id: 'appointments', label: 'Citas', value: appointmentsTotal, kind: 'money' as const },
    { id: 'express', label: 'Express', value: expressTotal, kind: 'money' as const },
    { id: 'products', label: 'Productos', value: productsTotal, kind: 'money' as const },
    { id: 'count', label: 'Cantidad de ventas', value: appointmentRows.value.length + expressRows.value.length + productRows.value.length, kind: 'count' as const },
  ]
})

function rangeLabel(value: string): string {
  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date)
}

function openNewSale(mode: 'express' | 'products'): void {
  menuOpen.value = false

  if (mode === 'express') {
    expressClientId.value = null
    expressClientQuery.value = ''
    expressDrawerOpen.value = true
    return
  }

  productClientId.value = null
  productClientQuery.value = ''
  productDrawerOpen.value = true
}

function openClientModal(target: 'express' | 'product'): void {
  clientModalTarget.value = target
  clientModalOpen.value = true
}

function openSaleDetail(row: SaleRecord): void {
  detailMode.value = 'products'
  detailSale.value = row
  detailRecord.value = null
  detailOpen.value = true
}

function openServiceDetail(row: ServiceRecord): void {
  detailMode.value = 'services'
  detailRecord.value = row
  detailSale.value = null
  detailOpen.value = true
}

function closeDetail(): void {
  detailOpen.value = false
  detailSale.value = null
  detailRecord.value = null
}

function handleProductConfirm(payload: {
  client_id: number | null
  lines: Array<{ id: number; name: string; unit_price: number; quantity: number; type: 'product' | 'service' }>
}): void {
  pendingSale.value = {
    client_id: payload.client_id,
    lines: payload.lines,
  }
  productDrawerOpen.value = false
  paymentOpen.value = true
}

function closePayment(): void {
  paymentOpen.value = false
  pendingSale.value = null
}

async function handlePay(payload: { payment_method: string; emit_receipt: boolean }): Promise<void> {
  if (!pendingSale.value) return
  savingSale.value = true

  try {
    await sales.create({
      client_id: pendingSale.value.client_id,
      payment_method: payload.payment_method,
      items: pendingSale.value.lines.map((line) => ({
        item_id: line.id,
        quantity: line.quantity,
        unit: 'und',
        unit_price: line.unit_price,
      })),
    })

    paymentOpen.value = false
    pendingSale.value = null
    notifySuccess('Venta registrada')
  } catch (err) {
    notifyError('No se pudo registrar la venta', (err as Error).message)
  } finally {
    savingSale.value = false
  }
}

async function handleExpressSubmit(payload: {
  client_id: number
  service_id: number
  stylist_id: number
  performed_at: string
  notes: string | null
  service_price: number
  products: Array<{
    item_id: number
    quantity: number
    unit: string
    unit_price: number
  }>
}): Promise<void> {
  savingExpress.value = true

  try {
    const created = await expressSales.create({
      ...payload,
      payment_method: 'cash',
    })
    expressSuccessTotal.value = Number(created.grand_total ?? created.total_amount)
    expressDrawerOpen.value = false
    expressSuccessOpen.value = true
    notifySuccess('Venta express registrada')
  } catch (err) {
    notifyError('No se pudo registrar la venta express', (err as Error).message)
  } finally {
    savingExpress.value = false
  }
}

async function submitClient(payload: {
  first_name: string
  last_name: string
  phone: string | null
  email: string | null
  address: string | null
  dni: string | null
  birth_date: string | null
  gender: string | null
}): Promise<void> {
  if (savingClient.value) return
  savingClient.value = true

  try {
    const created = await clientsStore.create(payload)
    await catalogs.load()

    if (clientModalTarget.value === 'express') {
      expressClientId.value = created.id
      expressClientQuery.value = created.full_name
    }

    if (clientModalTarget.value === 'product') {
      productClientId.value = created.id
      productClientQuery.value = created.full_name
    }

    clientModalOpen.value = false
    clientModalTarget.value = null
    notifySuccess('Cliente creado')
  } catch (err) {
    notifyError('No se pudo crear el cliente', (err as Error).message)
  } finally {
    savingClient.value = false
  }
}

function closeClientModal(): void {
  clientModalOpen.value = false
  clientModalTarget.value = null
}
</script>

<template>
  <section class="sales-view">
    <header class="sales-header">
      <div>
        <h2>Ventas ({{ activeCount }})</h2>
        <p class="subtitle">Consulta, filtra y exporta las ventas del salon.</p>
      </div>

      <div class="header-actions">
        <button class="btn-ghost" type="button">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3a1 1 0 0 1 1 1v8.58l2.3-2.29 1.4 1.41L12 16.42 7.3 11.7l1.4-1.41L11 12.58V4a1 1 0 0 1 1-1Zm-7 14h4v2H6a2 2 0 0 1-2-2v-3h2v3Zm15-3h2v3a2 2 0 0 1-2 2h-3v-2h3v-3Z" />
          </svg>
          <span>Exportar</span>
        </button>

        <div class="split-button">
          <button class="btn-primary main" type="button" @click="openNewSale('express')">Nueva Venta</button>
          <button class="btn-primary caret" type="button" @click="menuOpen = !menuOpen">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>

          <div v-if="menuOpen" class="action-menu">
            <button type="button" @click="openNewSale('express')">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" />
              </svg>
              <span>Express</span>
            </button>
            <button type="button" @click="openNewSale('products')">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m12 2 8 4.5v11L12 22l-8-4.5v-11L12 2Zm0 2.3L6.3 7.5 12 10.7l5.7-3.2L12 4.3Z" />
              </svg>
              <span>Productos</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <SalesSummaryCards :cards="summaryCards" />

    <div class="tabs">
      <button :class="{ active: activeTab === 'appointments' }" type="button" @click="activeTab = 'appointments'">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V3a1 1 0 0 1 1-1Z" />
        </svg>
        <span>Citas</span>
      </button>
      <button :class="{ active: activeTab === 'express' }" type="button" @click="activeTab = 'express'">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" />
        </svg>
        <span>Express</span>
      </button>
      <button :class="{ active: activeTab === 'products' }" type="button" @click="activeTab = 'products'">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="m12 2 8 4.5v11L12 22l-8-4.5v-11L12 2Zm0 2.3L6.3 7.5 12 10.7l5.7-3.2L12 4.3Z" />
        </svg>
        <span>Productos</span>
      </button>
    </div>

    <div class="filters">
      <label class="search-field">
        <input v-model="search" type="search" :placeholder="searchPlaceholder" />
        <span class="search-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z" />
          </svg>
        </span>
      </label>

      <div class="range-picker">
        <input v-model="fromDate" type="date" />
        <span>-</span>
        <input v-model="toDate" type="date" />
        <small>{{ rangeLabel(fromDate) }} - {{ rangeLabel(toDate) }}</small>
      </div>
    </div>

    <SalesAppointmentsTable
      v-if="activeTab === 'appointments'"
      :rows="pagedAppointments"
      :loading="records.loading.value"
      empty-text="No hay ventas de citas con este resultado"
    />
    <SalesExpressTable
      v-else-if="activeTab === 'express'"
      :rows="pagedExpress"
      :loading="expressSales.loading.value"
      empty-text="No hay ventas express con este resultado"
      @detail="openServiceDetail"
    />
    <SalesProductsTable
      v-else
      :rows="pagedProducts"
      :loading="sales.loading.value"
      empty-text="No hay ventas de productos con este resultado"
      @detail="openSaleDetail"
    />

    <div class="table-footer">
      <span>Items por pagina</span>
      <select v-model.number="itemsPerPage">
        <option :value="15">15</option>
        <option :value="20">20</option>
        <option :value="50">50</option>
      </select>
    </div>

    <ExpressSaleDrawer
      v-model:clientQuery="expressClientQuery"
      v-model:clientId="expressClientId"
      :open="expressDrawerOpen"
      :clients="catalogs.clients.value"
      :services="catalogs.services.value"
      :items="catalogs.items.value"
      :stylists="catalogs.stylists.value"
      :saving="savingExpress"
      @close="expressDrawerOpen = false"
      @create-client="openClientModal('express')"
      @submit="handleExpressSubmit"
    />

    <SalesDrawer
      v-model:clientQuery="productClientQuery"
      v-model:clientId="productClientId"
      :open="productDrawerOpen"
      mode="products"
      :clients="catalogs.clients.value"
      :items="catalogs.items.value"
      :services="catalogs.services.value"
      :saving="savingSale"
      @close="productDrawerOpen = false"
      @confirm="handleProductConfirm"
      @create-client="openClientModal('product')"
    />

    <SalesPaymentModal
      :open="paymentOpen"
      :client-name="paymentClient?.full_name ?? ''"
      :client-phone="paymentClient?.phone ?? ''"
      :client-email="paymentClient?.email ?? ''"
      :total="paymentTotal"
      :saving="savingSale"
      @close="closePayment"
      @pay="handlePay"
    />

    <SalesDetailModal
      :open="detailOpen"
      :mode="detailMode"
      :sale="detailSale"
      :record="detailRecord"
      @close="closeDetail"
    />

    <ClientQuickModal
      :open="clientModalOpen"
      :saving="savingClient"
      @close="closeClientModal"
      @save="submitClient"
    />

    <ExpressSuccessModal
      :open="expressSuccessOpen"
      :total="expressSuccessTotal"
      @close="expressSuccessOpen = false"
    />
  </section>
</template>

<style scoped>
.sales-view {
  display: grid;
  gap: 16px;
  position: relative;
}

.sales-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.sales-header h2 {
  margin: 0;
  color: #1f2539;
}

.subtitle {
  margin: 8px 0 0;
  color: #6e7a90;
  font-size: 1rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 14px;
  border: 1px solid rgba(18, 18, 23, 0.12);
  background: #fff;
  font-weight: 700;
  color: #2b3750;
}

.btn-ghost svg,
.split-button svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.split-button {
  position: relative;
  display: flex;
}

.btn-primary {
  border: none;
  background: #5949ec;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.btn-primary.main {
  padding: 11px 18px;
  border-radius: 14px 0 0 14px;
}

.btn-primary.caret {
  width: 44px;
  border-left: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 0 14px 14px 0;
}

.action-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 194px;
  padding: 10px;
  border-radius: 18px;
  background: #fff;
  border: 1px solid rgba(18, 18, 23, 0.08);
  box-shadow: 0 18px 34px rgba(17, 15, 20, 0.14);
  display: grid;
  gap: 4px;
  z-index: 8;
}

.action-menu button {
  display: flex;
  align-items: center;
  gap: 10px;
  border: none;
  background: transparent;
  padding: 10px 12px;
  border-radius: 12px;
  color: #25324c;
  font-weight: 600;
  text-align: left;
}

.tabs {
  display: flex;
  gap: 24px;
  border-bottom: 1px solid rgba(17, 15, 20, 0.08);
}

.tabs button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 0;
  border: none;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: #6e7a90;
  font-weight: 600;
  cursor: pointer;
}

.tabs button svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.tabs button.active {
  color: #5949ec;
  border-color: #5949ec;
}

.filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.search-field {
  position: relative;
  width: min(392px, 100%);
}

.search-field input {
  width: 100%;
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  padding: 11px 42px 11px 14px;
  font-size: 0.95rem;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #2d3748;
}

.search-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.range-picker {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 14px;
  background: #fff;
}

.range-picker input {
  border: none;
  background: transparent;
  color: #2b3750;
}

.range-picker small {
  grid-column: 1 / -1;
  color: #6e7a90;
  font-size: 0.78rem;
}

.table-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  align-items: center;
  color: #6f6770;
  font-size: 0.85rem;
}

.table-footer select {
  border: 1px solid rgba(17, 15, 20, 0.18);
  border-radius: 10px;
  padding: 6px 12px;
  background: #fff;
}

@media (max-width: 980px) {
  .sales-header,
  .filters {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .range-picker {
    width: 100%;
  }
}
</style>
