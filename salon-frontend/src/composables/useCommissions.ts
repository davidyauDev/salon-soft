import { shallowRef } from 'vue'
import { apiFetch } from '../lib/api'

export interface CommissionEntry {
  id: number
  amount: number
  calculated_at: string
  stylist?: { user?: { name: string } }
  record?: { service?: { name: string } }
}

const loading = shallowRef(false)
const commissions = shallowRef<CommissionEntry[]>([])
const summary = shallowRef<Array<{ stylist: string | null; total: number }>>([])

export function useCommissions() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: CommissionEntry[] }>('/api/commissions')
      commissions.value = response.data ?? []
      summary.value = await apiFetch('/api/commissions/summary')
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    commissions,
    summary,
    load,
  }
}
