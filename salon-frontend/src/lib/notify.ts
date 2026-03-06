import Swal from 'sweetalert2'

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
    showClass: { popup: 'swal-show' },
    hideClass: { popup: 'swal-hide' },
  })
}
