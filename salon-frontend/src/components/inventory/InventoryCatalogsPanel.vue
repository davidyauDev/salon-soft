<script setup lang="ts">
import { reactive } from 'vue'

const emit = defineEmits<{
  createCategory: [payload: { name: string; description?: string | null }]
  createBrand: [payload: { name: string }]
  createSupplier: [payload: { name: string; contact_name?: string | null; phone?: string | null }]
}>()

const form = reactive({
  categoryName: '',
  categoryDescription: '',
  brandName: '',
  supplierName: '',
  supplierContact: '',
  supplierPhone: '',
})

function submitCategory(): void {
  if (!form.categoryName.trim()) return
  emit('createCategory', {
    name: form.categoryName.trim(),
    description: form.categoryDescription.trim() || null,
  })
  form.categoryName = ''
  form.categoryDescription = ''
}

function submitBrand(): void {
  if (!form.brandName.trim()) return
  emit('createBrand', { name: form.brandName.trim() })
  form.brandName = ''
}

function submitSupplier(): void {
  if (!form.supplierName.trim()) return
  emit('createSupplier', {
    name: form.supplierName.trim(),
    contact_name: form.supplierContact.trim() || null,
    phone: form.supplierPhone.trim() || null,
  })
  form.supplierName = ''
  form.supplierContact = ''
  form.supplierPhone = ''
}
</script>

<template>
  <section class="panel">
    <div class="panel-header">
      <div>
        <h3>Catalogos</h3>
        <p class="panel-subtitle">Registra bases para clasificar productos y compras.</p>
      </div>
    </div>
    <div class="catalog-grid">
      <form class="catalog-card form-grid" @submit.prevent="submitCategory">
        <header class="catalog-card-header">
          <div>
            <h4>Categoria</h4>
            <p>Ej: Cabello, Coloracion, Higiene.</p>
          </div>
        </header>
        <label>
          Nombre
          <input v-model="form.categoryName" placeholder="Nombre de categoria" />
        </label>
        <label>
          Descripcion
          <input v-model="form.categoryDescription" placeholder="Breve descripcion (opcional)" />
        </label>
        <div class="form-actions">
          <button class="btn-primary" type="submit">Agregar categoria</button>
        </div>
      </form>
      <form class="catalog-card form-grid" @submit.prevent="submitBrand">
        <header class="catalog-card-header">
          <div>
            <h4>Marca</h4>
            <p>Ej: Wella, Kerastase, Loreal.</p>
          </div>
        </header>
        <label>
          Nombre
          <input v-model="form.brandName" placeholder="Nombre de marca" />
        </label>
        <div class="form-actions">
          <button class="btn-primary" type="submit">Agregar marca</button>
        </div>
      </form>
      <form class="catalog-card form-grid" @submit.prevent="submitSupplier">
        <header class="catalog-card-header">
          <div>
            <h4>Proveedor</h4>
            <p>Tu fuente de compras y reabastecimiento.</p>
          </div>
        </header>
        <label>
          Nombre
          <input v-model="form.supplierName" placeholder="Nombre de proveedor" />
        </label>
        <label>
          Contacto
          <input v-model="form.supplierContact" placeholder="Persona de contacto" />
        </label>
        <label>
          Telefono
          <input v-model="form.supplierPhone" placeholder="Numero de contacto" />
        </label>
        <div class="form-actions">
          <button class="btn-primary" type="submit">Agregar proveedor</button>
        </div>
      </form>
    </div>
  </section>
</template>

<style scoped>
.catalog-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.catalog-card {
  display: grid;
  gap: 14px;
  padding: 18px;
  border-radius: var(--radius-lg);
  background: var(--panel-bg);
  border: 1px solid var(--panel-border);
  box-shadow: var(--shadow-soft);
}

.catalog-card-header {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.catalog-card-header h4 {
  margin: 0;
  font-size: 1rem;
}

.catalog-card-header p {
  margin: 4px 0 0;
  font-size: 0.78rem;
  color: var(--ink-muted);
}

.catalog-card .form-actions {
  justify-self: start;
}

.catalog-card .btn-primary {
  width: fit-content;
  padding: 10px 14px;
  font-size: 0.85rem;
  line-height: 1.1;
  box-shadow: 0 10px 22px rgba(27, 15, 42, 0.18);
}

</style>
