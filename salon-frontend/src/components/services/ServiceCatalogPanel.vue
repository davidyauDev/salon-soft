<script setup lang="ts">
import { computed, shallowRef } from 'vue'
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
const expandedSectionKeys = shallowRef<string[]>([])

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
const isAccordionMode = computed(() => selectedCategoryId.value === null && !normalizedQuery.value)

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

function sectionKey(category: ServiceCategory | null): string {
  return category ? `category-${category.id}` : 'category-uncategorized'
}

function isSectionExpanded(category: ServiceCategory | null): boolean {
  if (!isAccordionMode.value) return true
  return expandedSectionKeys.value.includes(sectionKey(category))
}

function toggleSection(category: ServiceCategory | null): void {
  if (!isAccordionMode.value) return

  const key = sectionKey(category)
  expandedSectionKeys.value = expandedSectionKeys.value.includes(key)
    ? expandedSectionKeys.value.filter((item) => item !== key)
    : [...expandedSectionKeys.value, key]
}

function showAllCategories(): void {
  selectedCategoryId.value = null
  expandedSectionKeys.value = []
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
          @click="showAllCategories"
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
          <section
            v-for="section in visibleSections"
            :key="section.category?.id ?? 'uncat'"
            class="category-card"
            :class="{ collapsed: isAccordionMode && !isSectionExpanded(section.category) }"
          >
            <header class="category-header">
              <button
                class="category-toggle"
                type="button"
                :class="{ collapsible: isAccordionMode }"
                @click="toggleSection(section.category)"
              >
                <span class="category-chevron" :class="{ expanded: isSectionExpanded(section.category) }" aria-hidden="true">
                  <svg viewBox="0 0 24 24">
                    <path
                      d="M6 9l6 6 6-6"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </span>
                <h4>{{ categoryLabel(section.category) }}</h4>
                <span class="pill">{{ section.services.length }} servicios</span>
              </button>
              <div class="category-actions">
                <button
                  class="service-add-btn"
                  type="button"
                  @click.stop="emit('create-service', section.category?.id ?? null)"
                >
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                      d="M12 5v14M5 12h14"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.9"
                      stroke-linecap="round"
                    />
                  </svg>
                  <span>Servicio</span>
                </button>
                <template v-if="section.category">
                  <button
                    class="header-icon-btn"
                    type="button"
                    title="Editar categoria"
                    @click.stop="emit('edit-category', section.category)"
                  >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path
                        d="M12 20h9"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </button>
                  <button
                    class="header-icon-btn danger"
                    type="button"
                    title="Eliminar categoria"
                    @click.stop="emit('delete-category', section.category)"
                  >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path
                        d="M3 6h18"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                      />
                      <path
                        d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M19 6l-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M10 11v6M14 11v6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                      />
                    </svg>
                  </button>
                </template>
              </div>
            </header>

            <div v-if="isSectionExpanded(section.category) && !section.services.length" class="empty-row">
              Aun no hay servicios en esta categoria.
            </div>

            <div v-else-if="isSectionExpanded(section.category)" class="service-list">
              <article v-for="service in section.services" :key="service.id" class="service-row">
                <div class="service-main">
                  <span class="service-name">{{ service.name }}</span>
                  <span v-if="service.is_active === false" class="inactive-pill">Inactivo</span>
                  <span v-if="service.requires_deposit || (service.locations?.length ?? 0) > 0 || (service.stylists?.length ?? 0) > 0" class="service-meta">
                    {{ formatDuration(service.duration_min) }}
                    <span v-if="service.requires_deposit">- Sena {{ formatCurrency(Number(service.deposit_amount ?? 0)) }}</span>
                    <span v-if="(service.locations?.length ?? 0) > 0">- {{ service.locations?.length }} sedes</span>
                    <span v-if="(service.stylists?.length ?? 0) > 0">- {{ service.stylists?.length }} estilistas</span>
                  </span>
                </div>
                <div class="service-trailing">
                  <span class="service-duration">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <circle
                        cx="12"
                        cy="12"
                        r="8"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                      />
                      <path
                        d="M12 8v4l2.5 1.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span>{{ formatDuration(service.duration_min) }}</span>
                  </span>
                  <div class="service-price">
                    {{ formatCurrency(Number(service.base_price ?? 0)) }}
                  </div>
                  <div class="service-actions">
                    <button
                      class="service-icon-btn"
                      type="button"
                      title="Subir"
                      @click="emit('move-service', { service, direction: 'up' })"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                          d="M12 19V5M6 11l6-6 6 6"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                    <button
                      class="service-icon-btn"
                      type="button"
                      title="Bajar"
                      @click="emit('move-service', { service, direction: 'down' })"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                          d="M12 5v14M18 13l-6 6-6-6"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                    <button class="service-icon-btn" type="button" title="Editar" @click="emit('edit-service', service)">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                          d="M12 20h9"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                    <button class="service-icon-btn danger" type="button" title="Eliminar" @click="emit('delete-service', service)">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                          d="M3 6h18"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                        />
                        <path
                          d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M19 6l-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M10 11v6M14 11v6"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.9"
                          stroke-linecap="round"
                        />
                      </svg>
                    </button>
                  </div>
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

.category-card.collapsed {
  gap: 0;
}

.category-header {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
}

.category-toggle {
  border: none;
  background: transparent;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--ink-strong);
  font: inherit;
}

.category-toggle.collapsible {
  cursor: pointer;
}

.category-toggle h4 {
  margin: 0;
}

.category-chevron {
  width: 18px;
  height: 18px;
  color: #9a94a1;
  transition: transform 0.18s ease;
}

.category-chevron.expanded {
  transform: rotate(180deg);
}

.category-chevron svg {
  width: 100%;
  height: 100%;
}

.category-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.service-add-btn {
  border: 1px solid rgba(17, 15, 20, 0.12);
  background: rgba(255, 255, 255, 0.96);
  border-radius: 12px;
  padding: 0 14px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: var(--ink-strong);
  font-size: 0.9rem;
  font-weight: 600;
}

.service-add-btn svg {
  width: 16px;
  height: 16px;
}

.header-icon-btn,
.service-icon-btn {
  width: 34px;
  height: 34px;
  border: none;
  background: transparent;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--ink-strong);
  transition:
    background 0.18s ease,
    color 0.18s ease,
    transform 0.18s ease;
}

.header-icon-btn svg,
.service-icon-btn svg {
  width: 18px;
  height: 18px;
}

.header-icon-btn:hover,
.service-icon-btn:hover {
  background: rgba(17, 15, 20, 0.06);
  transform: translateY(-1px);
}

.header-icon-btn.danger,
.service-icon-btn.danger {
  color: #c85c49;
}

.service-list {
  display: grid;
  gap: 12px;
}

.service-row {
  display: grid;
  grid-template-columns: minmax(180px, 1fr) auto;
  gap: 18px;
  align-items: center;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(17, 15, 20, 0.08);
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

.service-trailing {
  display: flex;
  align-items: center;
  gap: 14px;
  justify-content: flex-end;
}

.service-duration {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(17, 15, 20, 0.1);
  background: rgba(255, 255, 255, 0.96);
  color: var(--ink-muted);
  font-size: 0.78rem;
  font-weight: 600;
}

.service-duration svg {
  width: 14px;
  height: 14px;
}

.service-price {
  font-weight: 700;
  color: #12a150;
  min-width: 106px;
  text-align: right;
}

.service-actions {
  display: flex;
  gap: 2px;
  justify-content: flex-end;
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

  .service-trailing {
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .service-actions {
    justify-content: flex-start;
  }
}
</style>
