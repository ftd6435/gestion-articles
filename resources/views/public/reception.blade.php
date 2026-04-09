<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réception {{ $selectedReception->reference }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @include('print.reception-details', ['selectedReception' => $selectedReception, 'showHeader' => true, 'modesPaiement' => $modesPaiement ?? []])
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>
</body>
</html>
