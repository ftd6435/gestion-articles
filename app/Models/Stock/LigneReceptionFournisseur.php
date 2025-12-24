<?php

namespace App\Models\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
use Illuminate\Database\Eloquent\Model;

class LigneReceptionFournisseur extends Model
{
    protected $fillable = [
        'reception_id',
        'article_id',
        'magasin_id',
        'etagere_id',
        'quantity',
        'date_expiration',
    ];

    public function reception()
    {
        return $this->belongsTo(ReceptionFournisseur::class, 'reception_id');
    }

    public function article()
    {
        return $this->belongsTo(ArticleModel::class, 'article_id');
    }

    public function magasin()
    {
        return $this->belongsTo(MagasinModel::class, 'magasin_id');
    }

    public function etagere()
    {
        return $this->belongsTo(EtagereModel::class, 'etagere_id');
    }
}
