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

export function useDashboard() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      data.value = await apiFetch<DashboardResponse>('/api/dashboard')
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    data,
    load,
  }
}
