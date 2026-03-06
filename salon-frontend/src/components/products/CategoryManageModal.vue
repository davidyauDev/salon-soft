<script setup lang="ts">
import type { InventoryCategory } from '../../composables/useInventoryCatalogs'

const props = defineProps<{
  open: boolean
  categories: InventoryCategory[]
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'create'): void
  (e: 'edit', category: InventoryCategory): void
  (e: 'remove', category: InventoryCategory): void
}>()
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <div class="title-group">
          <h3>Gestionar Categorias</h3>
          <button class="add-btn" type="button" @click="emit('create')">+</button>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <div class="table">
        <div class="table-head">
          <span>Nombre</span>
          <span class="actions-col">Acciones</span>
        </div>
        <div v-for="category in props.categories" :key="category.id" class="table-row">
          <span>{{ category.name }}</span>
          <div class="row-actions">
            <button class="icon-btn" type="button" @click="emit('edit', category)" aria-label="Editar">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M4 16.5V20h3.5L18 9.5l-3.5-3.5L4 16.5Zm16.7-10.8a1 1 0 0 0 0-1.4l-1-1a1 1 0 0 0-1.4 0l-1.6 1.6 3.5 3.5 1.5-1.7Z"
                />
              </svg>
            </button>
            <button class="icon-btn danger" type="button" @click="emit('remove', category)" aria-label="Eliminar">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 6h2v8h-2V9Zm4 0h2v8h-2V9Zm-8 0h2v8H6V9Z"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
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
  width: min(640px, 100%);
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

.title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.add-btn {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  border: none;
  background: #5a4bff;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
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

.table {
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 14px;
  overflow: hidden;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  padding: 12px 16px;
}

.table-head {
  background: #f6f7f9;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 600;
}

.table-row {
  border-top: 1px solid rgba(17, 15, 20, 0.08);
  font-size: 0.9rem;
}

.row-actions {
  display: flex;
  gap: 10px;
}

.icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: #1f1d29;
}

.icon-btn.danger {
  border-color: rgba(239, 68, 68, 0.3);
  background: rgba(239, 68, 68, 0.08);
}

.icon-btn.danger svg {
  fill: #ef4444;
}

.actions-col {
  text-align: right;
}
</style>
