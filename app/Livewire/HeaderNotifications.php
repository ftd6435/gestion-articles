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
    public $lowStockCount = 0;
    public $unpaidSalesCount = 0;
    public $unpaidReceptionsCount = 0;

    public function mount(): void
    {
        $this->refreshCounts();
    }

    public function refreshCounts(): void
    {
        $counts = Cache::remember('header_notifications_counts', 60, function () {
            $lowStockExpr = '
                (COALESCE((SELECT SUM(quantity) FROM ligne_reception_fournisseurs WHERE article_id = article_models.id), 0) -
                 COALESCE((SELECT SUM(lvc.quantity)
                           FROM ligne_vente_clients lvc
                           JOIN vente_models vm ON vm.id = lvc.vente_id
                           WHERE lvc.article_id = article_models.id AND vm.status != "ANNULEE"), 0))
            ';

            $lowStockCount = ArticleModel::query()
                ->where('status', true)
                ->whereRaw("{$lowStockExpr} BETWEEN ? AND ?", [1, 9])
                ->count();

            $unpaidSalesCount = VenteModel::query()
                ->where('status', 'IMPAYEE')
                ->count();

            $unpaidReceptionsCount = ReceptionFournisseur::query()
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
                ->havingRaw('total_paid <= 0 AND (total_no_discount - (total_no_discount * (remise_percent / 100))) > 0')
                ->count();

            return [
                'lowStockCount' => $lowStockCount,
                'unpaidSalesCount' => $unpaidSalesCount,
                'unpaidReceptionsCount' => $unpaidReceptionsCount,
            ];
        });

        $this->lowStockCount = (int) ($counts['lowStockCount'] ?? 0);
        $this->unpaidSalesCount = (int) ($counts['unpaidSalesCount'] ?? 0);
        $this->unpaidReceptionsCount = (int) ($counts['unpaidReceptionsCount'] ?? 0);
    }

    public function render()
    {
        return view('livewire.header-notifications');
    }
}
