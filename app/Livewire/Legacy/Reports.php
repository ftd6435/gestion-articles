<?php

namespace App\Livewire\Legacy;

use App\Models\DeviseModel;
use App\Models\Legacy\LegacyClientDebt;
use App\Models\Legacy\LegacyFournisseurDebt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Reports extends Component
{
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user?->canAccess('legacy.reports', 'view')) {
            abort(403);
        }
    }

    public function updatedDateFrom(): void
    {
        $this->normalizeDates();
    }

    public function updatedDateTo(): void
    {
        $this->normalizeDates();
    }

    private function normalizeDates(): void
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            [$this->dateFrom, $this->dateTo] = [$this->dateTo, $this->dateFrom];
        }
    }

    public function render()
    {
        view()->share('title', 'Anciens - Rapports');
        view()->share('breadcrumb', 'Anciens / Rapports');

        $openClientDebts = LegacyClientDebt::query()
            ->with('devise')
            ->withSum('payments as paid_sum', 'montant')
            ->where('is_closed', false)
            ->get();

        $openFournisseurDebts = LegacyFournisseurDebt::query()
            ->with('devise')
            ->withSum('payments as paid_sum', 'montant')
            ->where('is_closed', false)
            ->get();

        $openClientTotals = $openClientDebts
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

        $openFournisseurTotals = $openFournisseurDebts
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

        $clientPaidQuery = DB::table('legacy_client_debt_payments as p')
            ->join('legacy_client_debts as d', 'd.id', '=', 'p.legacy_client_debt_id')
            ->select('d.devise_id', DB::raw('SUM(p.montant) as total_paid'))
            ->when($this->dateFrom && $this->dateTo, fn($q) => $q->whereBetween('p.date_paiement', [$this->dateFrom, $this->dateTo]))
            ->groupBy('d.devise_id')
            ->get();

        $fournisseurPaidQuery = DB::table('legacy_fournisseur_debt_payments as p')
            ->join('legacy_fournisseur_debts as d', 'd.id', '=', 'p.legacy_fournisseur_debt_id')
            ->select('d.devise_id', DB::raw('SUM(p.montant) as total_paid'))
            ->when($this->dateFrom && $this->dateTo, fn($q) => $q->whereBetween('p.date_paiement', [$this->dateFrom, $this->dateTo]))
            ->groupBy('d.devise_id')
            ->get();

        $deviseIds = collect()
            ->merge($clientPaidQuery->pluck('devise_id'))
            ->merge($fournisseurPaidQuery->pluck('devise_id'))
            ->unique()
            ->filter()
            ->values()
            ->all();

        $devisesById = DeviseModel::query()->whereIn('id', $deviseIds)->get()->keyBy('id');

        $clientPaidTotals = $clientPaidQuery->map(function ($row) use ($devisesById) {
            return [
                'devise' => $devisesById->get($row->devise_id),
                'total_paid' => (float) $row->total_paid,
            ];
        });

        $fournisseurPaidTotals = $fournisseurPaidQuery->map(function ($row) use ($devisesById) {
            return [
                'devise' => $devisesById->get($row->devise_id),
                'total_paid' => (float) $row->total_paid,
            ];
        });

        $openClientCount = $openClientDebts->count();
        $openFournisseurCount = $openFournisseurDebts->count();

        $clientPaymentCount = DB::table('legacy_client_debt_payments as p')
            ->when($this->dateFrom && $this->dateTo, fn($q) => $q->whereBetween('p.date_paiement', [$this->dateFrom, $this->dateTo]))
            ->count();

        $fournisseurPaymentCount = DB::table('legacy_fournisseur_debt_payments as p')
            ->when($this->dateFrom && $this->dateTo, fn($q) => $q->whereBetween('p.date_paiement', [$this->dateFrom, $this->dateTo]))
            ->count();

        return view('livewire.legacy.reports', [
            'openClientTotals' => $openClientTotals,
            'openFournisseurTotals' => $openFournisseurTotals,
            'clientPaidTotals' => $clientPaidTotals,
            'fournisseurPaidTotals' => $fournisseurPaidTotals,
            'openClientCount' => $openClientCount,
            'openFournisseurCount' => $openFournisseurCount,
            'clientPaymentCount' => $clientPaymentCount,
            'fournisseurPaymentCount' => $fournisseurPaymentCount,
        ]);
    }
}
