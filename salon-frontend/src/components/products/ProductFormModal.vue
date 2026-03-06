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
})

const isEditing = computed(() => Boolean(props.initial))

function averageCost(item: ProductItem | null | undefined): string {
  if (!item?.lots?.length) return ''
  const total = item.lots.reduce((sum, lot) => sum + Number(lot.cost_per_base || 0), 0)
  return (total / item.lots.length).toFixed(2)
}

watch(
  () => [props.open, props.initial],
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
  },
  { immediate: true },
)

function normalizeValue(value: string | number): string {
  return String(value ?? '').trim()
}

function handleSubmit(): void {
  errors.name = ''
  errors.category_id = ''

  if (!normalizeValue(form.name)) {
    errors.name = 'Ingresa un nombre válido'
  }
  if (!form.category_id) {
    errors.category_id = 'Selecciona una categoría'
  }

  if (errors.name || errors.category_id) return

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
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card product-modal">
      <header class="modal-header">
        <div>
          <h3>{{ isEditing ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h3>
          <p class="panel-subtitle">
            Completa la informacion para {{ isEditing ? 'actualizar' : 'crear' }} un producto.
          </p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <form class="form-grid" @submit.prevent="handleSubmit">
        <div v-if="props.saving" class="loading-hint">Guardando producto...</div>
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
              :disabled="isEditing || props.saving"
            />
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
              :disabled="isEditing || props.saving"
            />
          </label>
          <label class="field">
            Precio de venta
            <input
              v-model="form.price"
              placeholder="S/. 0"
              type="number"
              min="0"
              step="0.01"
              :disabled="props.saving"
            />
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
          <button class="btn-ghost" type="button" @click="emit('close')" :disabled="props.saving">
            Cancelar
          </button>
          <button class="btn-primary" type="submit" :disabled="props.saving">
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
  width: min(760px, 100%);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
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

.form-grid {
  display: grid;
  gap: 16px;
}

.loading-hint {
  background: rgba(90, 75, 255, 0.08);
  color: #4f46e5;
  padding: 8px 12px;
  border-radius: 10px;
  font-size: 0.85rem;
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
.field select.error {
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
  background: #f2f3f7;
  font-weight: 700;
  cursor: pointer;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary {
  padding: 10px 18px;
  border-radius: 12px;
  border: none;
  background: #5a4bff;
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
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
