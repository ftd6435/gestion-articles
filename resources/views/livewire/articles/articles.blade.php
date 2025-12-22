<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Gestion des Articles</h1>
            <p class="text-muted mb-0">Gerez vos articles en un clique</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i> Nouvel article
        </button>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <!-- Search -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="form-control"
                            placeholder="Référence ou désignation">
                    </div>
                </div>

                <!-- Category -->
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Catégorie</label>
                    <select wire:model.live="filterCategory" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Devise -->
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Devise</label>
                    <select wire:model.live="filterDevise" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($devises as $devise)
                            <option value="{{ $devise->id }}">
                                {{ $devise->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-12 d-md-flex justify-content-end">
                    <button
                        wire:click="resetFilters"
                        @disabled(!$search && !$filterCategory && !$filterDevise)
                        class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                        <i class="fas fa-rotate-left me-1"></i>
                        Réinitialiser
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Article Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Liste des articles</h5>
            <span class="badge bg-primary">{{ count($articles) }} article(s)</span>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <!-- Desktop / Tablet -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Catégorie</th>
                            <th class="text-end">Prix achat</th>
                            <th class="text-end">Prix vente</th>
                            <th width="100" class="text-end">Status</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-semibold">
                                    {{ $article->reference }}
                                </td>

                                <td class="text-muted">
                                    {{ Str::limit($article->designation, 50) }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $article->category->name ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($article->prix_achat, 2, ',', ' ') ?? '—' }} {{ $article->devise->symbole ?? $article->devise->code }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($article->prix_vente, 2, ',', ' ') ?? '—' }} {{ $article->devise->symbole ?? $article->devise->code }}
                                </td>

                                <td class="text-end">
                                    <span class="badge {{ $article->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $article->status ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="edit({{ $article->id }})"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="tooltip"
                                                title="Modifier">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $article->id }})"
                                                class="btn btn-outline-{{ $article->status ? 'success' : 'secondary' }}"
                                                data-bs-toggle="tooltip"
                                                title="Changer le statut">
                                            <i class="fa fa-toggle-{{ $article->status ? 'on' : 'off' }}"></i>
                                        </button>

                                        <button wire:click="deleteConfirm({{ $article->id }})"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Aucun article trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="d-md-none">
                @forelse($articles as $article)
                    <div class="border-bottom p-3">
                        <div class="mb-2">
                            <h6 class="fw-semibold mb-1">
                                {{ $article->designation }}
                            </h6>

                            <p class="text-muted small mb-1">
                                Réf : <span class="fw-semibold">{{ $article->reference }}</span>
                                | <span class="text-info">PAU: {{ number_format($article->prix_achat, 2, ',', ' ') ?? '—' }} {{ $article->devise->symbole ?? $article->devise->code }}</span>
                                - <span class="text-success">PVU: {{ number_format($article->prix_achat, 2, ',', ' ') ?? '—' }} {{ $article->devise->symbole ?? $article->devise->code }}</span>
                            </p>

                            <p class="text-muted small mb-2">
                                Catégorie : {{ $article->category->name ?? '—' }}
                            </p>

                            <span class="badge {{ $article->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $article->status ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button wire:click="edit({{ $article->id }})"
                                    class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fa fa-pen me-1"></i> Modifier
                            </button>

                            <button wire:click="toggleStatus({{ $article->id }})"
                                    class="btn btn-sm btn-outline-{{ $article->status ? 'success' : 'secondary' }}">
                                <i class="fa fa-toggle-{{ $article->status ? 'on' : 'off' }}"></i>
                            </button>

                            <button wire:click="deleteConfirm({{ $article->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Aucun article trouvé</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <!-- Modal -->
    @include('livewire.articles.article-modal')

    <!-- Stats Summary -->
    <div class="row g-3 mt-2">
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total articles</p>
                            <h4 class="fw-bold mb-0">{{ count($articles) }}</h4>
                        </div>
                        <div class="rounded bg-primary bg-opacity-10 p-2">
                            <i class="fas fa-file-alt text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
