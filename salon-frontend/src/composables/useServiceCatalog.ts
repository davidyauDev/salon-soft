import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface ServiceCategory {
  id: number
  name: string
  is_active?: boolean
  sort_order?: number
  services_count?: number
}

export interface ServiceLocation {
  id: number
  name: string
  address?: string | null
  is_active?: boolean
}

export interface ServiceStylist {
  id: number
  user?: { name: string }
  is_active?: boolean
  commission_rate?: number
}

export interface ServiceItem {
  id: number
  category_id?: number | null
  name: string
  description?: string | null
  duration_min?: number | null
  base_price?: number | null
  requires_deposit?: boolean
  deposit_amount?: number | null
  is_active?: boolean
  sort_order?: number | null
  category?: ServiceCategory | null
  locations?: ServiceLocation[]
  stylists?: ServiceStylist[]
}

const loading = shallowRef(false)
const categories = shallowRef<ServiceCategory[]>([])
const services = shallowRef<ServiceItem[]>([])
const locations = shallowRef<ServiceLocation[]>([])
const stylists = shallowRef<ServiceStylist[]>([])

async function load(): Promise<void> {
  loading.value = true
  try {
    const [categoryRes, serviceRes, locationRes, stylistRes] = await Promise.all([
      apiFetch('/api/service-categories'),
      apiFetch('/api/services'),
      apiFetch('/api/locations'),
      apiFetch('/api/stylists'),
    ])
    categories.value = unwrapData(categoryRes) as ServiceCategory[]
    services.value = unwrapData(serviceRes) as ServiceItem[]
    locations.value = unwrapData(locationRes) as ServiceLocation[]
    stylists.value = unwrapData(stylistRes) as ServiceStylist[]
  } finally {
    loading.value = false
  }
}

async function createCategory(payload: Record<string, unknown>): Promise<ServiceCategory> {
  const response = await apiFetch<ServiceCategory>('/api/service-categories', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  const created = unwrapData(response) as ServiceCategory
  categories.value = [...categories.value, created].sort(
    (a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name),
  )
  return created
}

async function updateCategory(categoryId: number, payload: Record<string, unknown>): Promise<ServiceCategory> {
  const response = await apiFetch<ServiceCategory>(`/api/service-categories/${categoryId}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  const updated = unwrapData(response) as ServiceCategory
  categories.value = categories.value
    .map((category) => (category.id === categoryId ? updated : category))
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name))
  return updated
}

async function deleteCategory(categoryId: number): Promise<void> {
  await apiFetch(`/api/service-categories/${categoryId}`, {
    method: 'DELETE',
  })
  await load()
}

async function createService(payload: Record<string, unknown>): Promise<void> {
  await apiFetch('/api/services', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  await load()
}

async function updateService(serviceId: number, payload: Record<string, unknown>): Promise<void> {
  await apiFetch(`/api/services/${serviceId}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  await load()
}

async function deleteService(serviceId: number): Promise<void> {
  await apiFetch(`/api/services/${serviceId}`, {
    method: 'DELETE',
  })
  await load()
}

export function useServiceCatalog() {
  return {
    loading,
    categories,
    services,
    locations,
    stylists,
    load,
    createCategory,
    updateCategory,
    deleteCategory,
    createService,
    updateService,
    deleteService,
  }
}
