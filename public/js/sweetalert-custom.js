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

    // ============================================
    // ÉVÉNEMENTS POUR TOGGLE DEFAULT (DEVISE PAR DÉFAUT)
    // ============================================
    Livewire.on('confirm-toggle-default', (event) => {
        const id = event.id;
        const itemName = event.itemName || 'cet élément';
        const action = event.action || 'définir comme devise par défaut';
        const isCurrentDefault = event.isCurrentDefault || false;

        let htmlContent = `<div class="text-start">
            <p>Voulez-vous vraiment <strong>${action}</strong> la devise <strong>"${itemName}"</strong> ?</p>`;

        if (isCurrentDefault) {
            htmlContent += `<div class="alert alert-info small mt-2 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Une autre devise active sera automatiquement définie comme défaut si disponible.
            </div>`;
        } else if (action.includes('définir')) {
            htmlContent += `<div class="alert alert-info small mt-2 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Cette devise deviendra la devise par défaut du système.
            </div>`;
        }

        htmlContent += '</div>';

        Swal.fire({
            title: action.includes('définir') ? 'Définir comme devise par défaut ?' : 'Retirer devise par défaut ?',
            html: htmlContent,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action.includes('définir') ? '#28a745' : '#6c757d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: action.includes('définir') ? 'Oui, définir' : 'Oui, retirer',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
            customClass: {
                confirmButton: `btn ${action.includes('définir') ? 'btn-success' : 'btn-secondary'} me-2`,
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Confirmer le changement de devise par défaut
                Livewire.dispatch('confirmToggleDefault', { id: id });

                // Afficher loading
                Swal.fire({
                    title: 'Traitement en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        });
    });

    // ============================================
    // ÉVÉNEMENTS POUR TOGGLE STATUS (ACTIVE/INACTIVE)
    // ============================================
    Livewire.on('confirm-toggle-status', (event) => {
        const id = event.id;
        const itemName = event.itemName || 'cet élément';
        const action = event.action || 'modifier';
        const warning = event.warning || '';

        let htmlContent = `<div class="text-start">
            <p>Voulez-vous vraiment <strong>${action}</strong> la devise <strong>"${itemName}"</strong> ?</p>`;

        if (warning) {
            htmlContent += `<div class="alert alert-warning small mt-2 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${warning}
            </div>`;
        }

        htmlContent += '</div>';

        Swal.fire({
            title: action === 'désactiver' ? 'Désactiver la devise ?' : 'Activer la devise ?',
            html: htmlContent,
            icon: action === 'désactiver' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'désactiver' ? '#f39c12' : '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: action === 'désactiver' ? 'Oui, désactiver' : 'Oui, activer',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
            customClass: {
                confirmButton: `btn ${action === 'désactiver' ? 'btn-warning' : 'btn-primary'} me-2`,
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Confirmer le changement de statut
                Livewire.dispatch('confirmToggleStatus', { id: id });

                // Afficher loading
                Swal.fire({
                    title: action === 'désactiver' ? 'Désactivation en cours...' : 'Activation en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
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

