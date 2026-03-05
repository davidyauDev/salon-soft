<script setup lang="ts">
import { onMounted, reactive, shallowRef } from 'vue'
import { useCatalogs } from '../../composables/useCatalogs'
import { useSales } from '../../composables/useSales'
import { formatCurrency, formatDateTime } from '../../utils/format'
import { notifySuccess } from '../../lib/notify'

const catalogs = useCatalogs()
const sales = useSales()

const saleForm = reactive({
  client_id: '',
  payment_method: 'cash',
  items: [
    {
      item_id: '',
      quantity: '',
      unit: '',
      unit_price: '',
    },
  ],
})

const message = shallowRef<string | null>(null)
const savingSale = shallowRef(false)

onMounted(() => {
  catalogs.load()
  sales.load()
})

function addLine(): void {
  saleForm.items.push({ item_id: '', quantity: '', unit: '', unit_price: '' })
}

function removeLine(index: number): void {
  saleForm.items.splice(index, 1)
}

async function submitSale(): Promise<void> {
  if (savingSale.value) return
  savingSale.value = true
  message.value = null
  try {
    await sales.create({
      client_id: saleForm.client_id ? Number(saleForm.client_id) : null,
      payment_method: saleForm.payment_method,
      items: saleForm.items.map((line) => ({
        item_id: Number(line.item_id),
        quantity: Number(line.quantity),
        unit: line.unit,
        unit_price: Number(line.unit_price),
      })),
    })
    saleForm.items = [{ item_id: '', quantity: '', unit: '', unit_price: '' }]
    message.value = 'Venta registrada.'
    notifySuccess('Venta registrada')
  } finally {
    savingSale.value = false
  }
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Ventas</p>
        <h2>POS y productos</h2>
      </div>
    </header>

    <section class="panel">
      <h3>Registrar venta</h3>
      <form class="form-grid" @submit.prevent="submitSale">
        <label>
          Cliente
          <select v-model="saleForm.client_id">
            <option value="">Sin cliente</option>
            <option v-for="client in catalogs.clients.value" :key="client.id" :value="client.id">
              {{ client.full_name }}
            </option>
          </select>
        </label>
        <label>
          Metodo de pago
          <select v-model="saleForm.payment_method">
            <option value="cash">Efectivo</option>
            <option value="card">Tarjeta</option>
            <option value="yape">Yape</option>
          </select>
        </label>

        <div class="line-group">
          <p>Items</p>
          <div v-for="(line, index) in saleForm.items" :key="index" class="line-grid">
            <select v-model="line.item_id">
              <option value="">Producto</option>
              <option v-for="item in catalogs.items.value" :key="item.id" :value="item.id">
                {{ item.name }}
              </option>
            </select>
            <input v-model="line.quantity" type="number" step="0.01" placeholder="Cantidad" />
            <input v-model="line.unit" placeholder="Unidad" />
            <input v-model="line.unit_price" type="number" step="0.01" placeholder="Precio" />
          <button type="button" class="btn-ghost" @click="removeLine(index)">Quitar</button>
        </div>
        <button type="button" class="btn-ghost" @click="addLine">Agregar item</button>
      </div>

      <button class="btn-primary" type="submit" :disabled="savingSale">Registrar venta</button>
      <p v-if="message" class="form-message">{{ message }}</p>
    </form>
  </section>

    <section class="panel">
      <h3>Ventas recientes</h3>
      <div class="table">
        <div class="table-row table-head">
          <span>Fecha</span>
          <span>Metodo</span>
          <span>Total</span>
        </div>
        <div v-for="sale in sales.sales.value" :key="sale.id" class="table-row">
          <span>{{ formatDateTime(sale.sold_at) }}</span>
          <span>{{ sale.payment_method }}</span>
          <span>{{ formatCurrency(sale.total_amount) }}</span>
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

.line-group {
  display: grid;
  gap: 8px;
}

.line-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.6fr 0.6fr 0.6fr auto;
  gap: 8px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.8fr;
  gap: 12px;
}

.form-message {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}
</style>
