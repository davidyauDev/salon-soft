<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import type { ServiceCategory, ServiceItem, ServiceLocation, ServiceStylist } from '../../composables/useServiceCatalog'

const props = defineProps<{
  open: boolean
  saving: boolean
  categories: ServiceCategory[]
  locations: ServiceLocation[]
  stylists: ServiceStylist[]
  initial?: ServiceItem | null
  initialCategoryId?: number | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: {
    category_id: number | null
    name: string
    description: string | null
    duration_min: number | null
    base_price: number | null
    requires_deposit: boolean
    deposit_amount: number | null
    is_active: boolean
    location_ids: number[]
    stylist_ids: number[]
  }): void
}>()

const durationOptions = [30, 40, 45, 50, 60, 65, 75, 90, 120, 150]

const form = reactive({
  category_id: '',
  name: '',
  description: '',
  duration_min: '60',
  base_price: '',
  requires_deposit: false,
  deposit_amount: '',
  is_active: true,
  location_ids: [] as number[],
  stylist_ids: [] as number[],
})

const isEditing = computed(() => Boolean(props.initial))

watch(
  () => [props.open, props.initial, props.initialCategoryId] as const,
  ([open, initial, initialCategoryId]) => {
    if (!open) {
      form.category_id = ''
      form.name = ''
      form.description = ''
      form.duration_min = '60'
      form.base_price = ''
      form.requires_deposit = false
      form.deposit_amount = ''
      form.is_active = true
      form.location_ids = []
      form.stylist_ids = []
      return
    }

    if (initial) {
      form.category_id = initial.category_id ? String(initial.category_id) : ''
      form.name = initial.name ?? ''
      form.description = initial.description ?? ''
      form.duration_min = initial.duration_min ? String(initial.duration_min) : '60'
      form.base_price = initial.base_price != null ? String(initial.base_price) : ''
      form.requires_deposit = Boolean(initial.requires_deposit)
      form.deposit_amount = initial.deposit_amount != null ? String(initial.deposit_amount) : ''
      form.is_active = initial.is_active ?? true
      form.location_ids = (initial.locations ?? []).map((location: ServiceLocation) => location.id)
      form.stylist_ids = (initial.stylists ?? []).map((stylist: ServiceStylist) => stylist.id)
      return
    }

    form.category_id = initialCategoryId ? String(initialCategoryId) : ''
    form.name = ''
    form.description = ''
    form.duration_min = '60'
    form.base_price = ''
    form.requires_deposit = false
    form.deposit_amount = ''
    form.is_active = true
    form.location_ids = []
    form.stylist_ids = []
  },
  { immediate: true },
)

function toggleLocation(id: number): void {
  if (form.location_ids.includes(id)) {
    form.location_ids = form.location_ids.filter((item) => item !== id)
  } else {
    form.location_ids = [...form.location_ids, id]
  }
}

function toggleStylist(id: number): void {
  if (form.stylist_ids.includes(id)) {
    form.stylist_ids = form.stylist_ids.filter((item) => item !== id)
  } else {
    form.stylist_ids = [...form.stylist_ids, id]
  }
}

