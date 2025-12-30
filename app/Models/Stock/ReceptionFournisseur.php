<?php

namespace App\Models\Stock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReceptionFournisseur extends Model
{
    protected $fillable = [
        'reference',
        'commande_id',
        'date_reception',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date_reception' => 'datetime'
    ];

    public function commande()
    {
        return $this->belongsTo(CommandeFournisseur::class, 'commande_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function ligneReceptions()
    {
        return $this->hasMany(LigneReceptionFournisseur::class, 'reception_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementFournisseur::class, 'reception_id');
    }

    /**
     * Calculate total amount for this reception based on received quantities
     */
    public function getTotalAmountNoDiscount()
    {
        $total = 0;

        foreach ($this->ligneReceptions as $ligneReception) {
            // Find corresponding ligne commande
            $ligneCommande = $this->commande->ligneCommandes()
                ->where('article_id', $ligneReception->article_id)
                ->first();

            if ($ligneCommande) {
                $total += $ligneReception->quantity * $ligneCommande->unit_price;
            }
        }

        return $total;
    }

    /**
     * Calculate total amount for this reception based on received quantities
     */
    public function getTotalAmount()
    {
        $total = $this->getTotalAmountNoDiscount();

        // Apply the same discount percentage as the original commande
        if ($this->commande && $this->commande->remise > 0) {
            $discountAmount = $total * ($this->commande->remise / 100);
            $total = $total - $discountAmount;
        }

        return $total;
    }

    /**
     * Calculate the discount amount applied to this reception
     */
    public function getDiscountAmount()
    {
        $totalNoDiscount = $this->getTotalAmountNoDiscount();

        if ($this->commande && $this->commande->remise > 0) {
            return $totalNoDiscount * ($this->commande->remise / 100);
        }

        return 0;
    }

    /**
     * Get total paid amount for this reception
     */
    public function getTotalPaid()
    {
        return $this->paiements()->sum('montant');
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmount()
    {
        return $this->getTotalAmount() - $this->getTotalPaid();
    }

    /**
     * Check if reception is fully paid
     */
    public function isFullyPaid()
    {
        return $this->getRemainingAmount() <= 0;
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus()
    {
        $remaining = $this->getRemainingAmount();
        $total = $this->getTotalAmount();

        if ($remaining <= 0) {
            return 'PAYE';
        } elseif ($remaining < $total) {
            return 'PARTIEL';
        } else {
            return 'NON_PAYE';
        }
    }
}
