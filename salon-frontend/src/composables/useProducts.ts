import { computed, shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface ProductCategory {
  id: number
  name: string
}

export interface ProductBrand {
  id: number
  name: string
}

export interface ProductLot {
  id: number
  quantity_remaining: number
  cost_per_base: number
}

export interface ProductItem {
  id: number
  name: string
  description?: string | null
  sale_price: number | null
  stock_total: number
  category: ProductCategory | null
  brand: ProductBrand | null
  lots: ProductLot[]
}

const loading = shallowRef(false)
const items = shallowRef<ProductItem[]>([])
const error = shallowRef<string | null>(null)

export function useProducts() {
  async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const response = await apiFetch<ProductItem[]>('/api/inventory')
      items.value = unwrapData(response)
    } catch (err) {
      error.value = (err as Error).message
    } finally {
      loading.value = false
    }
  }

  async function createProduct(payload: {
    name: string
    description?: string | null
    category_id?: number | null
    brand_id?: number | null
    sale_price?: number | null
  }): Promise<ProductItem> {
    const response = await apiFetch<ProductItem>('/api/items', {
      method: 'POST',
      body: JSON.stringify({
        name: payload.name,
        description: payload.description ?? null,
        category_id: payload.category_id ?? null,
        brand_id: payload.brand_id ?? null,
        sale_price: payload.sale_price ?? null,
        type: 'sell_only',
        base_unit: 'und',
      }),
    })

    const item = unwrapData(response) as ProductItem
    await load()
    return item
  }

  async function updateProduct(id: number, payload: {
    name: string
    description?: string | null
    category_id?: number | null
    brand_id?: number | null
    sale_price?: number | null
  }): Promise<ProductItem> {
    const response = await apiFetch<ProductItem>(`/api/items/${id}`, {
      method: 'PUT',
      body: JSON.stringify({
        name: payload.name,
        description: payload.description ?? null,
        category_id: payload.category_id ?? null,
        brand_id: payload.brand_id ?? null,
        sale_price: payload.sale_price ?? null,
        type: 'sell_only',
        base_unit: 'und',
      }),
    })

    const item = unwrapData(response) as ProductItem
    await load()
    return item
  }

  async function deleteProduct(id: number): Promise<void> {
    await apiFetch(`/api/items/${id}`, { method: 'DELETE' })
    await load()
  }

  async function createInitialStock(payload: {
    item_id: number
    quantity: number
    cost_per_unit: number
  }): Promise<void> {
    await apiFetch('/api/purchases', {
      method: 'POST',
      body: JSON.stringify({
        item_id: payload.item_id,
        quantity: payload.quantity,
        unit: 'und',
        cost_per_unit: payload.cost_per_unit,
      }),
    })
    await load()
  }

  function clearCategoryFromItems(categoryId: number): void {
    items.value = items.value.map((item) =>
      item.category?.id === categoryId
        ? {
            ...item,
            category: null,
          }
        : item,
    )
  }

  const totalCount = computed(() => items.value.length)

  return {
    loading,
    items,
    error,
    totalCount,
    load,
    createProduct,
    updateProduct,
    deleteProduct,
    createInitialStock,
    clearCategoryFromItems,
  }
}
