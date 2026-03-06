<script setup lang="ts">
import { computed } from 'vue'
import type { ServiceCategory, ServiceItem } from '../../composables/useServiceCatalog'
import { formatCurrency } from '../../utils/format'

const props = defineProps<{
  categories: ServiceCategory[]
  services: ServiceItem[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'create-category'): void
  (e: 'edit-category', category: ServiceCategory): void
  (e: 'delete-category', category: ServiceCategory): void
  (e: 'create-service', categoryId: number | null): void
  (e: 'edit-service', service: ServiceItem): void
  (e: 'delete-service', service: ServiceItem): void
  (e: 'move-service', payload: { service: ServiceItem; direction: 'up' | 'down' }): void
}>()

const search = defineModel<string>('search', { default: '' })
const selectedCategoryId = defineModel<number | null>('selectedCategoryId', { default: null })

const normalizedQuery = computed(() => search.value.trim().toLowerCase())

const orderedCategories = computed(() =>
  [...props.categories].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name)),
)

const filteredServices = computed(() => {
  if (!normalizedQuery.value) return props.services
  return props.services.filter((service) => {
    const name = service.name?.toLowerCase() ?? ''
    return name.includes(normalizedQuery.value)
  })
})

const countSource = computed(() => (normalizedQuery.value ? filteredServices.value : props.services))

const servicesByCategory = computed(() => {
  const map = new Map<number | null, ServiceItem[]>()
  for (const service of filteredServices.value) {
    const key = service.category_id ?? null
    if (!map.has(key)) map.set(key, [])
    map.get(key)?.push(service)
  }

  for (const [key, list] of map.entries()) {
    list.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name))
    map.set(key, list)
  }

  return map
})

const categoryCounts = computed(() => {
  const map = new Map<number | null, number>()
  for (const service of countSource.value) {
    const key = service.category_id ?? null
    map.set(key, (map.get(key) ?? 0) + 1)
  }
  return map
})

const totalCount = computed(() => countSource.value.length)

const visibleSections = computed(() => {
  const sections: Array<{ category: ServiceCategory | null; services: ServiceItem[] }> = []
  const selected = selectedCategoryId.value

  if (selected !== null) {
    const category = orderedCategories.value.find((item) => item.id === selected) ?? null
    const services = servicesByCategory.value.get(selected) ?? []
    sections.push({ category, services })
    return sections
  }

  for (const category of orderedCategories.value) {
    sections.push({ category, services: servicesByCategory.value.get(category.id) ?? [] })
  }

  const uncategorized = servicesByCategory.value.get(null) ?? []
  if (uncategorized.length) {
    sections.push({ category: null, services: uncategorized })
  }

  return sections
})

function formatDuration(minutes?: number | null): string {
  if (!minutes) return 'Sin duracion'
  if (minutes >= 60) {
    const hours = Math.floor(minutes / 60)
    const rest = minutes % 60
    return rest ? `${hours}h ${rest}m` : `${hours}h`
  }
  return `${minutes} min`
}

function categoryLabel(category: ServiceCategory | null): string {
  return category?.name ?? 'Sin categoria'
}

function categoryCount(categoryId: number | null): number {
  return categoryCounts.value.get(categoryId ?? null) ?? 0
}
</script>

<template>
  <section class="panel service-catalog">
    <header class="catalog-header">
      <div>
        <p class="eyebrow">Servicios</p>
        <h3>Organiza tus servicios y categorias</h3>
        <p class="panel-subtitle">Configura duraciones, precios y personal asignado.</p>
      </div>
      <div class="header-actions">
        <label class="search-field">
          <span class="search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path
                d="M10.5 3a7.5 7.5 0 1 0 4.7 13.4l4.2 4.2a1 1 0 0 0 1.4-1.4l-4.2-4.2A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z"
              />
            </svg>
          </span>
          <input v-model="search" class="search-input" type="search" placeholder="Buscar por nombre de servicio..." />
        </label>
        <span class="pill">Servicios {{ totalCount }}</span>
        <button class="btn-ghost" type="button">Ver sitio web</button>
      </div>
    </header>

    <div class="catalog-body">
      <aside class="catalog-sidebar">
        <div class="sidebar-head">
          <h4>Categorias</h4>
          <button class="btn-ghost" type="button" @click="emit('create-category')">
            + Anadir categoria
          </button>
        </div>
        <button
          class="category-pill"
          :class="{ active: selectedCategoryId === null }"
          type="button"
          @click="selectedCategoryId = null"
        >
          <span>Todas las categorias</span>
          <span class="count">{{ totalCount }}</span>
        </button>
        <button
          v-for="category in orderedCategories"
          :key="category.id"
          class="category-pill"
          :class="{ active: selectedCategoryId === category.id }"
          type="button"
          @click="selectedCategoryId = category.id"
        >
          <span>{{ category.name }}</span>
          <span class="count">{{ categoryCount(category.id) }}</span>
        </button>
      </aside>

      <div class="catalog-main">
        <div v-if="props.loading" class="empty-state">Cargando servicios...</div>

        <div v-else-if="!filteredServices.length" class="empty-state">
          <p>No encontramos servicios con este filtro.</p>
          <button class="btn-primary" type="button" @click="emit('create-service', selectedCategoryId)">
            Crear servicio
          </button>
        </div>

        <div v-else class="category-list">
          <section v-for="section in visibleSections" :key="section.category?.id ?? 'uncat'" class="category-card">
            <header class="category-header">
              <div class="category-title">
                <h4>{{ categoryLabel(section.category) }}</h4>
                <span class="pill">{{ section.services.length }} servicios</span>
              </div>
              <div class="category-actions">
                <button
                  class="btn-ghost"
                  type="button"
                  @click="emit('create-service', section.category?.id ?? null)"
                >
                  + Servicio
                </button>
                <template v-if="section.category">
                  <button class="icon-btn" type="button" @click="emit('edit-category', section.category)">
                    Editar
                  </button>
                  <button class="icon-btn danger" type="button" @click="emit('delete-category', section.category)">
                    Eliminar
                  </button>
                </template>
              </div>
            </header>

            <div v-if="!section.services.length" class="empty-row">
              Aun no hay servicios en esta categoria.
            </div>

            <div v-else class="service-list">
              <article v-for="service in section.services" :key="service.id" class="service-row">
                <div class="service-main">
                  <span class="service-name">{{ service.name }}</span>
                  <span v-if="service.is_active === false" class="inactive-pill">Inactivo</span>
                  <span class="service-meta">
                    {{ formatDuration(service.duration_min) }}
                    <span v-if="service.requires_deposit">- Sena {{ formatCurrency(Number(service.deposit_amount ?? 0)) }}</span>
                    <span v-if="(service.locations?.length ?? 0) > 0">- {{ service.locations?.length }} sedes</span>
                    <span v-if="(service.stylists?.length ?? 0) > 0">- {{ service.stylists?.length }} estilistas</span>
                  </span>
                </div>
                <div class="service-price">
                  {{ formatCurrency(Number(service.base_price ?? 0)) }}
                </div>
                <div class="service-actions">
                  <button
                    class="icon-btn"
                    type="button"
                    title="Subir"
                    @click="emit('move-service', { service, direction: 'up' })"
                  >
                    ^
                  </button>
                  <button
                    class="icon-btn"
                    type="button"
                    title="Bajar"
                    @click="emit('move-service', { service, direction: 'down' })"
                  >
                    v
                  </button>
                  <button class="icon-btn" type="button" @click="emit('edit-service', service)">Editar</button>
                  <button class="icon-btn danger" type="button" @click="emit('delete-service', service)">
                    Eliminar
                  </button>
                </div>
              </article>
            </div>
          </section>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.service-catalog {
  gap: 20px;
}

