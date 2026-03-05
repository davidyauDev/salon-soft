const STORAGE_KEY = 'salon_token'

export function getToken(): string | null {
  return localStorage.getItem(STORAGE_KEY)
}

export function setToken(token: string | null): void {
  if (token) {
    localStorage.setItem(STORAGE_KEY, token)
  } else {
    localStorage.removeItem(STORAGE_KEY)
  }
}

export async function apiFetch<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const baseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'
  const headers = new Headers(options.headers)

  if (!headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  const token = getToken()
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  const response = await fetch(`${baseUrl}${path}`, {
    ...options,
    headers,
  })

  if (response.status === 204) {
    return null as T
  }

  const data = (await response.json()) as { message?: string } & T

  if (!response.ok) {
    throw new Error(data?.message ?? 'Error en la solicitud')
  }

  return data as T
}

export function unwrapData<T>(payload: T | { data: T }): T {
  if (payload && typeof payload === 'object' && 'data' in payload) {
    return (payload as { data: T }).data
  }
  return payload as T
}
