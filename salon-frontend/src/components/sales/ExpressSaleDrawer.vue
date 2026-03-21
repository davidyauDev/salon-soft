<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { CatalogItem, CatalogService, CatalogUser, ClientItem } from '../../composables/useCatalogs'
import { formatCurrency } from '../../utils/format'

interface ProductLine {
  id: number
  name: string
  unit: string
  quantity: number
  unit_price: number
}

const props = defineProps<{
  open: boolean
  clients: ClientItem[]
  services: CatalogService[]
  items: CatalogItem[]
  stylists: CatalogUser[]
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'create-client'): void
  (e: 'submit', payload: {
    client_id: number
    service_id: number
    stylist_id: number
    performed_at: string
    notes: string | null
    service_price: number
    products: Array<{
      item_id: number
      quantity: number
      unit: string
      unit_price: number
    }>
  }): void
}>()

const clientQuery = defineModel<string>('clientQuery', { default: '' })
const clientId = defineModel<number | null>('clientId', { default: null })

const form = reactive({
  serviceId: '',
  stylistId: '',
  performedAt: '',
  notes: '',
  servicePrice: '',
  selectedProductId: '',
  showProductPicker: false,
  showServicePicker: true,
  editingServicePrice: false,
  products: [] as ProductLine[],
})

const normalizedQuery = computed(() => clientQuery.value.trim().toLowerCase())

const suggestions = computed(() => {
  if (!normalizedQuery.value) return []

  return props.clients
    .filter((client) => {
      const name = client.full_name?.toLowerCase() ?? ''
      const phone = client.phone?.toLowerCase() ?? ''
      const email = client.email?.toLowerCase() ?? ''
      return name.includes(normalizedQuery.value) || phone.includes(normalizedQuery.value) || email.includes(normalizedQuery.value)
    })
    .slice(0, 6)
})

const selectedClient = computed(() =>
  props.clients.find((client) => client.id === clientId.value) ?? null,
)

const selectedService = computed(() =>
  props.services.find((service) => service.id === Number(form.serviceId)) ?? null,
)

const serviceTotal = computed(() => Number(form.servicePrice || 0))

const productsTotal = computed(() =>
  form.products.reduce((sum, product) => sum + product.quantity * product.unit_price, 0),
)

const totalAmount = computed(() => serviceTotal.value + productsTotal.value)

const showSuggestions = computed(() =>
  Boolean(normalizedQuery.value) &&
  suggestions.value.length > 0 &&
  (!selectedClient.value ||
    selectedClient.value.full_name.toLowerCase() !== normalizedQuery.value),
)

const canSubmit = computed(() =>
  Boolean(clientId.value) &&
  Boolean(form.serviceId) &&
  Boolean(form.stylistId) &&
  Boolean(form.performedAt) &&
  !props.saving,
)

