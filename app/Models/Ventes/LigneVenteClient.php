<?php

namespace App\Models\Ventes;

use App\Models\Articles\ArticleModel;
use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
use Illuminate\Database\Eloquent\Model;

class LigneVenteClient extends Model
{
    protected $fillable = [
        'vente_id',
        'article_id',
        'etagere_id',
        'magasin_id',
        'quantity',
        'unit_price',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Each detail belongs to a vente
    public function vente()
    {
        return $this->belongsTo(VenteModel::class, 'vente_id');
    }

    public function etagere()
    {
        return $this->belongsTo(EtagereModel::class, 'etagere_id');
    }

    public function magasin()
    {
        return $this->belongsTo(MagasinModel::class, 'magasin_id');
    }

    public function article()
    {
        return $this->belongsTo(ArticleModel::class, 'article_id');
    }
}
