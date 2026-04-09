<?php

namespace App\Livewire\Comptabilite;

use App\Models\Comptabilite\Operation;
use App\Models\Comptabilite\TypeOperation;
use App\Models\DeviseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Operations extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $deviseFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showDateFilters = false;

    public $types;
    public $devises;
    public $currentDevise;

    public $operationId;
    public $type_operation_id = '';
    public $devise_id = '';
    public $reason = '';
    public $amount;
    public $showModal = false;

    public $totalEntrees = 0;
    public $totalSorties = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'deviseFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'showDateFilters' => ['except' => false],
        'page' => ['except' => 1],
    ];

    protected function rules()
    {
        return [
            'type_operation_id' => ['required', 'exists:type_operations,id'],
            'devise_id' => ['required', 'exists:devise_models,id'],
            'reason' => 'required|string|min:2|max:255',
            'amount' => 'required|string',
        ];
    }

    protected function messages()
    {
        return [
            'type_operation_id.required' => 'Veuillez sélectionner un type.',
            'type_operation_id.exists' => 'Le type sélectionné est invalide.',
            'devise_id.required' => 'Veuillez sélectionner une devise.',
            'devise_id.exists' => 'La devise sélectionnée est invalide.',
            'reason.required' => 'Le motif est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.string' => 'Le montant est invalide.',
        ];
    }

    public function mount()
    {
        $this->types = TypeOperation::orderBy('nature')->orderBy('name')->get();
        $this->devises = DeviseModel::active()->orderByDesc('is_default')->orderBy('code')->get();
        $defaultDevise = DeviseModel::getDefaultDevise();
        $this->deviseFilter = (string) ($defaultDevise?->id ?? ($this->devises->first()?->id ?? ''));
        $this->currentDevise = $this->deviseFilter ? $this->devises->firstWhere('id', (int) $this->deviseFilter) : null;
        $this->recalculateTotals();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->recalculateTotals();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
        $this->recalculateTotals();
    }

    public function updatedDeviseFilter()
    {
        $this->currentDevise = $this->deviseFilter ? $this->devises->firstWhere('id', (int) $this->deviseFilter) : null;
        $this->resetPage();
        $this->recalculateTotals();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->recalculateTotals();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->recalculateTotals();
    }

    private function baseQuery()
    {
        return Operation::query()
            ->with(['typeOperation', 'devise'])
            ->when($this->search, function ($q) {
                $q->where('reason', 'like', '%' . $this->search . '%');
            })
            ->when($this->typeFilter, function ($q) {
                $q->where('type_operation_id', $this->typeFilter);
            })
            ->when($this->deviseFilter, function ($q) {
                $q->where('devise_id', $this->deviseFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest();
    }

    private function recalculateTotals(): void
    {
        $query = Operation::query()
            ->join('type_operations', 'type_operations.id', '=', 'operations.type_operation_id')
            ->when($this->search, function ($q) {
                $q->where('operations.reason', 'like', '%' . $this->search . '%');
            })
            ->when($this->typeFilter, function ($q) {
                $q->where('operations.type_operation_id', $this->typeFilter);
            })
            ->when($this->deviseFilter, function ($q) {
                $q->where('operations.devise_id', $this->deviseFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('operations.created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('operations.created_at', '<=', $this->dateTo);
            });

        $this->totalEntrees = (float) (clone $query)->where('type_operations.nature', 1)->sum('operations.amount');
        $this->totalSorties = (float) (clone $query)->where('type_operations.nature', 0)->sum('operations.amount');
    }

    public function toggleDateFilters(): void
    {
        $this->showDateFilters = !$this->showDateFilters;

        if (!$this->showDateFilters) {
            $this->dateFrom = '';
            $this->dateTo = '';
            $this->resetPage();
            $this->recalculateTotals();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'dateFrom', 'dateTo']);
        $this->showDateFilters = false;
        $this->resetPage();
        $this->recalculateTotals();
    }

    private function parseAmount($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = str_replace([' ', "\u{00A0}"], '', $raw);
        $raw = str_replace(',', '.', $raw);

        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    public function formatAmount(): void
    {
        $amount = $this->parseAmount($this->amount);
        if ($amount === null) {
            return;
        }

        $this->amount = number_format($amount, 0, ',', ' ');
    }

    public function resetForm(): void
    {
        $this->reset(['operationId', 'type_operation_id', 'devise_id', 'reason', 'amount']);
        $this->type_operation_id = '';
        $this->devise_id = $this->deviseFilter ?: (string) (DeviseModel::getDefaultDevise()?->id ?? '');
        $this->resetValidation();
    }

    public function create(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('comptabilite.operations', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des opérations.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('comptabilite.operations', 'update')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier des opérations.');
            return;
        }

        $op = Operation::findOrFail($id);
        $this->operationId = $op->id;
        $this->type_operation_id = $op->type_operation_id;
        $this->devise_id = (string) ($op->devise_id ?? '');
        $this->reason = $op->reason;
        $this->amount = number_format((float) $op->amount, 0, ',', ' ');
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function store(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->operationId) {
            if (!$currentUser?->canAccess('comptabilite.operations', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des opérations.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('comptabilite.operations', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des opérations.');
                return;
            }
        }

        $this->validate();

        $amountValue = $this->parseAmount($this->amount);
        if ($amountValue === null || $amountValue <= 0) {
            $this->addError('amount', 'Le montant doit être supérieur à 0.');
            return;
        }

        if ($this->operationId) {
            $op = Operation::findOrFail($this->operationId);
            $op->update([
                'type_operation_id' => $this->type_operation_id,
                'devise_id' => $this->devise_id,
                'reason' => $this->reason,
                'amount' => $amountValue,
            ]);
            $message = 'Opération modifiée avec succès.';
        } else {
            Operation::create([
                'type_operation_id' => $this->type_operation_id,
                'devise_id' => $this->devise_id,
                'reason' => $this->reason,
                'amount' => $amountValue,
            ]);
            $message = 'Opération ajoutée avec succès.';
        }

        $this->closeModal();
        $this->recalculateTotals();
        session()->flash('success', $message);
    }

    public function delete($id): void
    {
        Operation::findOrFail($id)->delete();
        $this->recalculateTotals();
        session()->flash('success', 'Opération supprimée avec succès.');
    }

    public function render()
    {
        view()->share('title', 'Opérations');
        view()->share('breadcrumb', 'Comptabilité / Opérations');

        $operations = $this->baseQuery()->paginate(12);

        return view('livewire.comptabilite.operations', [
            'operations' => $operations,
        ]);
    }
}
