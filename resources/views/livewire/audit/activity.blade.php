<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Audit activité</h1>
            <p class="text-muted mb-0">Historique des actions utilisateurs</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Recherche</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Action, modèle, user, ip...">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted">Utilisateur</label>
                    <select class="form-select" wire:model.live="userId">
                        <option value="">Tous</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted">Action</label>
                    <select class="form-select" wire:model.live="action">
                        <option value="">Toutes</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted">Modèle</label>
                    <select class="form-select" wire:model.live="model">
                        <option value="">Tous</option>
                        @foreach($models as $m)
                            <option value="{{ $m }}">{{ class_basename($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small text-muted">Du</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small text-muted">Au</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Modèle</th>
                            <th>IP</th>
                            <th>Appareil</th>
                            <th class="text-end">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user?->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $log->user?->email ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                        {{ $log->action ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $log->model ? class_basename($log->model) : '—' }}</td>
                                <td class="text-muted">{{ $log->ip ?? '—' }}</td>
                                <td class="text-muted small">
                                    <div>{{ $log->system ?? '—' }}</div>
                                    <div>{{ $log->browser ?? '—' }}</div>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="showDetails({{ $log->id }})">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa fa-clipboard-list fa-2x mb-2 opacity-50"></i>
                                    <div>Aucune activité</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    @if($showDetailsModal)
        <div class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white" style="max-width: 760px;">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white px-4 py-2">
                        <h5 class="modal-title">Détails activité</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        @if($selectedLog)
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="text-muted small">Utilisateur</div>
                                    <div class="fw-semibold">{{ $selectedLog->user?->name ?? '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Date</div>
                                    <div class="fw-semibold">{{ $selectedLog->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Action</div>
                                    <div class="fw-semibold">{{ $selectedLog->action ?? '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Modèle</div>
                                    <div class="fw-semibold">{{ $selectedLog->model ?? '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">IP</div>
                                    <div class="fw-semibold">{{ $selectedLog->ip ?? '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Appareil</div>
                                    <div class="fw-semibold">
                                        {{ $selectedLog->system ?? '—' }} / {{ $selectedLog->browser ?? '—' }} / {{ $selectedLog->machine ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-muted small mb-2">Data</div>
                            <pre class="bg-light border rounded p-3 mb-0" style="max-height: 320px; overflow:auto;">{{ json_encode($selectedLog->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </div>
                    <div class="modal-footer px-4 py-2">
                        <button class="btn btn-light" type="button" wire:click="closeDetails">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
