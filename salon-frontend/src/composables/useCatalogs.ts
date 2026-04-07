import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface CatalogItem {
  id: number
  name: string
  base_unit: string
  sale_price?: number | null
}

export interface CatalogService {
  id: number
  name: string
  duration_min?: number | null
  base_price: number
  is_active: boolean
}

export interface CatalogUser {
  id: number
  user?: { name: string }
  commission_rate?: number
}

export interface ClientItem {
  id: number
  full_name: string
  phone?: string | null
  email?: string | null
}

const loading = shallowRef(false)
const services = shallowRef<CatalogService[]>([])
const items = shallowRef<CatalogItem[]>([])
const stylists = shallowRef<CatalogUser[]>([])
const clients = shallowRef<ClientItem[]>([])

export function useCatalogs() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      services.value = unwrapData(await apiFetch('/api/services')) as CatalogService[]
      items.value = unwrapData(await apiFetch('/api/items')) as CatalogItem[]
      stylists.value = unwrapData(await apiFetch('/api/stylists')) as CatalogUser[]
      clients.value = unwrapData(await apiFetch('/api/clients')) as ClientItem[]
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    services,
    items,
    stylists,
    clients,
    load,
  }
}