watch(
  () => props.open,
  (open) => {
    if (!open) return
    clientQuery.value = ''
    clientId.value = null
    form.serviceId = ''
    form.stylistId = ''
    form.performedAt = new Date().toISOString().slice(0, 10)
    form.notes = ''
    form.servicePrice = ''
    form.selectedProductId = ''
    form.showProductPicker = false
    form.showServicePicker = true
    form.editingServicePrice = false
    form.products = []
  },
  { immediate: true },
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

watch(
  () => form.serviceId,
  (value) => {
    const service = props.services.find((entry) => entry.id === Number(value))
    if (!service) return
    form.servicePrice = String(service.base_price ?? 0)
    form.showServicePicker = false
    form.editingServicePrice = false
  },
)

function selectClient(client: ClientItem): void {
  clientId.value = client.id
  clientQuery.value = client.full_name
}

function clearClient(): void {
  clientId.value = null
  clientQuery.value = ''
}

function toggleServiceEditor(): void {
  form.editingServicePrice = !form.editingServicePrice
}

function removeService(): void {
  form.serviceId = ''
  form.servicePrice = ''
  form.showServicePicker = true
  form.editingServicePrice = false
}

function addProduct(): void {
  const selected = props.items.find((item) => item.id === Number(form.selectedProductId))
  if (!selected) return

  const existing = form.products.find((product) => product.id === selected.id)
  if (existing) {
    existing.quantity += 1
  } else {
    form.products.push({
      id: selected.id,
      name: selected.name,
      unit: selected.base_unit,
      quantity: 1,
      unit_price: Number(selected.sale_price ?? 0),
    })
  }

  form.selectedProductId = ''
  form.showProductPicker = false
}

function updateQty(index: number, delta: number): void {
  const product = form.products[index]
  if (!product) return
  product.quantity = Math.max(1, product.quantity + delta)
}

function removeProduct(index: number): void {
  form.products.splice(index, 1)
}

function submit(): void {
  if (!canSubmit.value || !clientId.value || !selectedService.value) return

  emit('submit', {
    client_id: clientId.value,
    service_id: selectedService.value.id,
    stylist_id: Number(form.stylistId),
    performed_at: form.performedAt,
    notes: form.notes.trim() || null,
    service_price: Number(form.servicePrice || selectedService.value.base_price || 0),
    products: form.products.map((product) => ({
      item_id: product.id,
      quantity: product.quantity,
      unit: product.unit,
      unit_price: product.unit_price,
    })),
  })
}
</script>

<template>
  <div v-if="props.open" class="drawer-backdrop" @click.self="emit('close')">
    <aside class="drawer">
      <header class="drawer-header">
        <div>
          <h3>Venta Express</h3>
          <p>Registra una venta al instante sin necesidad de agendar una cita.</p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <div class="drawer-body">
        <section v-if="!selectedClient" class="drawer-section">
          <div class="field-row">
            <label class="field-label row-label">Cliente</label>
            <div class="field-grow client-row">
              <div class="input-wrap">
                <input v-model="clientQuery" placeholder="Buscar o crear cliente" :disabled="props.saving" />
                <span class="search-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24">
                    <path d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z" />
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
                    <span class="avatar mini">{{ client.full_name.slice(0, 1).toUpperCase() }}</span>
                    <span>{{ client.full_name }}</span>
                  </button>
                </div>
              </div>
              <button class="add-client-btn" type="button" @click="emit('create-client')">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M15 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm-3-7a7 7 0 1 1 0 14 7 7 0 0 1 0-14Zm8 11v2h-2v-2h-2v-2h2v-2h2v2h2v2h-2Z" />
                </svg>
              </button>
            </div>
          </div>
        </section>

        <section v-if="selectedClient" class="drawer-section compact-section">
          <div class="summary-top">
            <div class="summary-main">
              <span class="avatar">{{ selectedClient.full_name.slice(0, 2).toUpperCase() }}</span>
              <strong>{{ selectedClient.full_name }}</strong>
            </div>
            <div class="summary-actions">
              <button class="icon-action subtle" type="button" @click="clearClient">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 7h2v8h-2v-8Zm4 0h2v8h-2v-8ZM7 10h2v8H7v-8Z" />
                </svg>
              </button>
              <button class="action-link" type="button" @click="clearClient">Quitar</button>
            </div>
          </div>
          <div class="summary-meta">
            <span>Numero</span>
            <span>{{ selectedClient.phone ?? '-' }}</span>
            <span>Email</span>
            <span>{{ selectedClient.email ?? '-' }}</span>
          </div>
        </section>

        <section class="drawer-section">
          <div v-if="form.showServicePicker || !selectedService" class="field-row">
            <label class="field-label row-label">Servicio</label>
            <div class="field-grow">
              <select
                v-model="form.serviceId"
                class="field-control"
                :disabled="props.saving"
              >
                <option value="">Seleccionar un servicio</option>
                <option v-for="service in props.services" :key="service.id" :value="service.id">
                  {{ service.name }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="selectedService" class="service-summary">
            <div class="summary-top">
              <div class="service-main">
                <span class="service-index">01.</span>
                <strong>{{ selectedService.name }}</strong>
              </div>
              <div class="summary-actions">
                <button class="icon-action subtle" type="button" @click="removeService">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 7h2v8h-2v-8Zm4 0h2v8h-2v-8ZM7 10h2v8H7v-8Z" />
                  </svg>
                </button>
                <button class="action-link" type="button" @click="removeService">Quitar</button>
              </div>
            </div>

            <div class="service-bottom">
              <div class="service-duration">
                <span>Duracion</span>
                <strong>{{ selectedService.duration_min ?? 60 }} min</strong>
              </div>
              <div class="service-price-wrap">
                <strong class="service-price">{{ formatCurrency(Number(form.servicePrice || 0)) }}</strong>
                <button class="icon-action" type="button" @click="toggleServiceEditor">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m16.86 3.49 3.65 3.65-10.6 10.6-4.49.84.84-4.49 10.6-10.6Zm-9.46 11.7-.33 1.73 1.73-.33 8.88-8.88-1.4-1.4-8.88 8.88Z" />
                  </svg>
                </button>
              </div>
            </div>

            <input
              v-if="form.editingServicePrice"
              v-model="form.servicePrice"
              class="field-control inline-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="Precio del servicio"
            />
          </div>
        </section>

        <section class="drawer-section">
          <div class="field-row">
            <label class="field-label row-label">Staff</label>
            <div class="field-grow">
              <select v-model="form.stylistId" class="field-control" :disabled="props.saving">
                <option value="">Seleccionar staff</option>
                <option v-for="stylist in props.stylists" :key="stylist.id" :value="stylist.id">
                  {{ stylist.user?.name ?? `Staff ${stylist.id}` }}
                </option>
              </select>
            </div>
          </div>
        </section>

        <section class="drawer-section">
          <div class="field-row">
            <label class="field-label row-label">Fecha</label>
            <div class="field-grow">
              <input v-model="form.performedAt" class="field-control" type="date" :disabled="props.saving" />
            </div>
          </div>
        </section>

        <section class="drawer-section note-section">
          <label class="field-label">Nota</label>
          <textarea
            v-model="form.notes"
            class="field-control textarea"
            placeholder="Anade una nota opcional"
            :disabled="props.saving"
          ></textarea>
        </section>

        <section class="drawer-section action-section">
          <div class="action-grid">
            <button class="soft-action" type="button" @click="form.showServicePicker = true">
              <span>+</span>
              <span>Anadir servicio</span>
            </button>
            <button class="soft-action" type="button" @click="form.showProductPicker = !form.showProductPicker">
              <span>+</span>
              <span>Anadir producto</span>
            </button>
          </div>

          <select
            v-if="form.showProductPicker"
            v-model="form.selectedProductId"
            class="field-control"
            :disabled="props.saving"
            @change="addProduct"
          >
            <option value="">Seleccionar un producto</option>
            <option v-for="item in props.items" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select>
        </section>

        <section v-if="form.products.length" class="drawer-section">
          <div v-for="(product, index) in form.products" :key="product.id" class="product-card">
            <div class="product-top">
              <div>
                <strong>{{ product.name }}</strong>
                <small>{{ formatCurrency(product.unit_price) }} c/u</small>
              </div>
              <button class="icon-action subtle" type="button" @click="removeProduct(index)">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 7h2v8h-2v-8Zm4 0h2v8h-2v-8ZM7 10h2v8H7v-8Z" />
                </svg>
              </button>
            </div>
            <div class="product-bottom">
              <div class="qty-box">
                <button type="button" @click="updateQty(index, -1)">-</button>
                <span>{{ product.quantity }}</span>
                <button type="button" @click="updateQty(index, 1)">+</button>
              </div>
              <strong class="product-total">{{ formatCurrency(product.quantity * product.unit_price) }}</strong>
            </div>
          </div>
        </section>
      </div>

      <footer class="drawer-footer">
        <div class="total-card">
          <span>Total:</span>
          <strong>{{ formatCurrency(totalAmount) }}</strong>
        </div>
        <div class="footer-actions">
          <button class="btn-ghost" type="button" @click="emit('close')">Cancelar</button>
          <button class="btn-primary" type="button" :disabled="!canSubmit" @click="submit">
            {{ props.saving ? 'Confirmando...' : 'Confirmar' }}
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
  z-index: 70;
}

.drawer {
  width: min(510px, 100%);
  height: 100%;
  background: #fff;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: -18px 0 38px rgba(17, 15, 20, 0.18);
}

.drawer-header,
.drawer-section,
.drawer-footer {
  padding: 0 18px;
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding-top: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid rgba(17, 15, 20, 0.08);
}

.drawer-header h3 {
  margin: 0;
  color: #1f2539;
  font-size: 1.2rem;
  font-weight: 800;
}

.drawer-header p {
  margin: 6px 0 0;
  color: #6e7a90;
  font-size: 0.82rem;
  font-weight: 600;
}

.close-button {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: none;
  background: #eef2f8;
  cursor: pointer;
  font-size: 1rem;
}

.drawer-body {
  flex: 1;
  overflow-y: auto;
}

.drawer-section {
  display: grid;
  gap: 10px;
  padding-top: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(17, 15, 20, 0.08);
}

.field-row {
  display: grid;
  grid-template-columns: 64px 1fr;
  align-items: center;
  gap: 12px;
}

.row-label {
  padding-left: 6px;
}

.field-grow {
  min-width: 0;
}

.field-label {
  color: #66758e;
  font-size: 0.85rem;
  font-weight: 700;
}

.field-control {
  width: 100%;
  border: 1px solid rgba(31, 42, 62, 0.14);
  border-radius: 14px;
  background: #fff;
  padding: 12px 14px;
  font-size: 0.98rem;
  color: #24324e;
}

.textarea {
  min-height: 76px;
  resize: none;
}

.client-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: start;
}

