/**
 * Global SweetAlert Confirm Helper
 * Usage:
 * window.confirmAction({
 *   title: 'Supprimer ?',
 *   text: 'Action irréversible',
 *   confirmText: 'Oui, supprimer',
 *   onConfirm: () => {}
 * })
 */
window.confirmAction = function ({
    title = 'Êtes-vous sûr ?',
    text = 'Cette action est irréversible',
    icon = 'warning',
    confirmText = 'Confirmer',
    cancelText = 'Annuler',
    confirmColor = '#dc3545',
    cancelColor = '#6c757d',
    onConfirm = null
}) {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: cancelColor,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        
        // 🎬 Animations & Design
        customClass: {
            popup: 'swal-popup',
            title: 'swal-title',
            confirmButton: 'swal-confirm-btn',
            cancelButton: 'swal-cancel-btn'
        },
        showClass: {
            popup: 'animate__animated animate__zoomIn animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut animate__faster'
        },

        buttonsStyling: false,
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm()
        }
    })
}
