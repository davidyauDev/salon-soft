<script setup lang="ts">
import { reactive, watch } from 'vue'

interface CatalogItem {
  id: number
  name: string
}

interface CatalogUser {
  id: number
  user?: { name: string }
}

interface ClientItem {
  id: number
  full_name: string
}

const props = defineProps<{
  open: boolean
  saving: boolean
  services: CatalogItem[]
  stylists: CatalogUser[]
  clients: ClientItem[]
  items: CatalogItem[]
}>()

const emit = defineEmits<{
  close: []
  submit: [payload: {
    service_id: number
    stylist_id: number
    client_id: number | null
    total_amount: number
    payment_method: string
    consumptions: Array<{ item_id: number; quantity: number; unit: string }>
  }]
}>()

const form = reactive({
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

watch(
  () => props.open,
  (open) => {
    if (!open) {
      form.service_id = ''
      form.stylist_id = ''
      form.client_id = ''
      form.total_amount = ''
      form.payment_method = 'cash'
      form.consumptions = [{ item_id: '', quantity: '', unit: '' }]
    }
  },
)

function addConsumption(): void {
  form.consumptions.push({ item_id: '', quantity: '', unit: '' })
}

function removeConsumption(index: number): void {
  form.consumptions.splice(index, 1)
}

function handleSubmit(): void {
  emit('submit', {
    service_id: Number(form.service_id),
    stylist_id: Number(form.stylist_id),
    client_id: form.client_id ? Number(form.client_id) : null,
    total_amount: Number(form.total_amount),
    payment_method: form.payment_method,
    consumptions: form.consumptions.map((line) => ({
      item_id: Number(line.item_id),
      quantity: Number(line.quantity),
      unit: line.unit,
    })),
  })
}

function handleClose(): void {
  emit('close')
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="handleClose">
    <div class="modal-card">
      <div class="panel-header">
        <div>
          <h3>Registrar atencion</h3>
          <p class="panel-subtitle">Registra la atencion realizada y el consumo real.</p>
        </div>
      </div>
      <form class="form-grid" @submit.prevent="handleSubmit">
        <label>
          Servicio
          <select v-model="form.service_id">
            <option value="">Seleccionar</option>
            <option v-for="service in props.services" :key="service.id" :value="service.id">
              {{ service.name }}
            </option>
          </select>
        </label>
        <label>
          Estilista
          <select v-model="form.stylist_id">
            <option value="">Seleccionar</option>
            <option v-for="stylist in props.stylists" :key="stylist.id" :value="stylist.id">
              {{ stylist.user?.name }}
            </option>
          </select>
        </label>
        <label>
          Cliente
          <select v-model="form.client_id">
            <option value="">Sin cliente</option>
            <option v-for="client in props.clients" :key="client.id" :value="client.id">
              {{ client.full_name }}
            </option>
          </select>
        </label>
        <label>
          Total cobrado
          <input v-model="form.total_amount" type="number" step="0.01" />
        </label>
        <label>
          Metodo de pago
          <select v-model="form.payment_method">
            <option value="cash">Efectivo</option>
            <option value="card">Tarjeta</option>
            <option value="yape">Yape</option>
          </select>
        </label>

        <div class="consumption-block">
          <p>Consumos reales</p>
          <div v-for="(line, index) in form.consumptions" :key="index" class="line-grid">
            <select v-model="line.item_id">
              <option value="">Item</option>
              <option v-for="item in props.items" :key="item.id" :value="item.id">
                {{ item.name }}
              </option>
            </select>
            <input v-model="line.quantity" type="number" step="0.01" placeholder="Cantidad" />
            <input v-model="line.unit" placeholder="Unidad" />
            <button type="button" class="btn-ghost" @click="removeConsumption(index)">Quitar</button>
          </div>
          <button type="button" class="btn-ghost" @click="addConsumption">Agregar insumo</button>
        </div>

        <div class="form-actions">
          <button class="btn-primary" type="submit" :disabled="props.saving">Registrar</button>
          <button class="btn-ghost" type="button" :disabled="props.saving" @click="handleClose">
            Cerrar
          </button>
        </div>
      </form>
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
  background: var(--panel-bg);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
  display: grid;
  gap: 16px;
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

.line-grid input,
.line-grid select {
  width: 100%;
}

@media (max-width: 720px) {
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
}
</style>
