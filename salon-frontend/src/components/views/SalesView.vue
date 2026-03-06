<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue'
import { useSales } from '../../composables/useSales'
import { useServiceRecords } from '../../composables/useServiceRecords'
import { useCatalogs } from '../../composables/useCatalogs'
import { useClients } from '../../composables/useClients'
import { notifyError, notifySuccess } from '../../lib/notify'
import SalesProductsTable from '../sales/SalesProductsTable.vue'
import SalesServicesTable from '../sales/SalesServicesTable.vue'
import SalesDrawer from '../sales/SalesDrawer.vue'
import SalesPaymentModal from '../sales/SalesPaymentModal.vue'
import ClientQuickModal from '../sales/ClientQuickModal.vue'

const catalogs = useCatalogs()
const sales = useSales()
const records = useServiceRecords()
const clientsStore = useClients()

const activeTab = shallowRef<'services' | 'products'>('products')
const search = shallowRef('')
const dateRange = shallowRef('19/2/2026 - 21/3/2026')
const itemsPerPage = shallowRef(20)

const drawerOpen = shallowRef(false)
const paymentOpen = shallowRef(false)
const savingSale = shallowRef(false)

const drawerClientQuery = shallowRef('')
const drawerClientId = shallowRef<number | null>(null)

const clientModalOpen = shallowRef(false)
const savingClient = shallowRef(false)

const pendingSale = shallowRef<
  | {
      mode: 'services' | 'products'
      client_id: number | null
      lines: Array<{ id: number; name: string; unit_price: number; quantity: number; type: 'product' | 'service' }>
    }
  | null
>(null)

onMounted(() => {
  catalogs.load()
  sales.load()
  records.load()
})

const activeCount = computed(() =>
  activeTab.value === 'products' ? sales.total.value : records.total.value,
)

const filteredProductSales = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return sales.sales.value
  return sales.sales.value.filter((sale) => {
    const client = sale.client?.full_name?.toLowerCase() ?? ''
    const products = (sale.items ?? [])
      .map((line) => line.item?.name?.toLowerCase() ?? '')
      .join(' ')
    return client.includes(query) || products.includes(query)
  })
})

const filteredServiceSales = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return records.records.value
  return records.records.value.filter((record) => {
    const client = record.client?.full_name?.toLowerCase() ?? ''
    const service = record.service?.name?.toLowerCase() ?? ''
    return client.includes(query) || service.includes(query)
  })
})

const pagedProducts = computed(() => filteredProductSales.value.slice(0, itemsPerPage.value))
const pagedServices = computed(() => filteredServiceSales.value.slice(0, itemsPerPage.value))

const paymentClient = computed(() => {
  if (!pendingSale.value?.client_id) return null
  return catalogs.clients.value.find((client) => client.id === pendingSale.value?.client_id) ?? null
})

const paymentTotal = computed(() => {
  if (!pendingSale.value) return 0
  return pendingSale.value.lines.reduce((sum, line) => sum + line.unit_price * line.quantity, 0)
})

function openNewSale(): void {
  drawerClientQuery.value = ''
  drawerClientId.value = null
  drawerOpen.value = true
}

function openClientModal(): void {
  clientModalOpen.value = true
}

function handleConfirm(payload: {
  client_id: number | null
  lines: Array<{ id: number; name: string; unit_price: number; quantity: number; type: 'product' | 'service' }>
}): void {
  pendingSale.value = {
    mode: activeTab.value,
    client_id: payload.client_id,
    lines: payload.lines,
  }
  drawerOpen.value = false
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
    if (pendingSale.value.mode === 'products') {
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
      notifySuccess('Venta registrada')
    } else {
      const stylistId = catalogs.stylists.value[0]?.id
      if (!stylistId) {
        notifyError('No hay estilistas disponibles')
        return
      }
      const line = pendingSale.value.lines[0]
      if (!line) {
        notifyError('Selecciona un servicio')
        return
      }
      await records.create({
        service_id: line.id,
        stylist_id: stylistId,
        client_id: pendingSale.value.client_id,
        total_amount: line.unit_price * line.quantity,
        payment_method: payload.payment_method,
        consumptions: [],
      })
      notifySuccess('Servicio registrado')
    }

    paymentOpen.value = false
    pendingSale.value = null
  } catch (err) {
    notifyError('No se pudo registrar la venta', (err as Error).message)
  } finally {
    savingSale.value = false
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
    drawerClientId.value = created.id
    drawerClientQuery.value = created.full_name
    clientModalOpen.value = false
    notifySuccess('Cliente creado')
  } catch (err) {
    notifyError('No se pudo crear el cliente', (err as Error).message)
  } finally {
    savingClient.value = false
  }
}
</script>

