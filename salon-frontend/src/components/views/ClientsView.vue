<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue'
import { useClients, type ClientProfile } from '../../composables/useClients'
import { notifySuccess } from '../../lib/notify'
import ClientDetailModal from '../clients/ClientDetailModal.vue'
import ClientFormModal from '../clients/ClientFormModal.vue'
import ClientListPanel from '../clients/ClientListPanel.vue'
import type { ClientFormState } from '../../types/client'

const clients = useClients()

const selectedId = shallowRef<number | null>(null)
const editingId = shallowRef<number | null>(null)
const search = shallowRef('')
const showForm = shallowRef(false)
const showDetail = shallowRef(false)
const saving = shallowRef(false)
const formSeed = shallowRef<ClientFormState>(createEmptyForm())

const whatsappMessage =
  'Hola {{name}}, te escribimos desde Salon Control. Quieres agendar tu proxima cita?'

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

const selectedClient = computed(() => {
  if (!selectedId.value) return null
  return clients.clients.value.find((client) => client.id === selectedId.value) ?? null
})

const clientStats = computed(() => {
  const history = clients.history.value
  if (!history) {
    return { totalSales: 0, serviceCount: 0, avgTicket: 0 }
  }

  const serviceTotal = history.services.reduce(
    (sum, service) => sum + Number(service.total_amount ?? 0),
    0,
  )
  const salesTotal = history.sales.reduce((sum, sale) => sum + Number(sale.total_amount ?? 0), 0)
  const totalSales = serviceTotal + salesTotal
  const serviceCount = history.services.length
  const avgTicket = serviceCount ? totalSales / serviceCount : 0

  return { totalSales, serviceCount, avgTicket }
})

onMounted(() => {
  clients.load()
})

function createEmptyForm(): ClientFormState {
  return {
    first_name: '',
    last_name: '',
    phone_country: '+51',
    phone_number: '',
    email: '',
    dni: '',
    address: '',
    birth_date: '',
    gender: '',
    notes: '',
  }
}

function splitName(client: ClientProfile): { first_name: string; last_name: string } {
  if (client.first_name || client.last_name) {
    return {
      first_name: client.first_name ?? '',
      last_name: client.last_name ?? '',
    }
  }

  const parts = (client.full_name ?? '').trim().split(/\s+/).filter(Boolean)
  if (!parts.length) {
    return { first_name: '', last_name: '' }
  }

  return {
    first_name: parts[0],
    last_name: parts.slice(1).join(' '),
  }
}

function splitPhone(phone?: string | null): { country: string; number: string } {
  if (!phone) return { country: '+51', number: '' }
  const raw = phone.replace(/\s+/g, '')
  if (raw.startsWith('+')) {
    const match = raw.match(/^(\+\d{1,3})(.*)$/)
    if (match) {
      return { country: match[1], number: match[2] ?? '' }
    }
  }

  const digits = raw.replace(/\D/g, '')
  if (digits.startsWith('51') && digits.length > 9) {
    return { country: '+51', number: digits.slice(2) }
  }

  return { country: '+51', number: digits }
}

function normalizeOptional(value: string): string | null {
  const cleaned = value.trim()
  return cleaned ? cleaned : null
}

function combinePhone(country: string, number: string): string | null {
  const digits = number.replace(/\D/g, '')
  if (!digits) return null
  const code = country.trim() || '+51'
  return `${code}${digits}`
}

function openCreate(): void {
  editingId.value = null
  formSeed.value = createEmptyForm()
  showForm.value = true
}

function openEdit(id: number): void {
  const target = clients.clients.value.find((client) => client.id === id)
  if (!target) return
  const name = splitName(target)
  const phone = splitPhone(target.phone)
  editingId.value = id
  formSeed.value = {
    first_name: name.first_name,
    last_name: name.last_name,
    phone_country: phone.country,
    phone_number: phone.number,
    email: target.email ?? '',
    dni: target.dni ?? '',
    address: target.address ?? '',
    birth_date: target.birth_date ?? '',
    gender: target.gender ?? '',
    notes: target.notes ?? '',
  }
  showForm.value = true
}

function closeModal(): void {
  showForm.value = false
  editingId.value = null
}

function closeDetail(): void {
  showDetail.value = false
}

async function saveClient(payload: ClientFormState): Promise<void> {
  if (saving.value) return
  saving.value = true

  const apiPayload = {
    first_name: payload.first_name.trim(),
    last_name: payload.last_name.trim(),
    phone: combinePhone(payload.phone_country, payload.phone_number),
    email: normalizeOptional(payload.email),
    dni: normalizeOptional(payload.dni),
    address: normalizeOptional(payload.address),
    birth_date: normalizeOptional(payload.birth_date),
    gender: normalizeOptional(payload.gender),
    notes: normalizeOptional(payload.notes),
  }

  try {
    if (editingId.value) {
      await clients.update(editingId.value, apiPayload)
      notifySuccess('Cliente actualizado')
    } else {
      await clients.create(apiPayload)
      notifySuccess('Cliente registrado')
    }
  } finally {
    saving.value = false
  }

  showForm.value = false
  editingId.value = null

  if (selectedId.value) {
    await clients.loadHistory(selectedId.value)
  }
}


async function selectClient(id: number): Promise<void> {
  selectedId.value = id
  await clients.loadHistory(id)
  showDetail.value = true
}

function openWhatsApp(client: ClientProfile): void {
  const phone = client.phone
  if (!phone) return
  const message = whatsappMessage.replace('{{name}}', client.full_name)
  const url = `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`
  window.open(url, '_blank', 'noopener,noreferrer')
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Clientes</p>
        <h2>Relacion y fidelizacion</h2>
      </div>
    </header>

    <ClientListPanel
      v-model="search"
      :clients="filteredClients"
      :selected-id="selectedId"
      @create="openCreate"
      @select="selectClient"
      @edit="openEdit"
      @whatsapp="openWhatsApp"
    />

    <ClientFormModal
      v-if="showForm"
      :initial="formSeed"
      :saving="saving"
      :is-editing="editingId !== null"
      @save="saveClient"
      @close="closeModal"
    />

    <ClientDetailModal
      v-if="showDetail"
      :client="selectedClient"
      :history="clients.history.value"
      :stats="clientStats"
      @edit="openEdit"
      @whatsapp="openWhatsApp"
      @close="closeDetail"
    />
  </section>
</template>

<style scoped>
.view {
  display: grid;
  gap: 24px;
}
</style>
