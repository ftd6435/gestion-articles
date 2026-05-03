<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Initial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            .table thead th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @include('print.stock-initial-details', [
                    'stocks' => $stocks,
                    'filters' => $filters,
                    'showHeader' => true,
                ])
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3 no-print">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>
</body>

</html>
