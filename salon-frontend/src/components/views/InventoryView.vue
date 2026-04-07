<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue'
import { useInventory } from '../../composables/useInventory'
import { useInventoryCatalogs } from '../../composables/useInventoryCatalogs'
import { confirmDelete } from '../../lib/confirm'
import { notifySuccess } from '../../lib/notify'
import type { InventoryItemForm, InventoryPurchaseForm } from '../../types/inventory'
import InventoryItemFormPanel from '../inventory/InventoryItemForm.vue'
import InventoryPurchaseFormPanel from '../inventory/InventoryPurchaseForm.vue'
import InventoryTable from '../inventory/InventoryTable.vue'
import InventoryCatalogsPanel from '../inventory/InventoryCatalogsPanel.vue'

const inventory = useInventory()
const catalogs = useInventoryCatalogs()

const formMessage = shallowRef<string | null>(null)
const purchaseMessage = shallowRef<string | null>(null)
const editingItemId = shallowRef<number | null>(null)
const savingItem = shallowRef(false)
const savingPurchase = shallowRef(false)

const itemForm = shallowRef<InventoryItemForm>({
  name: '',
  type: 'mixed',
  base_unit: 'ml',
  sale_price: '',
  stock_minimum: '',
  category_id: '',
  brand_id: '',
  sku: '',
  barcode: '',
  reorder_point: '',
  reorder_qty: '',
})

const purchaseForm = shallowRef<InventoryPurchaseForm>({
  item_id: '',
  quantity: '',
  unit: '',
  cost_per_unit: '',
  supplier_id: '',
  lot_code: '',
  invoice_number: '',
  expires_at: '',
})

const isEditing = computed(() => editingItemId.value !== null)
const selectedPurchaseItem = computed(() =>
  inventory.items.value.find((item) => item.id === Number(purchaseForm.value.item_id)),
)

onMounted(() => {
  inventory.load()
  catalogs.load()
})

async function submitItem(): Promise<void> {
  if (savingItem.value) return
  savingItem.value = true
  formMessage.value = null

  const payload = {
    name: itemForm.value.name.trim(),
    type: itemForm.value.type,
    base_unit: itemForm.value.base_unit.trim(),
    sale_price: itemForm.value.sale_price ? Number(itemForm.value.sale_price) : null,
    stock_minimum: itemForm.value.stock_minimum ? Number(itemForm.value.stock_minimum) : 0,
    category_id: itemForm.value.category_id ? Number(itemForm.value.category_id) : null,
    brand_id: itemForm.value.brand_id ? Number(itemForm.value.brand_id) : null,
    sku: itemForm.value.sku.trim() || null,
    barcode: itemForm.value.barcode.trim() || null,
    reorder_point: itemForm.value.reorder_point ? Number(itemForm.value.reorder_point) : 0,
    reorder_qty: itemForm.value.reorder_qty ? Number(itemForm.value.reorder_qty) : 0,
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

  if (!purchaseForm.value.item_id) {
    purchaseMessage.value = 'Selecciona un item.'
    savingPurchase.value = false
    return
  }

  try {
    await inventory.createPurchase({
      item_id: Number(purchaseForm.value.item_id),
      quantity: Number(purchaseForm.value.quantity),
      unit: purchaseForm.value.unit || selectedPurchaseItem.value?.base_unit || 'und',
      cost_per_unit: Number(purchaseForm.value.cost_per_unit),
      supplier_id: purchaseForm.value.supplier_id ? Number(purchaseForm.value.supplier_id) : null,
      lot_code: purchaseForm.value.lot_code.trim() || null,
      invoice_number: purchaseForm.value.invoice_number.trim() || null,
      expires_at: purchaseForm.value.expires_at || null,
    })
    purchaseMessage.value = 'Compra registrada.'
    notifySuccess('Compra registrada')
    resetPurchaseForm()
  } finally {
    savingPurchase.value = false
  }
}

function resetItemForm(): void {
  itemForm.value = {
    name: '',
    type: 'mixed',
    base_unit: 'ml',
    sale_price: '',
    stock_minimum: '',
    category_id: '',
    brand_id: '',
    sku: '',
    barcode: '',
    reorder_point: '',
    reorder_qty: '',
  }
}

function resetPurchaseForm(): void {
  purchaseForm.value = {
    item_id: '',
    quantity: '',
    unit: '',
    cost_per_unit: '',
    supplier_id: '',
    lot_code: '',
    invoice_number: '',
    expires_at: '',
  }
}

function startEdit(itemId: number): void {
  const target = inventory.items.value.find((item) => item.id === itemId)
  if (!target) return
  editingItemId.value = itemId
  itemForm.value = {
    name: target.name,
    type: target.type,
    base_unit: target.base_unit,
    sale_price: target.sale_price ? String(target.sale_price) : '',
    stock_minimum: String(target.stock_minimum ?? ''),
    category_id: target.category ? String(target.category.id) : '',
    brand_id: target.brand ? String(target.brand.id) : '',
    sku: target.sku ?? '',
    barcode: target.barcode ?? '',
    reorder_point: String(target.reorder_point ?? ''),
    reorder_qty: String(target.reorder_qty ?? ''),
  }
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

async function createCategory(payload: { name: string; description?: string | null }): Promise<void> {
  await catalogs.createCategory(payload)
  notifySuccess('Categoria registrada')
}

async function createBrand(payload: { name: string }): Promise<void> {
  await catalogs.createBrand(payload)
  notifySuccess('Marca registrada')
}

async function createSupplier(payload: {
  name: string
  contact_name?: string | null
  phone?: string | null
}): Promise<void> {
  await catalogs.createSupplier(payload)
  notifySuccess('Proveedor registrado')
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
      <InventoryItemFormPanel
        v-model="itemForm"
        :categories="catalogs.categories.value"
        :brands="catalogs.brands.value"
        :saving="savingItem"
        :is-editing="isEditing"
        :message="formMessage"
        @submit="submitItem"
        @cancel="cancelEdit"
      />
      <InventoryPurchaseFormPanel
        v-model="purchaseForm"
        :items="inventory.items.value"
        :suppliers="catalogs.suppliers.value"
        :saving="savingPurchase"
        :message="purchaseMessage"
        @submit="submitPurchase"
      />
    </div>

    <InventoryCatalogsPanel
      @create-category="createCategory"
      @create-brand="createBrand"
      @create-supplier="createSupplier"
    />

    <InventoryTable
      :items="inventory.items.value"
      :loading="inventory.loading.value"
      :error="inventory.error.value"
      @edit="startEdit"
      @remove="removeItem"
    />
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
</style>
