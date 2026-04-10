<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="d-flex flex-column h-100">
        @php
            $company = \App\Models\CompanySetting::query()->first();
            $brandName = $company?->short_name ?: ($company?->name ?: config('app.name'));
            $brandName = \Illuminate\Support\Str::limit($brandName, 14);
            $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
            $userHasAccess = auth()->user()->isSuperAdmin() || auth()->user()->hasAnyAccess();
        @endphp

        <!-- Logo -->
        <div class="sidebar-logo d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @if ($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="Logo" class="sidebar-brand-logo me-2">
                @else
                    <div class="sidebar-brand-icon me-2">
                        <i class="fas fa-bolt"></i>
                    </div>
                @endif
                <h4 class="mb-0 fw-bold sidebar-brand-name">{{ $brandName }}</h4>
            </div>
            <button class="btn btn-link text-white d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav flex-grow-1">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                @if($userHasAccess)
                    @access('dashboard')
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-th-large"></i>
                                <span>Tableau de bord</span>
                            </a>
                        </li>
                    @endaccess
                @endif

                <!-- Ventes (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showVentesMenu =
                            auth()->user()->canAccess('ventes.ventes') ||
                            auth()->user()->canAccess('ventes.rapports');
                    @endphp
                    @if($showVentesMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('ventes*') ? 'active' : '' }}" data-bs-toggle="collapse"
                                href="#ventesMenu" role="button" aria-expanded="false">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Ges. Ventes</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('ventes*') ? 'show' : '' }}" id="ventesMenu">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('ventes.ventes')
                                        <li class="nav-item mt-2">
                                            <a href="/ventes/ventes"
                                                class="nav-link {{ request()->is('ventes/ventes') ? 'active' : '' }}">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Ventes</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="/ventes/historique"
                                                class="nav-link {{ request()->is('ventes/historique') ? 'active' : '' }}">
                                                <i class="fas fa-history"></i>
                                                <span>Historique</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('ventes.rapports')
                                        <li class="nav-item">
                                            <a href="/ventes/rapports"
                                                class="nav-link {{ request()->is('ventes/rapports') ? 'active' : '' }}">
                                                <i class="fas fa-chart-line"></i>
                                                <span>Rapports</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Articles -->
                @if($userHasAccess)
                    @access('articles')
                        <li class="nav-item">
                            <a href="/articles" class="nav-link {{ request()->is('articles') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Articles</span>
                            </a>
                        </li>
                    @endaccess
                @endif

                <!-- Stock (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showStockMenu =
                            auth()->user()->canAccess('stock.commandes') ||
                            auth()->user()->canAccess('stock.approvisions') ||
                            auth()->user()->canAccess('stock.paiements');
                    @endphp
                    @if($showStockMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('stock*') ? 'active' : '' }}" data-bs-toggle="collapse"
                                href="#stockMenu" role="button" aria-expanded="false">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Stock</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('stock*') ? 'show' : '' }}" id="stockMenu">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('stock.commandes')
                                        <li class="nav-item mt-2">
                                            <a href="/stock/commandes"
                                                class="nav-link {{ request()->is('stock/commandes') ? 'active' : '' }}">
                                                <i class="fa-solid fa-cart-plus"></i>
                                                <span>Commandes</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('stock.approvisions')
                                        <li class="nav-item">
                                            <a href="/stock/approvisions"
                                                class="nav-link {{ request()->is('stock/approvisions') ? 'active' : '' }}">
                                                <i class="fa-solid fa-truck-arrow-right"></i>
                                                <span>Approvisions</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('stock.paiements')
                                        <li class="nav-item">
                                            <a href="/stock/approvisions/paiements"
                                                class="nav-link {{ request()->is('stock/approvisions/paiements') ? 'active' : '' }}">
                                                <i class="fa-regular fa-money-bill-1"></i>
                                                <span>Paiements</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Clients -->
                @if($userHasAccess)
                    @access('clients')
                        <li class="nav-item">
                            <a href="/clients" class="nav-link {{ request()->is('clients') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-group"></i>
                                <span>Clients</span>
                            </a>
                        </li>
                    @endaccess
                @endif

                <!-- Fournisseurs -->
                @if($userHasAccess)
                    @access('fournisseurs')
                        <li class="nav-item">
                            <a href="/fournisseurs" class="nav-link {{ request()->is('fournisseurs') ? 'active' : '' }}">
                                <i class="fa-solid fa-truck-field"></i>
                                <span>Fournisseurs</span>
                            </a>
                        </li>
                    @endaccess
                @endif

                <!-- Warehouse (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showWarehouseMenu =
                            auth()->user()->canAccess('warehouse.magasins') ||
                            auth()->user()->canAccess('warehouse.etageres');
                    @endphp
                    @if($showWarehouseMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse*') ? 'active' : '' }}" data-bs-toggle="collapse"
                                href="#warehouse" role="button" aria-expanded="false">
                                <i class="fa-solid fa-warehouse"></i>
                                <span>Ges. Entrépôts</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('warehouse*') ? 'show' : '' }}" id="warehouse">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('warehouse.magasins')
                                        <li class="nav-item mt-2">
                                            <a href="{{ route('warehouse.magasins') }}"
                                                class="nav-link {{ request()->is('warehouse/magasins') ? 'active' : '' }}">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Magasins</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('warehouse.etageres')
                                        <li class="nav-item">
                                            <a href="{{ route('warehouse.etageres') }}"
                                                class="nav-link {{ request()->is('warehouse/etageres') ? 'active' : '' }}">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Etagères</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Configuration (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showConfigurationMenu =
                            auth()->user()->canAccess('configuration.categories') ||
                            auth()->user()->canAccess('configuration.devises') ||
                            auth()->user()->canAccess('configuration.settings');
                    @endphp
                    @if($showConfigurationMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('configuration*') ? 'active' : '' }}"
                                data-bs-toggle="collapse" href="#configuration" role="button" aria-expanded="false">
                                <i class="fa-solid fa-gears"></i>
                                <span>Configuration</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('configuration*') ? 'show' : '' }}" id="configuration">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('configuration.categories')
                                        <li class="nav-item mt-2">
                                            <a href="{{ route('configuration.categories') }}"
                                                class="nav-link {{ request()->is('configuration/categories') ? 'active' : '' }}">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Catégorie d'articles</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('configuration.devises')
                                        <li class="nav-item">
                                            <a href="{{ route('configuration.devises') }}"
                                                class="nav-link {{ request()->is('configuration/devises') ? 'active' : '' }}">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Devise</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('configuration.settings')
                                        <li class="nav-item">
                                            <a href="{{ route('configuration.settings') }}"
                                                class="nav-link {{ request()->is('configuration/settings') ? 'active' : '' }}">
                                                <i class="fas fa-building"></i>
                                                <span>Entreprise</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Comptabilité (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showComptabiliteMenu =
                            auth()->user()->canAccess('comptabilite.types-operations') ||
                            auth()->user()->canAccess('comptabilite.operations');
                    @endphp
                    @if($showComptabiliteMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('comptabilite*') ? 'active' : '' }}"
                                data-bs-toggle="collapse" href="#comptabilite" role="button" aria-expanded="false">
                                <i class="fa-solid fa-calculator"></i>
                                <span>Comptabilité</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('comptabilite*') ? 'show' : '' }}" id="comptabilite">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('comptabilite.types-operations')
                                        <li class="nav-item mt-2">
                                            <a href="{{ route('comptabilite.types-operations') }}"
                                                class="nav-link {{ request()->is('comptabilite/types-operations') ? 'active' : '' }}">
                                                <i class="fas fa-list"></i>
                                                <span>Types d'opérations</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('comptabilite.operations')
                                        <li class="nav-item">
                                            <a href="{{ route('comptabilite.operations') }}"
                                                class="nav-link {{ request()->is('comptabilite/operations') ? 'active' : '' }}">
                                                <i class="fas fa-receipt"></i>
                                                <span>Opérations</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Audit (Dropdown) -->
                @if($userHasAccess)
                    @php
                        $showAuditMenu =
                            auth()->user()->canAccess('audit.stock-article') ||
                            auth()->user()->canAccess('audit.activity');
                    @endphp
                    @if($showAuditMenu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('audit*') ? 'active' : '' }}" data-bs-toggle="collapse"
                                href="#audit" role="button" aria-expanded="false">
                                <i class="fas fa-shield-alt"></i>
                                <span>Audit</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <div class="collapse {{ request()->is('audit*') ? 'show' : '' }}" id="audit">
                                <ul class="nav flex-column dropdown-menu-custom">
                                    @access('audit.stock-article')
                                        <li class="nav-item mt-2">
                                            <a href="{{ route('audit.stock-article') }}"
                                                class="nav-link {{ request()->is('audit/stock-article') ? 'active' : '' }}">
                                                <i class="fas fa-boxes"></i>
                                                <span>Stock article</span>
                                            </a>
                                        </li>
                                    @endaccess
                                    @access('audit.activity')
                                        <li class="nav-item">
                                            <a href="{{ route('audit.activity') }}"
                                                class="nav-link {{ request()->is('audit/activity') ? 'active' : '' }}">
                                                <i class="fas fa-clipboard-list"></i>
                                                <span>Activités</span>
                                            </a>
                                        </li>
                                    @endaccess
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                <!-- Configuration (Dropdown) -->
                <li class="nav-item">
                    <a class="nav-link mb-2 {{ request()->is('settings*') ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#settings" role="button" aria-expanded="false">
                        <i class="fa-solid fa-gear"></i>
                        <span>Paramètres</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </a>
                    <div class="collapse {{ request()->is('settings*') ? 'show' : '' }}" id="settings">
                        <ul class="nav flex-column dropdown-menu-custom">
                            @if($userHasAccess)
                                @access('settings.users')
                                    <li class="nav-item">
                                        <a href="{{ route('settings.users') }}"
                                            class="nav-link {{ request()->is('settings/users') ? 'active' : '' }}">
                                            <i class="fas fa-user-plus"></i>
                                            <span>Les Utilisateurs</span>
                                        </a>
                                    </li>
                                @endaccess
                            @endif
                            <li class="nav-item">
                                <a href="{{ route('settings.profile') }}"
                                    class="nav-link {{ request()->is('settings/profile') ? 'active' : '' }}">
                                    <i class="fas fa-user"></i>
                                    <span>Mon Profil</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- User Info -->
        <div class="p-3 border-top border-white border-opacity-10 sidebar-user-footer">
            <a href="{{ route('settings.profile') }}" class="sidebar-user-card">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ Auth::user()->image ? asset(Auth::user()->image) : asset('images/avatar.png') }}"
                        class="sidebar-user-avatar" alt="Avatar">
                    <div class="flex-grow-1 min-w-0">
                        <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                        <div class="sidebar-user-meta">
                            @if (Auth::user()->email)
                                <i class="fas fa-envelope me-1"></i>{{ Auth::user()->email }}
                            @else
                                <i class="fas fa-phone me-1"></i>{{ Auth::user()->telephone }}
                            @endif
                        </div>
                    </div>
                    <i class="fas fa-chevron-right sidebar-user-chevron"></i>
                </div>
            </a>
        </div>
    </div>
</aside>
