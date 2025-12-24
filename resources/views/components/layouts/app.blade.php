<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <link rel="stylesheet" href="{{ asset('css/sweetalert-custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body>
    @include('partials.sidebar')
    @include('partials.sidebar-overlay')

    <div class="main-content" id="mainContent">
        @include('partials.header')
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts
    <script src="{{ asset('js/sweetalert-custom.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        function printPaiement() {
            const content = document.getElementById('paiementDetailsContent');

            if (!content) {
                alert('Contenu à imprimer introuvable');
                return;
            }

            const printWindow = window.open('', '', 'height=900,width=700');

            printWindow.document.write(`
                <html>
                <head>
                    <title>Reçu de paiement</title>
                    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                        }

                        .modal-header,
                        .modal-footer,
                        .btn {
                            display: none !important;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                    </style>
                </head>
                <body>
                    ${content.innerHTML}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    </script>
</body>
</html>
