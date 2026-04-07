<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue'
import { useProducts } from '../../composables/useProducts'
import { useInventoryCatalogs } from '../../composables/useInventoryCatalogs'
import { confirmDelete } from '../../lib/confirm'
import { notifyCategoryChanged, notifyError, notifyProductSaved, notifySuccess } from '../../lib/notify'
import type { InventoryCategory } from '../../composables/useInventoryCatalogs'
import type { ProductItem } from '../../composables/useProducts'
import ProductTable from '../products/ProductTable.vue'
import ProductFormModal from '../products/ProductFormModal.vue'
import CategoryFormModal from '../products/CategoryFormModal.vue'
import CategoryManageModal from '../products/CategoryManageModal.vue'

const products = useProducts()
const catalogs = useInventoryCatalogs()

const search = shallowRef('')
const itemsPerPage = shallowRef(100)

const showProductModal = shallowRef(false)
const showCategoryForm = shallowRef(false)
const showCategoryManage = shallowRef(false)
const savingProduct = shallowRef(false)
const savingCategory = shallowRef(false)
const processingCategoryId = shallowRef<number | null>(null)
const editingProduct = shallowRef<ProductItem | null>(null)
const editingCategory = shallowRef<InventoryCategory | null>(null)
const returnToProductModal = shallowRef(false)

onMounted(() => {
  products.load()
  catalogs.load()
})

const filteredProducts = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return products.items.value
  return products.items.value.filter((item) => {
    const name = item.name?.toLowerCase() ?? ''
    const brand = item.brand?.name?.toLowerCase() ?? ''
    return name.includes(query) || brand.includes(query)
  })
})

const pagedProducts = computed(() => filteredProducts.value.slice(0, itemsPerPage.value))

function openCreateProduct(): void {
  editingProduct.value = null
  showProductModal.value = true
}

function openEditProduct(item: ProductItem): void {
  editingProduct.value = item
  showProductModal.value = true
}

function closeProductModal(): void {
  if (savingProduct.value) return
  showProductModal.value = false
  editingProduct.value = null
}

function openCategoryManager(): void {
  showCategoryManage.value = true
}

function closeCategoryManager(): void {
  if (processingCategoryId.value !== null) return
  showCategoryManage.value = false
}

function openCategoryForm(category: InventoryCategory | null = null): void {
  editingCategory.value = category
  showCategoryForm.value = true
}

function closeCategoryForm(force = false): void {
  if (savingCategory.value && !force) return
  showCategoryForm.value = false
  editingCategory.value = null
  if (returnToProductModal.value) {
    showProductModal.value = true
    returnToProductModal.value = false
  }
}

function openCategoryFromProduct(): void {
  returnToProductModal.value = true
  showProductModal.value = false
  openCategoryForm(null)
}

function sanitizeProductErrorMessage(message: string): string {
  const normalized = message.toLowerCase()

  if (
    normalized.includes('numeric value out of range')
    || normalized.includes('numeric field overflow')
    || normalized.includes('out of range')
  ) {
    return 'Revisa stock, costo o precio. Uno de los valores es demasiado grande.'
  }

  return message
}

async function resolveBrandId(name: string): Promise<number | null> {
  const trimmed = name.trim()
  if (!trimmed) return null
  const match = catalogs.brands.value.find(
    (brand) => brand.name.toLowerCase() === trimmed.toLowerCase(),
  )
  if (match) return match.id

  await catalogs.createBrand({ name: trimmed })
  const created = catalogs.brands.value.find(
    (brand) => brand.name.toLowerCase() === trimmed.toLowerCase(),
  )
  return created?.id ?? null
}

async function submitProduct(payload: {
  name: string
  category_id: number
  brand: string
  stock: string
  cost: string
  price: string
  description: string
}): Promise<void> {
  if (savingProduct.value) return
  savingProduct.value = true

  try {
    const brandId = await resolveBrandId(payload.brand)
    const priceValue = payload.price ? Number(payload.price) : 0
    const categoryName = catalogs.categories.value.find((category) => category.id === payload.category_id)?.name ?? null

    if (editingProduct.value) {
      await products.updateProduct(editingProduct.value.id, {
        name: payload.name,
        category_id: payload.category_id,
        brand_id: brandId,
        sale_price: priceValue,
        description: payload.description,
      })
      showProductModal.value = false
      editingProduct.value = null
      notifyProductSaved({
        name: payload.name,
        mode: 'updated',
        category: categoryName,
        brand: payload.brand || null,
      })
    } else {
      const created = await products.createProduct({
        name: payload.name,
        category_id: payload.category_id,
        brand_id: brandId,
        sale_price: priceValue,
        description: payload.description,
      })

      const stockValue = payload.stock ? Number(payload.stock) : 0
      const costValue = payload.cost ? Number(payload.cost) : 0
      let successNote: string | null = null

      if (stockValue > 0) {
        try {
          await products.createInitialStock({
            item_id: created.id,
            quantity: stockValue,
            cost_per_unit: costValue,
          })
        } catch {
          successNote = 'El producto se guardo, pero el stock inicial no pudo registrarse. Revisalo desde inventario.'
        }
      }

      showProductModal.value = false
      editingProduct.value = null
      notifyProductSaved({
        name: payload.name,
        mode: 'created',
        category: categoryName,
        brand: payload.brand || null,
        stock: stockValue,
        note: successNote,
      })
    }
  } catch (err) {
    notifyError('No se pudo guardar el producto', sanitizeProductErrorMessage((err as Error).message))
  } finally {
    savingProduct.value = false
  }
}

