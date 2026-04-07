<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { InventoryCategory } from '../../composables/useInventoryCatalogs'
import type { ProductItem } from '../../composables/useProducts'

const props = defineProps<{
  open: boolean
  saving: boolean
  categories: InventoryCategory[]
  initial?: ProductItem | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: {
    name: string
    category_id: number
    brand: string
    stock: string
    cost: string
    price: string
    description: string
  }): void
  (e: 'create-category'): void
}>()

const form = reactive({
  name: '',
  category_id: '',
  brand: '',
  stock: '',
  cost: '',
  price: '',
  description: '',
})

const errors = reactive({
  name: '',
  category_id: '',
  stock: '',
  cost: '',
  price: '',
})

const isEditing = computed(() => Boolean(props.initial))
const MAX_STOCK = 999999999
const MAX_MONEY = 9999999999.99

function averageCost(item: ProductItem | null | undefined): string {
  if (!item?.lots?.length) return ''
  const total = item.lots.reduce((sum, lot) => sum + Number(lot.cost_per_base || 0), 0)
  return (total / item.lots.length).toFixed(2)
}

watch(
  () => [props.open, props.initial] as const,
  ([open, initial]) => {
    if (!open) return
    form.name = initial?.name ?? ''
    form.category_id = initial?.category?.id ? String(initial.category.id) : ''
    form.brand = initial?.brand?.name ?? ''
    form.stock = initial?.stock_total ? String(Math.round(initial.stock_total)) : ''
    form.cost = averageCost(initial)
    form.price = initial?.sale_price !== null && initial?.sale_price !== undefined ? String(initial.sale_price) : ''
    form.description = initial?.description ?? ''
    errors.name = ''
    errors.category_id = ''
    errors.stock = ''
    errors.cost = ''
    errors.price = ''
  },
  { immediate: true },
)

function normalizeValue(value: string | number): string {
  return String(value ?? '').trim()
}

function requestClose(): void {
  if (props.saving) return
  emit('close')
}

function parseOptionalNumber(value: string): number | null {
  const normalized = normalizeValue(value)
  if (!normalized) return null
  const parsed = Number(normalized)
  return Number.isFinite(parsed) ? parsed : Number.NaN
}

