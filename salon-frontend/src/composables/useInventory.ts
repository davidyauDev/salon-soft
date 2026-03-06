import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface InventoryItem {
  id: number
  name: string
  type: string
  base_unit: string
  sale_price: number | null
  stock_minimum: number
  sku: string | null
  barcode: string | null
  reorder_point: number
  reorder_qty: number
  stock_total: number
  category: { id: number; name: string } | null
  brand: { id: number; name: string } | null
  units: Array<{ id: number; unit: string; factor_to_base: number; is_base: boolean }>
  lots: Array<{ id: number; quantity_remaining: number; cost_per_base: number }>
}

const loading = shallowRef(false)
const items = shallowRef<InventoryItem[]>([])
const error = shallowRef<string | null>(null)

export function useInventory() {
  async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const response = await apiFetch<InventoryItem[]>('/api/inventory')
      items.value = unwrapData(response)
    } catch (err) {
      error.value = (err as Error).message
    } finally {
      loading.value = false
    }
  }

  async function createItem(payload: {
    name: string
    type: string
    base_unit: string
    sale_price?: number | null
    stock_minimum?: number | null
    category_id?: number | null
    brand_id?: number | null
    sku?: string | null
    barcode?: string | null
    reorder_point?: number | null
    reorder_qty?: number | null
  }): Promise<void> {
    await apiFetch('/api/items', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function createPurchase(payload: {
    item_id: number
    quantity: number
    unit: string
    cost_per_unit: number
    supplier_id?: number | null
    lot_code?: string | null
    invoice_number?: string | null
    expires_at?: string | null
  }): Promise<void> {
    await apiFetch('/api/purchases', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function updateItem(id: number, payload: Record<string, unknown>): Promise<void> {
    await apiFetch(`/api/items/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function deleteItem(id: number): Promise<void> {
    await apiFetch(`/api/items/${id}`, { method: 'DELETE' })
    await load()
  }

  return {
    loading,
    items,
    error,
    load,
    createItem,
    createPurchase,
    updateItem,
    deleteItem,
  }
}
