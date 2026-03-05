<script setup lang="ts">
import { computed, onMounted, reactive, shallowRef } from 'vue'
import { useCatalogs } from '../../composables/useCatalogs'
import { useServiceRecords } from '../../composables/useServiceRecords'
import { apiFetch } from '../../lib/api'
import { notifySuccess } from '../../lib/notify'
import { confirmDelete } from '../../lib/confirm'
import { formatDateTime, formatCurrency } from '../../utils/format'

const catalogs = useCatalogs()
const records = useServiceRecords()

const serviceForm = reactive({
  name: '',
  base_price: '',
})

const recordForm = reactive({
  service_id: '',
  stylist_id: '',
  client_id: '',
  total_amount: '',
  payment_method: 'cash',
  consumptions: [
    {
      item_id: '',
      quantity: '',
      unit: '',
    },
  ],
})

const message = shallowRef<string | null>(null)
const savingService = shallowRef(false)
const savingRecord = shallowRef(false)
const savingEdit = shallowRef(false)
const search = shallowRef('')
const range = shallowRef<'all' | 'today' | 'week'>('all')
const dateFilter = shallowRef('')
const editingRecord = shallowRef<null | {
  id: number
  service_id?: number
  stylist_id?: number
  client_id?: number | null
  total_amount: number
  payment_method?: string | null
  performed_at?: string
  status?: string
}>(null)

const editForm = reactive({
  service_id: '',
  stylist_id: '',
  client_id: '',
  total_amount: '',
  payment_method: 'cash',
  performed_at: '',
})

const filteredRecords = computed(() => {
  const query = search.value.trim().toLowerCase()
  const now = new Date()
  const startOfWeek = new Date(now)
  startOfWeek.setDate(now.getDate() - 6)
  startOfWeek.setHours(0, 0, 0, 0)
  const selectedDate = dateFilter.value ? new Date(`${dateFilter.value}T00:00:00`) : null

  return records.records.value
    .filter((record) => {
      if (!query) return true
      const service = record.service?.name?.toLowerCase() ?? ''
      const client = record.client?.full_name?.toLowerCase() ?? ''
      const stylist = record.stylist?.user?.name?.toLowerCase() ?? ''
      return service.includes(query) || client.includes(query) || stylist.includes(query)
    })
    .filter((record) => {
      if (selectedDate) {
        const date = new Date(record.performed_at)
        if (Number.isNaN(date.getTime())) return false
        return (
          date.getFullYear() === selectedDate.getFullYear() &&
          date.getMonth() === selectedDate.getMonth() &&
          date.getDate() === selectedDate.getDate()
        )
      }
      if (range.value === 'all') return true
      const date = new Date(record.performed_at)
      if (Number.isNaN(date.getTime())) return false
      if (range.value === 'today') {
        return (
          date.getFullYear() === now.getFullYear() &&
          date.getMonth() === now.getMonth() &&
          date.getDate() === now.getDate()
        )
      }
      return date >= startOfWeek && date <= now
    })
    .sort((a, b) => new Date(b.performed_at).getTime() - new Date(a.performed_at).getTime())
})

onMounted(() => {
  catalogs.load()
  records.load()
})

async function submitService(): Promise<void> {
  if (savingService.value) return
  savingService.value = true
  try {
    await apiFetch('/api/services', {
      method: 'POST',
      body: JSON.stringify({
        name: serviceForm.name,
        base_price: Number(serviceForm.base_price),
        is_active: true,
      }),
    })
    serviceForm.name = ''
    serviceForm.base_price = ''
    await catalogs.load()
    notifySuccess('Servicio registrado')
  } finally {
    savingService.value = false
  }
}

