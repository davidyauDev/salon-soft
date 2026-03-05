<script setup lang="ts">
import { computed, onMounted, reactive, shallowRef } from 'vue'
import { useClients } from '../../composables/useClients'
import { formatCurrency, formatDateTime } from '../../utils/format'
import { confirmDelete } from '../../lib/confirm'
import { notifySuccess } from '../../lib/notify'

const clients = useClients()
const form = reactive({
  full_name: '',
  phone: '',
  email: '',
})

const selectedId = shallowRef<number | null>(null)
const editingId = shallowRef<number | null>(null)
const isEditing = computed(() => editingId.value !== null)
const search = shallowRef('')
const saving = shallowRef(false)
const showForm = shallowRef(false)

const whatsappMessage =
  'Hola {{name}}, te escribimos desde Salon Control. ¿Quieres agendar tu próxima cita?'

const filteredClients = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return clients.clients.value
  return clients.clients.value.filter((client) => {
    const name = client.full_name?.toLowerCase() ?? ''
    const email = client.email?.toLowerCase() ?? ''
    const phone = client.phone?.toLowerCase() ?? ''
    return name.includes(query) || email.includes(query) || phone.includes(query)
  })
})

onMounted(() => {
  clients.load()
})

async function submit(): Promise<void> {
  if (saving.value) return
  saving.value = true
  const payload = {
    full_name: form.full_name,
    phone: form.phone || null,
    email: form.email || null,
  }
  try {
    if (editingId.value) {
      await clients.update(editingId.value, payload)
      editingId.value = null
      notifySuccess('Cliente actualizado')
    } else {
      await clients.create(payload)
      notifySuccess('Cliente registrado')
    }
  } finally {
    saving.value = false
  }
  resetForm()
  showForm.value = false
}

function resetForm(): void {
  form.full_name = ''
  form.phone = ''
  form.email = ''
}

function startEdit(id: number): void {
  const target = clients.clients.value.find((client) => client.id === id)
  if (!target) return
  editingId.value = id
  showForm.value = true
  form.full_name = target.full_name
  form.phone = target.phone ?? ''
  form.email = target.email ?? ''
}

function cancelEdit(): void {
  editingId.value = null
  showForm.value = false
  resetForm()
}

async function removeClient(id: number): Promise<void> {
  const ok = await confirmDelete('Eliminar este cliente? Sus compras y servicios quedaran registrados.')
  if (!ok) return
  await clients.remove(id)
  if (selectedId.value === id) {
    selectedId.value = null
  }
}

async function selectClient(id: number): Promise<void> {
  selectedId.value = id
  await clients.loadHistory(id)
}

function toggleForm(): void {
  if (showForm.value) {
    cancelEdit()
    return
  }
  showForm.value = true
}

function normalizePhone(phone?: string | null): string | null {
  if (!phone) return null
  const digits = phone.replace(/\D/g, '')
  if (!digits) return null
  if (digits.startsWith('51') || digits.length > 9) {
    return digits
  }
  return `51${digits}`
}

