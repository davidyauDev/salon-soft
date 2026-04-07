<script setup lang="ts">
import { shallowRef } from 'vue'
import { useAuth } from '../../composables/useAuth'

const auth = useAuth()
const email = shallowRef('admin@salon.test')
const password = shallowRef('secret123')
const rememberMe = shallowRef(true)
const errorMessage = shallowRef<string | null>(null)
const loading = shallowRef(false)
const loginImage = '/images/login-trendbelleza-premium.jpg'

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
  <main class="login-page">
    <section class="login-form-panel">
      <div class="login-form-content">
        <div class="login-copy">
          <span class="copy-eyebrow">Trend Belleza</span>
          <h1>Iniciar sesion</h1>
          <p>Ingresa tu usuario y contrasena para administrar citas, servicios y clientes.</p>
        </div>

        <form class="login-form" @submit.prevent="submit">
          <label>
            Correo electronico o usuario
            <input
              v-model="email"
              class="input login-input"
              type="text"
              autocomplete="username"
              placeholder="admin@salon.test"
              :disabled="loading"
            />
          </label>

          <div class="password-row">
            <label>
              Contrasena
              <input
                v-model="password"
                class="input login-input"
                type="password"
                autocomplete="current-password"
                placeholder="********"
                :disabled="loading"
              />
            </label>
            <a class="forgot-link" href="#" @click.prevent>Olvido su contrasena?</a>
          </div>

          <label class="remember-row">
            <input v-model="rememberMe" type="checkbox" :disabled="loading" />
            <span>Mantener sesion activa</span>
          </label>

          <button class="login-button" type="submit" :disabled="loading">
            {{ loading ? 'Ingresando...' : 'Iniciar sesion' }}
          </button>

          <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
          <p class="login-note">No tiene una cuenta? Comunicate con un administrador.</p>
        </form>
      </div>
    </section>

    <section class="login-hero-panel" aria-hidden="true">
      <img class="hero-image" :src="loginImage" alt="" />
      <div class="hero-overlay"></div>

      <div class="hero-copy">
        <div class="hero-kicker">Operación de alto valor</div>
        <h2>Una herramienta seria para salones con identidad fuerte.</h2>
        <p>
          Controla citas, clientas, inventario y pagos desde un portal pensado para uso diario,
          con una estética alineada a Trend Belleza.
        </p>
        <div class="hero-accent"></div>
        <p class="hero-subcopy">Belleza, agenda y operación en un solo lugar.</p>
      </div>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  background: #fff;
}

.login-form-panel {
  display: grid;
  align-items: center;
  justify-items: center;
  padding: clamp(40px, 6vw, 96px);
  background: #fff;
}

.login-form-content {
  width: min(100%, 520px);
  display: grid;
  gap: 28px;
}

.login-copy {
  display: grid;
  gap: 10px;
  padding-left: 16px;
  border-left: 4px solid var(--accent);
}

.copy-eyebrow {
  color: #111111;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.34em;
  text-transform: uppercase;
}

.login-copy h1 {
  margin: 0;
  color: #191919;
  font-family: var(--font-display);
  font-size: clamp(2.2rem, 4vw, 3.4rem);
  line-height: 0.95;
  letter-spacing: -0.05em;
}

.login-copy p {
  margin: 0;
  max-width: 34ch;
  color: #6f6963;
  font-size: 1rem;
}

.login-form {
  display: grid;
  gap: 18px;
}

.login-form label {
  display: grid;
  gap: 9px;
  color: #191919;
  font-size: 0.86rem;
  font-weight: 700;
}

.login-input {
  height: 58px;
  background: #fffdfa;
  border-color: rgba(25, 25, 25, 0.12);
  border-radius: 14px;
  padding-inline: 16px;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
}

.password-row {
  display: grid;
  gap: 10px;
}

.forgot-link {
  justify-self: end;
  color: var(--accent);
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 700;
}

.remember-row {
  display: inline-flex !important;
  align-items: center;
  gap: 10px;
  color: #191919 !important;
  font-size: 0.9rem !important;
  font-weight: 500 !important;
}

.remember-row input {
  width: 16px;
  height: 16px;
  accent-color: var(--accent);
}

.login-button {
  margin-top: 6px;
  width: 100%;
  height: 56px;
  border: none;
  border-radius: 16px;
  background: #111111;
  color: #fff;
  font-size: 1rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 18px 30px rgba(17, 17, 17, 0.14);
}

.login-button:hover:not(:disabled) {
  transform: translateY(-1px);
  background: var(--accent);
}

.login-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-error {
  margin: 0;
  color: #8a3b37;
  font-size: 0.88rem;
  font-weight: 600;
}

.login-note {
  margin: 2px 0 0;
  color: #6f6963;
  font-size: 0.88rem;
}

.login-hero-panel {
  position: relative;
  overflow: hidden;
  min-height: 100vh;
  background:
    linear-gradient(135deg, rgba(17, 17, 17, 0.96), rgba(31, 31, 31, 0.9)),
    #111111;
}

.hero-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  opacity: 0.34;
  filter: grayscale(1) contrast(1.08);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 70% 28%, rgba(255, 255, 255, 0.08), transparent 30%),
    radial-gradient(circle at 18% 75%, rgba(15, 118, 110, 0.18), transparent 28%),
    linear-gradient(180deg, rgba(17, 17, 17, 0.18), rgba(17, 17, 17, 0.84));
}

.hero-copy {
  position: relative;
  z-index: 1;
  height: 100%;
  display: grid;
  align-content: center;
  justify-items: start;
  gap: 20px;
  padding: clamp(48px, 7vw, 100px);
  color: #fff;
}

.hero-kicker {
  color: var(--accent);
  font-size: 0.82rem;
  font-weight: 800;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.hero-copy h2 {
  margin: 0;
  max-width: 13ch;
  font-family: var(--font-display);
  font-size: clamp(2.6rem, 5vw, 4.8rem);
  line-height: 0.92;
  letter-spacing: -0.05em;
  text-wrap: balance;
}

.hero-copy p {
  margin: 0;
  max-width: 30ch;
  font-size: 1.05rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.9);
}

.hero-accent {
  width: 92px;
  height: 6px;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--accent), #d8efe9);
}

.hero-subcopy {
  max-width: 24ch;
  font-size: 0.98rem !important;
  color: rgba(255, 255, 255, 0.8) !important;
}

@media (max-width: 900px) {
  .login-page {
    grid-template-columns: 1fr;
  }

  .login-hero-panel {
    min-height: 44vh;
    order: -1;
  }

  .hero-copy {
    padding: 40px 28px;
  }
}

@media (max-width: 560px) {
  .login-form-panel {
    padding: 24px 16px 32px;
  }

  .login-form-content {
    gap: 22px;
  }

  .login-copy {
    padding-left: 12px;
  }

  .login-hero-panel {
    min-height: 34vh;
  }

  .hero-copy h2 {
    max-width: 11ch;
  }
}
</style>
