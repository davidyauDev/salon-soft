import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'
import type { ServiceRecord } from './useServiceRecords'

const loading = shallowRef(false)
const records = shallowRef<ServiceRecord[]>([])
const total = shallowRef(0)

export function useExpressSales() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: ServiceRecord[]; meta?: { total?: number } }>('/api/express-sales')
      records.value = unwrapData(response)
      total.value = response?.meta?.total ?? records.value.length
    } finally {
      loading.value = false
    }
  }

  async function create(payload: Record<string, unknown>): Promise<ServiceRecord> {
    const response = await apiFetch<ServiceRecord>('/api/express-sales', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    const created = unwrapData(response)
    await load()
    return created
  }

  return {
    loading,
    records,
    total,
    load,
    create,
  }
}
