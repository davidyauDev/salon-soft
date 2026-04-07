import Swal from 'sweetalert2'

interface ProductSavedNoticeOptions {
  name: string
  mode: 'created' | 'updated'
  category?: string | null
  brand?: string | null
  stock?: number | null
  note?: string | null
}

interface CategoryNoticeOptions {
  name: string
  mode: 'created' | 'updated' | 'deleted'
}

function escapeHtml(value: string): string {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

export function notifySuccess(title: string, text?: string): void {
  void Swal.fire({
    icon: 'success',
    title,
    text,
    iconColor: 'var(--accent)',
    background: 'var(--panel-bg)',
    color: 'var(--ink-strong)',
    customClass: {
      popup: 'swal-card',
      title: 'swal-title',
      htmlContainer: 'swal-text',
    },
    showConfirmButton: false,
    timer: 1800,
    timerProgressBar: true,
    backdrop: 'rgba(24, 20, 31, 0.28)',
    width: 460,
    showClass: { popup: 'swal-show' },
    hideClass: { popup: 'swal-hide' },
  })
}

export function notifyProductSaved(options: ProductSavedNoticeOptions): void {
  const title = options.mode === 'created' ? 'Producto creado' : 'Producto actualizado'
  const badge = options.mode === 'created' ? 'Listo para vender' : 'Cambios aplicados'
  const stockLabel =
    options.mode === 'created'
      ? options.stock && options.stock > 0
        ? `Stock inicial: ${options.stock} und`
        : 'Sin stock inicial'
      : 'Inventario sincronizado'

  const chips = [
    options.category ? `Categoria: ${escapeHtml(options.category)}` : null,
    options.brand ? `Marca: ${escapeHtml(options.brand)}` : null,
    stockLabel,
  ].filter(Boolean)

  const html = `
    <div class="swal-product-body">
      <span class="swal-product-badge">${badge}</span>
      <p class="swal-product-message">
        <strong>${escapeHtml(options.name)}</strong> se guardo correctamente en tu inventario.
      </p>
      ${options.note ? `<p class="swal-product-note">${escapeHtml(options.note)}</p>` : ''}
      <div class="swal-product-chips">
        ${chips.map((chip) => `<span class="swal-product-chip">${chip}</span>`).join('')}
      </div>
    </div>
  `

  void Swal.fire({
    icon: 'success',
    title,
    html,
    iconColor: 'var(--accent)',
    background: 'var(--panel-bg)',
    color: 'var(--ink-strong)',
    customClass: {
      popup: 'swal-card swal-product-popup',
      title: 'swal-title swal-product-title',
      htmlContainer: 'swal-text swal-product-text',
    },
    showConfirmButton: false,
    timer: 2400,
    timerProgressBar: true,
    backdrop: 'rgba(24, 20, 31, 0.3)',
    width: 520,
    showClass: { popup: 'swal-show' },
    hideClass: { popup: 'swal-hide' },
  })
}

export function notifyCategoryChanged(options: CategoryNoticeOptions): void {
  const titleMap = {
    created: 'Categoria creada',
    updated: 'Categoria actualizada',
    deleted: 'Categoria eliminada',
  } as const

  const badgeMap = {
    created: 'Lista para usar',
    updated: 'Cambios aplicados',
    deleted: 'Productos liberados',
  } as const

  const messageMap = {
    created: 'Ya puedes usarla al registrar productos.',
    updated: 'La lista se actualizo inmediatamente.',
    deleted: 'Los productos vinculados quedaron como "Sin categoria".',
  } as const

  const html = `
    <div class="swal-product-body">
      <span class="swal-product-badge">${badgeMap[options.mode]}</span>
      <p class="swal-product-message">
        <strong>${escapeHtml(options.name)}</strong>. ${messageMap[options.mode]}
      </p>
    </div>
  `

  void Swal.fire({
    icon: 'success',
    title: titleMap[options.mode],
    html,
    iconColor: 'var(--accent)',
    background: 'var(--panel-bg)',
    color: 'var(--ink-strong)',
    customClass: {
      popup: 'swal-card swal-product-popup',
      title: 'swal-title swal-product-title',
      htmlContainer: 'swal-text swal-product-text',
    },
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true,
    backdrop: 'rgba(24, 20, 31, 0.3)',
    width: 500,
    showClass: { popup: 'swal-show' },
    hideClass: { popup: 'swal-hide' },
  })
}

export function notifyError(title: string, text?: string): void {
  void Swal.fire({
    icon: 'error',
    title,
    text,
    iconColor: '#ef4444',
    background: 'var(--panel-bg)',
    color: 'var(--ink-strong)',
    customClass: {
      popup: 'swal-card',
      title: 'swal-title',
      htmlContainer: 'swal-text',
    },
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true,
    backdrop: 'rgba(24, 20, 31, 0.28)',
    width: 460,
    showClass: { popup: 'swal-show' },
    hideClass: { popup: 'swal-hide' },
  })
}
