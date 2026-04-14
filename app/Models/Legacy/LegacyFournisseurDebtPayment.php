<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyFournisseurDebtPayment extends Model
{
    protected $fillable = [
        'legacy_fournisseur_debt_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(LegacyFournisseurDebt::class, 'legacy_fournisseur_debt_id');
    }
}

