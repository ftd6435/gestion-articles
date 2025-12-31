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
                        @if(isset($breadcrumb))
                            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb }}</li>
                        @endif
                    </ol>
                </nav>
            </div>

            <!-- Right: Search + Profile -->
            <div class="col-6 col-md-6 d-flex align-items-center justify-content-end">
                <!-- Search Bar -->
                <div class="input-group me-3 d-none d-md-flex" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher...">
                </div>

                <!-- Notifications -->
                <button class="btn btn-link text-dark position-relative p-2 me-2 me-md-3">
                    <i class="fas fa-bell fs-5"></i>
                    <span class="notification-badge">3</span>
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center p-2"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <img src="{{ asset('images/avatar.png') }}"
                             class="profile-img me-0 me-md-2"
                             alt="Profile">
                        <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
