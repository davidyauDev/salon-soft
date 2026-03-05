<script setup lang="ts">
import { shallowRef } from 'vue'
import { useAuth } from '../../composables/useAuth'

const auth = useAuth()
const email = shallowRef('admin@salon.test')
const password = shallowRef('secret123')
const errorMessage = shallowRef<string | null>(null)
const loading = shallowRef(false)

async function submit(): Promise<void> {
  loading.value = true
  errorMessage.value = null
  try {
    await auth.login(email.value, password.value)
  } catch (error) {
    errorMessage.value = (error as Error).message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrap">
    <section class="login-card">
      <div class="login-head">
        <span class="login-chip">Salon Control</span>
        <h1>Inicia sesion</h1>
        <p>Accede al control operativo del salon.</p>
      </div>

      <form class="login-form" @submit.prevent="submit">
        <label>
          Correo
          <input class="input" v-model="email" type="email" placeholder="admin@salon.test" />
        </label>
        <label>
          Contrasena
          <input class="input" v-model="password" type="password" placeholder="********" />
        </label>

        <button class="btn-primary login-button" type="submit" :disabled="loading">
          {{ loading ? 'Ingresando...' : 'Ingresar' }}
        </button>

        <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
      </form>
    </section>
  </div>
</template>

<style scoped>
.login-wrap {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 40px;
}

.login-card {
  width: min(520px, 92vw);
  background: rgba(255, 255, 255, 0.95);
  border-radius: 28px;
  padding: 36px;
  border: 1px solid rgba(17, 15, 20, 0.08);
  box-shadow: 0 18px 40px rgba(24, 18, 36, 0.12);
  display: grid;
  gap: 24px;
}

.login-chip {
  display: inline-flex;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(214, 106, 86, 0.1);
  color: var(--accent);
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.login-head h1 {
  margin: 12px 0 4px;
  font-family: var(--font-display);
  font-size: 2rem;
}

.login-head p {
  margin: 0;
  color: var(--ink-muted);
}

.login-form {
  display: grid;
  gap: 16px;
}

.login-form label {
  font-size: 0.8rem;
  color: var(--ink-muted);
  display: grid;
  gap: 8px;
}

.login-button {
  width: 100%;
  padding: 14px;
  border-radius: 16px;
}

.login-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-error {
  margin: 0;
  color: #b24b3a;
  font-size: 0.85rem;
}
</style>