.catalog-header {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}

.search-field {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(23, 20, 26, 0.12);
  border-radius: var(--radius-md);
  padding: 6px 12px;
  min-width: 260px;
  flex: 1 1 240px;
}

.search-icon {
  width: 18px;
  height: 18px;
  color: var(--ink-muted);
}

.search-icon svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.9rem;
  width: 100%;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
  color: var(--ink-muted);
  border: 1px solid rgba(23, 20, 26, 0.1);
  background: rgba(255, 255, 255, 0.8);
}

.catalog-body {
  display: grid;
  grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
  gap: 20px;
}

.catalog-sidebar {
  background: rgba(255, 255, 255, 0.85);
  border-radius: var(--radius-lg);
  padding: 16px;
  border: 1px solid rgba(17, 15, 20, 0.08);
  display: grid;
  gap: 10px;
  height: fit-content;
}

.sidebar-head {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sidebar-head h4 {
  margin: 0;
}

.category-pill {
  border: 1px solid rgba(17, 15, 20, 0.08);
  background: rgba(255, 255, 255, 0.9);
  border-radius: 999px;
  padding: 10px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 0.85rem;
}

.category-pill.active {
  border-color: rgba(214, 106, 86, 0.55);
  box-shadow: 0 10px 18px rgba(214, 106, 86, 0.15);
  font-weight: 600;
}

.category-pill .count {
  background: rgba(17, 15, 20, 0.06);
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
}

.catalog-main {
  display: grid;
  gap: 16px;
}

.category-list {
  display: grid;
  gap: 16px;
}

.category-card {
  border-radius: var(--radius-lg);
  border: 1px solid rgba(17, 15, 20, 0.08);
  padding: 16px;
  background: rgba(255, 255, 255, 0.9);
  display: grid;
  gap: 12px;
}

.category-header {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
}

.category-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.category-title h4 {
  margin: 0;
}

.category-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.service-list {
  display: grid;
  gap: 10px;
}

.service-row {
  display: grid;
  grid-template-columns: minmax(180px, 1.5fr) auto auto;
  gap: 12px;
  align-items: center;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(17, 15, 20, 0.06);
}

.service-main {
  display: grid;
  gap: 4px;
}

.service-name {
  font-weight: 600;
}

.inactive-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.7rem;
  color: #b24b3a;
  background: rgba(178, 75, 58, 0.12);
  border: 1px solid rgba(178, 75, 58, 0.2);
  width: fit-content;
}

.service-meta {
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.service-price {
  font-weight: 600;
  color: var(--accent);
  min-width: 90px;
  text-align: right;
}

.service-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.icon-btn {
  border: 1px solid rgba(17, 15, 20, 0.1);
  background: rgba(255, 255, 255, 0.9);
  border-radius: 10px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 0.78rem;
  font-weight: 600;
  height: 32px;
}

.icon-btn.danger {
  border-color: rgba(178, 75, 58, 0.3);
  color: #b24b3a;
  background: rgba(178, 75, 58, 0.08);
}

.empty-state,
.empty-row {
  padding: 18px;
  border-radius: var(--radius-md);
  border: 1px dashed rgba(23, 20, 26, 0.2);
  text-align: center;
  color: var(--ink-muted);
  display: grid;
  gap: 10px;
}

@media (max-width: 980px) {
  .catalog-body {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .service-row {
    grid-template-columns: 1fr;
  }

  .service-price {
    text-align: left;
  }

  .service-actions {
    justify-content: flex-start;
  }
}
</style>
