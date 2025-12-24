<div>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Clients</h1>
            <p class="text-muted mb-0">Gérez vos clients et leurs informations</p>
        </div>

        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>
            Nouveau client
        </button>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">

        <!-- Card Header with Tabs -->
        <div class="card-header bg-primary text-white border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <!-- Tabs -->
                <ul class="nav nav-pills supplier-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-table"
                                type="button">
                            <i class="fa fa-table me-2"></i> Tableau
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-cards"
                                type="button">
                            <i class="fa fa-id-card me-2"></i> Cartes
                        </button>
                    </li>
                </ul>

                <span class="badge bg-white text-primary px-3 py-2 fw-semibold">
                    {{ count($clients) }} client(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <div class="tab-content">

                <!-- ================= TABLE TAB ================= -->
                <div class="tab-pane fade show active" id="tab-table">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60"></th>
                                    <th>Nom</th>
                                    <th>Téléphone</th>
                                    <th>Type</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($clients as $client)
                                    <tr>

                                        <!-- Avatar -->
                                        <td>
                                            <div class="supplier-avatar">
                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                            </div>
                                        </td>

                                        <td class="fw-semibold">{{ $client->name }}</td>

                                        <td class="text-muted">{{ $client->telephone }}</td>

                                        <td>
                                            @php
                                                $typeClasses = match($client->type) {
                                                    'GROSSISTE'  => 'bg-primary',
                                                    'DETAILLANT' => 'bg-success',
                                                    'MIXTE'      => 'bg-warning',
                                                    default      => 'bg-secondary',
                                                };
                                            @endphp

                                            <span class="badge {{ $typeClasses }}">
                                                {{ ucfirst(strtolower($client->type)) }}
                                            </span>
                                        </td>

                                        <td class="text-muted">
                                            {{ $client->email ?? '—' }}
                                        </td>

                                        <td>
                                            <span class="badge {{ $client->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $client->status ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button wire:click="view({{ $client->id }})"
                                                        class="btn btn-outline-info">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <button wire:click="edit({{ $client->id }})"
                                                        class="btn btn-outline-primary">
                                                    <i class="fa fa-pen"></i>
                                                </button>

                                                <button wire:click="toggleStatus({{ $client->id }})"
                                                        class="btn btn-outline-{{ $client->status ? 'success' : 'secondary' }}">
                                                    <i class="fa fa-toggle-{{ $client->status ? 'on' : 'off' }}"></i>
                                                </button>

                                                <button wire:click="deleteConfirm({{ $client->id }})"
                                                        class="btn btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fa fa-users fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">Aucun client trouvé</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- ================= CARD TAB ================= -->
                <div class="tab-pane fade" id="tab-cards">

                    <div class="row g-3 p-3">
                        @forelse($clients as $client)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="supplier-avatar me-3">
                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                            </div>

                                            <div>
                                                <h6 class="fw-semibold mb-0">{{ $client->name }}</h6>
                                                <small class="text-muted">{{ $client->telephone }}</small>
                                            </div>
                                        </div>

                                        <p class="text-muted small mb-1">
                                            @php
                                                $typeClasses = match($client->type) {
                                                    'GROSSISTE'  => 'bg-primary',
                                                    'DETAILLANT' => 'bg-success',
                                                    'MIXTE'      => 'bg-warning',
                                                    default      => 'bg-secondary',
                                                };
                                            @endphp

                                            Type :
                                            <span class="badge {{ $typeClasses }}">
                                                {{ ucfirst(strtolower($client->type)) }}
                                            </span>
                                        </p>

                                        <p class="text-muted small mb-2">
                                            {{ $client->email ?? 'Aucun email' }}
                                        </p>

                                        <span class="badge {{ $client->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $client->status ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>

                                    <div class="card-footer bg-white d-flex gap-2">
                                        <button wire:click="view({{ $client->id }})"
                                                class="btn btn-sm btn-outline-info flex-fill">
                                            <i class="fa fa-eye me-1"></i> Détails
                                        </button>

                                        <button wire:click="edit({{ $client->id }})"
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $client->id }})"
                                                class="btn btn-outline-{{ $client->status ? 'success' : 'secondary' }}">
                                            <i class="fa fa-toggle-{{ $client->status ? 'on' : 'off' }}"></i>
                                        </button>

                                        <button wire:click="deleteConfirm({{ $client->id }})"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fa fa-users fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Aucun client trouvé</p>
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>
    </div>

    @if ($showModal)
        @include('livewire.client-modal')
    @endif
</div>
