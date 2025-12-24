<?php

namespace App\Models\Stock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaiementFournisseur extends Model
{
    protected $fillable = [
        'commande_id',
        'reference',
        'notes',
        'reception_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'created_by',
        'updated_by'
    ];

    public function commande()
    {
        return $this->belongsTo(CommandeFournisseur::class, 'commande_id');
    }

    public function reception()
    {
        return $this->belongsTo(ReceptionFournisseur::class, 'reception_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the remaining amount to pay for this reception
     */
    public function getRemainingAmountAttribute()
    {
        if (!$this->reception) return 0;

        $totalAmount = $this->reception->getTotalAmount();
        $paidAmount = PaiementFournisseur::where('reception_id', $this->reception_id)
            ->where('id', '!=', $this->id)
            ->sum('montant');

        return $totalAmount - $paidAmount;
    }
}
