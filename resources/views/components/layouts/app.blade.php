<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <link rel="stylesheet" href="{{ asset('css/sweetalert-custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body>

    <!-- App Loader -->
    {{-- <div id="app-loader" class="loader-visible">
        <div class="app-loader-content">
            <div class="app-loader-logo">
                <i class="fas fa-cube pulse"></i>
            </div>
            <div class="app-loader-spinner"></div>
            <div class="app-loader-text" id="loader-text">Initialisation...</div>
            <div class="app-loader-progress">
                <div class="app-loader-progress-bar" id="loader-progress"></div>
            </div>
        </div>
    </div> --}}

    @include('partials.sidebar')
    @include('partials.sidebar-overlay')

    <div class="main-content" id="mainContent">
        @include('partials.header')
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>

    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @livewireScripts
    <script src="{{ asset('js/sweetalert-custom.js') }}"></script>
    <script src="{{ asset('js/print.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    {{-- <script src="{{ asset('js/loader.js') }}"></script> --}}

    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                // Create a new Alert instance
                const alertInstance = new bootstrap.Alert(alert);
                // Trigger the close method
                alertInstance.close();
            });
        }, 5000);
    </script>
</body>
</html>
