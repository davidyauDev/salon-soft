<script setup lang="ts">
import { onMounted, reactive, shallowRef, computed } from 'vue'
import { useInventory } from '../../composables/useInventory'
import { confirmDelete } from '../../lib/confirm'
import { notifySuccess } from '../../lib/notify'

const inventory = useInventory()

const itemForm = reactive({
  name: '',
  type: 'mixed',
  base_unit: 'ml',
  sale_price: '',
  stock_minimum: '',
})

const purchaseForm = reactive({
  item_id: '',
  quantity: '',
  unit: '',
  cost_per_unit: '',
})

const formMessage = shallowRef<string | null>(null)
const purchaseMessage = shallowRef<string | null>(null)
const editingItemId = shallowRef<number | null>(null)
const savingItem = shallowRef(false)
const savingPurchase = shallowRef(false)

const isEditing = computed(() => editingItemId.value !== null)

const selectedItem = computed(() => {
  return inventory.items.value.find((item) => item.id === Number(purchaseForm.item_id))
})

onMounted(() => {
  inventory.load()
})

async function submitItem(): Promise<void> {
  if (savingItem.value) return
  savingItem.value = true
  formMessage.value = null
  const payload = {
    name: itemForm.name,
    type: itemForm.type,
    base_unit: itemForm.base_unit,
    sale_price: itemForm.sale_price ? Number(itemForm.sale_price) : null,
    stock_minimum: itemForm.stock_minimum ? Number(itemForm.stock_minimum) : 0,
  }
  try {
    if (editingItemId.value) {
      await inventory.updateItem(editingItemId.value, payload)
      formMessage.value = 'Item actualizado.'
      editingItemId.value = null
      notifySuccess('Item actualizado')
    } else {
      await inventory.createItem(payload)
      formMessage.value = 'Item creado.'
      notifySuccess('Item registrado')
    }
  } finally {
    savingItem.value = false
  }
  resetItemForm()
}

async function submitPurchase(): Promise<void> {
  if (savingPurchase.value) return
  savingPurchase.value = true
  purchaseMessage.value = null
  if (!purchaseForm.item_id) {
    purchaseMessage.value = 'Selecciona un item.'
    savingPurchase.value = false
    return
  }
  try {
    await inventory.createPurchase({
      item_id: Number(purchaseForm.item_id),
      quantity: Number(purchaseForm.quantity),
      unit: purchaseForm.unit || selectedItem.value?.base_unit || 'und',
      cost_per_unit: Number(purchaseForm.cost_per_unit),
    })
    purchaseForm.quantity = ''
    purchaseForm.cost_per_unit = ''
    purchaseMessage.value = 'Compra registrada.'
    notifySuccess('Compra registrada')
  } finally {
    savingPurchase.value = false
  }
}

function resetItemForm(): void {
  itemForm.name = ''
  itemForm.type = 'mixed'
  itemForm.base_unit = 'ml'
  itemForm.sale_price = ''
  itemForm.stock_minimum = ''
}

function startEdit(itemId: number): void {
  const target = inventory.items.value.find((item) => item.id === itemId)
  if (!target) return
  editingItemId.value = itemId
  itemForm.name = target.name
  itemForm.type = target.type
  itemForm.base_unit = target.base_unit
  itemForm.sale_price = target.sale_price ? String(target.sale_price) : ''
  itemForm.stock_minimum = String(target.stock_minimum ?? '')
}

function cancelEdit(): void {
  editingItemId.value = null
  resetItemForm()
}

async function removeItem(itemId: number): Promise<void> {
  const ok = await confirmDelete('Eliminar este item? Esto elimina su configuracion.')
  if (!ok) return
  await inventory.deleteItem(itemId)
}

