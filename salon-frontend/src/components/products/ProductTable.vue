<script setup lang="ts">
import type { ProductItem } from '../../composables/useProducts'
import { formatCurrency } from '../../utils/format'

const props = defineProps<{
  items: ProductItem[]
  loading: boolean
  error: string | null
}>()

const emit = defineEmits<{
  (e: 'edit', item: ProductItem): void
  (e: 'remove', item: ProductItem): void
}>()

function averageCost(item: ProductItem): number {
  if (!item.lots?.length) return 0
  const total = item.lots.reduce((sum, lot) => sum + Number(lot.cost_per_base || 0), 0)
  return total / item.lots.length
}

function formatStock(value: number): string {
  return Math.round(value).toString()
}
</script>

<template>
  <section class="products-table">
    <div v-if="props.loading" class="table-empty">Cargando productos...</div>
    <div v-else-if="props.error" class="table-empty error">{{ props.error }}</div>
    <div v-else-if="!props.items.length" class="table-empty">No hay productos registrados.</div>
      <div v-else class="table-wrap">
        <div class="table-row table-head">
          <span>Nombre</span>
          <span>Categoria</span>
          <span>Marca</span>
          <span>Stock</span>
          <span>Costo</span>
          <span>Precio</span>
          <span class="actions-col">Acciones</span>
        </div>
        <div v-for="item in props.items" :key="item.id" class="table-row">
        <div class="cell-name" data-label="Nombre">
          <p class="name">{{ item.name }}</p>
        </div>
        <span class="category-pill" data-label="Categoria">{{ item.category?.name ?? 'Sin categoria' }}</span>
        <span class="brand-text" data-label="Marca">{{ item.brand?.name ?? '-' }}</span>
        <span class="stock-pill" data-label="Stock">{{ formatStock(item.stock_total) }}</span>
          <span class="cost" data-label="Costo">{{ formatCurrency(averageCost(item)) }}</span>
          <span class="price" data-label="Precio">{{ formatCurrency(Number(item.sale_price ?? 0)) }}</span>
          <div class="row-actions" data-label="Acciones">
          <button class="icon-btn" type="button" @click="emit('edit', item)" aria-label="Editar">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path
                d="M12 20h9"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
          <button class="icon-btn danger" type="button" @click="emit('remove', item)" aria-label="Eliminar">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path
                d="M3 6h18"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
              />
              <path
                d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M19 6l-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M10 11v6M14 11v6"
                stroke="currentColor"
                stroke-width="1.9"
                stroke-linecap="round"
              />
            </svg>
          </button>
          </div>
        </div>
      </div>
    </section>
  </template>

<style scoped>
.products-table {
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 14px;
  background: #fff;
  overflow: hidden;
}

.table-wrap {
  display: grid;
}

.table-row {
  display: grid;
  grid-template-columns: 1.8fr 1.2fr 1fr 0.7fr 0.8fr 0.8fr 0.7fr;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  border-top: 1px solid rgba(17, 15, 20, 0.06);
  font-size: 0.92rem;
}

.table-head {
  background: #f4efe7;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 600;
  border-top: none;
}

.cell-name .name {
  margin: 0;
  font-weight: 600;
}

.category-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.12);
  color: #0b534b;
  font-size: 0.8rem;
  font-weight: 600;
  width: fit-content;
}

.brand-text {
  color: var(--ink-muted);
}

.stock-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 24px;
  border-radius: 999px;
  background: #111111;
  color: #fff;
  font-weight: 700;
  font-size: 0.8rem;
}

.cost {
  color: #6b6b7a;
}

.price {
  font-weight: 700;
}

.actions-col {
  text-align: center;
}

.row-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
}

.icon-btn {
  width: 20px;
  height: 20px;
  padding: 0;
  border: none;
  background: transparent;
  display: grid;
  place-items: center;
  cursor: pointer;
  color: var(--ink-strong);
  transition:
    color 0.18s ease,
    opacity 0.18s ease,
    transform 0.18s ease;
}

.icon-btn svg {
  width: 18px;
  height: 18px;
  overflow: visible;
}

.icon-btn.danger {
  color: #0b534b;
}

.icon-btn:hover {
  opacity: 0.72;
  transform: translateY(-1px);
}

.table-empty {
  padding: 18px;
  color: var(--ink-muted);
  font-size: 0.9rem;
}

.table-empty.error {
  color: #8a3b37;
}

@media (max-width: 1000px) {
  .table-row {
    grid-template-columns: 1.4fr 1fr 0.8fr 0.6fr 0.8fr 0.8fr auto;
    font-size: 0.85rem;
  }
}

@media (max-width: 840px) {
  .table-head {
    display: none;
  }

  .table-row {
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    padding: 14px;
    border-radius: 16px;
    border-top: 1px solid rgba(17, 15, 20, 0.06);
  }

  .table-row > * {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
  }

  .table-row > *::before {
    content: attr(data-label);
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--ink-muted);
    font-weight: 600;
  }

  .cell-name {
    grid-column: 1 / -1;
  }

  .cell-name .name {
    margin: 0;
    font-size: 0.95rem;
  }

  .category-pill,
  .stock-pill {
    width: fit-content;
  }

  .row-actions {
    grid-column: 1 / -1;
    justify-content: flex-end;
    padding-top: 4px;
  }

  .row-actions::before {
    margin-right: auto;
  }
}
</style>
