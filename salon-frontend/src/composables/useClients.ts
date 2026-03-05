import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface ClientProfile {
  id: number
  full_name: string
  phone?: string
  email?: string
}

export interface ClientHistory {
  client: ClientProfile
  services: Array<{
    id: number
    performed_at: string
    total_amount: number
    service?: { name: string }
  }>
  sales: Array<{
    id: number
    sold_at: string
    total_amount: number
  }>
}

const loading = shallowRef(false)
const clients = shallowRef<ClientProfile[]>([])
const history = shallowRef<ClientHistory | null>(null)

export function useClients() {
  async function load(): Promise<void> {
    loading.value = true
    try {
      const response = await apiFetch<{ data: ClientProfile[] }>('/api/clients')
      clients.value = unwrapData(response)
    } finally {
      loading.value = false
    }
  }

  async function create(payload: Record<string, unknown>): Promise<void> {
    await apiFetch('/api/clients', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function update(clientId: number, payload: Record<string, unknown>): Promise<void> {
    await apiFetch(`/api/clients/${clientId}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
    await load()
  }

  async function remove(clientId: number): Promise<void> {
    await apiFetch(`/api/clients/${clientId}`, { method: 'DELETE' })
    await load()
  }

  async function loadHistory(clientId: number): Promise<void> {
    history.value = await apiFetch<ClientHistory>(`/api/clients/${clientId}/history`)
  }

  return {
    loading,
    clients,
    history,
    load,
    create,
    update,
    remove,
    loadHistory,
  }
}
