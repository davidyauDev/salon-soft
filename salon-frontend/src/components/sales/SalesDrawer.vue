<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { CatalogService, ClientItem } from '../../composables/useCatalogs'

interface CatalogItem {
  id: number
  name: string
  sale_price?: number | null
}

const props = defineProps<{
  open: boolean
  mode: 'products' | 'services'
  clients: ClientItem[]
  items: CatalogItem[]
  services: CatalogService[]
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'confirm', payload: {
    client_id: number | null
    lines: Array<{
      id: number
      name: string
      unit_price: number
      quantity: number
      type: 'product' | 'service'
    }>
  }): void
  (e: 'create-client'): void
}>()

const clientQuery = defineModel<string>('clientQuery', { default: '' })
const clientId = defineModel<number | null>('clientId', { default: null })

const form = reactive({
  selectedItemId: '',
  selectedServiceId: '',
  lines: [] as Array<{ id: number; name: string; unit_price: number; quantity: number; type: 'product' | 'service' }>,
})

const normalizedQuery = computed(() => clientQuery.value.trim().toLowerCase())

const suggestions = computed(() => {
  if (!normalizedQuery.value) return []
  return props.clients
    .filter((client) => {
      const name = client.full_name?.toLowerCase() ?? ''
      const phone = client.phone?.toLowerCase() ?? ''
      const email = client.email?.toLowerCase() ?? ''
      return (
        name.includes(normalizedQuery.value) ||
        phone.includes(normalizedQuery.value) ||
        email.includes(normalizedQuery.value)
      )
    })
    .slice(0, 6)
})

const selectedClient = computed(() =>
  props.clients.find((client) => client.id === clientId.value) ?? null,
)

const totalAmount = computed(() =>
  form.lines.reduce((sum, line) => sum + line.unit_price * line.quantity, 0),
)

const canConfirm = computed(() => form.lines.length > 0 && !props.saving)

const showSuggestions = computed(() =>
  Boolean(normalizedQuery.value) &&
    suggestions.value.length > 0 &&
    (!selectedClient.value ||
      selectedClient.value.full_name.toLowerCase() !== normalizedQuery.value),
)

watch(
  () => props.open,
  (open) => {
    if (!open) return
    clientQuery.value = ''
    clientId.value = null
    form.selectedItemId = ''
    form.selectedServiceId = ''
    form.lines = []
  },
)

watch(
  () => clientQuery.value,
  (value) => {
    const match = props.clients.find(
      (client) => client.full_name.toLowerCase() === value.trim().toLowerCase(),
    )
    clientId.value = match?.id ?? null
  },
)

watch(
  () => clientId.value,
  (value) => {
    if (!value) return
    const match = props.clients.find((client) => client.id === value)
    if (match) {
      clientQuery.value = match.full_name
    }
  },
)

function selectClient(client: ClientItem): void {
  clientId.value = client.id
  clientQuery.value = client.full_name
}

function addProductLine(itemId: number): void {
  const item = props.items.find((entry) => entry.id === itemId)
  if (!item) return
  const existing = form.lines.find((line) => line.type === 'product' && line.id === itemId)
  if (existing) {
    existing.quantity += 1
  } else {
    form.lines.push({
      id: item.id,
      name: item.name,
      unit_price: Number(item.sale_price ?? 0),
      quantity: 1,
      type: 'product',
    })
  }
}

function addServiceLine(serviceId: number): void {
  const service = props.services.find((entry) => entry.id === serviceId)
  if (!service) return
  form.lines = [
    {
      id: service.id,
      name: service.name,
      unit_price: Number(service.base_price ?? 0),
      quantity: 1,
      type: 'service',
    },
  ]
}

function handleSelect(): void {
  if (props.mode === 'products' && form.selectedItemId) {
    addProductLine(Number(form.selectedItemId))
    form.selectedItemId = ''
  }
  if (props.mode === 'services' && form.selectedServiceId) {
    addServiceLine(Number(form.selectedServiceId))
    form.selectedServiceId = ''
  }
}

function removeLine(index: number): void {
  form.lines.splice(index, 1)
}

function updateQty(index: number, delta: number): void {
  const line = form.lines[index]
  if (!line) return
  line.quantity = Math.max(1, line.quantity + delta)
}

function confirmSale(): void {
  if (!canConfirm.value) return
  emit('confirm', {
    client_id: clientId.value,
    lines: form.lines,
  })
}

function clearClient(): void {
  clientQuery.value = ''
  clientId.value = null
}
</script>

