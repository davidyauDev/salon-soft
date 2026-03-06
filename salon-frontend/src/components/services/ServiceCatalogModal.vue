<script setup lang="ts">
import { computed, reactive, watch } from 'vue'

const props = defineProps<{
  open: boolean
  saving: boolean
  initial?: { id: number; name: string; base_price: number } | null
}>()

const emit = defineEmits<{
  close: []
  submit: [payload: { name: string; base_price: number }]
}>()

const form = reactive({
  name: '',
  base_price: '',
})

const isEditing = computed(() => Boolean(props.initial))

watch(
  () => [props.open, props.initial],
  ([open, initial]) => {
    if (open) {
      form.name = initial?.name ?? ''
      form.base_price = initial?.base_price != null ? String(initial.base_price) : ''
      return
    }
    form.name = ''
    form.base_price = ''
  },
  { immediate: true },
)

function handleSubmit(): void {
  if (!form.name.trim()) return
  emit('submit', {
    name: form.name.trim(),
    base_price: Number(form.base_price || 0),
  })
}

function handleClose(): void {
  emit('close')
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="handleClose">
    <div class="modal-card">
      <div class="panel-header">
        <div>
          <h3>{{ isEditing ? 'Editar servicio (catalogo)' : 'Nuevo servicio (catalogo)' }}</h3>
          <p class="panel-subtitle">
            {{ isEditing ? 'Ajusta nombre y precio base del servicio.' : 'Define precio base y habilita el servicio en agenda.' }}
          </p>
        </div>
      </div>
      <form class="form-grid" @submit.prevent="handleSubmit">
        <label>
          Nombre
          <input v-model="form.name" required />
        </label>
        <label>
          Precio base
          <input v-model="form.base_price" type="number" step="0.01" />
        </label>
        <div class="form-actions">
          <button class="btn-primary" type="submit" :disabled="props.saving">
            {{ isEditing ? 'Guardar cambios' : 'Guardar servicio' }}
          </button>
          <button class="btn-ghost" type="button" :disabled="props.saving" @click="handleClose">
            Cerrar
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
  width: min(520px, 100%);
  background: var(--panel-bg);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
  display: grid;
  gap: 16px;
}
</style>
