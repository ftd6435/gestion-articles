/**
 * ============================================
 * SWEETALERT2 + LIVEWIRE INTEGRATION
 * ============================================
 * Ajoutez ce code dans public/js/app.js
 * OU directement dans votre layout avant </body>
 */

// Fonction globale pour confirmer avec SweetAlert2
window.confirmDelete = function(id, componentName, itemName = 'cet élément') {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: `Voulez-vous vraiment supprimer ${itemName} ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Dispatch l'événement de suppression au composant Livewire
            Livewire.dispatch('confirmDelete', { id: id });

            // Afficher un message de chargement
            Swal.fire({
                title: 'Suppression en cours...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });
};

// Écouter les événements Livewire pour fermer le loading et afficher le résultat
document.addEventListener('livewire:initialized', () => {

    // Événement de confirmation de suppression
    Livewire.on('confirm-delete', (event) => {
        const id = event.id;
        const itemName = event.itemName || 'cet élément';

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Voulez-vous vraiment supprimer ${itemName} ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Confirmer la suppression
                Livewire.dispatch('confirmDelete', { id: id });

                // Afficher loading
                Swal.fire({
                    title: 'Suppression en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        });
    });

    // Événement de succès
    Livewire.on('delete-success', (event) => {
        Swal.fire({
            title: 'Supprimé !',
            text: event.message || 'L\'élément a été supprimé avec succès.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            timer: 2000,
            timerProgressBar: true
        });
    });

    // Événement d'erreur
    Livewire.on('delete-error', (event) => {
        Swal.fire({
            title: 'Erreur !',
            text: event.message || 'Une erreur est survenue lors de la suppression.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    });

    // Événement de succès général
    Livewire.on('success', (event) => {
        Swal.fire({
            title: 'Succès !',
            text: event.message,
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            timer: 2000,
            timerProgressBar: true
        });
    });

    // Événement d'erreur général
    Livewire.on('error', (event) => {
        Swal.fire({
            title: 'Erreur !',
            text: event.message,
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    });
});
