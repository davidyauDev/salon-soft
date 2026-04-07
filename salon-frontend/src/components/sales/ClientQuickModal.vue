<script setup lang="ts">
import { reactive, watch } from 'vue'

const props = defineProps<{
  open: boolean
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'save', payload: {
    first_name: string
    last_name: string
    phone: string | null
    email: string | null
    address: string | null
    dni: string | null
    birth_date: string | null
    gender: string | null
  }): void
}>()

const form = reactive({
  first_name: '',
  last_name: '',
  phone_country: '+51',
  phone_number: '',
  email: '',
  address: '',
  dni: '',
  birth_date: '',
  gender: '',
  showMore: false,
})

watch(
  () => props.open,
  (open) => {
    if (!open) return
    form.first_name = ''
    form.last_name = ''
    form.phone_country = '+51'
    form.phone_number = ''
    form.email = ''
    form.address = ''
    form.dni = ''
    form.birth_date = ''
    form.gender = ''
    form.showMore = false
  },
)

function submit(): void {
  emit('save', {
    first_name: form.first_name.trim(),
    last_name: form.last_name.trim(),
    phone: form.phone_number.trim() ? `${form.phone_country}${form.phone_number.trim()}` : null,
    email: form.email.trim() || null,
    address: form.address.trim() || null,
    dni: form.dni.trim() || null,
    birth_date: form.birth_date || null,
    gender: form.gender || null,
  })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <h3>Crear nuevo cliente</h3>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <form class="form-grid" @submit.prevent="submit">
        <div class="row two-col">
          <label>
            Nombres *
            <input v-model="form.first_name" required placeholder="Nombres" :disabled="props.saving" />
          </label>
          <label>
            Apellidos
            <input v-model="form.last_name" placeholder="Apellidos" :disabled="props.saving" />
          </label>
        </div>

        <label>
          Numero de celular
          <div class="phone-field">
            <select v-model="form.phone_country" :disabled="props.saving">
              <option value="+51">+51</option>
              <option value="+57">+57</option>
              <option value="+56">+56</option>
              <option value="+54">+54</option>
              <option value="+1">+1</option>
              <option value="+34">+34</option>
            </select>
            <input v-model="form.phone_number" placeholder="999 888 777" :disabled="props.saving" />
          </div>
        </label>

        <label>
          Email
          <input v-model="form.email" type="email" placeholder="Email" :disabled="props.saving" />
        </label>

        <button class="more-btn" type="button" @click="form.showMore = !form.showMore">
          <span>{{ form.showMore ? 'Ocultar' : 'Mas informacion' }}</span>
        </button>

        <div v-if="form.showMore" class="more-grid">
          <label>
            Direccion
            <input v-model="form.address" placeholder="Direccion" :disabled="props.saving" />
          </label>
          <label>
            DNI
            <input v-model="form.dni" placeholder="DNI" :disabled="props.saving" />
          </label>
          <div class="row two-col">
            <label>
              Fecha de nac.
              <input v-model="form.birth_date" type="date" :disabled="props.saving" />
            </label>
            <label>
              Genero
              <select v-model="form.gender" :disabled="props.saving">
                <option value="">Genero</option>
                <option value="female">Femenino</option>
                <option value="male">Masculino</option>
                <option value="other">Otro</option>
                <option value="unspecified">No especifica</option>
              </select>
            </label>
          </div>
        </div>

        <div class="actions">
          <button class="btn-ghost" type="button" @click="emit('close')" :disabled="props.saving">
            Cancelar
          </button>
          <button class="btn-primary" type="submit" :disabled="props.saving">
            {{ props.saving ? 'Creando...' : 'Crear cliente' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 20, 0.45);
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 60;
}

.modal-card {
  width: min(560px, 100%);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 16px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-button {
  border: none;
  background: rgba(17, 15, 20, 0.08);
  width: 36px;
  height: 36px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1rem;
}

.form-grid {
  display: grid;
  gap: 14px;
}

.row.two-col {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

label {
  display: grid;
  gap: 8px;
  font-size: 0.8rem;
  color: #6f6770;
}

input,
select {
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: 12px;
  padding: 12px 14px;
  background: #fff;
  font-size: 0.95rem;
}

.phone-field {
  display: grid;
  grid-template-columns: 90px 1fr;
  gap: 8px;
}

.more-btn {
  border: none;
  background: transparent;
  color: var(--accent);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.more-grid {
  display: grid;
  gap: 12px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary {
  background: #111111;
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 10px 18px;
  font-weight: 700;
}

.btn-ghost {
  background: #fffdfb;
  border: 1px solid rgba(25, 25, 25, 0.12);
  border-radius: 12px;
  padding: 10px 18px;
  font-weight: 700;
}

@media (max-width: 600px) {
  .row.two-col {
    grid-template-columns: 1fr;
  }
}
</style>
