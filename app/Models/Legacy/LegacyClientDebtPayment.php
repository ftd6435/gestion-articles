<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyClientDebtPayment extends Model
{
    protected $fillable = [
        'legacy_client_debt_id',
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
        return $this->belongsTo(LegacyClientDebt::class, 'legacy_client_debt_id');
    }
}