function handleSubmit(): void {
  if (!form.name.trim()) return
  emit('submit', {
    category_id: form.category_id ? Number(form.category_id) : null,
    name: form.name.trim(),
    description: form.description.trim() || null,
    duration_min: form.duration_min ? Number(form.duration_min) : null,
    base_price: form.base_price ? Number(form.base_price) : null,
    requires_deposit: form.requires_deposit,
    deposit_amount: form.requires_deposit && form.deposit_amount ? Number(form.deposit_amount) : null,
    is_active: form.is_active,
    location_ids: form.location_ids,
    stylist_ids: form.stylist_ids,
  })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card service-modal">
      <header class="modal-header">
        <div>
          <h3>{{ isEditing ? 'Editar servicio' : 'Crear nuevo servicio' }}</h3>
          <p class="panel-subtitle">
            {{ isEditing ? 'Actualiza precio, duracion y equipos asignados.' : 'Completa la informacion para el catalogo.' }}
          </p>
        </div>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <form class="form-grid" @submit.prevent="handleSubmit">
        <div class="form-grid two-col">
          <label>
            Categoria
            <select v-model="form.category_id" required>
              <option value="">Seleccionar</option>
              <option v-for="category in props.categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>
          <label>
            Nombre del servicio
            <input v-model="form.name" required placeholder="Ej: Corte Clasico" />
          </label>
        </div>

        <label>
          Descripcion (opcional)
          <textarea v-model="form.description" rows="3" placeholder="Describe brevemente el servicio" />
        </label>

        <div class="form-grid two-col">
          <label>
            Duracion
            <select v-model="form.duration_min">
              <option v-for="minutes in durationOptions" :key="minutes" :value="String(minutes)">
                {{ minutes }} min
              </option>
            </select>
          </label>
          <label>
            Precio (S/.)
            <input v-model="form.base_price" type="number" step="0.01" placeholder="Precio del servicio" />
          </label>
        </div>

        <div class="deposit-card">
          <div class="deposit-head">
            <div>
              <h4>Pago adelantado (sena)</h4>
              <p>Requiere un anticipo para confirmar la cita.</p>
            </div>
            <label class="switch">
              <input v-model="form.requires_deposit" type="checkbox" />
              <span class="slider"></span>
            </label>
          </div>
          <label>
            Monto de sena
            <input
              v-model="form.deposit_amount"
              type="number"
              step="0.01"
              :disabled="!form.requires_deposit"
              :required="form.requires_deposit"
              placeholder="S/ 0.00"
            />
          </label>
        </div>

        <div class="form-grid two-col">
          <label class="toggle-field">
            <span>Servicio activo</span>
            <input v-model="form.is_active" type="checkbox" />
          </label>
          <div class="helper-note">
            <strong>Tip:</strong> Marca el servicio como activo para mostrarlo en la agenda.
          </div>
        </div>

        <section class="assign-block">
          <h4>Ubicaciones disponibles</h4>
          <p>Selecciona las sedes donde se ofrece este servicio.</p>
          <div class="option-grid">
            <button
              v-for="location in props.locations"
              :key="location.id"
              type="button"
              class="option-card"
              :class="{ active: form.location_ids.includes(location.id) }"
              @click="toggleLocation(location.id)"
            >
              <span class="option-name">{{ location.name }}</span>
              <span class="option-meta">{{ location.address ?? 'Direccion no registrada' }}</span>
            </button>
          </div>
        </section>

        <section class="assign-block">
          <h4>Personal asignado</h4>
          <p>Selecciona los estilistas que realizan este servicio.</p>
          <div class="option-grid">
            <button
              v-for="stylist in props.stylists"
              :key="stylist.id"
              type="button"
              class="option-card"
              :class="{ active: form.stylist_ids.includes(stylist.id) }"
              @click="toggleStylist(stylist.id)"
            >
              <span class="option-name">{{ stylist.user?.name ?? 'Estilista' }}</span>
              <span class="option-meta">Comision {{ stylist.commission_rate ?? 0 }}%</span>
            </button>
          </div>
        </section>

        <div class="form-actions">
          <button class="btn-primary" type="submit" :disabled="props.saving">
            {{ isEditing ? 'Guardar cambios' : 'Crear servicio' }}
          </button>
          <button class="btn-ghost" type="button" :disabled="props.saving" @click="emit('close')">
            Cancelar
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
  width: min(720px, 100%);
  max-height: 92vh;
  overflow-y: auto;
  background: var(--panel-bg);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
  display: grid;
  gap: 16px;
}

.service-modal {
  scroll-behavior: smooth;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
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

textarea {
  resize: vertical;
  min-height: 90px;
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: var(--radius-md);
  padding: 12px 14px;
  background: #fff;
  font-size: 0.95rem;
}

textarea:focus {
  outline: none;
  border-color: rgba(214, 106, 86, 0.5);
  box-shadow: 0 0 0 3px rgba(214, 106, 86, 0.15);
}

.deposit-card {
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.1);
  background: rgba(255, 255, 255, 0.9);
  padding: 16px;
  display: grid;
  gap: 12px;
}

.deposit-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.deposit-head h4 {
  margin: 0;
}

.deposit-head p {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.switch {
  position: relative;
  display: inline-flex;
  align-items: center;
  width: 46px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background: rgba(17, 15, 20, 0.1);
  border-radius: 999px;
  transition: 0.2s ease;
}

.slider::before {
  content: '';
  position: absolute;
  height: 20px;
  width: 20px;
  left: 3px;
  top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: 0.2s ease;
  box-shadow: 0 6px 12px rgba(17, 15, 20, 0.2);
}

.switch input:checked + .slider {
  background: rgba(53, 32, 99, 0.75);
}

.switch input:checked + .slider::before {
  transform: translateX(20px);
}

.toggle-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(23, 20, 26, 0.12);
  background: rgba(255, 255, 255, 0.95);
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.toggle-field input {
  width: 42px;
  height: 22px;
}

.helper-note {
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(17, 15, 20, 0.05);
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.assign-block {
  display: grid;
  gap: 8px;
}

.assign-block h4 {
  margin: 0;
}

.assign-block p {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ink-muted);
}

.option-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.option-card {
  border: 1px solid rgba(17, 15, 20, 0.1);
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.95);
  padding: 12px;
  display: grid;
  gap: 4px;
  text-align: left;
  cursor: pointer;
  transition: border 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.option-card.active {
  border-color: rgba(214, 106, 86, 0.6);
  box-shadow: 0 10px 18px rgba(214, 106, 86, 0.15);
  transform: translateY(-1px);
}

.option-name {
  font-weight: 600;
}

.option-meta {
  font-size: 0.78rem;
  color: var(--ink-muted);
}

@media (max-width: 720px) {
  .modal-card {
    width: 100%;
  }

  .deposit-head {
    flex-direction: column;
  }
}
</style>
