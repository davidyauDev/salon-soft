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
  background: #fffdfb;
  border: 1px solid rgba(25, 25, 25, 0.08);
  border-radius: 20px;
  padding: 18px 20px;
  display: grid;
  gap: 6px;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
  position: relative;
  overflow: hidden;
}

.kpi-card::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--accent);
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
  color: #6a6a6a;
}

.trend-down .kpi-change {
  color: #8a3b37;
}
</style>