.input-wrap {
  position: relative;
}

.input-wrap input {
  width: 100%;
  border: 1px solid rgba(31, 42, 62, 0.14);
  border-radius: 14px;
  background: #fff;
  padding: 12px 42px 12px 14px;
  font-size: 0.98rem;
  color: #24324e;
}

.search-icon {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #1c2233;
}

.search-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.add-client-btn {
  width: 46px;
  height: 46px;
  border: 1px solid rgba(90, 75, 255, 0.12);
  border-radius: 14px;
  background: #eef0ff;
  display: grid;
  place-items: center;
}

.add-client-btn svg {
  width: 20px;
  height: 20px;
  fill: #5949ec;
}

.suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid rgba(17, 15, 20, 0.08);
  border-radius: 14px;
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
  background: #f2f4f8;
  border: none;
  border-radius: 12px;
  padding: 10px 12px;
  text-align: left;
  color: #23304d;
  font-weight: 600;
}

.compact-section {
  gap: 10px;
}

.summary-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.summary-main,
.service-main {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  color: #21304b;
}

.summary-main strong,
.service-main strong {
  font-size: 1rem;
}

.summary-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.summary-actions::before {
  content: '';
  width: 1px;
  height: 22px;
  background: rgba(31, 42, 62, 0.18);
  margin-right: 2px;
}

