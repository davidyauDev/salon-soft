import { shallowRef } from 'vue'
import { apiFetch } from '../lib/api'

interface DashboardResponse {
  kpis: {
    services_today: number
    sales_today: number
    consumption_cost_today: number
    commissions_today: number
  }
  alerts: Array<{
    id: number
    name: string
    stock_total: number
    stock_minimum: number
    base_unit: string
  }>
  recent_services: Array<{
    id: number
    performed_at: string
    client: string | null
    service: string | null
    stylist: string | null
  }>
  top_commissions: Array<{
    stylist: string | null
    total: number
  }>
}

const loading = shallowRef(false)
const data = shallowRef<DashboardResponse | null>(null)
const error = shallowRef<string | null>(null)

export function useDashboard() {
  async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      data.value = await apiFetch<DashboardResponse>('/api/dashboard')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'No se pudo cargar el dashboard.'
      data.value = null
    } finally {
      loading.value = false
    }
  }

  return {
    error,
    loading,
    data,
    load,
  }
}
