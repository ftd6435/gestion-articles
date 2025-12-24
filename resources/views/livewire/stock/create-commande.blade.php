<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                Nouvelle Commande Fournisseur
            </h1>
            <p class="text-muted mb-0">Créez une commande et ajoutez les lignes de commande</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('stock.commandes') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Commande Fournisseur Form -->
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-primary" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Informations Commande
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <!-- Référence -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Référence <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="reference"
                                class="form-control @error('reference') is-invalid @enderror"
                                readonly>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fournisseur -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Fournisseur <span class="text-danger">*</span>
                            </label>
                            <select
                                wire:model="fournisseur_id"
                                class="form-select @error('fournisseur_id') is-invalid @enderror">
                                <option value="">-- Sélectionner --</option>
                                @foreach($fournisseurs as $fournisseur)
                                    <option value="{{ $fournisseur->id }}">{{ $fournisseur->name }} - {{ $fournisseur->telephone }}</option>
                                @endforeach
                            </select>
                            @error('fournisseur_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Devise -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Devise <span class="text-danger">*</span>
                            </label>
                            <select
                                wire:model.live="devise_id"
                                class="form-select @error('devise_id') is-invalid @enderror">
                                <option value="">-- Sélectionner --</option>
                                @foreach($devises as $devise)
                                    <option value="{{ $devise->id }}">
                                        {{ $devise->code }} - {{ $devise->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('devise_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Taux de change & Remise -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Taux de change</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="taux_change"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Remise (%)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model.live="remise"
                                    class="form-control"
                                    min="0"
                                    max="100">
                            </div>
                        </div>

                        <!-- Date & Status -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    wire:model="date_commande"
                                    class="form-control @error('date_commande') is-invalid @enderror">
                                @error('date_commande')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select wire:model="status" class="form-select">
                                    <option value="EN_COURS">En cours</option>
                                    <option value="PARTIELLE">Partielle</option>
                                    <option value="TERMINEE">Terminée</option>
                                    <option value="ANNULEE">Annulée</option>
                                </select>
                            </div>
                        </div>

                        <!-- Total Amount Display -->
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total Commande:</span>
                            <span class="fs-4 fw-bold">{{ number_format($totalAmount, 2) }}
                                {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                            <button type="button" wire:click="resetForm" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i> Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Ligne Commande Form & List -->
        <div class="col-12 col-md-8">
            <!-- Add Ligne Form -->
            <div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);">
                <div class="card-header" style="background: linear-gradient(135deg, #9c27b0 0%, #ba68c8 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Ajouter une Ligne de Commande
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Article -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Article <span class="text-danger">*</span>
                            </label>
                            <select
                                wire:model="article_id"
                                class="form-select @error('article_id') is-invalid @enderror">
                                <option value="">-- Sélectionner --</option>
                                @foreach($articles as $article)
                                    <option value="{{ $article->id }}">
                                        {{ $article->reference }} - {{ $article->designation }}
                                    </option>
                                @endforeach
                            </select>
                            @error('article_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Quantité <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                wire:model="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                min="1"
                                step="1">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="col-md-3" x-data="priceInput(@this, 'unit_price')">
                            <label class="form-label fw-semibold">
                                Prix U. <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                x-model="display"
                                @input="format"
                                inputmode="decimal"
                                class="form-control @error('unit_price') is-invalid @enderror"
                                placeholder="0.00">

                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Add Button -->
                        <div class="col-12">
                            <button
                                type="button"
                                wire:click="addLigne"
                                class="btn btn-success w-100">
                                <i class="fas fa-plus me-2"></i> Ajouter la ligne
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List of Lines -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Lignes de Commande
                    </h5>
                    <span class="badge bg-primary">{{ count($lignes) }} ligne(s)</span>
                </div>
                <div class="card-body p-0">
                    @if(empty($lignes))
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Aucune ligne ajoutée pour le moment</p>
                            <p class="small text-muted">Utilisez le formulaire ci-dessus pour ajouter des lignes</p>
                        </div>
                    @else
                        <!-- Desktop View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Article</th>
                                        <th width="15%">Qté</th>
                                        <th width="20%">Prix U.</th>
                                        <th width="20%">Sous-total</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lignes as $index => $ligne)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $ligne['article_code'] }}</div>
                                                <small class="text-muted">{{ $ligne['article_name'] }}</small>
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    wire:change="updateLigneQuantity({{ $index }}, $event.target.value)"
                                                    value="{{ $ligne['quantity'] }}"
                                                    class="form-control form-control-sm"
                                                    min="1"
                                                    style="width: 80px;">
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    wire:change="updateLignePrice({{ $index }}, $event.target.value)"
                                                    value="{{ $ligne['unit_price'] }}"
                                                    class="form-control form-control-sm"
                                                    min="0"
                                                    step="0.01"
                                                    style="width: 100px;">
                                            </td>
                                            <td class="fw-bold">{{ number_format($ligne['subtotal'], 2) }}</td>
                                            <td>
                                                <button
                                                    wire:click="removeLigne({{ $index }})"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td colspan="2" class="fw-bold fs-5 text-primary">
                                            {{ number_format(collect($lignes)->sum('subtotal'), 2) }}
                                        </td>
                                    </tr>
                                    @if($remise > 0)
                                        <tr>
                                            <td colspan="3" class="text-end">Remise ({{ $remise }}%):</td>
                                            <td colspan="2" class="text-danger">
                                                - {{ number_format(collect($lignes)->sum('subtotal') * ($remise / 100), 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total Final:</td>
                                            <td colspan="2" class="fw-bold fs-5 text-success">
                                                {{ number_format($totalAmount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>

                        <!-- Mobile View -->
                        <div class="d-md-none">
                            @foreach($lignes as $index => $ligne)
                                <div class="border-bottom p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $ligne['article_code'] }}</div>
                                            <small class="text-muted">{{ $ligne['article_name'] }}</small>
                                        </div>
                                        <button
                                            wire:click="removeLigne({{ $index }})"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="small text-muted">Qté</label>
                                            <input
                                                type="number"
                                                wire:change="updateLigneQuantity({{ $index }}, $event.target.value)"
                                                value="{{ $ligne['quantity'] }}"
                                                class="form-control form-control-sm"
                                                min="1">
                                        </div>
                                        <div class="col-4">
                                            <label class="small text-muted">Prix U.</label>
                                            <input
                                                type="number"
                                                wire:change="updateLignePrice({{ $index }}, $event.target.value)"
                                                value="{{ $ligne['unit_price'] }}"
                                                class="form-control form-control-sm"
                                                min="0"
                                                step="0.01">
                                        </div>
                                        <div class="col-4">
                                            <label class="small text-muted">Total</label>
                                            <div class="fw-bold">{{ number_format($ligne['subtotal'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="p-3 bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Total:</span>
                                    <span class="fw-bold text-primary">
                                        {{ number_format(collect($lignes)->sum('subtotal'), 2) }}
                                    </span>
                                </div>
                                @if($remise > 0)
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Remise ({{ $remise }}%):</span>
                                        <span>- {{ number_format(collect($lignes)->sum('subtotal') * ($remise / 100), 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total Final:</span>
                                        <span class="text-success fs-5">{{ number_format($totalAmount, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
