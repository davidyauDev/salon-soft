import { shallowRef } from 'vue'
import { apiFetch, unwrapData } from '../lib/api'

export interface AuditLogEntry {
  id: number
  action: string
  entity_type: string
  entity_id: number | null
  old_values?: Record<string, unknown> | null
  new_values?: Record<string, unknown> | null
  ip_address?: string | null
  user_agent?: string | null
  created_at: string
  user?: { id: number; name?: string; email?: string }
}

const loading = shallowRef(false)
const logs = shallowRef<AuditLogEntry[]>([])
const meta = shallowRef<Record<string, unknown> | null>(null)

export function useAuditLogs() {
  async function load(params: Record<string, string | number | null | undefined> = {}): Promise<void> {
    loading.value = true
    try {
      const search = new URLSearchParams()
      Object.entries(params).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') return
        search.set(key, String(value))
      })
      const query = search.toString()
      const response = await apiFetch<{ data: AuditLogEntry[]; meta?: Record<string, unknown> }>(
        `/api/audit-logs${query ? `?${query}` : ''}`,
      )
      logs.value = unwrapData(response)
      meta.value = (response as { meta?: Record<string, unknown> }).meta ?? null
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    logs,
    meta,
    load,
  }
}
