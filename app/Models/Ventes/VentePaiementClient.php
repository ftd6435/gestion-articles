<?php

namespace App\Models\Ventes;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentePaiementClient extends Model
{
    use HasFactory;

    protected $table = 'vente_paiement_clients';

    protected $fillable = [
        'vente_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
    ];

    /**
     * Get the vente that owns the payment.
     */
    public function vente()
    {
        return $this->belongsTo(VenteModel::class, 'vente_id');
    }

    /**
     * Get the user who created the payment.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the payment.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
