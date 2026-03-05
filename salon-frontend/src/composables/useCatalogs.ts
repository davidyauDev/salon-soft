import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface CatalogItem {
  id: number
  name: string
}

export interface CatalogUser {
  id: number
  user?: { name: string }
  commission_rate?: number
}

export interface ClientItem {
  id: number
  full_name: string
}

const loading = shallowRef(false)
const services = shallowRef<CatalogItem[]>([])
const items = shallowRef<Array<{ id: number; name: string; base_unit: string }>>([])
const stylists = shallowRef<CatalogUser[]>([])
const clients = shallowRef<ClientItem[]>([])

export function useCatalogs() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      services.value = unwrapData(await apiFetch('/api/services')) as CatalogItem[]
      items.value = unwrapData(await apiFetch('/api/items')) as Array<{
        id: number
        name: string
        base_unit: string
      }>
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
