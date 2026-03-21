<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { InventoryCategory } from '../../composables/useInventoryCatalogs'

const props = defineProps<{
  open: boolean
  saving: boolean
  initial?: InventoryCategory | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: { name: string }): void
}>()

const form = reactive({ name: '' })
const error = reactive({ name: '' })

const isEditing = computed(() => Boolean(props.initial))

watch(
  () => [props.open, props.initial],
  ([open, initial]) => {
    if (!open) return
    form.name = initial?.name ?? ''
    error.name = ''
  },
  { immediate: true },
)

function requestClose(): void {
  if (props.saving) return
  emit('close')
}

function handleSubmit(): void {
  error.name = ''
  if (!form.name.trim()) {
    error.name = 'Ingresa un nombre válido'
    return
  }
  emit('submit', { name: form.name.trim() })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="requestClose">
    <div class="modal-card">
      <div v-if="props.saving" class="saving-overlay" aria-live="polite">
        <span class="saving-spinner" aria-hidden="true"></span>
        <div class="saving-copy">
          <strong>{{ isEditing ? 'Actualizando categoria...' : 'Creando categoria...' }}</strong>
          <p>Guardando cambios y actualizando el listado.</p>
        </div>
      </div>

      <header class="modal-header">
        <h3>{{ isEditing ? 'Editar Categoria' : 'Crear Nueva Categoria' }}</h3>
        <button class="close-button" type="button" :disabled="props.saving" @click="requestClose">X</button>
      </header>

      <form class="form-grid" @submit.prevent="handleSubmit">
        <label class="field">
          Nombre de la Categoria *
          <input v-model="form.name" placeholder="Ej: Cuidado de cabello" :class="{ error: error.name }" :disabled="props.saving" />
          <span v-if="error.name" class="error-text">{{ error.name }}</span>
        </label>
        <div class="form-actions">
          <button class="btn-ghost" type="button" @click="requestClose" :disabled="props.saving">Cancelar</button>
          <button class="btn-primary submit-btn" type="submit" :disabled="props.saving">
            <span v-if="props.saving" class="btn-spinner" aria-hidden="true"></span>
            {{ props.saving ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear categoria' }}
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
  padding: 20px;
  z-index: 40;
}

.modal-card {
  position: relative;
  width: min(620px, 100%);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 16px;
}

.saving-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.76);
  backdrop-filter: blur(6px);
  border-radius: 18px;
}

.saving-copy {
  display: grid;
  gap: 4px;
  max-width: 240px;
}

.saving-copy strong {
  color: #1f1d29;
}

.saving-copy p {
  margin: 0;
  color: #6f6770;
  font-size: 0.9rem;
}

.saving-spinner,
.btn-spinner {
  width: 18px;
  height: 18px;
  border-radius: 999px;
  border: 2px solid rgba(90, 75, 255, 0.18);
  border-top-color: #5a4bff;
  animation: spin 0.8s linear infinite;
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

.close-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-grid {
  display: grid;
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 0.8rem;
  color: #6f6770;
}

.field input {
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: 12px;
  padding: 12px 14px;
  background: #f7f7fb;
  font-size: 0.95rem;
}

.field input.error {
  border-color: #f43f5e;
  box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.12);
}

.error-text {
  color: #f43f5e;
  font-size: 0.75rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-spinner {
  width: 14px;
  height: 14px;
  border-color: rgba(255, 255, 255, 0.28);
  border-top-color: #fff;
}

.btn-primary {
  padding: 10px 18px;
  border-radius: 12px;
  border: none;
  background: #8d84ff;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled,
.btn-ghost:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-ghost {
  padding: 10px 18px;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
  font-weight: 600;
  cursor: pointer;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

