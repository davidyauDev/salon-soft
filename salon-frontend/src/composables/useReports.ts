import { shallowRef } from 'vue'
import { apiFetch } from '../lib/api'

interface StockAlert {
  id: number
  name: string
  stock_total: number
  stock_minimum: number
  base_unit: string
}

const loading = shallowRef(false)
const stockLow = shallowRef<StockAlert[]>([])
const salesTotal = shallowRef(0)
const consumptionTotal = shallowRef(0)

export function useReports() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      stockLow.value = await apiFetch('/api/reports/stock-low')
      const sales = await apiFetch<{ total_sales: number }>('/api/reports/sales-summary')
      const consumption = await apiFetch<{ total_consumption: number }>(
        '/api/reports/consumption-summary',
      )
      salesTotal.value = sales.total_sales
      consumptionTotal.value = consumption.total_consumption
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    stockLow,
    salesTotal,
    consumptionTotal,
    load,
  }
}
