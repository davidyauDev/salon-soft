<script setup lang="ts">
import { computed } from 'vue'
import type { InventoryItem } from '../../composables/useInventory'
import type { InventorySupplier } from '../../composables/useInventoryCatalogs'
import type { InventoryPurchaseForm } from '../../types/inventory'

const model = defineModel<InventoryPurchaseForm>({ required: true })

const props = defineProps<{
  items: InventoryItem[]
  suppliers: InventorySupplier[]
  saving: boolean
  message: string | null
}>()

const emit = defineEmits<{
  submit: []
}>()

const selectedItem = computed(() =>
  props.items.find((item) => item.id === Number(model.value.item_id)),
)

function handleSubmit(): void {
  emit('submit')
}
</script>

<template>
  <section class="panel">
    <h3>Registrar compra</h3>
    <form class="form-grid two-col" @submit.prevent="handleSubmit">
      <label class="span-2">
        Item
        <select v-model="model.item_id">
          <option value="">Seleccionar</option>
          <option v-for="item in props.items" :key="item.id" :value="String(item.id)">
            {{ item.name }}
          </option>
        </select>
      </label>
      <label class="span-2">
        Proveedor
        <select v-model="model.supplier_id">
          <option value="">Sin proveedor</option>
          <option v-for="supplier in props.suppliers" :key="supplier.id" :value="String(supplier.id)">
            {{ supplier.name }}
          </option>
        </select>
      </label>
      <label>
        Cantidad
        <input v-model="model.quantity" type="number" step="0.01" />
      </label>
      <label>
        Unidad
        <select v-model="model.unit">
          <option
            v-for="unit in selectedItem?.units ?? []"
            :key="unit.id"
            :value="unit.unit"
          >
            {{ unit.unit }}
          </option>
        </select>
      </label>
      <label>
        Costo por unidad
        <input v-model="model.cost_per_unit" type="number" step="0.01" />
      </label>
      <label>
        Lote
        <input v-model="model.lot_code" placeholder="Lote interno" />
      </label>
      <label>
        Factura
        <input v-model="model.invoice_number" placeholder="Numero de factura" />
      </label>
      <label>
        Vencimiento
        <input v-model="model.expires_at" type="date" />
      </label>
      <div class="form-actions span-2">
        <button class="btn-primary" type="submit" :disabled="props.saving">Registrar compra</button>
      </div>
      <p v-if="props.message" class="form-message span-2">{{ props.message }}</p>
    </form>
  </section>
</template>

<style scoped>
.span-2 {
  grid-column: span 2;
}

.form-message {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}
</style>
