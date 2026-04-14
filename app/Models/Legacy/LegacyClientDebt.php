<?php

namespace App\Models\Legacy;

use App\Models\ClientModel;
use App\Models\DeviseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegacyClientDebt extends Model
{
    protected $fillable = [
        'client_id',
        'devise_id',
        'due_amount',
        'debt_date',
        'notes',
        'is_closed',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_amount' => 'decimal:2',
        'debt_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(DeviseModel::class, 'devise_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LegacyClientDebtPayment::class, 'legacy_client_debt_id');
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('montant');
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->due_amount - $this->paidAmount());
    }
}

