<script setup lang="ts">
import { computed } from 'vue'

type NotificationTone = 'critical' | 'warning' | 'info'

interface NotificationItem {
  title: string
  detail: string
  time: string
  tone: NotificationTone
}

const props = withDefaults(
  defineProps<{
    variant?: 'compact' | 'panel'
    title?: string
    countLabel?: string
    items?: NotificationItem[]
  }>(),
  {
    variant: 'panel',
    title: 'Notificaciones',
    countLabel: '3 nuevas',
  },
)

const defaultItems: NotificationItem[] = [
  {
    title: 'Stock bajo en coloración',
    detail: 'Tinte 8.8 y oxidante 20 vol requieren reposición hoy.',
    time: '09:20',
    tone: 'critical',
  },
  {
    title: 'Cita confirmada',
    detail: 'María Torres reprogramó su turno para las 11:30.',
    time: '10:05',
    tone: 'info',
  },
  {
    title: 'Caja activa',
    detail: 'Se registraron ventas por productos y servicios en la mañana.',
    time: '10:42',
    tone: 'warning',
  },
]

const visibleItems = computed(() => {
  const items = props.items?.length ? props.items : defaultItems
  return props.variant === 'compact' ? items.slice(0, 2) : items
})
</script>

<template>
  <section class="notification-stack" :class="props.variant">
    <header class="notification-head">
      <div class="notification-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24">
          <path
            d="M12 2a5 5 0 0 0-5 5v2.1c0 .89-.24 1.77-.69 2.55L5.1 13.1A2 2 0 0 0 6.8 16h10.4a2 2 0 0 0 1.7-2.9l-1.21-1.45a4.97 4.97 0 0 1-.69-2.55V7a5 5 0 0 0-5-5Zm0 20a3 3 0 0 0 2.83-2h-5.66A3 3 0 0 0 12 22Z"
          />
        </svg>
      </div>

      <div class="notification-title">
        <p class="notification-eyebrow">Centro de alertas</p>
        <h3>{{ props.title }}</h3>
      </div>

      <span class="notification-count">{{ props.countLabel }}</span>
    </header>

    <div class="notification-list">
      <article v-for="item in visibleItems" :key="`${item.title}-${item.time}`" class="notification-item">
        <span class="tone-dot" :class="item.tone" aria-hidden="true"></span>
        <div class="notification-copy">
          <p>{{ item.title }}</p>
          <small>{{ item.detail }}</small>
        </div>
        <time>{{ item.time }}</time>
      </article>
    </div>
  </section>
</template>

<style scoped>
.notification-stack {
  display: grid;
  gap: 14px;
  border-radius: 18px;
  border: 1px solid rgba(25, 25, 25, 0.08);
  background: linear-gradient(180deg, rgba(255, 253, 251, 0.98), rgba(245, 252, 250, 0.96));
  padding: 16px 18px;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
}

.notification-stack.compact {
  min-width: min(100%, 320px);
}

.notification-head {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
}

.notification-mark {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: rgba(15, 118, 110, 0.1);
  color: var(--accent);
}

.notification-mark svg {
  width: 20px;
  height: 20px;
  fill: currentColor;
}

.notification-eyebrow {
  margin: 0;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  color: var(--ink-muted);
}

.notification-title h3 {
  margin: 4px 0 0;
  font-size: 1rem;
  color: var(--ink-strong);
}

.notification-count {
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(15, 118, 110, 0.1);
  color: #0b534b;
  font-size: 0.74rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.notification-list {
  display: grid;
  gap: 10px;
}

.notification-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: start;
  padding: 12px 14px;
  border-radius: 14px;
  background: #fffdfa;
  border: 1px solid rgba(25, 25, 25, 0.08);
}

.tone-dot {
  width: 10px;
  height: 10px;
  margin-top: 4px;
  border-radius: 999px;
}

.tone-dot.critical {
  background: #8a3b37;
}

.tone-dot.warning {
  background: var(--accent);
}

.tone-dot.info {
  background: #bfd9d4;
}

.notification-copy {
  display: grid;
  gap: 2px;
}

.notification-copy p {
  margin: 0;
  font-weight: 700;
  color: var(--ink-strong);
}

.notification-copy small {
  color: var(--ink-muted);
  line-height: 1.45;
}

.notification-item time {
  font-size: 0.74rem;
  color: var(--ink-muted);
  white-space: nowrap;
}

.compact .notification-item {
  padding: 10px 12px;
}

.compact .notification-copy p {
  font-size: 0.88rem;
}

.compact .notification-copy small {
  font-size: 0.78rem;
}

@media (max-width: 900px) {
  .notification-stack.compact {
    width: 100%;
  }
}
</style>
