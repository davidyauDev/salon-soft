import { computed, shallowRef } from 'vue'
import { apiFetch, getToken, setToken } from '../lib/api'

interface UserProfile {
  id: number
  name: string
  email: string
  role: string
}

const token = shallowRef<string | null>(getToken())
const user = shallowRef<UserProfile | null>(null)
const ready = shallowRef(false)

export function useAuth() {
  const isAuthenticated = computed(() => Boolean(token.value))

  async function initialize(): Promise<void> {
    if (token.value) {
      try {
        user.value = await apiFetch<UserProfile>('/api/auth/me')
      } catch {
        token.value = null
        setToken(null)
      }
    }
    ready.value = true
  }

  async function login(email: string, password: string): Promise<void> {
    const response = await apiFetch<{
      token: string
      user: UserProfile
    }>('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({
        email,
        password,
        device_name: 'salon-frontend',
      }),
    })

    token.value = response.token
    user.value = response.user
    setToken(response.token)
  }

  async function logout(): Promise<void> {
    try {
      await apiFetch('/api/auth/logout', { method: 'POST' })
    } finally {
      token.value = null
      user.value = null
      setToken(null)
    }
  }

  return {
    user,
    ready,
    isAuthenticated,
    initialize,
    login,
    logout,
  }
}
