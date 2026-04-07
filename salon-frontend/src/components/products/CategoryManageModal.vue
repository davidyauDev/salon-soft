<script setup lang="ts">
import type { InventoryCategory } from '../../composables/useInventoryCatalogs'

const props = defineProps<{
  open: boolean
  categories: InventoryCategory[]
  processingId?: number | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'create'): void
  (e: 'edit', category: InventoryCategory): void
  (e: 'remove', category: InventoryCategory): void
}>()

function requestClose(): void {
  if (props.processingId !== null && props.processingId !== undefined) return
  emit('close')
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="requestClose">
    <div class="modal-card">
      <div v-if="props.processingId !== null && props.processingId !== undefined" class="modal-inline-status">
        <span class="inline-spinner" aria-hidden="true"></span>
        <span>Eliminando categoria...</span>
      </div>

      <header class="modal-header">
        <div class="title-group">
          <h3>Gestionar Categorias</h3>
          <button
            class="add-btn"
            type="button"
            :disabled="props.processingId !== null && props.processingId !== undefined"
            @click="emit('create')"
          >
            +
          </button>
        </div>
        <button
          class="close-button"
          type="button"
          :disabled="props.processingId !== null && props.processingId !== undefined"
          @click="requestClose"
        >
          X
        </button>
      </header>

      <div class="table">
        <div class="table-head">
          <span>Nombre</span>
          <span class="actions-col">Acciones</span>
        </div>
        <div
          v-for="category in props.categories"
          :key="category.id"
          class="table-row"
          :class="{ processing: props.processingId === category.id }"
        >
          <span>{{ category.name }}</span>
          <div class="row-actions">
            <button
              class="icon-btn"
              type="button"
              :disabled="props.processingId !== null && props.processingId !== undefined"
              @click="emit('edit', category)"
              aria-label="Editar"
            >
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
            <button
              class="icon-btn danger"
              type="button"
              :disabled="props.processingId !== null && props.processingId !== undefined"
              @click="emit('remove', category)"
              aria-label="Eliminar"
            >
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
  max-height: min(88vh, 760px);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 24px;
  display: grid;
  gap: 16px;
  overflow: auto;
}

.modal-inline-status {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  width: fit-content;
  padding: 10px 14px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.1);
  color: #0b534b;
  font-size: 0.85rem;
  font-weight: 600;
}

.inline-spinner {
  width: 14px;
  height: 14px;
  border-radius: 999px;
  border: 2px solid rgba(15, 118, 110, 0.18);
  border-top-color: var(--accent);
  animation: spin 0.8s linear infinite;
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
  background: #111111;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
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

.add-btn:disabled,
.close-button:disabled,
.icon-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

.table {
  border: 1px solid rgba(25, 25, 25, 0.12);
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
  background: #f4efe7;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 600;
}

.table-row {
  border-top: 1px solid rgba(25, 25, 25, 0.08);
  font-size: 0.9rem;
}

.table-row.processing {
  opacity: 0.55;
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

.actions-col {
  text-align: center;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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

  .title-group {
    flex-wrap: wrap;
    align-items: center;
  }

  .table-head {
    display: none;
  }

  .table-row {
    grid-template-columns: 1fr;
    gap: 10px;
    align-items: start;
  }

  .row-actions {
    justify-content: flex-end;
    width: 100%;
  }

  .icon-btn {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: 1px solid rgba(17, 15, 20, 0.1);
    background: #fffdfa;
  }
}
</style>
