<script setup lang="ts">
import type { InventoryItem } from '../../composables/useInventory'

const props = defineProps<{
  items: InventoryItem[]
  loading: boolean
  error: string | null
}>()

const emit = defineEmits<{
  edit: [itemId: number]
  remove: [itemId: number]
}>()

function isLowStock(item: InventoryItem): boolean {
  return item.stock_total < item.stock_minimum
}
</script>

<template>
  <section class="panel">
    <div class="panel-header">
      <div>
        <h3>Stock actual</h3>
        <p class="panel-subtitle">Edita o elimina items desde aqui.</p>
      </div>
    </div>
    <p v-if="props.loading">Cargando inventario...</p>
    <p v-else-if="props.error" class="error">{{ props.error }}</p>
    <div v-else class="table">
      <div class="table-row table-head">
        <span>Item</span>
        <span>Categoria</span>
        <span>Tipo</span>
        <span>Stock</span>
        <span>Minimo</span>
        <span>Acciones</span>
      </div>
      <div v-for="item in props.items" :key="item.id" class="table-row">
        <span class="item-name">{{ item.name }}</span>
        <span class="muted">{{ item.category?.name ?? 'Sin categoria' }}</span>
        <span class="item-type">{{ item.type }}</span>
        <span class="stock-pill" :class="{ low: isLowStock(item) }">
          {{ item.stock_total.toFixed(2) }} {{ item.base_unit }}
        </span>
        <span class="muted">{{ item.stock_minimum }} {{ item.base_unit }}</span>
        <div class="row-actions">
          <button class="btn-ghost" type="button" @click="emit('edit', item.id)">Editar</button>
          <button class="btn-danger" type="button" @click="emit('remove', item.id)">
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.table-row {
  display: grid;
  grid-template-columns: 1.6fr 1fr 0.8fr 0.9fr 0.9fr auto;
  gap: 12px;
  align-items: center;
}

.error {
  color: #b24b3a;
}

.item-name {
  font-weight: 600;
}

.item-type {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  color: var(--ink-muted);
}

.stock-pill {
  justify-self: start;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(27, 15, 42, 0.08);
  color: var(--ink-strong);
  font-size: 0.78rem;
}

.stock-pill.low {
  background: rgba(178, 75, 58, 0.12);
  color: #b24b3a;
}

.row-actions {
  display: flex;
  gap: 8px;
}

.muted {
  color: var(--ink-muted);
  font-size: 0.85rem;
}
</style>
