<!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="d-flex flex-column h-100">
            <!-- Logo -->
            <div class="sidebar-logo d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded p-2 me-2">
                        <i class="fas fa-bolt text-primary"></i>
                    </div>
                    <h4 class="mb-0 fw-bold">GestionStock</h4>
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
                            <span>Tableau de bord</span>
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
                            <span>Gestion Ventes</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('ventes*') ? 'show' : '' }}" id="ventesMenu">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item mt-2">
                                    <a href="/ventes/ventes" class="nav-link {{ request()->is('ventes/ventes') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Ventes</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/ventes/rapports" class="nav-link {{ request()->is('ventes/rapports') ? 'active' : '' }}">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Rapports</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/ventes/historique" class="nav-link {{ request()->is('ventes/historique') ? 'active' : '' }}">
                                        <i class="fas fa-history"></i>
                                        <span>Historique</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Articles -->
                    <li class="nav-item">
                        <a href="/articles" class="nav-link {{ request()->is('articles') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Articles</span>
                        </a>
                    </li>

                    <!-- Stock (Dropdown) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('stock*') ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#stockMenu"
                           role="button"
                           aria-expanded="false">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Stock</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('commandes*') ? 'show' : '' }}" id="stockMenu">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item mt-2">
                                    <a href="/stock/commandes" class="nav-link {{ request()->is('stock/commandes') ? 'active' : '' }}">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span>Commandes</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stock/approvisions" class="nav-link {{ request()->is('stock/approvisions') ? 'active' : '' }}">
                                        <i class="fa-solid fa-truck-arrow-right"></i>
                                        <span>Approvisions</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stock/approvisions/paiements" class="nav-link {{ request()->is('stock/approvisions/paiements') ? 'active' : '' }}">
                                        <i class="fa-regular fa-money-bill-1"></i>
                                        <span>Paiments</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Clients -->
                    <li class="nav-item">
                        <a href="/clients" class="nav-link {{ request()->is('clients') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-group"></i>
                            <span>Clients</span>
                        </a>
                    </li>

                    <!-- Fournisseurs -->
                    <li class="nav-item">
                        <a href="/fournisseurs" class="nav-link {{ request()->is('fournisseurs') ? 'active' : '' }}">
                            <i class="fa-solid fa-truck-field"></i>
                            <span>Fournisseurs</span>
                        </a>
                    </li>

                    <!-- Warehouse (Dropdown) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('warehouse*') ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#warehouse"
                           role="button"
                           aria-expanded="false">
                            <i class="fa-solid fa-warehouse"></i>
                            <span>Gestion Entrépôts</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('warehouse*') ? 'show' : '' }}" id="warehouse">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item mt-2">
                                    <a href="{{ route('warehouse.magasins') }}" class="nav-link {{ request()->is('warehouse/magasins') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Magasins</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('warehouse.etageres') }}" class="nav-link {{ request()->is('warehouse/etageres') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Etagères</span>
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
                                    <a href="{{ route('configuration.devises') }}" class="nav-link {{ request()->is('configuration/devises') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Devise</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Configuration (Dropdown) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#settings"
                           role="button"
                           aria-expanded="false">
                            <i class="fa-solid fa-gear"></i>
                            <span>Paramètres</span>
                            <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="collapse {{ request()->is('settings*') ? 'show' : '' }}" id="settings">
                            <ul class="nav flex-column dropdown-menu-custom">
                                <li class="nav-item mt-2">
                                    <a href="{{ route('settings.register') }}" class="nav-link {{ request()->is('settings/register') ? 'active' : '' }}">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Ajouter utilisateur</span>
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a href="{{ route('settings.profile') }}" class="nav-link {{ request()->is('settings/profile') ? 'active' : '' }}">
                                        <i class="fas fa-user"></i>
                                        <span>Profil</span>
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
                    <img src="{{ Auth::user()->image ? asset(Auth::user()->image) : asset('images/avatar.png') }}"
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
