<script setup lang="ts">
import { computed, onMounted, reactive } from 'vue'
import { useAuditLogs } from '../../composables/useAuditLogs'
import { formatDateTime } from '../../utils/format'

const audit = useAuditLogs()

const filters = reactive({
  action: '',
  entity_type: '',
  entity_id: '',
  user_id: '',
  from: '',
  to: '',
})

const entityOptions = [
  { label: 'Items', value: 'App\\\\Models\\\\Item' },
  { label: 'Clientes', value: 'App\\\\Models\\\\Client' },
  { label: 'Servicios', value: 'App\\\\Models\\\\Service' },
  { label: 'Estilistas', value: 'App\\\\Models\\\\Stylist' },
  { label: 'Servicios (registro)', value: 'App\\\\Models\\\\ServiceRecord' },
]

const actionLabel = (action: string): string => {
  switch (action) {
    case 'update':
      return 'Actualizo'
    case 'delete':
      return 'Elimino'
    case 'cancel':
      return 'Anulo'
    default:
      return action
  }
}

const entityLabel = (entityType: string): string => {
  const match = entityOptions.find((item) => item.value === entityType)
  return match?.label ?? entityType.split('\\').pop() ?? entityType
}

const totalCount = computed(() => audit.meta.value?.total ?? audit.logs.value.length)

onMounted(() => {
  audit.load()
})

async function applyFilters(): Promise<void> {
  await audit.load({
    action: filters.action || null,
    entity_type: filters.entity_type || null,
    entity_id: filters.entity_id || null,
    user_id: filters.user_id || null,
    from: filters.from || null,
    to: filters.to || null,
  })
}

async function clearFilters(): Promise<void> {
  filters.action = ''
  filters.entity_type = ''
  filters.entity_id = ''
  filters.user_id = ''
  filters.from = ''
  filters.to = ''
  await audit.load()
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Auditoria</p>
        <h2>Tracking de cambios</h2>
      </div>
    </header>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h3>Filtros</h3>
          <p class="panel-subtitle">Busca quien edito, anulo o elimino registros.</p>
        </div>
        <span class="pill">Total {{ totalCount }}</span>
      </div>
      <form class="form-grid three-col" @submit.prevent="applyFilters">
        <label>
          Accion
          <select v-model="filters.action">
            <option value="">Todas</option>
            <option value="update">Actualizaciones</option>
            <option value="delete">Eliminaciones</option>
            <option value="cancel">Anulaciones</option>
          </select>
        </label>
        <label>
          Modulo
          <select v-model="filters.entity_type">
            <option value="">Todos</option>
            <option v-for="option in entityOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </label>
        <label>
          ID entidad
          <input v-model="filters.entity_id" type="number" placeholder="Ej: 12" />
        </label>
        <label>
          Usuario (ID)
          <input v-model="filters.user_id" type="number" placeholder="Ej: 1" />
        </label>
        <label>
          Desde
          <input v-model="filters.from" type="date" />
        </label>
        <label>
          Hasta
          <input v-model="filters.to" type="date" />
        </label>
        <div class="form-actions">
          <button class="btn-primary" type="submit" :disabled="audit.loading.value">Aplicar</button>
          <button class="btn-ghost" type="button" @click="clearFilters">Limpiar</button>
        </div>
      </form>
    </section>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h3>Ultimos movimientos</h3>
          <p class="panel-subtitle">Detalle de cambios y anulaciones.</p>
        </div>
      </div>
      <div class="table">
        <div class="table-row table-head">
          <span>Fecha</span>
          <span>Usuario</span>
          <span>Accion</span>
          <span>Modulo</span>
          <span>ID</span>
          <span>Cambios</span>
        </div>
        <div v-if="audit.loading.value" class="empty-row">Cargando movimientos...</div>
        <div v-else-if="audit.logs.value.length === 0" class="empty-row">
          No hay movimientos para estos filtros.
        </div>
        <div v-for="log in audit.logs.value" :key="log.id" class="table-row">
          <span data-label="Fecha">{{ formatDateTime(log.created_at) }}</span>
          <span data-label="Usuario">{{ log.user?.name ?? 'Sistema' }}</span>
          <span class="action-pill" data-label="Accion">{{ actionLabel(log.action) }}</span>
          <span data-label="Modulo">{{ entityLabel(log.entity_type) }}</span>
          <span data-label="ID">#{{ log.entity_id ?? '—' }}</span>
          <details class="change-details">
            <summary>Ver cambios</summary>
            <div class="change-grid">
              <div>
                <p class="change-title">Antes</p>
                <pre>{{ JSON.stringify(log.old_values ?? {}, null, 2) }}</pre>
              </div>
              <div>
                <p class="change-title">Despues</p>
                <pre>{{ JSON.stringify(log.new_values ?? {}, null, 2) }}</pre>
              </div>
            </div>
          </details>
        </div>
      </div>
    </section>
  </section>
</template>

<style scoped>
.view {
  display: grid;
  gap: 24px;
}

.form-grid.three-col {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
  color: var(--ink-muted);
  border: 1px solid rgba(23, 20, 26, 0.1);
  background: rgba(255, 255, 255, 0.8);
}

.table {
  overflow-x: auto;
}

.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.8fr 1fr 0.6fr 1.2fr;
  gap: 12px;
  min-width: 860px;
}

.action-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.7rem;
  color: var(--ink-strong);
  background: rgba(27, 15, 42, 0.08);
}

.change-details {
  background: rgba(255, 255, 255, 0.8);
  border: 1px dashed rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  padding: 8px 10px;
}

.change-details summary {
  cursor: pointer;
  font-weight: 600;
}

.change-grid {
  display: grid;
  gap: 12px;
  margin-top: 10px;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.change-title {
  margin: 0 0 6px;
  font-size: 0.75rem;
  color: var(--ink-muted);
}

.change-details pre {
  margin: 0;
  font-size: 0.75rem;
  background: rgba(17, 15, 20, 0.06);
  padding: 8px;
  border-radius: 10px;
  white-space: pre-wrap;
}

.empty-row {
  padding: 14px 16px;
  border-radius: var(--radius-md);
  border: 1px dashed rgba(17, 15, 20, 0.12);
  color: var(--ink-muted);
  background: rgba(255, 255, 255, 0.7);
}

@media (max-width: 960px) {
  .form-grid.three-col {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 680px) {
  .form-grid.three-col {
    grid-template-columns: 1fr;
  }

  .table-row.table-head {
    display: none;
  }

  .table-row {
    min-width: 0;
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    padding: 14px;
    border-top: 1px solid rgba(17, 15, 20, 0.06);
    background: rgba(255, 255, 255, 0.72);
    border-radius: 16px;
    margin-top: 10px;
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

  .change-details {
    grid-column: 1 / -1;
  }
}
</style>
