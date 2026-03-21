<script setup lang="ts">
import { computed } from 'vue'
import type { SaleRecord } from '../../composables/useSales'
import type { ServiceRecord } from '../../composables/useServiceRecords'
import { formatCurrency, formatDateTime } from '../../utils/format'

const props = defineProps<{
  open: boolean
  mode: 'products' | 'services'
  sale?: SaleRecord | null
  record?: ServiceRecord | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const title = computed(() => (props.mode === 'products' ? 'Detalle de venta' : 'Detalle de servicio'))

const headerData = computed(() => {
  if (props.mode === 'products' && props.sale) {
    return {
      id: props.sale.id,
      client: props.sale.client?.full_name ?? 'Sin cliente',
      date: formatDateTime(props.sale.sold_at),
      method: props.sale.payment_method ?? '-',
      total: Number(props.sale.total_amount ?? 0),
    }
  }
  if (props.mode === 'services' && props.record) {
    return {
      id: props.record.id,
      client: props.record.client?.full_name ?? 'Sin cliente',
      date: formatDateTime(props.record.performed_at),
      method: props.record.payment_method ?? '-',
      total: Number(props.record.grand_total ?? props.record.total_amount ?? 0),
    }
  }
  return null
})

const items = computed(() => {
  if (props.mode === 'products') {
    return (
      props.sale?.items?.map((line) => ({
        name: line.item?.name ?? 'Producto',
        quantity: line.quantity_base ?? 0,
        unitPrice: line.unit_price_base ?? 0,
      })) ??
      []
    )
  }
  if (props.record?.service?.name) {
    const serviceLine = [
      {
        name: props.record.service.name,
        quantity: 1,
        unitPrice: Number(props.record.total_amount ?? 0),
      },
    ]
    const productLines = props.record.sale?.items?.map((line) => ({
      name: line.item?.name ?? 'Producto',
      quantity: Number(line.quantity_base ?? 0),
      unitPrice: Number(line.unit_price_base ?? 0),
    })) ?? []

    return [...serviceLine, ...productLines]
  }
  return []
})

const linesLabel = computed(() => {
  if (props.mode === 'products') return 'Productos'
  return props.record?.sale?.items?.length ? 'Conceptos' : 'Servicio'
})
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <div>
          <h3>{{ title }}</h3>
          <p class="panel-subtitle">Resumen de la venta registrada.</p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <div v-if="headerData" class="info-grid">
        <div>
          <span class="label">ID</span>
          <p>{{ headerData.id }}</p>
        </div>
        <div>
          <span class="label">Cliente</span>
          <p>{{ headerData.client }}</p>
        </div>
        <div>
          <span class="label">Fecha</span>
          <p>{{ headerData.date }}</p>
        </div>
        <div>
          <span class="label">Metodo</span>
          <p>{{ headerData.method }}</p>
        </div>
      </div>

      <div class="items">
        <div class="items-head">
          <span>{{ linesLabel }}</span>
          <span>Cantidad</span>
          <span>Precio</span>
          <span>Total</span>
        </div>
        <div v-for="item in items" :key="item.name" class="items-row">
          <span>{{ item.name }}</span>
          <span>{{ item.quantity }}</span>
          <span>{{ formatCurrency(Number(item.unitPrice)) }}</span>
          <span>{{ formatCurrency(Number(item.unitPrice) * Number(item.quantity)) }}</span>
        </div>
        <div v-if="!items.length" class="empty">Sin items registrados.</div>
      </div>

      <div v-if="headerData" class="total-card">
        <span>Total:</span>
        <strong>{{ formatCurrency(headerData.total) }}</strong>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 20, 0.45);
  display: grid;
  place-items: center;
  padding: 20px;
  z-index: 60;
}

.modal-card {
  width: min(640px, 100%);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
  display: grid;
  gap: 16px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.close-button {
  border: none;
  background: rgba(17, 15, 20, 0.08);
  width: 36px;
  height: 36px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  background: #f6f7f9;
  border-radius: 12px;
  padding: 12px 14px;
}

.label {
  font-size: 0.7rem;
  color: #6f6770;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.info-grid p {
  margin: 4px 0 0;
  font-weight: 600;
}

.items {
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  overflow: hidden;
}

.items-head,
.items-row {
  display: grid;
  grid-template-columns: 1.6fr 0.6fr 0.8fr 0.8fr;
  gap: 12px;
  align-items: center;
  padding: 10px 14px;
}

.items-head {
  background: #f6f7f9;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 600;
}

.items-row {
  border-top: 1px solid rgba(17, 15, 20, 0.06);
  font-size: 0.9rem;
}

.empty {
  padding: 12px 14px;
  text-align: center;
  color: #6f6770;
}

.total-card {
  border: 1px solid rgba(17, 15, 20, 0.1);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

@media (max-width: 640px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .items-head,
  .items-row {
    grid-template-columns: 1fr 0.6fr 0.8fr;
  }

  .items-head span:last-child,
  .items-row span:last-child {
    display: none;
  }
}
</style>
