<script setup lang="ts">
import { computed, reactive, watch } from 'vue'

const props = defineProps<{
  open: boolean
  clientName: string
  clientPhone: string
  clientEmail: string
  total: number
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'pay', payload: { payment_method: string; emit_receipt: boolean }): void
}>()

const state = reactive({
  payment_method: 'yape',
  emit_receipt: false,
})

const methods = [
  { id: 'yape', label: 'Yape/Plin' },
  { id: 'card', label: 'Tarjeta' },
  { id: 'cash', label: 'Efectivo' },
  { id: 'transfer', label: 'Transferencia' },
]

watch(
  () => props.open,
  (open) => {
    if (!open) return
    state.payment_method = 'yape'
    state.emit_receipt = false
  },
)

const disabled = computed(() => props.saving)

function handlePay(): void {
  if (disabled.value) return
  emit('pay', {
    payment_method: state.payment_method,
    emit_receipt: state.emit_receipt,
  })
}
</script>

<template>
  <div v-if="props.open" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-card">
      <header class="modal-header">
        <h3>Realizar Pago</h3>
        <button class="close-button" type="button" @click="emit('close')">X</button>
      </header>

      <section class="client-summary">
        <p class="client-name">{{ props.clientName || 'Sin cliente' }}</p>
        <p class="client-detail">Numero: {{ props.clientPhone || '-' }}</p>
        <p class="client-detail">Email: {{ props.clientEmail || '-' }}</p>
      </section>

      <section class="payment-box">
        <div class="box-title">Metodo de pago</div>
        <div class="chip-group">
          <button
            v-for="method in methods"
            :key="method.id"
            type="button"
            class="chip"
            :class="{ active: state.payment_method === method.id }"
            @click="state.payment_method = method.id"
          >
            {{ method.label }}
          </button>
        </div>
      </section>

      <section class="payment-box toggle-row">
        <span>Emitir comprobante</span>
        <label class="switch">
          <input v-model="state.emit_receipt" type="checkbox" />
          <span class="slider"></span>
        </label>
      </section>

      <footer class="modal-footer">
        <div class="total-card">
          <span>Total:</span>
          <strong>S/. {{ props.total.toFixed(2) }}</strong>
        </div>
        <button class="btn-primary" type="button" :disabled="props.saving" @click="handlePay">
          {{ props.saving ? 'Procesando...' : 'Pagar y Finalizar' }}
        </button>
      </footer>
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
  z-index: 60;
}

.modal-card {
  width: min(560px, 100%);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  box-shadow: 0 18px 40px rgba(17, 15, 20, 0.2);
  padding: 22px;
  display: grid;
  gap: 18px;
}

.modal-header {
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

.client-summary {
  border-bottom: 1px solid rgba(17, 15, 20, 0.08);
  padding-bottom: 12px;
}

.client-name {
  margin: 0;
  font-weight: 600;
}

.client-detail {
  margin: 2px 0 0;
  font-size: 0.8rem;
  color: #6f6770;
}

.payment-box {
  border: 1px solid rgba(17, 15, 20, 0.08);
  border-radius: 12px;
  padding: 12px 14px;
  display: grid;
  gap: 12px;
}

.box-title {
  font-size: 0.8rem;
  color: #6f6770;
}

.chip-group {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.chip {
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: #f6f7f9;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
}

.chip.active {
  background: #5a4bff;
  color: #fff;
  border-color: #5a4bff;
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
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
  background: #d6d6e0;
  transition: 0.2s;
  border-radius: 999px;
}

.slider:before {
  position: absolute;
  content: '';
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background: white;
  transition: 0.2s;
  border-radius: 999px;
}

.switch input:checked + .slider {
  background: #5a4bff;
}

.switch input:checked + .slider:before {
  transform: translateX(20px);
}

.modal-footer {
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

.btn-primary {
  background: #5a4bff;
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 12px 16px;
  font-weight: 600;
}
</style>
