<?php

namespace App\Livewire\Legacy;

use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Legacy\LegacyFournisseurDebt;
use App\Models\Legacy\LegacyFournisseurDebtPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FournisseurDebts extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'open';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 12;

    public $showDebtModal = false;
    public $debtId;
    public $fournisseur_id = '';
    public $devise_id = '';
    public $due_amount = '';
    public $debt_date = '';
    public $notes = '';

    public $showPaymentModal = false;
    public $paymentDebtId;
    public $payment_date = '';
    public $payment_amount = '';
    public $payment_mode = 'cash';
    public $payment_notes = '';

    public $showViewModal = false;
    public $viewDebtId;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'open'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user?->canAccess('legacy.fournisseurs', 'view')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->normalizeDates();
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->normalizeDates();
        $this->resetPage();
    }

    private function normalizeDates(): void
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            [$this->dateFrom, $this->dateTo] = [$this->dateTo, $this->dateFrom];
        }
    }

    public function openDebtModal(?int $id = null): void
    {
        $user = Auth::user();
        if ($id) {
            if (!$user?->canAccess('legacy.fournisseurs', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des dettes.');
                return;
            }
        } else {
            if (!$user?->canAccess('legacy.fournisseurs', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des dettes.');
                return;
            }
        }

        $this->resetValidation();
        $this->debtId = $id;

        if ($id) {
            $debt = LegacyFournisseurDebt::findOrFail($id);
            $this->fournisseur_id = (string) $debt->fournisseur_id;
            $this->devise_id = (string) $debt->devise_id;
            $this->due_amount = (string) $debt->due_amount;
            $this->debt_date = $debt->debt_date?->format('Y-m-d') ?? '';
            $this->notes = (string) ($debt->notes ?? '');
        } else {
            $defaultDevise = DeviseModel::getDefaultDevise();
            $this->fournisseur_id = '';
            $this->devise_id = (string) ($defaultDevise?->id ?? '');
            $this->due_amount = '';
            $this->debt_date = now()->format('Y-m-d');
            $this->notes = '';
        }

        $this->showDebtModal = true;
    }

    public function closeDebtModal(): void
    {
        $this->showDebtModal = false;
        $this->reset(['debtId', 'fournisseur_id', 'devise_id', 'due_amount', 'debt_date', 'notes']);
        $this->resetValidation();
    }

    public function saveDebt(): void
    {
        $user = Auth::user();
        if ($this->debtId) {
            if (!$user?->canAccess('legacy.fournisseurs', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des dettes.');
                return;
            }
        } else {
            if (!$user?->canAccess('legacy.fournisseurs', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des dettes.');
                return;
            }
        }

        $this->validate([
            'fournisseur_id' => ['required', 'exists:fournisseur_models,id'],
            'devise_id' => ['required', 'exists:devise_models,id'],
            'due_amount' => ['required', 'numeric', 'min:0.01'],
            'debt_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($this->debtId) {
            $debt = LegacyFournisseurDebt::findOrFail($this->debtId);
            if ($debt->is_closed) {
                session()->flash('error', 'Cette dette est clôturée.');
                return;
            }

            $debt->update([
                'fournisseur_id' => (int) $this->fournisseur_id,
                'devise_id' => (int) $this->devise_id,
                'due_amount' => (float) $this->due_amount,
                'debt_date' => $this->debt_date ?: null,
                'notes' => $this->notes ?: null,
                'updated_by' => Auth::id(),
            ]);
        } else {
            LegacyFournisseurDebt::create([
                'fournisseur_id' => (int) $this->fournisseur_id,
                'devise_id' => (int) $this->devise_id,
                'due_amount' => (float) $this->due_amount,
                'debt_date' => $this->debt_date ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }

        session()->flash('success', 'Dette enregistrée avec succès.');
        $this->closeDebtModal();
    }

    public function openPaymentModal(int $debtId): void
    {
        $user = Auth::user();
        if (!$user?->canAccess('legacy.fournisseurs', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission d\'enregistrer des paiements.');
            return;
        }

        $debt = LegacyFournisseurDebt::findOrFail($debtId);
        if ($debt->is_closed) {
            session()->flash('error', 'Cette dette est déjà clôturée.');
            return;
        }

        $this->resetValidation();
        $this->paymentDebtId = $debtId;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_amount = '';
        $this->payment_mode = 'cash';
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->reset(['paymentDebtId', 'payment_date', 'payment_amount', 'payment_mode', 'payment_notes']);
        $this->resetValidation();
    }

    public function openViewModal(int $debtId): void
    {
        $user = Auth::user();
        if (!$user?->canAccess('legacy.fournisseurs', 'view')) {
            abort(403);
        }

        $this->viewDebtId = $debtId;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->reset(['viewDebtId']);
    }

    private function generatePaymentReference(): string
    {
        return 'LSP-' . now()->format('ymd') . '-' . random_int(100000, 999999);
    }

    public function savePayment(): void
    {
        $user = Auth::user();
        if (!$user?->canAccess('legacy.fournisseurs', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission d\'enregistrer des paiements.');
            return;
        }

        $debt = LegacyFournisseurDebt::findOrFail((int) $this->paymentDebtId);
        if ($debt->is_closed) {
            session()->flash('error', 'Cette dette est déjà clôturée.');
            return;
        }

        $this->validate([
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', 'string', 'max:50'],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $amount = (float) $this->payment_amount;
        $remaining = $debt->remainingAmount();
        if ($amount > $remaining) {
            $this->addError('payment_amount', 'Le montant dépasse le reste à payer.');
            return;
        }

        DB::transaction(function () use ($debt, $amount) {
            $reference = $this->generatePaymentReference();
            while (LegacyFournisseurDebtPayment::where('reference', $reference)->exists()) {
                $reference = $this->generatePaymentReference();
            }

            LegacyFournisseurDebtPayment::create([
                'legacy_fournisseur_debt_id' => $debt->id,
                'date_paiement' => $this->payment_date,
                'montant' => $amount,
                'mode_paiement' => $this->payment_mode,
                'reference' => $reference,
                'notes' => $this->payment_notes ?: null,
                'created_by' => Auth::id(),
            ]);

            $newPaid = (float) $debt->payments()->sum('montant');
            $newRemaining = max(0, (float) $debt->due_amount - $newPaid);
            if ($newRemaining <= 0.00001) {
                $debt->update([
                    'is_closed' => true,
                    'closed_at' => now(),
                    'updated_by' => Auth::id(),
                ]);
            } else {
                $debt->update(['updated_by' => Auth::id()]);
            }
        });

        session()->flash('success', 'Paiement enregistré avec succès.');
        $this->closePaymentModal();
    }

    public function render()
    {
        view()->share('title', 'Anciens - Dettes fournisseurs');
        view()->share('breadcrumb', 'Anciens / Dettes fournisseurs');

        $baseQuery = LegacyFournisseurDebt::query()
            ->with(['fournisseur', 'devise'])
            ->withSum('payments as paid_sum', 'montant')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->whereHas('fournisseur', function ($fq) use ($term) {
                    $fq->where('name', 'like', $term)->orWhere('telephone', 'like', $term);
                });
            })
            ->when($this->dateFrom && $this->dateTo, fn ($q) => $q->whereBetween('debt_date', [$this->dateFrom, $this->dateTo]));

        $filteredQuery = (clone $baseQuery)
            ->when($this->statusFilter === 'open', fn ($q) => $q->where('is_closed', false))
            ->when($this->statusFilter === 'closed', fn ($q) => $q->where('is_closed', true));

        $debts = $filteredQuery
            ->orderByDesc('debt_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $fournisseurs = FournisseurModel::query()->orderBy('name')->get();
        $devises = DeviseModel::query()->orderBy('is_default', 'desc')->orderBy('code')->get();

        $statsTotal = (clone $baseQuery)->count();
        $statsOpen = (clone $baseQuery)->where('is_closed', false)->count();
        $statsClosed = (clone $baseQuery)->where('is_closed', true)->count();

        $openDebtsForTotals = (clone $baseQuery)->where('is_closed', false)->get();
        $openRemainingByDevise = $openDebtsForTotals
            ->groupBy('devise_id')
            ->map(function ($items) {
                $devise = $items->first()?->devise;
                $total = 0;
                foreach ($items as $d) {
                    $total += max(0, (float) $d->due_amount - (float) ($d->paid_sum ?? 0));
                }
                return [
                    'devise' => $devise,
                    'total_remaining' => $total,
                    'count' => $items->count(),
                ];
            })
            ->values();

        $printDebts = (clone $filteredQuery)
            ->orderByDesc('debt_date')
            ->orderByDesc('id')
            ->get();

        $paymentDebt = null;
        $paymentDebtPayments = collect();
        if ($this->showPaymentModal && $this->paymentDebtId) {
            $paymentDebt = LegacyFournisseurDebt::with(['fournisseur', 'devise'])->find($this->paymentDebtId);
            if ($paymentDebt) {
                $paymentDebtPayments = $paymentDebt->payments()->orderByDesc('date_paiement')->orderByDesc('id')->get();
            }
        }

        $viewDebt = null;
        $viewDebtPayments = collect();
        if ($this->showViewModal && $this->viewDebtId) {
            $viewDebt = LegacyFournisseurDebt::query()
                ->with(['fournisseur', 'devise'])
                ->withSum('payments as paid_sum', 'montant')
                ->find($this->viewDebtId);
            if ($viewDebt) {
                $viewDebtPayments = $viewDebt->payments()->orderByDesc('date_paiement')->orderByDesc('id')->get();
            }
        }

        return view('livewire.legacy.fournisseur-debts', [
            'debts' => $debts,
            'fournisseurs' => $fournisseurs,
            'devises' => $devises,
            'paymentDebt' => $paymentDebt,
            'paymentDebtPayments' => $paymentDebtPayments,
            'statsTotal' => $statsTotal,
            'statsOpen' => $statsOpen,
            'statsClosed' => $statsClosed,
            'openRemainingByDevise' => $openRemainingByDevise,
            'printDebts' => $printDebts,
            'viewDebt' => $viewDebt,
            'viewDebtPayments' => $viewDebtPayments,
        ]);
    }
}
