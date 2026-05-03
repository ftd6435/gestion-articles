@php
    $company = \App\Models\CompanySetting::query()->first();
@endphp

<!-- Header -->
<header class="header">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center g-0">
            <!-- Left: Menu Button + Breadcrumb -->
            <div class="col-6 col-md-6 d-flex align-items-center">
                <button class="btn btn-link text-dark p-2 me-2 me-md-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars fs-5"></i>
                </button>

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Tableau de bord</a></li>
                        @if (isset($breadcrumb))
                            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb }}</li>
                        @endif
                    </ol>
                </nav>
            </div>

            <!-- Company Info (Print Only) -->
            <div class="company-info-print d-none">
                @if ($company?->logo_path)
                    <img src="{{ asset($company->logo_path) }}" alt="Logo" class="company-logo-print">
                @endif
                <div class="company-details-print">
                    <h5 class="company-name-print">{{ $company?->name ?? config('app.name') }}</h5>
                    @if ($company?->slogan)
                        <p class="company-slogan-print">{{ $company->slogan }}</p>
                    @endif
                </div>
            </div>

            <!-- Right: Search + Profile -->
            <div class="col-6 col-md-6 d-flex align-items-center justify-content-end">
                <!-- Barre de recherche Livewire -->
                <div class="me-3 d-none d-md-block">
                    <livewire:global-search />
                </div>

                <!-- Version mobile de la recherche -->
                <div class="me-2 d-md-none">
                    <button class="btn btn-link text-dark p-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileSearch" aria-expanded="false">
                        <i class="fas fa-search fs-5"></i>
                    </button>
                </div>
                <!-- Notifications -->
                <livewire:header-notifications />


                <!-- Barre de recherche mobile (collapse) -->
                <div class="row collapse" id="mobileSearch">
                    <div class="col-12 py-2">
                        <div class="px-2">
                            <livewire:global-search />
                        </div>
                    </div>
                </div>
                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center p-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->image ? asset(Auth::user()->image) : asset('images/avatar.png') }}"
                            class="profile-img me-0 me-md-2" alt="Profile">
                        <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('settings.profile') }}"><i
                                    class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings.profile') }}"><i
                                    class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="{{ route('logout') }}"><i
                                    class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

@push('styles')
    <style>
        .header .breadcrumb a,
        .header .breadcrumb-item.active,
        .header .dropdown-toggle,
        .header .dropdown-toggle span {
            color: #fff !important;
        }

        .header .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.6);
        }

        .company-info-print {
            display: none !important;
        }

        #mobileSearch {
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            background: white;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ff4757;
            color: white;
            font-size: 0.6rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }

        .dropdown-toggle::after {
            display: none;
        }

        @media print {
            .company-info-print {
                display: flex !important;
                align-items: center;
                padding: 20px 0;
                border-bottom: 2px solid #0b1f4b;
                margin-bottom: 20px;
            }

            .company-logo-print {
                height: 60px;
                width: auto;
                max-width: 200px;
                object-fit: contain;
                margin-right: 20px;
            }

            .company-name-print {
                font-size: 1.5rem;
                font-weight: bold;
                color: #0b1f4b !important;
                margin: 0;
            }

            .company-slogan-print {
                font-size: 0.9rem;
                color: #666 !important;
                margin: 5px 0 0 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
            }
        }

        // Close mobile search when clicking outside
        document.addEventListener('click', function(e) {
            const mobileSearch = document.getElementById('mobileSearch');
            const searchToggle = document.querySelector('[data-bs-target="#mobileSearch"]');

            if (mobileSearch && mobileSearch.classList.contains('show')) {
                if (!mobileSearch.contains(e.target) && searchToggle && !searchToggle.contains(e.target)) {
                    const bsCollapse = new bootstrap.Collapse(mobileSearch);
                    bsCollapse.hide();
                }
            }
        });
    </script>
@endpush
