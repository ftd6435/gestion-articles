<?php

namespace App\Models\Ventes;

use App\Models\ClientModel;
use App\Models\DeviseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class VenteModel extends Model
{
    protected $fillable = [
        'reference',
        'client_id',
        'devise_id',
        'taux',
        'remise',
        'date_facture',
        'type_vente',
        'status',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // A facture belongs to a client
    public function client()
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    // A facture belongs to a devise
    public function devise()
    {
        return $this->belongsTo(DeviseModel::class, 'devise_id');
    }

    public function ligneVentes()
    {
        return $this->hasMany(LigneVenteClient::class, 'vente_id');
    }

    // User who created the facture
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // User who last updated the facture
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function paiements()
    {
        return $this->hasMany(VentePaiementClient::class, 'vente_id');
    }
    /**
     * Calculate total amount after discount
     */
    public function totalAfterRemise()
    {
        $subtotal = $this->ligneVentes->sum(function ($ligne) {
            return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
        });

        $discountAmount = $subtotal * ($this->remise / 100);

        return $subtotal - $discountAmount;
    }

    /**
     * Get subtotal (before discount)
     */
    public function subTotal()
    {
        return $this->ligneVentes->sum(function ($ligne) {
            return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
        });
    }

    /**
     * Get discount amount
     */
    public function discountAmount()
    {
        return $this->subTotal() * ($this->remise / 100);
    }

    /**
     * Get total paid amount
     */
    public function totalPaid()
    {
        return $this->paiements()->sum('montant');
    }

    /**
     * Get remaining amount
     */
    public function remainingAmount()
    {
        return max(0, $this->totalAfterRemise() - $this->totalPaid());
    }
}