async function removeProduct(item: ProductItem): Promise<void> {
  const ok = await confirmDelete('Eliminar este producto?')
  if (!ok) return
  try {
    await products.deleteProduct(item.id)
    notifySuccess('Producto eliminado')
  } catch (err) {
    notifyError('No se pudo eliminar el producto', (err as Error).message)
  }
}

async function submitCategory(payload: { name: string }): Promise<void> {
  if (savingCategory.value) return
  savingCategory.value = true
  try {
    if (editingCategory.value) {
      await catalogs.updateCategory(editingCategory.value.id, { name: payload.name })
      closeCategoryForm(true)
      notifyCategoryChanged({ name: payload.name, mode: 'updated' })
    } else {
      await catalogs.createCategory({ name: payload.name })
      closeCategoryForm(true)
      notifyCategoryChanged({ name: payload.name, mode: 'created' })
    }
    void products.load()
  } catch (err) {
    notifyError('No se pudo guardar la categoria', (err as Error).message)
  } finally {
    savingCategory.value = false
  }
}

async function removeCategory(category: InventoryCategory): Promise<void> {
  if (processingCategoryId.value !== null) return
  const ok = await confirmDelete('Eliminar esta categoria?')
  if (!ok) return
  processingCategoryId.value = category.id
  try {
    await catalogs.deleteCategory(category.id)
    products.clearCategoryFromItems(category.id)
    notifyCategoryChanged({ name: category.name, mode: 'deleted' })
    void products.load()
  } catch (err) {
    notifyError('No se pudo eliminar la categoria', (err as Error).message)
  } finally {
    processingCategoryId.value = null
  }
}

function editCategory(category: InventoryCategory): void {
  openCategoryForm(category)
}
</script>

<template>
  <section class="products-view">
    <header class="products-header">
      <div>
        <h2>Productos</h2>
        <p class="subtitle">Gestiona tu inventario de productos</p>
      </div>
      <div class="header-actions">
        <button class="btn-ghost" type="button" @click="openCategoryManager">Categorias</button>
        <button class="btn-primary" type="button" @click="openCreateProduct">+ Crear Producto</button>
      </div>
    </header>

    <div class="search-row">
      <label class="search-field">
        <span class="search-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24">
            <path
              d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z"
            />
          </svg>
        </span>
        <input v-model="search" type="search" placeholder="Buscar por nombre de producto..." />
      </label>
    </div>

    <ProductTable
      :items="pagedProducts"
      :loading="products.loading.value"
      :error="products.error.value"
      @edit="openEditProduct"
      @remove="removeProduct"
    />

    <div class="table-footer">
      <span>Items por pagina</span>
      <select v-model.number="itemsPerPage">
        <option :value="15">15</option>
        <option :value="20">20</option>
        <option :value="50">50</option>
        <option :value="100">100</option>
      </select>
    </div>

    <ProductFormModal
      :open="showProductModal"
      :saving="savingProduct"
      :categories="catalogs.categories.value"
      :initial="editingProduct"
      @close="closeProductModal"
      @submit="submitProduct"
      @create-category="openCategoryFromProduct"
    />

    <CategoryManageModal
      :open="showCategoryManage"
      :categories="catalogs.categories.value"
      :processing-id="processingCategoryId"
      @close="closeCategoryManager"
      @create="openCategoryForm"
      @edit="editCategory"
      @remove="removeCategory"
    />

    <CategoryFormModal
      :open="showCategoryForm"
      :saving="savingCategory"
      :initial="editingCategory"
      @close="closeCategoryForm"
      @submit="submitCategory"
    />
  </section>
</template>

<style scoped>
.products-view {
  display: grid;
  gap: 18px;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.products-header h2 {
  margin: 0;
  font-size: 1.6rem;
}

.subtitle {
  margin: 6px 0 0;
  color: #6f6770;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.btn-primary {
  padding: 10px 16px;
  border-radius: 12px;
  border: none;
  background: #5a4bff;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.btn-ghost {
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
  font-weight: 600;
  cursor: pointer;
}

.search-row {
  display: flex;
  justify-content: flex-start;
}

.search-field {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  background: #fff;
  min-width: 320px;
}

.search-field input {
  border: none;
  outline: none;
  font-size: 0.95rem;
  width: 100%;
}

.search-icon {
  width: 16px;
  height: 16px;
  display: grid;
  place-items: center;
  color: #6f6770;
}

.search-icon svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.table-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  color: #6f6770;
  font-size: 0.85rem;
}

.table-footer select {
  border: 1px solid rgba(17, 15, 20, 0.18);
  border-radius: 10px;
  padding: 6px 12px;
  background: #fff;
}

@media (max-width: 900px) {
  .products-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .search-field {
    width: 100%;
    min-width: auto;
  }
}
</style>
