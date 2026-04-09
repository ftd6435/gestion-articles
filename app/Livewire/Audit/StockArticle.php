<?php

namespace App\Livewire\Audit;

use App\Models\Articles\ArticleModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockArticle extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $daysThreshold = 30;
    public $includeExpired = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'daysThreshold' => ['except' => 30],
        'includeExpired' => ['except' => false],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['search', 'daysThreshold', 'includeExpired'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'daysThreshold', 'includeExpired']);
        $this->daysThreshold = 30;
        $this->includeExpired = false;
        $this->resetPage();
    }

    private function stockExpression(): string
    {
        return '
            (COALESCE((SELECT SUM(quantity) FROM ligne_reception_fournisseurs WHERE article_id = article_models.id), 0) -
             COALESCE((SELECT SUM(lvc.quantity)
                       FROM ligne_vente_clients lvc
                       JOIN vente_models vm ON vm.id = lvc.vente_id
                       WHERE lvc.article_id = article_models.id AND vm.status != "ANNULEE"), 0))
        ';
    }

    public function render()
    {
        view()->share('title', 'Audit stock (expiration)');
        view()->share('breadcrumb', 'Audit / Stock article');

        $today = Carbon::today();
        $limitDate = $today->copy()->addDays((int) $this->daysThreshold);

        $stockExpr = $this->stockExpression();
        $expiryCondition = $this->includeExpired ? '' : " AND date_expiration >= '{$today->toDateString()}'";
        $expiryConditionAlias = $this->includeExpired ? '' : " AND lr.date_expiration >= '{$today->toDateString()}'";

        $minExpirySub = "(SELECT MIN(date_expiration) FROM ligne_reception_fournisseurs WHERE article_id = article_models.id AND date_expiration IS NOT NULL{$expiryCondition})";
        $qtyWithExpirySub = "(SELECT COALESCE(SUM(quantity), 0) FROM ligne_reception_fournisseurs WHERE article_id = article_models.id AND date_expiration IS NOT NULL{$expiryCondition})";

        $magasinSub = "(SELECT m.nom
            FROM ligne_reception_fournisseurs lr
            JOIN magasin_models m ON m.id = lr.magasin_id
            WHERE lr.article_id = article_models.id AND lr.date_expiration IS NOT NULL{$expiryConditionAlias}
            ORDER BY lr.date_expiration ASC, lr.id ASC
            LIMIT 1)";

        $etagereSub = "(SELECT e.code_etagere
            FROM ligne_reception_fournisseurs lr
            JOIN etagere_models e ON e.id = lr.etagere_id
            WHERE lr.article_id = article_models.id AND lr.date_expiration IS NOT NULL{$expiryConditionAlias}
            ORDER BY lr.date_expiration ASC, lr.id ASC
            LIMIT 1)";

        $articles = ArticleModel::query()
            ->with('category')
            ->where('status', true)
            ->select('article_models.*')
            ->selectRaw("{$stockExpr} as stock")
            ->selectRaw("{$minExpirySub} as next_expiration")
            ->selectRaw("{$qtyWithExpirySub} as qty_with_expiration")
            ->selectRaw("{$magasinSub} as magasin")
            ->selectRaw("{$etagereSub} as etagere")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('ligne_reception_fournisseurs')
                    ->whereColumn('article_id', 'article_models.id')
                    ->whereNotNull('date_expiration');
            })
            ->whereRaw("{$stockExpr} > 0")
            ->whereRaw("{$minExpirySub} <= ?", [$limitDate->toDateString()])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('reference', 'like', $term)
                        ->orWhere('designation', 'like', $term)
                        ->orWhereHas('category', function ($c) use ($term) {
                            $c->where('name', 'like', $term);
                        });
                });
            })
            ->orderByRaw("{$minExpirySub} asc")
            ->paginate(12);

        return view('livewire.audit.stock-article', [
            'articles' => $articles,
            'today' => $today,
        ]);
    }
}
