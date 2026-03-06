<script setup lang="ts">
import { computed } from 'vue'
import type { InventoryCategory, InventoryBrand } from '../../composables/useInventoryCatalogs'
import type { InventoryItemForm } from '../../types/inventory'

const model = defineModel<InventoryItemForm>({ required: true })

const props = defineProps<{
  categories: InventoryCategory[]
  brands: InventoryBrand[]
  saving: boolean
  isEditing: boolean
  message: string | null
}>()

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

const canSubmit = computed(
  () => model.value.name.trim().length > 0 && model.value.base_unit.trim().length > 0,
)

function handleSubmit(): void {
  emit('submit')
}

function handleCancel(): void {
  emit('cancel')
}
</script>

<template>
  <section class="panel">
    <div class="panel-header">
      <div>
        <h3>{{ props.isEditing ? 'Editar item' : 'Registrar nuevo item' }}</h3>
        <p class="panel-subtitle">Productos e insumos para venta o consumo.</p>
      </div>
    </div>
    <form class="form-grid two-col" @submit.prevent="handleSubmit">
      <label class="span-2">
        Nombre
        <input v-model="model.name" required />
      </label>
      <label>
        Tipo
        <select v-model="model.type">
          <option value="mixed">Mixto</option>
          <option value="sell_only">Solo venta</option>
          <option value="consume_only">Solo consumo</option>
        </select>
      </label>
      <label>
        Categoria
        <select v-model="model.category_id">
          <option value="">Sin categoria</option>
          <option v-for="category in props.categories" :key="category.id" :value="String(category.id)">
            {{ category.name }}
          </option>
        </select>
      </label>
      <label>
        Marca
        <select v-model="model.brand_id">
          <option value="">Sin marca</option>
          <option v-for="brand in props.brands" :key="brand.id" :value="String(brand.id)">
            {{ brand.name }}
          </option>
        </select>
      </label>
      <label>
        Unidad base
        <input v-model="model.base_unit" placeholder="ml, und, g" />
      </label>
      <label>
        Precio venta (opcional)
        <input v-model="model.sale_price" type="number" step="0.01" />
      </label>
      <label>
        Stock minimo
        <input v-model="model.stock_minimum" type="number" step="0.01" />
      </label>
      <label>
        Punto de reposicion
        <input v-model="model.reorder_point" type="number" step="0.01" />
      </label>
      <label>
        Cantidad de reposicion
        <input v-model="model.reorder_qty" type="number" step="0.01" />
      </label>
      <label>
        SKU
        <input v-model="model.sku" placeholder="SKU interno" />
      </label>
      <label>
        Codigo de barras
        <input v-model="model.barcode" placeholder="Barcode" />
      </label>
      <div class="form-actions span-2">
        <button class="btn-primary" type="submit" :disabled="props.saving || !canSubmit">
          {{ props.isEditing ? 'Actualizar item' : 'Guardar item' }}
        </button>
        <button
          v-if="props.isEditing"
          class="btn-ghost"
          type="button"
          :disabled="props.saving"
          @click="handleCancel"
        >
          Cancelar
        </button>
      </div>
      <p v-if="props.message" class="form-message span-2">{{ props.message }}</p>
    </form>
  </section>
</template>

<style scoped>
.span-2 {
  grid-column: span 2;
}

.form-message {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}
</style>
