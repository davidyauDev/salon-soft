import Swal from 'sweetalert2'

export async function confirmDelete(
  message = 'Eliminar este registro? Esta accion no se puede deshacer.',
  confirmText = 'Eliminar'
): Promise<boolean> {
  const result = await Swal.fire({
    title: 'Confirmar eliminacion',
    text: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: 'Cancelar',
    background: 'var(--panel-bg)',
    color: 'var(--ink-strong)',
    customClass: {
      popup: 'swal-card',
      title: 'swal-title',
      htmlContainer: 'swal-text',
      confirmButton: 'swal-confirm',
      cancelButton: 'swal-cancel',
    },
    buttonsStyling: false,
  })

  return result.isConfirmed
}