async function submitRecord(): Promise<void> {
  if (savingRecord.value) return
  savingRecord.value = true
  message.value = null
  try {
    await records.create({
      service_id: Number(recordForm.service_id),
      stylist_id: Number(recordForm.stylist_id),
      client_id: recordForm.client_id ? Number(recordForm.client_id) : null,
      total_amount: Number(recordForm.total_amount),
      payment_method: recordForm.payment_method,
      consumptions: recordForm.consumptions.map((line) => ({
        item_id: Number(line.item_id),
        quantity: Number(line.quantity),
        unit: line.unit,
      })),
    })
    recordForm.total_amount = ''
    recordForm.consumptions = [
      {
        item_id: '',
        quantity: '',
        unit: '',
      },
    ]
    message.value = 'Servicio registrado.'
    notifySuccess('Servicio registrado')
  } finally {
    savingRecord.value = false
  }
}

function addConsumption(): void {
  recordForm.consumptions.push({ item_id: '', quantity: '', unit: '' })
}

function removeConsumption(index: number): void {
  recordForm.consumptions.splice(index, 1)
}

function openEdit(record: {
  id: number
  service_id?: number
  stylist_id?: number
  client_id?: number | null
  total_amount: number
  payment_method?: string | null
  performed_at?: string
  status?: string
}): void {
  if (record.status === 'cancelled') return
  editingRecord.value = record
  editForm.service_id = record.service_id ? String(record.service_id) : ''
  editForm.stylist_id = record.stylist_id ? String(record.stylist_id) : ''
  editForm.client_id = record.client_id ? String(record.client_id) : ''
  editForm.total_amount = String(record.total_amount ?? '')
  editForm.payment_method = record.payment_method ?? 'cash'
  editForm.performed_at = record.performed_at
    ? new Date(record.performed_at).toISOString().slice(0, 16)
    : ''
}

function closeEdit(): void {
  editingRecord.value = null
  editForm.service_id = ''
  editForm.stylist_id = ''
  editForm.client_id = ''
  editForm.total_amount = ''
  editForm.payment_method = 'cash'
  editForm.performed_at = ''
}

async function submitEdit(): Promise<void> {
  if (!editingRecord.value || savingEdit.value) return
  savingEdit.value = true
  try {
    await records.update(editingRecord.value.id, {
      service_id: Number(editForm.service_id),
      stylist_id: Number(editForm.stylist_id),
      client_id: editForm.client_id ? Number(editForm.client_id) : null,
      total_amount: Number(editForm.total_amount),
      payment_method: editForm.payment_method,
      performed_at: editForm.performed_at ? new Date(editForm.performed_at).toISOString() : null,
    })
    notifySuccess('Servicio actualizado')
    closeEdit()
  } finally {
    savingEdit.value = false
  }
}

