<?php

namespace App\Livewire;

use App\Models\Articles\ArticleModel;
use App\Models\Stock\ReceptionFournisseur;
use App\Models\Ventes\VenteModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HeaderNotifications extends Component
{
    // Stock
    public $lowStockCount = 0;
    public $outOfStockCount = 0;

    // Sales (clients)
    public $unpaidSalesCount = 0;
    public $partialSalesCount = 0;

    // Receptions (fournisseurs)
    public $unpaidReceptionsCount = 0;
    public $partialReceptionsCount = 0;

    public function mount(): void
    {
        $this->refreshCounts();
    }

    public function refreshCounts(): void
    {
        $counts = Cache::remember('header_notifications_counts_v2', 60, function () {
            $stockExpr = '
                (COALESCE((SELECT SUM(quantity) FROM stock_initial_articles WHERE article_id = article_models.id), 0) +
                 COALESCE((SELECT SUM(quantity) FROM ligne_reception_fournisseurs WHERE article_id = article_models.id), 0) -
                 COALESCE((SELECT SUM(lvc.quantity)
                           FROM ligne_vente_clients lvc
                           JOIN vente_models vm ON vm.id = lvc.vente_id
                           WHERE lvc.article_id = article_models.id AND vm.status != "ANNULEE"), 0))
            ';

            $outOfStockCount = ArticleModel::query()
                ->where('status', true)
                ->whereRaw("{$stockExpr} < ?", [10])
                ->count();

            $lowStockCount = ArticleModel::query()
                ->where('status', true)
                ->whereRaw("{$stockExpr} BETWEEN ? AND ?", [10, 20])
                ->count();

            // Sales: unpaid (IMPAYEE) vs partially paid (PARTIELLE)
            $unpaidSalesCount = VenteModel::query()
                ->where('status', 'IMPAYEE')
                ->count();

            $partialSalesCount = VenteModel::query()
                ->where('status', 'PARTIELLE')
                ->count();

            // Receptions: unpaid vs partially paid
            $receptions = ReceptionFournisseur::query()
                ->select('reception_fournisseurs.id')
                ->selectSub(
                    DB::table('paiement_fournisseurs')
                        ->selectRaw('COALESCE(SUM(montant), 0)')
                        ->whereColumn('reception_id', 'reception_fournisseurs.id'),
                    'total_paid'
                )
                ->selectSub(
                    DB::table('ligne_reception_fournisseurs as lr')
                        ->join('ligne_commande_fournisseurs as lc', function ($join) {
                            $join->on('lc.article_id', '=', 'lr.article_id');
                        })
                        ->selectRaw('COALESCE(SUM(lr.quantity * lc.unit_price), 0)')
                        ->whereColumn('lr.reception_id', 'reception_fournisseurs.id')
                        ->whereColumn('lc.commande_id', 'reception_fournisseurs.commande_id'),
                    'total_no_discount'
                )
                ->selectSub(
                    DB::table('commande_fournisseurs')
                        ->selectRaw('COALESCE(remise, 0)')
                        ->whereColumn('id', 'reception_fournisseurs.commande_id'),
                    'remise_percent'
                )
                ->havingRaw('(total_no_discount - (total_no_discount * (remise_percent / 100))) > 0')
                ->get();

            $unpaidReceptionsCount = $receptions->filter(fn($r) => (float) $r->total_paid <= 0)->count();
            $partialReceptionsCount = $receptions->filter(fn($r) => (float) $r->total_paid > 0 && (float) $r->total_paid < ((float) $r->total_no_discount * (1 - (float) $r->remise_percent / 100)))->count();

            return [
                'lowStockCount'          => $lowStockCount,
                'outOfStockCount'        => $outOfStockCount,
                'unpaidSalesCount'       => $unpaidSalesCount,
                'partialSalesCount'      => $partialSalesCount,
                'unpaidReceptionsCount'  => $unpaidReceptionsCount,
                'partialReceptionsCount' => $partialReceptionsCount,
            ];
        });

        $this->lowStockCount          = (int) ($counts['lowStockCount'] ?? 0);
        $this->outOfStockCount        = (int) ($counts['outOfStockCount'] ?? 0);
        $this->unpaidSalesCount       = (int) ($counts['unpaidSalesCount'] ?? 0);
        $this->partialSalesCount      = (int) ($counts['partialSalesCount'] ?? 0);
        $this->unpaidReceptionsCount  = (int) ($counts['unpaidReceptionsCount'] ?? 0);
        $this->partialReceptionsCount = (int) ($counts['partialReceptionsCount'] ?? 0);
    }

    public function render()
    {
        return view('livewire.header-notifications');
    }
}
