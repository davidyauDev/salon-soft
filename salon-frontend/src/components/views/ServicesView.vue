<script setup lang="ts">
import { onMounted, shallowRef } from 'vue'
import { useServiceCatalog, type ServiceCategory, type ServiceItem } from '../../composables/useServiceCatalog'
import { notifyCategoryChanged, notifyError, notifySuccess } from '../../lib/notify'
import { confirmDelete } from '../../lib/confirm'
import ServiceCatalogPanel from '../services/ServiceCatalogPanel.vue'
import ServiceCategoryModal from '../services/ServiceCategoryModal.vue'
import ServiceFormModal from '../services/ServiceFormModal.vue'

const catalog = useServiceCatalog()

const search = shallowRef('')
const selectedCategoryId = shallowRef<number | null>(null)

const showCategoryModal = shallowRef(false)
const showServiceModal = shallowRef(false)
const savingCategory = shallowRef(false)
const savingService = shallowRef(false)

const categoryEditing = shallowRef<ServiceCategory | null>(null)
const serviceEditing = shallowRef<ServiceItem | null>(null)
const initialServiceCategoryId = shallowRef<number | null>(null)

onMounted(() => {
  catalog.load()
})

function openCategoryCreate(): void {
  categoryEditing.value = null
  showCategoryModal.value = true
}

function openCategoryEdit(category: ServiceCategory): void {
  categoryEditing.value = category
  showCategoryModal.value = true
}

function closeCategoryModal(): void {
  if (savingCategory.value) return
  showCategoryModal.value = false
  categoryEditing.value = null
}

function closeServiceModal(): void {
  showServiceModal.value = false
  serviceEditing.value = null
  initialServiceCategoryId.value = null
}

async function submitCategory(payload: { name: string; is_active: boolean }): Promise<void> {
  if (savingCategory.value) return
  savingCategory.value = true
  try {
    if (categoryEditing.value) {
      await catalog.updateCategory(categoryEditing.value.id, payload)
      showCategoryModal.value = false
      categoryEditing.value = null
      notifyCategoryChanged({ name: payload.name, mode: 'updated' })
    } else {
      await catalog.createCategory(payload)
      showCategoryModal.value = false
      categoryEditing.value = null
      notifyCategoryChanged({ name: payload.name, mode: 'created' })
    }
  } catch (err) {
    notifyError('No se pudo guardar la categoria', (err as Error).message)
  } finally {
    savingCategory.value = false
  }
}

async function removeCategory(category: ServiceCategory): Promise<void> {
  const ok = await confirmDelete('Eliminar esta categoria? Esto no elimina servicios asociados.')
  if (!ok) return
  await catalog.deleteCategory(category.id)
  if (selectedCategoryId.value === category.id) {
    selectedCategoryId.value = null
  }
  notifySuccess('Categoria eliminada')
}

function openServiceCreate(categoryId: number | null): void {
  serviceEditing.value = null
  initialServiceCategoryId.value = categoryId
  showServiceModal.value = true
}

function openServiceEdit(service: ServiceItem): void {
  serviceEditing.value = service
  initialServiceCategoryId.value = null
  showServiceModal.value = true
}

async function submitService(payload: {
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
}): Promise<void> {
  if (savingService.value) return
  savingService.value = true
  try {
    if (serviceEditing.value) {
      await catalog.updateService(serviceEditing.value.id, payload)
      notifySuccess('Servicio actualizado')
    } else {
      await catalog.createService(payload)
      notifySuccess('Servicio creado')
    }
    closeServiceModal()
  } finally {
    savingService.value = false
  }
}

async function removeService(service: ServiceItem): Promise<void> {
  const ok = await confirmDelete('Eliminar este servicio?')
  if (!ok) return
  await catalog.deleteService(service.id)
  notifySuccess('Servicio eliminado')
}

async function moveService(payload: { service: ServiceItem; direction: 'up' | 'down' }): Promise<void> {
  const { service, direction } = payload
  const siblings = catalog.services.value
    .filter((item) => (item.category_id ?? null) === (service.category_id ?? null))
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name))

  const index = siblings.findIndex((item) => item.id === service.id)
  if (index === -1) return
  const targetIndex = direction === 'up' ? index - 1 : index + 1
  if (targetIndex < 0 || targetIndex >= siblings.length) return

  const currentOrder = service.sort_order ?? 0
  const target = siblings[targetIndex]
  const targetOrder = target.sort_order ?? 0

  await catalog.updateService(service.id, { sort_order: targetOrder })
  await catalog.updateService(target.id, { sort_order: currentOrder })
}
</script>

<template>
  <section class="view">
    <header class="view-header">
      <div>
        <p class="eyebrow">Servicios</p>
        <h2>Catalogo de servicios</h2>
      </div>
    </header>

    <ServiceCatalogPanel
      v-model:search="search"
      v-model:selectedCategoryId="selectedCategoryId"
      :categories="catalog.categories.value"
      :services="catalog.services.value"
      :loading="catalog.loading.value"
      @create-category="openCategoryCreate"
      @edit-category="openCategoryEdit"
      @delete-category="removeCategory"
      @create-service="openServiceCreate"
      @edit-service="openServiceEdit"
      @delete-service="removeService"
      @move-service="moveService"
    />

    <ServiceCategoryModal
      :open="showCategoryModal"
      :saving="savingCategory"
      :initial="categoryEditing"
      @close="closeCategoryModal"
      @submit="submitCategory"
    />

    <ServiceFormModal
      :open="showServiceModal"
      :saving="savingService"
      :initial="serviceEditing"
      :categories="catalog.categories.value"
      :locations="catalog.locations.value"
      :stylists="catalog.stylists.value"
      :initial-category-id="initialServiceCategoryId"
      @close="closeServiceModal"
      @submit="submitService"
    />
  </section>
</template>

<style scoped>
.view {
  display: grid;
  gap: 24px;
}
</style>
