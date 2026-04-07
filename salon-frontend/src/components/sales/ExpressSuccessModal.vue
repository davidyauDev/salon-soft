<script setup lang="ts">
import { formatCurrency } from '../../utils/format'

const props = defineProps<{
  open: boolean
  total: number
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div v-if="props.open" class="success-backdrop" @click.self="emit('close')">
    <div class="success-card">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="m9.55 16.6-3.9-3.9 1.4-1.4 2.5 2.48 7.35-7.34 1.4 1.41-8.75 8.75Z" />
        </svg>
      </div>
      <h3>¡Pago confirmado!</h3>
      <p>El pago de {{ formatCurrency(props.total) }} ha sido confirmado.</p>
      <button class="btn-primary" type="button" @click="emit('close')">Volver a ventas</button>
    </div>
  </div>
</template>

<style scoped>
.success-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 20, 0.45);
  display: grid;
  place-items: center;
  padding: 20px;
  z-index: 90;
}

.success-card {
  width: min(460px, 100%);
  min-height: 520px;
  background:
    radial-gradient(circle at top center, rgba(180, 247, 202, 0.8), transparent 36%),
    #fff;
  border-radius: 22px;
  display: grid;
  align-content: center;
  justify-items: center;
  gap: 18px;
  padding: 48px 28px;
  text-align: center;
}

.success-icon {
  width: 58px;
  height: 58px;
  border-radius: 999px;
  background: #c9f7d9;
  display: grid;
  place-items: center;
}

.success-icon svg {
  width: 34px;
  height: 34px;
  fill: #20bf55;
}

.success-card h3 {
  margin: 0;
  color: #212638;
  font-size: 1.9rem;
}

.success-card p {
  margin: 0;
  color: #54627a;
  font-size: 1rem;
}

.btn-primary {
  margin-top: 8px;
  background: #5949ec;
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 14px 22px;
  font-weight: 700;
}
</style>
