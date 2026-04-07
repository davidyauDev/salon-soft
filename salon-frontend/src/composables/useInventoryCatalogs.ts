import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface InventoryCategory {
  id: number
  name: string
  description?: string | null
  is_active: boolean
}

export interface InventoryBrand {
  id: number
  name: string
  is_active: boolean
}

export interface InventorySupplier {
  id: number
  name: string
  contact_name?: string | null
  phone?: string | null
  email?: string | null
  address?: string | null
  notes?: string | null
  is_active: boolean
}

const loading = shallowRef(false)
const categories = shallowRef<InventoryCategory[]>([])
const brands = shallowRef<InventoryBrand[]>([])
const suppliers = shallowRef<InventorySupplier[]>([])
const error = shallowRef<string | null>(null)

export function useInventoryCatalogs() {
  async function load(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      categories.value = unwrapData(await apiFetch('/api/categories')) as InventoryCategory[]
      brands.value = unwrapData(await apiFetch('/api/brands')) as InventoryBrand[]
      suppliers.value = unwrapData(await apiFetch('/api/suppliers')) as InventorySupplier[]
    } catch (err) {
      error.value = (err as Error).message
    } finally {
      loading.value = false
    }
  }

  async function createCategory(payload: {
    name: string
    description?: string | null
  }): Promise<InventoryCategory> {
    const response = await apiFetch<InventoryCategory>('/api/categories', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    const created = unwrapData(response) as InventoryCategory
    categories.value = [...categories.value, created].sort((a, b) => a.name.localeCompare(b.name))
    return created
  }

  async function updateCategory(id: number, payload: {
    name: string
    description?: string | null
  }): Promise<InventoryCategory> {
    const response = await apiFetch<InventoryCategory>(`/api/categories/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
    const updated = unwrapData(response) as InventoryCategory
    categories.value = categories.value
      .map((category) => (category.id === id ? updated : category))
      .sort((a, b) => a.name.localeCompare(b.name))
    return updated
  }

  async function deleteCategory(id: number): Promise<void> {
    await apiFetch(`/api/categories/${id}`, { method: 'DELETE' })
    categories.value = categories.value.filter((category) => category.id !== id)
  }

  async function createBrand(payload: { name: string }): Promise<void> {
    await apiFetch('/api/brands', { method: 'POST', body: JSON.stringify(payload) })
    await load()
  }

  async function createSupplier(payload: {
    name: string
    contact_name?: string | null
    phone?: string | null
    email?: string | null
  }): Promise<void> {
    await apiFetch('/api/suppliers', { method: 'POST', body: JSON.stringify(payload) })
    await load()
  }

  return {
    loading,
    error,
    categories,
    brands,
    suppliers,
    load,
    createCategory,
    updateCategory,
    deleteCategory,
    createBrand,
    createSupplier,
  }
}