function openWhatsApp(client: { full_name: string; phone?: string | null }): void {
  const normalized = normalizePhone(client.phone)
  if (!normalized) return
  const message = whatsappMessage.replace('{{name}}', client.full_name)
  const url = `https://wa.me/${normalized}?text=${encodeURIComponent(message)}`
  window.open(url, '_blank', 'noopener,noreferrer')
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Clientes</p>
        <h2>Historial y fidelizacion</h2>
      </div>
    </header>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h3>Clientes</h3>
          <p class="panel-subtitle">Gestiona datos y envia mensajes por WhatsApp.</p>
        </div>
        <div class="header-actions">
          <input
            v-model="search"
            class="search-input"
            type="search"
            placeholder="Buscar cliente..."
          />
          <span class="pill">Resultados {{ filteredClients.length }}</span>
          <button class="btn-primary" type="button" @click="toggleForm">
            {{ showForm ? 'Cerrar' : 'Nuevo cliente' }}
          </button>
        </div>
      </div>

      <div class="table">
        <div class="table-row table-head">
          <span>Cliente</span>
          <span>Telefono</span>
          <span>Email</span>
          <span>Acciones</span>
        </div>
        <div
          v-for="client in filteredClients"
          :key="client.id"
          class="table-row"
          :class="{ active: client.id === selectedId }"
        >
          <button class="link-button" type="button" @click="selectClient(client.id)">
            <span class="client-name">{{ client.full_name }}</span>
          </button>
          <span class="client-meta">{{ client.phone ?? 'Sin telefono' }}</span>
          <span class="client-meta">{{ client.email ?? 'Sin email' }}</span>
          <div class="row-actions">
            <button class="btn-ghost" type="button" @click="startEdit(client.id)">Editar</button>
            <button class="btn-danger" type="button" @click="removeClient(client.id)">Eliminar</button>
            <button
              class="btn-whatsapp"
              type="button"
              :disabled="!client.phone"
              title="Enviar WhatsApp"
              @click="openWhatsApp(client)"
            >
              <span class="whatsapp-icon" aria-hidden="true">
                <svg viewBox="0 0 32 32">
                  <path
                    d="M16 3.2a12.8 12.8 0 0 0-10.9 19.7L3.2 28.8l6.2-1.6A12.8 12.8 0 1 0 16 3.2Zm0 2.4a10.4 10.4 0 0 1 8.9 15.8l-.3.5.9 3.3-3.4-.9-.5.3A10.4 10.4 0 1 1 16 5.6Zm5.8 12.5c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2s-.8 1-.9 1.2c-.2.2-.3.3-.6.1a8.6 8.6 0 0 1-2.5-1.6 9.3 9.3 0 0 1-1.7-2.1c-.2-.3 0-.4.1-.6.1-.1.2-.3.3-.4.1-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.7-1.8-1-2.4-.3-.7-.6-.6-.8-.6h-.7a1.4 1.4 0 0 0-1 .5c-.4.4-1.4 1.3-1.4 3.1s1.4 3.5 1.6 3.8c.2.3 2.8 4.3 6.8 6 .9.4 1.6.7 2.1.9.9.3 1.7.3 2.3.2.7-.1 1.8-.8 2.1-1.5.3-.7.3-1.3.2-1.5-.1-.2-.3-.3-.6-.5Z"
                  />
                </svg>
              </span>
              WhatsApp
            </button>
          </div>
        </div>
      </div>
    </section>

    <div v-if="showForm" class="modal-backdrop" @click.self="cancelEdit">
      <div class="modal-card">
        <div class="panel-header">
          <div>
            <h3>{{ isEditing ? 'Editar cliente' : 'Nuevo cliente' }}</h3>
            <p class="panel-subtitle">Registra datos rapidos para ventas y servicios.</p>
          </div>
        </div>
        <form class="form-grid two-col" @submit.prevent="submit">
          <label class="full">
            Nombre completo
            <input v-model="form.full_name" required />
          </label>
          <label>
            Telefono
            <input v-model="form.phone" placeholder="+51 999 888 777" />
          </label>
          <label>
            Email
            <input v-model="form.email" type="email" placeholder="cliente@email.com" />
          </label>
          <div class="form-actions">
            <button class="btn-primary" type="submit" :disabled="saving">
              {{ isEditing ? 'Actualizar cliente' : 'Guardar cliente' }}
            </button>
            <button class="btn-ghost" type="button" :disabled="saving" @click="cancelEdit">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>

    <section v-if="clients.history.value" class="panel">
      <h3>Historial de {{ clients.history.value.client.full_name }}</h3>
      <div class="history-grid">
        <div>
          <h4>Servicios</h4>
          <div class="history-list">
            <div v-for="service in clients.history.value.services" :key="service.id" class="history-row">
              <span>{{ formatDateTime(service.performed_at) }}</span>
              <span>{{ service.service?.name ?? 'Servicio' }}</span>
              <span>{{ formatCurrency(service.total_amount) }}</span>
            </div>
          </div>
        </div>
        <div>
          <h4>Ventas</h4>
          <div class="history-list">
            <div v-for="sale in clients.history.value.sales" :key="sale.id" class="history-row">
              <span>{{ formatDateTime(sale.sold_at) }}</span>
              <span>{{ formatCurrency(sale.total_amount) }}</span>
            </div>
          </div>
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

.form-grid .full {
  grid-column: 1 / -1;
}

.header-actions {
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

.table {
  display: grid;
  gap: 10px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1.2fr auto;
  gap: 12px;
  align-items: center;
}

.table-row.table-head {
  font-weight: 600;
  background: rgba(17, 15, 20, 0.06);
}

.table-row.active {
  border-color: rgba(214, 106, 86, 0.6);
  box-shadow: 0 0 0 2px rgba(214, 106, 86, 0.12);
}

.client-name {
  display: block;
  font-weight: 600;
  color: var(--ink-strong);
}

.client-meta {
  display: block;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.row-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.link-button {
  border: none;
  background: transparent;
  padding: 0;
  text-align: left;
  cursor: pointer;
}

.btn-whatsapp {
  border: 1px solid rgba(37, 211, 102, 0.45);
  background: rgba(37, 211, 102, 0.18);
  color: #0b6e3f;
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.whatsapp-icon {
  display: inline-flex;
  width: 18px;
  height: 18px;
}

.whatsapp-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
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

.form-actions {
  grid-column: 1 / -1;
}

.history-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

.history-list {
  display: grid;
  gap: 8px;
}

.history-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.8fr;
  gap: 12px;
}

@media (max-width: 760px) {
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .search-input {
    width: 100%;
    min-width: 0;
  }

  .table {
    overflow-x: auto;
  }

  .table-row {
    grid-template-columns: 1fr;
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