function isLowStock(item: { stock_total: number; stock_minimum: number }): boolean {
  return item.stock_total < item.stock_minimum
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Inventario</p>
        <h2>Control de stock y compras</h2>
      </div>
    </header>

    <div class="view-grid">
      <section class="panel">
        <div class="panel-header">
          <div>
            <h3>{{ isEditing ? 'Editar item' : 'Registrar nuevo item' }}</h3>
            <p class="panel-subtitle">Productos e insumos para venta o consumo.</p>
          </div>
        </div>
        <form class="form-grid" @submit.prevent="submitItem">
          <label>
            Nombre
            <input v-model="itemForm.name" required />
          </label>
          <label>
            Tipo
            <select v-model="itemForm.type">
              <option value="mixed">Mixto</option>
              <option value="sell_only">Solo venta</option>
              <option value="consume_only">Solo consumo</option>
            </select>
          </label>
          <label>
            Unidad base
            <input v-model="itemForm.base_unit" placeholder="ml, und, g" />
          </label>
          <label>
            Precio venta (opcional)
            <input v-model="itemForm.sale_price" type="number" step="0.01" />
          </label>
          <label>
            Stock minimo
            <input v-model="itemForm.stock_minimum" type="number" step="0.01" />
          </label>
          <div class="form-actions">
            <button class="btn-primary" type="submit" :disabled="savingItem">
              {{ isEditing ? 'Actualizar item' : 'Guardar item' }}
            </button>
            <button
              v-if="isEditing"
              class="btn-ghost"
              type="button"
              :disabled="savingItem"
              @click="cancelEdit"
            >
              Cancelar
            </button>
          </div>
          <p v-if="formMessage" class="form-message">{{ formMessage }}</p>
        </form>
      </section>

      <section class="panel">
        <h3>Registrar compra</h3>
        <form class="form-grid" @submit.prevent="submitPurchase">
          <label>
            Item
            <select v-model="purchaseForm.item_id">
              <option value="">Seleccionar</option>
              <option v-for="item in inventory.items.value" :key="item.id" :value="item.id">
                {{ item.name }}
              </option>
            </select>
          </label>
          <label>
            Cantidad
            <input v-model="purchaseForm.quantity" type="number" step="0.01" />
          </label>
          <label>
            Unidad
            <select v-model="purchaseForm.unit">
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
            <input v-model="purchaseForm.cost_per_unit" type="number" step="0.01" />
          </label>
          <button class="btn-primary" type="submit" :disabled="savingPurchase">Registrar compra</button>
          <p v-if="purchaseMessage" class="form-message">{{ purchaseMessage }}</p>
        </form>
      </section>
    </div>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h3>Stock actual</h3>
          <p class="panel-subtitle">Edita o elimina items desde aqui.</p>
        </div>
      </div>
      <p v-if="inventory.loading.value">Cargando inventario...</p>
      <p v-else-if="inventory.error.value" class="error">{{ inventory.error.value }}</p>
      <div v-else class="table">
        <div class="table-row table-head">
          <span>Item</span>
          <span>Tipo</span>
          <span>Stock</span>
          <span>Minimo</span>
          <span>Acciones</span>
        </div>
        <div v-for="item in inventory.items.value" :key="item.id" class="table-row">
          <span class="item-name">{{ item.name }}</span>
          <span class="item-type">{{ item.type }}</span>
          <span class="stock-pill" :class="{ low: isLowStock(item) }">
            {{ item.stock_total.toFixed(2) }} {{ item.base_unit }}
          </span>
          <span class="muted">{{ item.stock_minimum }} {{ item.base_unit }}</span>
          <div class="row-actions">
            <button class="btn-ghost" type="button" @click="startEdit(item.id)">Editar</button>
            <button class="btn-danger" type="button" @click="removeItem(item.id)">Eliminar</button>
          </div>
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

.view-header h2 {
  margin: 6px 0 0;
}

.view-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}

.form-message {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 12px;
  align-items: center;
}

.error {
  color: #b24b3a;
}

.item-name {
  font-weight: 600;
}

.item-type {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  color: var(--ink-muted);
}

.stock-pill {
  justify-self: start;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(27, 15, 42, 0.08);
  color: var(--ink-strong);
  font-size: 0.78rem;
}

.stock-pill.low {
  background: rgba(178, 75, 58, 0.12);
  color: #b24b3a;
}

.row-actions {
  display: flex;
  gap: 8px;
}

.muted {
  color: var(--ink-muted);
  font-size: 0.85rem;
}
</style>
