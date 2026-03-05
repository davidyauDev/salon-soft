import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface SaleRecord {
  id: number
  sold_at: string
  total_amount: number
  payment_method: string
}

const loading = shallowRef(false)
const sales = shallowRef<SaleRecord[]>([])

export function useSales() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: SaleRecord[] }>('/api/sales')
      sales.value = unwrapData(response)
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
    load,
    create,
  }
}