function handleSubmit(): void {
  errors.name = ''
  errors.category_id = ''
  errors.stock = ''
  errors.cost = ''
  errors.price = ''

  const stockValue = parseOptionalNumber(form.stock)
  const costValue = parseOptionalNumber(form.cost)
  const priceValue = parseOptionalNumber(form.price)

  if (!normalizeValue(form.name)) {
    errors.name = 'Ingresa un nombre válido'
  }
  if (!form.category_id) {
    errors.category_id = 'Selecciona una categoría'
  }
  if (stockValue !== null && (Number.isNaN(stockValue) || stockValue < 0 || stockValue > MAX_STOCK)) {
    errors.stock = `Ingresa un stock entre 0 y ${MAX_STOCK.toLocaleString('en-US')}`
  }
  if (costValue !== null && (Number.isNaN(costValue) || costValue < 0 || costValue > MAX_MONEY)) {
    errors.cost = 'Ingresa un costo válido y menor a 9,999,999,999.99'
  }
  if (priceValue !== null && (Number.isNaN(priceValue) || priceValue < 0 || priceValue > MAX_MONEY)) {
    errors.price = 'Ingresa un precio válido y menor a 9,999,999,999.99'
  }

  if (errors.name || errors.category_id || errors.stock || errors.cost || errors.price) return

  emit('submit', {
    name: normalizeValue(form.name),
    category_id: Number(form.category_id),
    brand: normalizeValue(form.brand),
    stock: normalizeValue(form.stock),
    cost: normalizeValue(form.cost),
    price: normalizeValue(form.price),
    description: normalizeValue(form.description),
  })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="requestClose">
    <div class="modal-card product-modal">
      <div v-if="props.saving" class="saving-overlay" aria-live="polite">
        <span class="saving-spinner" aria-hidden="true"></span>
        <div class="saving-copy">
          <strong>{{ isEditing ? 'Actualizando producto...' : 'Registrando producto...' }}</strong>
          <p>Estamos guardando la informacion y preparando el cierre del formulario.</p>
        </div>
      </div>

      <header class="modal-header">
        <div>
          <h3>{{ isEditing ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h3>
          <p class="panel-subtitle">
            Completa la informacion para {{ isEditing ? 'actualizar' : 'crear' }} un producto.
          </p>
        </div>
        <button class="close-button" type="button" :disabled="props.saving" @click="requestClose">X</button>
      </header>

      <form class="form-grid" @submit.prevent="handleSubmit">
        <div class="form-row">
          <label class="field">
            Nombre del Producto *
            <input
              v-model="form.name"
              placeholder="Ej: Shampoo Loreal"
              :class="{ error: errors.name }"
              :disabled="props.saving"
            />
            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
          </label>
          <label class="field">
            Categoria *
            <div class="select-group">
              <select v-model="form.category_id" :class="{ error: errors.category_id }" :disabled="props.saving">
                <option value="" disabled>Selecciona la categoria</option>
                <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <button class="add-btn" type="button" @click="emit('create-category')" :disabled="props.saving">
                +
              </button>
            </div>
            <span v-if="errors.category_id" class="error-text">{{ errors.category_id }}</span>
          </label>
        </div>

        <div class="form-row">
          <label class="field">
            Marca
            <input v-model="form.brand" placeholder="Ej: Loreal" :disabled="props.saving" />
          </label>
          <label class="field">
            Stock
            <input
              v-model="form.stock"
              placeholder="0"
              type="number"
              min="0"
              max="999999999"
              :disabled="isEditing || props.saving"
              :class="{ error: errors.stock }"
            />
            <span v-if="errors.stock" class="error-text">{{ errors.stock }}</span>
          </label>
        </div>

        <div class="form-row">
          <label class="field">
            Costo de producto
            <input
              v-model="form.cost"
              placeholder="S/. 0"
              type="number"
              min="0"
              step="0.01"
              max="9999999999.99"
              :disabled="isEditing || props.saving"
              :class="{ error: errors.cost }"
            />
            <span v-if="errors.cost" class="error-text">{{ errors.cost }}</span>
          </label>
          <label class="field">
            Precio de venta
            <input
              v-model="form.price"
              placeholder="S/. 0"
              type="number"
              min="0"
              step="0.01"
              max="9999999999.99"
              :disabled="props.saving"
              :class="{ error: errors.price }"
            />
            <span v-if="errors.price" class="error-text">{{ errors.price }}</span>
          </label>
        </div>

        <label class="field">
          Descripcion opcional
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Ej: Shampoo Loreal"
            :disabled="props.saving"
          ></textarea>
        </label>

        <div class="form-actions">
          <button class="btn-ghost" type="button" @click="requestClose" :disabled="props.saving">
            Cancelar
          </button>
          <button class="btn-primary submit-btn" type="submit" :disabled="props.saving">
            <span v-if="props.saving" class="btn-spinner" aria-hidden="true"></span>
            {{ props.saving ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear producto' }}
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
  width: min(760px, 100%);
  max-height: min(90vh, 900px);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 18px;
  overflow: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.modal-header h3 {
  margin: 0;
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
  max-width: 280px;
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
  border: 2px solid rgba(15, 118, 110, 0.18);
  border-top-color: var(--accent);
  animation: spin 0.8s linear infinite;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 0.8rem;
  color: #6f6770;
}

.field input,
.field select,
.field textarea {
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: 12px;
  padding: 12px 14px;
  background: #fff;
  font-size: 0.95rem;
}

.field textarea {
  resize: vertical;
}

.field input.error,
.field select.error,
.field textarea.error {
  border-color: #f43f5e;
  box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.12);
}

.error-text {
  color: #f43f5e;
  font-size: 0.75rem;
}

.select-group {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  align-items: center;
}

.add-btn {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #edf8f6;
  font-weight: 700;
  cursor: pointer;
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
  background: var(--accent-strong);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled,
.btn-ghost:disabled,
.add-btn:disabled {
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

@media (max-width: 700px) {
  .modal-backdrop {
    padding: 12px;
    align-items: stretch;
  }

  .modal-card {
    width: 100%;
    max-height: calc(100vh - 24px);
    padding: 18px;
    border-radius: 20px;
  }

  .modal-header {
    align-items: flex-start;
  }

  .modal-header h3 {
    font-size: 1.1rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .select-group {
    grid-template-columns: 1fr;
  }

  .add-btn {
    width: 100%;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions > * {
    width: 100%;
  }

  .saving-overlay {
    flex-direction: column;
    text-align: center;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

