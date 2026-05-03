<?php

namespace App\Models\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\User;
use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
use Illuminate\Database\Eloquent\Model;

class StockInitialArticle extends Model
{
    protected $fillable = [
        'article_id',
        'magasin_id',
        'etagere_id',
        'quantity',
        'date_expiration',
        'date_inventaire',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date_expiration' => 'date',
        'date_inventaire' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Filter by article
     */
    public function scopeForArticle($query, $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    /**
     * Scope: Filter by warehouse
     */
    public function scopeForMagasin($query, $magasinId)
    {
        return $query->where('magasin_id', $magasinId);
    }

    /**
     * Scope: Filter by shelf
     */
    public function scopeForEtagere($query, $etagereId)
    {
        return $query->where('etagere_id', $etagereId);
    }

    /**
     * Scope: Items with expiration date
     */
    public function scopeExpirable($query)
    {
        return $query->whereNotNull('date_expiration');
    }

    /**
     * Scope: Expired items
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('date_expiration')
            ->where('date_expiration', '<', now());
    }

    /**
     * Scope: Soon to expire (within X days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('date_expiration')
            ->where('date_expiration', '>=', now())
            ->where('date_expiration', '<=', now()->addDays($days));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if item has expired
     */
    public function isExpired(): bool
    {
        return $this->date_expiration && $this->date_expiration->isPast();
    }

    /**
     * Check if item is expiring soon (within X days)
     */
    public function isExpiringSoon($days = 30): bool
    {
        if (!$this->date_expiration) {
            return false;
        }

        return $this->date_expiration->isFuture()
            && $this->date_expiration->diffInDays(now()) <= $days;
    }

    /**
     * Get days until expiration (negative if expired)
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->date_expiration) {
            return null;
        }

        return now()->diffInDays($this->date_expiration, false);
    }
}
