import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface ServiceRecord {
  id: number
  performed_at: string
  total_amount: number
  status?: string
  payment_method?: string | null
  service_id?: number
  stylist_id?: number
  client_id?: number | null
  service?: { name: string }
  stylist?: { user?: { name: string } }
  client?: { full_name: string }
}

const loading = shallowRef(false)
const records = shallowRef<ServiceRecord[]>([])

export function useServiceRecords() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: ServiceRecord[] }>('/api/service-records')
      records.value = unwrapData(response)
    } finally {
      loading.value = false
    }
  }

  async function create(payload: Record<string, unknown>): Promise<void> {
    await apiFetch('/api/service-records', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function update(recordId: number, payload: Record<string, unknown>): Promise<void> {
    await apiFetch(`/api/service-records/${recordId}`, {
      method: 'PATCH',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function cancel(recordId: number): Promise<void> {
    await apiFetch(`/api/service-records/${recordId}/cancel`, { method: 'PATCH' })
    await load()
  }

  return {
    loading,
    records,
    load,
    create,
    update,
    cancel,
  }
}
