import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface SaleItemLine {
  id: number
  item_id: number
  quantity_base: number
  unit_price_base: number
  item?: { name: string }
}

export interface SaleClient {
  id: number
  full_name: string
  phone?: string | null
  email?: string | null
}

export interface SaleRecord {
  id: number
  sold_at: string
  total_amount: number
  payment_method: string
  client?: SaleClient | null
  items?: SaleItemLine[]
}

const loading = shallowRef(false)
const sales = shallowRef<SaleRecord[]>([])
const total = shallowRef(0)

export function useSales() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: SaleRecord[]; meta?: { total?: number } }>('/api/sales')
      sales.value = unwrapData(response)
      total.value = response?.meta?.total ?? sales.value.length
    } finally {
      loading.value = false
    }
  }

  async function create(payload: Record<string, unknown>): Promise<void> {
    await apiFetch('/api/sales', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    await load()
  }

  return {
    loading,
    sales,
    total,
    load,
    create,
  }
}