async function cancelRecord(recordId: number): Promise<void> {
  const ok = await confirmDelete('Anular este servicio? La comision quedara en cero.', 'Anular')
  if (!ok) return
  await records.cancel(recordId)
  notifySuccess('Servicio anulado')
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Servicios</p>
        <h2>Agenda y consumo real</h2>
      </div>
    </header>

    <div class="view-grid">
      <section class="panel">
        <div class="panel-header">
          <div>
            <h3>Nuevo servicio (catalogo)</h3>
            <p class="panel-subtitle">Define precio base y habilita el servicio en agenda.</p>
          </div>
          <span class="pill">Catalogo</span>
        </div>
        <form class="form-grid" @submit.prevent="submitService">
          <label>
            Nombre
            <input v-model="serviceForm.name" required />
          </label>
          <label>
            Precio base
            <input v-model="serviceForm.base_price" type="number" step="0.01" />
          </label>
          <div class="form-actions">
            <button class="btn-primary" type="submit" :disabled="savingService">Guardar servicio</button>
            <button class="btn-ghost" type="button" @click="serviceForm.name = ''; serviceForm.base_price = ''">
              Limpiar
            </button>
          </div>
        </form>
      </section>

      <section class="panel">
        <h3>Registrar servicio</h3>
        <form class="form-grid" @submit.prevent="submitRecord">
          <label>
            Servicio
            <select v-model="recordForm.service_id">
              <option value="">Seleccionar</option>
              <option v-for="service in catalogs.services.value" :key="service.id" :value="service.id">
                {{ service.name }}
              </option>
            </select>
          </label>
          <label>
            Estilista
            <select v-model="recordForm.stylist_id">
              <option value="">Seleccionar</option>
              <option
                v-for="stylist in catalogs.stylists.value"
                :key="stylist.id"
                :value="stylist.id"
              >
                {{ stylist.user?.name }}
              </option>
            </select>
          </label>
          <label>
            Cliente
            <select v-model="recordForm.client_id">
              <option value="">Sin cliente</option>
              <option v-for="client in catalogs.clients.value" :key="client.id" :value="client.id">
                {{ client.full_name }}
              </option>
            </select>
          </label>
          <label>
            Total cobrado
            <input v-model="recordForm.total_amount" type="number" step="0.01" />
          </label>
          <label>
            Metodo de pago
            <select v-model="recordForm.payment_method">
              <option value="cash">Efectivo</option>
              <option value="card">Tarjeta</option>
              <option value="yape">Yape</option>
            </select>
          </label>

          <div class="consumption-block">
            <p>Consumos reales</p>
            <div v-for="(line, index) in recordForm.consumptions" :key="index" class="line-grid">
              <select v-model="line.item_id">
                <option value="">Item</option>
                <option v-for="item in catalogs.items.value" :key="item.id" :value="item.id">
                  {{ item.name }}
                </option>
              </select>
              <input v-model="line.quantity" type="number" step="0.01" placeholder="Cantidad" />
              <input v-model="line.unit" placeholder="Unidad" />
              <button type="button" class="btn-ghost" @click="removeConsumption(index)">Quitar</button>
            </div>
            <button type="button" class="btn-ghost" @click="addConsumption">Agregar insumo</button>
          </div>

          <button class="btn-primary" type="submit" :disabled="savingRecord">Registrar</button>
          <p v-if="message" class="form-message">{{ message }}</p>
        </form>
      </section>
    </div>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h3>Ultimos servicios</h3>
          <p class="panel-subtitle">Busca por cliente, estilista o servicio.</p>
        </div>
        <div class="search-wrap">
          <input
            v-model="search"
            class="search-input"
            type="search"
            placeholder="Buscar en servicios..."
          />
          <select v-model="range" class="select">
            <option value="all">Todo</option>
            <option value="today">Hoy</option>
            <option value="week">Ultimos 7 dias</option>
          </select>
          <input v-model="dateFilter" class="search-input" type="date" />
          <span class="pill">Resultados {{ filteredRecords.length }}</span>
        </div>
      </div>
        <div class="table">
          <div class="table-row table-head">
            <span>Fecha</span>
            <span>Servicio</span>
            <span>Cliente</span>
            <span>Estilista</span>
            <span>Total</span>
            <span>Acciones</span>
          </div>
          <div v-if="filteredRecords.length === 0" class="empty-row">
            Sin resultados para los filtros actuales.
          </div>
          <div v-for="record in filteredRecords" :key="record.id" class="table-row">
            <span>{{ formatDateTime(record.performed_at) }}</span>
            <span>{{ record.service?.name ?? 'Servicio' }}</span>
            <span>{{ record.client?.full_name ?? 'Sin cliente' }}</span>
            <span>{{ record.stylist?.user?.name ?? 'Estilista' }}</span>
            <span>
              {{ formatCurrency(record.total_amount) }}
              <span v-if="record.status === 'cancelled'" class="status-pill">Anulado</span>
            </span>
            <div class="row-actions">
              <button
                class="btn-ghost"
                type="button"
                :disabled="record.status === 'cancelled'"
                @click="openEdit(record)"
              >
                Editar
              </button>
              <button
                class="btn-danger"
                type="button"
                :disabled="record.status === 'cancelled'"
                @click="cancelRecord(record.id)"
              >
                Anular
              </button>
            </div>
          </div>
        </div>
      </section>

    <div v-if="editingRecord" class="modal-backdrop" @click.self="closeEdit">
      <div class="modal-card">
        <div class="panel-header">
          <div>
            <h3>Editar servicio</h3>
            <p class="panel-subtitle">Actualiza datos generales del servicio.</p>
          </div>
        </div>
        <form class="form-grid" @submit.prevent="submitEdit">
          <label>
            Servicio
            <select v-model="editForm.service_id" required>
              <option value="">Seleccionar</option>
              <option v-for="service in catalogs.services.value" :key="service.id" :value="service.id">
                {{ service.name }}
              </option>
            </select>
          </label>
          <label>
            Estilista
            <select v-model="editForm.stylist_id" required>
              <option value="">Seleccionar</option>
              <option
                v-for="stylist in catalogs.stylists.value"
                :key="stylist.id"
                :value="stylist.id"
              >
                {{ stylist.user?.name }}
              </option>
            </select>
          </label>
          <label>
            Cliente
            <select v-model="editForm.client_id">
              <option value="">Sin cliente</option>
              <option v-for="client in catalogs.clients.value" :key="client.id" :value="client.id">
                {{ client.full_name }}
              </option>
            </select>
          </label>
          <label>
            Total cobrado
            <input v-model="editForm.total_amount" type="number" step="0.01" />
          </label>
          <label>
            Metodo de pago
            <select v-model="editForm.payment_method">
              <option value="cash">Efectivo</option>
              <option value="card">Tarjeta</option>
              <option value="yape">Yape</option>
            </select>
          </label>
          <label>
            Fecha y hora
            <input v-model="editForm.performed_at" type="datetime-local" />
          </label>
          <div class="form-actions">
            <button class="btn-primary" type="submit" :disabled="savingEdit">Guardar cambios</button>
            <button class="btn-ghost" type="button" :disabled="savingEdit" @click="closeEdit">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<style scoped>
.view {
  display: grid;
  gap: 24px;
}

.view-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  align-items: start;
}

