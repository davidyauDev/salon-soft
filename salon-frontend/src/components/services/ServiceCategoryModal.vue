<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { ServiceCategory } from '../../composables/useServiceCatalog'

const props = defineProps<{
  open: boolean
  saving: boolean
  initial?: ServiceCategory | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: { name: string; is_active: boolean }): void
}>()

const form = reactive({
  name: '',
  is_active: true,
})

const isEditing = computed(() => Boolean(props.initial))

watch(
  () => [props.open, props.initial] as const,
  ([open, initial]) => {
    if (open) {
      form.name = initial?.name ?? ''
      form.is_active = initial?.is_active ?? true
      return
    }
    form.name = ''
    form.is_active = true
  },
  { immediate: true },
)

function requestClose(): void {
  if (props.saving) return
  emit('close')
}

function handleSubmit(): void {
  if (!form.name.trim()) return
  emit('submit', { name: form.name.trim(), is_active: form.is_active })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="requestClose">
    <div class="modal-card">
      <div v-if="props.saving" class="saving-overlay" aria-live="polite">
        <span class="saving-spinner" aria-hidden="true"></span>
        <div class="saving-copy">
          <strong>{{ isEditing ? 'Actualizando categoria...' : 'Creando categoria...' }}</strong>
          <p>Guardando cambios y cerrando el formulario.</p>
        </div>
      </div>

      <header class="modal-header">
        <div>
          <h3>{{ isEditing ? 'Editar categoria' : 'Nueva categoria' }}</h3>
          <p class="panel-subtitle">
            {{ isEditing ? 'Actualiza el nombre y estado de la categoria.' : 'Crea una categoria para organizar tus servicios.' }}
          </p>
        </div>
        <button class="close-button" type="button" :disabled="props.saving" @click="requestClose">X</button>
      </header>

      <form class="form-grid" @submit.prevent="handleSubmit">
        <label>
          Nombre de la categoria
          <input v-model="form.name" required placeholder="Ej: Cortes de Cabello" :disabled="props.saving" />
        </label>
        <label class="toggle-field">
          <span>Categoria activa</span>
          <input v-model="form.is_active" type="checkbox" :disabled="props.saving" />
        </label>
        <div class="form-actions">
          <button class="btn-primary submit-btn" type="submit" :disabled="props.saving">
            <span v-if="props.saving" class="btn-spinner" aria-hidden="true"></span>
            {{ isEditing ? 'Guardar cambios' : 'Crear categoria' }}
          </button>
          <button class="btn-ghost" type="button" :disabled="props.saving" @click="requestClose">
            Cancelar
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
  width: min(520px, 100%);
  background: var(--panel-bg);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
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
  border-radius: var(--radius-lg);
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
  color: var(--ink-muted);
  font-size: 0.9rem;
}

.saving-spinner,
.btn-spinner {
  width: 18px;
  height: 18px;
  border-radius: 999px;
  border: 2px solid rgba(15, 118, 110, 0.18);
  border-top-color: var(--accent);
  animation: spin 0.8s linear infinite;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.close-button {
  border: none;
  background: rgba(25, 25, 25, 0.08);
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

.toggle-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(23, 20, 26, 0.12);
  background: rgba(255, 255, 255, 0.95);
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.toggle-field input {
  width: 42px;
  height: 22px;
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

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
