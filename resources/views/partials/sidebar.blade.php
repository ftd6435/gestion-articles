<!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="d-flex flex-column h-100">
            <!-- Logo -->
            <div class="sidebar-logo d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded p-2 me-2">
                        <i class="fas fa-bolt text-primary"></i>
                    </div>
                    <h4 class="mb-0 fw-bold">AdminPro</h4>
                </div>
                <button class="btn btn-link text-white d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav flex-grow-1">
                <ul class="nav flex-column">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Articles -->
                    <li class="nav-item">
                        <a href="/articles" class="nav-link {{ request()->is('articles') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Articles</span>
                        </a>
                    </li>

                    <!-- Ventes (Dropdown) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('ventes*') ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#ventesMenu"
                           role="button"
                           aria-expanded="false">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Ventes</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('ventes*') ? 'show' : '' }}" id="ventesMenu">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item">
                                    <a href="/ventes/jour" class="nav-link {{ request()->is('ventes/jour') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Ventes du jour</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/ventes/historique" class="nav-link {{ request()->is('ventes/historique') ? 'active' : '' }}">
                                        <i class="fas fa-history"></i>
                                        <span>Historique des ventes</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/ventes/rapports" class="nav-link {{ request()->is('ventes/rapports') ? 'active' : '' }}">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Rapports</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Configuration (Dropdown) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('configuration*') ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#configuration"
                           role="button"
                           aria-expanded="false">
                            <i class="fa-solid fa-gears"></i>
                            <span>Configuration</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('configuration*') ? 'show' : '' }}" id="configuration">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item mt-2">
                                    <a href="{{ route('configuration.categories') }}" class="nav-link {{ request()->is('configuration/categories') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Catégorie d'articles</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link {{ request()->is('configuration/devises') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Devise</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- User Info -->
            <div class="p-3 border-top border-white border-opacity-10">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/avatar.png') }}"
                         class="rounded-circle me-2"
                         width="40"
                         height="40"
                         alt="Avatar">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ Auth::user()->name }}</div>
                        <div class="small text-white-50"> {{ Auth::user()->email ? 'Email: ' : 'Tel: ' }} {{ Auth::user()->email ?? Auth::user()->telephone }}</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
