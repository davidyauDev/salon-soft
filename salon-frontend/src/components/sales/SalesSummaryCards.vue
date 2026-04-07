<script setup lang="ts">
import { formatCurrency } from '../../utils/format'

interface SummaryCard {
  id: string
  label: string
  value: number
  kind: 'money' | 'count'
}

defineProps<{
  cards: SummaryCard[]
}>()
</script>

<template>
  <section class="summary-grid">
    <article v-for="card in cards" :key="card.id" class="summary-card">
      <div class="summary-head">
        <span class="summary-icon" :class="`icon-${card.id}`">
          <svg v-if="card.id === 'total'" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M11 4a1 1 0 0 1 2 0v1.08a4.5 4.5 0 0 1 1.6 8.62l-4.15 1.78a2.5 2.5 0 0 0 .99 4.8h1.12a2.5 2.5 0 0 0 2.5-2.5 1 1 0 1 1 2 0 4.5 4.5 0 0 1-4.06 4.48V23a1 1 0 1 1-2 0v-1.08a4.5 4.5 0 0 1-1.6-8.62l4.15-1.78a2.5 2.5 0 0 0-.99-4.8h-1.12a2.5 2.5 0 0 0-2.5 2.5 1 1 0 1 1-2 0A4.5 4.5 0 0 1 11 5.08V4Z" />
          </svg>
          <svg v-else-if="card.id === 'appointments'" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V3a1 1 0 0 1 1-1Zm12 8H5v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7ZM6 6a1 1 0 0 0-1 1v1h14V7a1 1 0 0 0-1-1H6Z" />
          </svg>
          <svg v-else-if="card.id === 'express'" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" />
          </svg>
          <svg v-else-if="card.id === 'products'" viewBox="0 0 24 24" aria-hidden="true">
            <path d="m12 2 8 4.5v11L12 22l-8-4.5v-11L12 2Zm0 2.3L6.3 7.5 12 10.7l5.7-3.2L12 4.3Zm-6 5v7l5 2.8v-7L6 9.3Zm7 9.8 5-2.8v-7l-5 2.8v7Z" />
          </svg>
          <svg v-else viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 5a1 1 0 0 1 1-1h5v5H5a1 1 0 0 1-1-1V5Zm10-1h5a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-5V4Zm0 11h5a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-5v-5ZM4 16a1 1 0 0 1 1-1h5v5H5a1 1 0 0 1-1-1v-3Zm7-12h2v16h-2V4Z" />
          </svg>
        </span>
        <span class="summary-label">{{ card.label }}</span>
      </div>
      <strong class="summary-value">
        {{ card.kind === 'money' ? formatCurrency(card.value) : card.value }}
      </strong>
    </article>
  </section>
</template>

<style scoped>
.summary-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 12px;
}

.summary-card {
  border: 1px solid rgba(25, 25, 25, 0.08);
  border-radius: 18px;
  background: rgba(255, 253, 251, 0.92);
  padding: 16px 18px;
  display: grid;
  gap: 10px;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.75) inset;
}

.summary-head {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--ink-muted);
  font-size: 0.98rem;
}

.summary-icon {
  width: 18px;
  height: 18px;
  color: var(--accent);
}

.summary-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.summary-value {
  color: var(--ink-strong);
  font-size: 1.12rem;
  font-weight: 800;
}

@media (max-width: 1180px) {
  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>