<template>
  <section class="sales-view">
    <header class="sales-header">
      <div>
        <h2>Ventas ({{ activeCount }})</h2>
        <p class="subtitle">Consulta, filtra y exporta los productos de la empresa.</p>
      </div>
      <button class="btn-ghost" type="button">Exportar</button>
    </header>

    <div class="tabs">
      <button :class="{ active: activeTab === 'services' }" type="button" @click="activeTab = 'services'">
        Servicios
      </button>
      <button :class="{ active: activeTab === 'products' }" type="button" @click="activeTab = 'products'">
        Productos
      </button>
    </div>

    <div class="filters">
      <label class="search-field">
        <input v-model="search" type="search" placeholder="Buscar por cliente o producto" />
        <span class="search-icon">
          <svg viewBox="0 0 24 24">
            <path
              d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z"
            />
          </svg>
        </span>
      </label>
      <div class="filter-actions">
        <button class="date-chip" type="button">
          {{ dateRange }}
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M7 2h2v2h6V2h2v2h3v18H4V4h3V2Zm12 8H5v10h14V10Z"
            />
          </svg>
        </button>
        <button class="btn-primary" type="button" @click="openNewSale">+ Nueva venta</button>
      </div>
    </div>

    <SalesProductsTable
      v-if="activeTab === 'products'"
      :rows="pagedProducts"
      :loading="sales.loading.value"
      empty-text="No hay ventas con este resultado"
    />
    <SalesServicesTable
      v-else
      :rows="pagedServices"
      :loading="records.loading.value"
      empty-text="No hay servicios con este resultado"
    />

    <div class="table-footer">
      <span>Items por pagina</span>
      <select v-model.number="itemsPerPage">
        <option :value="15">15</option>
        <option :value="20">20</option>
        <option :value="50">50</option>
      </select>
    </div>

    <SalesDrawer
      v-model:clientQuery="drawerClientQuery"
      v-model:clientId="drawerClientId"
      :open="drawerOpen"
      :mode="activeTab"
      :clients="catalogs.clients.value"
      :items="catalogs.items.value"
      :services="catalogs.services.value"
      :saving="savingSale"
      @close="drawerOpen = false"
      @confirm="handleConfirm"
      @create-client="openClientModal"
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

    <ClientQuickModal
      :open="clientModalOpen"
      :saving="savingClient"
      @close="clientModalOpen = false"
      @save="submitClient"
    />
  </section>
</template>

<style scoped>
.sales-view {
  display: grid;
  gap: 16px;
}

.sales-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.sales-header h2 {
  margin: 0;
}

.subtitle {
  margin: 6px 0 0;
  color: #6f6770;
}

.btn-ghost {
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
  font-weight: 600;
}

.tabs {
  display: flex;
  gap: 16px;
  border-bottom: 1px solid rgba(17, 15, 20, 0.08);
}

.tabs button {
  border: none;
  background: transparent;
  padding: 10px 0;
  font-weight: 600;
  color: #6f6770;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}

.tabs button.active {
  color: #5a4bff;
  border-color: #5a4bff;
}

.filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.search-field {
  position: relative;
  width: 320px;
}

.search-field input {
  width: 100%;
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  padding: 10px 40px 10px 14px;
  font-size: 0.95rem;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6f6770;
}

.search-icon svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.filter-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.date-chip {
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  padding: 10px 14px;
  background: #fff;
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-chip svg {
  width: 16px;
  height: 16px;
  fill: #6f6770;
}

.btn-primary {
  padding: 10px 16px;
  border-radius: 12px;
  border: none;
  background: #5a4bff;
  color: #fff;
  font-weight: 600;
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
  .filters {
    flex-direction: column;
    align-items: flex-start;
  }

  .search-field {
    width: 100%;
  }

  .filter-actions {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
