<script setup lang="ts">
import { computed, onMounted, shallowRef, watch } from 'vue'
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

const itemsPerPage = shallowRef(12)
const currentPage = shallowRef(1)

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

const totalProducts = computed(() => products.items.value.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalProducts.value / itemsPerPage.value)))
const pagedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return products.items.value.slice(start, start + itemsPerPage.value)
})

const pageStart = computed(() => (totalProducts.value ? (currentPage.value - 1) * itemsPerPage.value + 1 : 0))
const pageEnd = computed(() => Math.min(currentPage.value * itemsPerPage.value, totalProducts.value))

watch(
  [itemsPerPage, totalProducts],
  () => {
    if (currentPage.value > totalPages.value) currentPage.value = totalPages.value
    if (currentPage.value < 1) currentPage.value = 1
  },
  { immediate: true },
)

function goToPage(page: number): void {
  currentPage.value = Math.min(Math.max(page, 1), totalPages.value)
}

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

    <ProductTable
      :items="pagedProducts"
      :loading="products.loading.value"
      :error="products.error.value"
      @edit="openEditProduct"
      @remove="removeProduct"
    />

    <div class="table-footer">
      <div class="page-meta">
        <span>Mostrando</span>
        <strong>{{ pageStart }}-{{ pageEnd }}</strong>
        <span>de {{ totalProducts }}</span>
      </div>
      <div class="page-controls">
        <label>
          Por pagina
          <select v-model.number="itemsPerPage">
            <option :value="8">8</option>
            <option :value="12">12</option>
            <option :value="20">20</option>
            <option :value="30">30</option>
          </select>
        </label>
        <div class="pager">
          <button class="pager-btn" type="button" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
            Anterior
          </button>
          <span class="pager-count">Pagina {{ currentPage }} / {{ totalPages }}</span>
          <button class="pager-btn" type="button" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
            Siguiente
          </button>
        </div>
      </div>
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
  color: #6f6963;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.btn-primary {
  padding: 10px 16px;
  border-radius: 12px;
  border: none;
  background: #111111;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(17, 17, 17, 0.14);
}

.btn-ghost {
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid rgba(25, 25, 25, 0.12);
  background: #fffdfb;
  font-weight: 700;
  cursor: pointer;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  color: #6f6963;
  font-size: 0.85rem;
  flex-wrap: wrap;
}

.page-meta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.page-meta strong {
  color: #111111;
}

.page-controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.page-controls label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pager {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pager-btn {
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid rgba(25, 25, 25, 0.12);
  background: #fffdfb;
  cursor: pointer;
  font-weight: 600;
}

.pager-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.pager-count {
  color: #6f6963;
  font-size: 0.82rem;
}

.table-footer select {
  border: 1px solid rgba(25, 25, 25, 0.12);
  border-radius: 10px;
  padding: 6px 12px;
  background: #fffdfb;
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

  .header-actions > * {
    flex: 1 1 160px;
  }

  .table-footer {
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .products-header h2 {
    font-size: 1.35rem;
  }

  .header-actions > * {
    flex: 1 1 100%;
  }

  .page-controls {
    width: 100%;
    justify-content: flex-start;
  }

  .page-controls label,
  .pager {
    width: 100%;
  }

  .pager {
    justify-content: space-between;
  }

  .page-controls select {
    width: 100%;
  }
}
</style>