<template>
  <div v-if="props.open" class="drawer-backdrop" @click.self="emit('close')">
    <aside class="drawer">
      <header class="drawer-header">
        <h3>Nueva venta</h3>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <section class="drawer-section">
        <label class="field">Cliente</label>
        <div class="client-search">
          <div class="input-wrap">
            <input v-model="clientQuery" placeholder="Buscar o crear cliente" :disabled="props.saving" />
            <span class="search-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path
                  d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z"
                />
              </svg>
            </span>
            <div v-if="showSuggestions" class="suggestions">
              <button
                v-for="client in suggestions"
                :key="client.id"
                type="button"
                class="suggestion"
                @click="selectClient(client)"
              >
                <span class="avatar">{{ client.full_name.slice(0, 1).toUpperCase() }}</span>
                <span>{{ client.full_name }}</span>
              </button>
            </div>
          </div>
          <button class="icon-btn" type="button" :disabled="props.saving" @click="emit('create-client')">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M15 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm-3-7a7 7 0 1 1 0 14 7 7 0 0 1 0-14Zm8 11v2h-2v-2h-2v-2h2v-2h2v2h2v2h-2Z" />
            </svg>
          </button>
        </div>

        <div v-if="selectedClient" class="client-card">
          <div class="avatar">{{ selectedClient.full_name.slice(0, 2).toUpperCase() }}</div>
          <div class="client-info">
            <p class="client-name">{{ selectedClient.full_name }}</p>
            <p class="client-detail">Numero: {{ selectedClient.phone ?? '-' }}</p>
            <p class="client-detail">Email: {{ selectedClient.email ?? '-' }}</p>
          </div>
          <button class="link-btn" type="button" @click="clearClient">Quitar</button>
        </div>
      </section>

      <section class="drawer-section">
        <label class="field">{{ props.mode === 'products' ? 'Producto' : 'Servicio' }}</label>
        <select
          v-if="props.mode === 'products'"
          v-model="form.selectedItemId"
          class="select"
          :disabled="props.saving"
          @change="handleSelect"
        >
          <option value="">Seleccionar un producto</option>
          <option v-for="item in props.items" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select>
        <select
          v-else
          v-model="form.selectedServiceId"
          class="select"
          :disabled="props.saving"
          @change="handleSelect"
        >
          <option value="">Seleccionar un servicio</option>
          <option v-for="service in props.services" :key="service.id" :value="service.id">
            {{ service.name }}
          </option>
        </select>
      </section>

      <section class="drawer-section" v-if="form.lines.length">
        <div v-for="(line, index) in form.lines" :key="`${line.type}-${line.id}`" class="line-card">
          <div class="line-head">
            <span class="line-title">{{ String(index + 1).padStart(2, '0') }}. {{ line.name }}</span>
            <button class="link-btn" type="button" @click="removeLine(index)">Quitar</button>
          </div>
          <div class="line-meta">
            <div class="qty">
              <button type="button" @click="updateQty(index, -1)">-</button>
              <span>{{ line.quantity }}</span>
              <button type="button" @click="updateQty(index, 1)">+</button>
            </div>
            <div class="price">Precio unit. <strong>S/. {{ line.unit_price.toFixed(2) }}</strong></div>
            <div class="total">S/ {{ (line.unit_price * line.quantity).toFixed(2) }}</div>
          </div>
        </div>
      </section>

      <footer class="drawer-footer">
        <div class="total-card">
          <span>Total:</span>
          <strong>S/. {{ totalAmount.toFixed(2) }}</strong>
        </div>
        <div class="footer-actions">
          <button class="btn-ghost" type="button" @click="emit('close')">Cancelar</button>
          <button class="btn-primary" type="button" :disabled="!canConfirm" @click="confirmSale">
            Confirmar
          </button>
        </div>
      </footer>
    </aside>
  </div>
</template>

<style scoped>
.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 20, 0.45);
  display: flex;
  justify-content: flex-end;
  z-index: 50;
}

.drawer {
  width: min(520px, 100%);
  background: #fff;
  height: 100%;
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  box-shadow: -20px 0 40px rgba(17, 15, 20, 0.18);
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.drawer-section {
  display: grid;
  gap: 10px;
}

.field {
  font-size: 0.8rem;
  color: #6f6770;
}

.client-search {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  align-items: start;
}

.input-wrap {
  position: relative;
}

.input-wrap input,
.select {
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: 12px;
  padding: 12px 40px 12px 14px;
  font-size: 0.95rem;
  width: 100%;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6f6770;
}

.search-icon svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.suggestions {
  position: absolute;
  top: 52px;
  left: 0;
  right: 0;
  background: #fff;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 16px 30px rgba(17, 15, 20, 0.12);
  padding: 8px;
  display: grid;
  gap: 6px;
  z-index: 5;
}

.suggestion {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #f3f4f7;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #eef0ff;
  display: grid;
  place-items: center;
}

.icon-btn svg {
  width: 18px;
  height: 18px;
  fill: #5a4bff;
}

.client-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
  border: 1px solid rgba(17, 15, 20, 0.08);
  padding: 12px 14px;
  border-radius: 12px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: #e6e7ef;
  display: grid;
  place-items: center;
  font-weight: 600;
}

.client-info {
  display: grid;
  gap: 2px;
}

.client-name {
  margin: 0;
  font-weight: 600;
}

.client-detail {
  margin: 0;
  font-size: 0.78rem;
  color: #6f6770;
}

.link-btn {
  border: none;
  background: transparent;
  color: #5a4bff;
  cursor: pointer;
  font-weight: 600;
}

.line-card {
  border: 1px solid rgba(17, 15, 20, 0.08);
  border-radius: 12px;
  padding: 12px 14px;
  display: grid;
  gap: 8px;
}

.line-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.line-title {
  font-weight: 600;
}

.line-meta {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
}

.qty {
  display: flex;
  align-items: center;
  gap: 8px;
}

.qty button {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #fff;
}

.price {
  font-size: 0.8rem;
  color: #6f6770;
}

.total {
  font-weight: 700;
  color: #10b981;
}

.drawer-footer {
  margin-top: auto;
  display: grid;
  gap: 12px;
}

.total-card {
  border: 1px solid rgba(17, 15, 20, 0.1);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.btn-primary {
  background: #5a4bff;
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 12px 16px;
  font-weight: 600;
}

.btn-ghost {
  background: #fff;
  border: 1px solid rgba(17, 15, 20, 0.12);
  border-radius: 12px;
  padding: 12px 16px;
  font-weight: 600;
}
</style>