.consumption-block {
  display: grid;
  gap: 8px;
}

.line-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.6fr 0.6fr auto;
  gap: 8px;
}

.form-grid input,
.form-grid select,
.line-grid input,
.line-grid select {
  width: 100%;
}

.table {
  overflow-x: auto;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 1.2fr 1.2fr 1fr 0.7fr auto;
  gap: 12px;
  min-width: 760px;
}

.form-message {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
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

.form-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.search-input {
  min-width: 220px;
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: var(--radius-md);
  padding: 10px 12px;
  background: #fff;
  font-size: 0.9rem;
}

.search-input:focus {
  outline: none;
  border-color: rgba(214, 106, 86, 0.5);
  box-shadow: 0 0 0 3px rgba(214, 106, 86, 0.15);
}

.empty-row {
  padding: 14px 16px;
  border-radius: var(--radius-md);
  border: 1px dashed rgba(17, 15, 20, 0.12);
  color: var(--ink-muted);
  background: rgba(255, 255, 255, 0.7);
}

.row-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  margin-left: 8px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.7rem;
  color: #b24b3a;
  background: rgba(178, 75, 58, 0.12);
  border: 1px solid rgba(178, 75, 58, 0.2);
}

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

@media (max-width: 720px) {
  .view-grid {
    grid-template-columns: 1fr;
  }

  .search-wrap {
    width: 100%;
    justify-content: flex-start;
  }

  .search-input,
  .select {
    width: 100%;
    min-width: 0;
  }

  .line-grid {
    grid-template-columns: 1fr;
  }

  .line-grid button {
    grid-column: 1 / -1;
    justify-self: stretch;
  }

  .consumption-block .btn-ghost {
    width: 100%;
  }

  .row-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .modal-card {
    width: 100%;
  }
}
</style>
