<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { ClientFormState } from '../../types/client'

const props = defineProps<{
  initial: ClientFormState
  saving?: boolean
  isEditing?: boolean
}>()

const emit = defineEmits<{
  (e: 'save', payload: ClientFormState): void
  (e: 'close'): void
}>()

const form = reactive<ClientFormState>({
  first_name: '',
  last_name: '',
  phone_country: '+51',
  phone_number: '',
  email: '',
  dni: '',
  address: '',
  birth_date: '',
  gender: '',
  notes: '',
})

watch(
  () => props.initial,
  (value) => {
    Object.assign(form, value)
  },
  { immediate: true, deep: true },
)

const title = computed(() => (props.isEditing ? 'Editar cliente' : 'Crear nuevo cliente'))
const confirmLabel = computed(() => (props.isEditing ? 'Actualizar' : 'Confirmar'))

const calendarPreview = computed(() => {
  if (!form.birth_date) {
    return { day: '--', month: 'Selecciona fecha', year: '' }
  }
  const date = new Date(form.birth_date)
  if (Number.isNaN(date.getTime())) {
    return { day: '--', month: 'Fecha invalida', year: '' }
  }
  const day = new Intl.DateTimeFormat('es-PE', { day: '2-digit' }).format(date)
  const month = new Intl.DateTimeFormat('es-PE', { month: 'long' }).format(date)
  const year = new Intl.DateTimeFormat('es-PE', { year: 'numeric' }).format(date)
  return { day, month, year }
})

function submit(): void {
  emit('save', { ...form })
}
</script>

<template>
  <div class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <div>
          <h3>{{ title }}</h3>
          <p class="panel-subtitle">Completa los datos clave para reservas, ventas y fidelizacion.</p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <form class="form-grid two-col" @submit.prevent="submit">
        <label>
          Nombre
          <input v-model="form.first_name" required placeholder="Ingresa el nombre" />
        </label>
        <label>
          Apellidos
          <input v-model="form.last_name" required placeholder="Ingresa los apellidos" />
        </label>

        <label>
          Numero de celular
          <div class="phone-field">
            <select v-model="form.phone_country" class="select">
              <option value="+51">+51</option>
              <option value="+57">+57</option>
              <option value="+56">+56</option>
              <option value="+54">+54</option>
              <option value="+1">+1</option>
              <option value="+34">+34</option>
            </select>
            <input v-model="form.phone_number" placeholder="999 888 777" />
          </div>
        </label>
        <label>
          Correo electronico
          <input v-model="form.email" type="email" placeholder="cliente@email.com" />
        </label>

        <label>
          DNI
          <input v-model="form.dni" placeholder="Documento de identidad" />
        </label>
        <label>
          Direccion
          <input v-model="form.address" placeholder="Ingresa la direccion" />
        </label>

        <label class="date-group">
          Fecha de nacimiento
          <div class="date-row">
            <div class="date-input">
              <input v-model="form.birth_date" type="date" />
              <span class="calendar-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                  <path
                    d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v12A2.5 2.5 0 0 1 19.5 21h-15A2.5 2.5 0 0 1 2 18.5v-12A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 7h-15v9.5a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5V9ZM4.5 6a.5.5 0 0 0-.5.5V7h15v-.5a.5.5 0 0 0-.5-.5h-14Z"
                  />
                </svg>
              </span>
            </div>
            <div class="calendar-preview">
              <span class="day">{{ calendarPreview.day }}</span>
              <span class="month">{{ calendarPreview.month }}</span>
              <span class="year">{{ calendarPreview.year }}</span>
            </div>
          </div>
        </label>
        <label>
          Genero
          <select v-model="form.gender" class="select">
            <option value="">Selecciona</option>
            <option value="female">Femenino</option>
            <option value="male">Masculino</option>
            <option value="other">Otro</option>
            <option value="unspecified">No especifica</option>
          </select>
        </label>

        <label class="full">
          Notas
          <textarea v-model="form.notes" rows="3" placeholder="Preferencias, alergias o comentarios utiles"></textarea>
        </label>

        <div class="form-actions">
          <button class="btn-ghost" type="button" :disabled="props.saving" @click="emit('close')">
            Cancelar
          </button>
          <button class="btn-primary" type="submit" :disabled="props.saving">
            {{ confirmLabel }}
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
  background: rgba(15, 12, 20, 0.5);
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 40;
}

.modal-card {
  width: min(760px, 100%);
  max-height: 90vh;
  overflow: auto;
  background: var(--panel-bg);
  border-radius: var(--radius-xl);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 20px 45px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 18px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
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

.form-grid textarea {
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: var(--radius-md);
  padding: 12px 14px;
  font-size: 0.95rem;
  resize: vertical;
}

.form-grid .full {
  grid-column: 1 / -1;
}

.phone-field {
  display: grid;
  grid-template-columns: 90px 1fr;
  gap: 8px;
}

.date-group {
  grid-column: 1 / -1;
}

.date-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 12px;
  align-items: center;
}

.date-input {
  position: relative;
  display: flex;
  align-items: center;
}

.date-input input {
  width: 100%;
  padding-right: 40px;
}

.calendar-icon {
  position: absolute;
  right: 12px;
  width: 18px;
  height: 18px;
  color: var(--ink-muted);
}

.calendar-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.calendar-preview {
  border-radius: var(--radius-md);
  border: 1px dashed rgba(23, 20, 26, 0.2);
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.9);
  display: grid;
  gap: 4px;
  text-transform: capitalize;
}

.calendar-preview .day {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--ink-strong);
}

.calendar-preview .month {
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.calendar-preview .year {
  font-size: 0.75rem;
  color: var(--ink-muted);
}

@media (max-width: 760px) {
  .modal-card {
    width: 100%;
  }

  .date-row {
    grid-template-columns: 1fr;
  }
}
</style>
