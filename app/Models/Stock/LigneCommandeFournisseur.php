<?php

namespace App\Models\Stock;

use App\Models\Articles\ArticleModel;
use Illuminate\Database\Eloquent\Model;

class LigneCommandeFournisseur extends Model
{
    protected $fillable = [
        'commande_id',
        'article_id',
        'quantity',
        'unit_price'
    ];

    public function commande()
    {
        return $this->belongsTo(CommandeFournisseur::class, 'commande_id');
    }

    public function article()
    {
        return $this->belongsTo(ArticleModel::class, 'article_id');
    }
}
