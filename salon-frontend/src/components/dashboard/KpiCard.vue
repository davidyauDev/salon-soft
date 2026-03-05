<script setup lang="ts">
interface Props {
  title: string
  value: string
  change: string
  trend: 'up' | 'down' | 'neutral'
}

const props = defineProps<Props>()
</script>

<template>
  <article class="kpi-card" :class="`trend-${props.trend}`">
    <p class="kpi-title">{{ props.title }}</p>
    <h3 class="kpi-value">{{ props.value }}</h3>
    <p class="kpi-change">{{ props.change }}</p>
  </article>
</template>

<style scoped>
.kpi-card {
  background: var(--card);
  border: 1px solid rgba(17, 15, 20, 0.08);
  border-radius: 20px;
  padding: 18px 20px;
  display: grid;
  gap: 6px;
  box-shadow: var(--shadow-soft);
}

.kpi-title {
  margin: 0;
  font-size: 0.85rem;
  color: var(--ink-muted);
}

.kpi-value {
  margin: 0;
  font-size: 1.6rem;
  color: var(--ink-strong);
}

.kpi-change {
  margin: 0;
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.trend-up .kpi-change {
  color: #1f7a57;
}

.trend-down .kpi-change {
  color: #b24b3a;
}
</style>
