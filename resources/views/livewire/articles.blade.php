<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="display-5 fw-bold mb-1">Articles</h1>
            <p class="text-muted mb-0">Gérez vos articles et publications</p>
        </div>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nouvel article
        </button>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            wire:model.live="search"
                            class="form-control"
                            placeholder="Rechercher par titre ou auteur...">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Catégorie</label>
                    <select wire:model.live="filterCategory" class="form-select">
                        <option value="">Toutes les catégories</option>
                        <option value="Tutoriel">Tutoriel</option>
                        <option value="Guide">Guide</option>
                        <option value="Actualité">Actualité</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="row g-4 mb-4">
        @forelse($filteredArticles as $article)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <!-- Card Header with Image -->
                <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center"
                     style="height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-file-alt text-white opacity-50" style="font-size: 3rem;"></i>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    <!-- Category Badge -->
                    <span class="badge bg-primary mb-2">{{ $article['categorie'] }}</span>

                    <!-- Title -->
                    <h5 class="card-title fw-bold mb-2">{{ $article['titre'] }}</h5>

                    <!-- Meta Info -->
                    <div class="d-flex text-muted small mb-3">
                        <div class="me-3">
                            <i class="fas fa-user me-1"></i>
                            <span>{{ $article['auteur'] }}</span>
                        </div>
                        <div>
                            <i class="fas fa-eye me-1"></i>
                            <span>{{ $article['vues'] }}</span>
                        </div>
                    </div>

                    <!-- Status & Date -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        @if($article['statut'] === 'Publié')
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> {{ $article['statut'] }}
                            </span>
                        @elseif($article['statut'] === 'Brouillon')
                            <span class="badge bg-secondary">
                                <i class="fas fa-edit me-1"></i> {{ $article['statut'] }}
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="fas fa-clock me-1"></i> {{ $article['statut'] }}
                            </span>
                        @endif

                        <small class="text-muted">{{ $article['date'] }}</small>
                    </div>

                    <!-- Actions -->
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Éditer
                        </button>
                        <button
                            wire:click="deleteArticle({{ $article['id'] }})"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer cet article ?"
                            class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4 class="fw-bold mb-2">Aucun article trouvé</h4>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Stats Summary -->
    <div class="row g-3">
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

        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Publiés</p>
                            <h4 class="fw-bold mb-0">
                                {{ count(array_filter($articles, fn($a) => $a['statut'] === 'Publié')) }}
                            </h4>
                        </div>
                        <div class="rounded bg-success bg-opacity-10 p-2">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Brouillons</p>
                            <h4 class="fw-bold mb-0">
                                {{ count(array_filter($articles, fn($a) => $a['statut'] === 'Brouillon')) }}
                            </h4>
                        </div>
                        <div class="rounded bg-secondary bg-opacity-10 p-2">
                            <i class="fas fa-edit text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Vues totales</p>
                            <h4 class="fw-bold mb-0">
                                {{ array_sum(array_column($articles, 'vues')) }}
                            </h4>
                        </div>
                        <div class="rounded bg-info bg-opacity-10 p-2">
                            <i class="fas fa-eye text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