.summary-meta {
  display: grid;
  grid-template-columns: 76px 1fr;
  gap: 8px 14px;
  color: #21304b;
  font-size: 0.82rem;
}

.summary-meta span:nth-child(odd) {
  color: #71809a;
  font-weight: 700;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 999px;
  background: #e8eaef;
  color: #4b566d;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.avatar.mini {
  width: 28px;
  height: 28px;
  font-size: 0.8rem;
}

.service-summary {
  display: grid;
  gap: 10px;
}

.service-index {
  color: #697789;
  font-weight: 700;
}

.service-bottom,
.product-top,
.product-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.service-duration {
  display: grid;
  gap: 4px;
  color: #73839c;
  font-size: 0.84rem;
}

.service-duration strong {
  color: #24304a;
  font-weight: 700;
}

.service-price-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.service-price,
.product-total {
  color: #13b79e;
  font-size: 1.05rem;
}

.icon-action {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: none;
  background: transparent;
  display: grid;
  place-items: center;
}

.icon-action svg {
  width: 18px;
  height: 18px;
  fill: #1f2539;
}

.icon-action.subtle {
  width: 28px;
  height: 28px;
}

.icon-action.subtle svg {
  width: 16px;
  height: 16px;
}

.action-link {
  border: none;
  background: transparent;
  color: #27324a;
  font-weight: 700;
  padding: 0;
}

.inline-input {
  margin-top: 2px;
}

.action-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.soft-action {
  border: none;
  border-radius: 12px;
  background: #e9edff;
  color: #5949ec;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-weight: 700;
}

.product-card {
  border: 1px solid rgba(17, 15, 20, 0.08);
  border-radius: 14px;
  background: #fff;
  padding: 12px 14px;
}

.product-card small {
  display: block;
  margin-top: 3px;
  color: #6f7b8f;
  font-size: 0.8rem;
}

.qty-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-box button {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: 1px solid rgba(31, 42, 62, 0.16);
  background: #fff;
}

.drawer-footer {
  margin-top: auto;
  border-top: 1px solid rgba(17, 15, 20, 0.08);
  padding-top: 16px;
  padding-bottom: 18px;
  display: grid;
  gap: 12px;
  background: #fff;
}

.total-card {
  border: 1px solid rgba(17, 15, 20, 0.1);
  border-radius: 14px;
  background: #fff;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #1f2539;
  font-size: 1.05rem;
}

.footer-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.btn-ghost,
.btn-primary {
  border-radius: 12px;
  padding: 14px 16px;
  font-weight: 700;
  font-size: 1rem;
}

.btn-ghost {
  border: 1px solid rgba(54, 69, 94, 0.3);
  background: #fff;
  color: #1f2539;
}

.btn-primary {
  border: none;
  background: #5949ec;
  color: #fff;
}

.btn-primary:disabled {
  background: #e8edf6;
  color: #91a0b8;
}

@media (max-width: 560px) {
  .field-row,
  .action-grid,
  .footer-actions {
    grid-template-columns: 1fr;
  }

  .row-label {
    padding-left: 0;
  }
}
</style>
