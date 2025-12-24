<?php

namespace App\Models\Stock;

use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommandeFournisseur extends Model
{
    protected $fillable = [
        'reference',
        'fournisseur_id',
        'devise_id',
        'taux_change',
        'remise',
        'date_commande',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date_commande' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function fournisseur()
    {
        return $this->belongsTo(FournisseurModel::class, 'fournisseur_id');
    }

    public function devise()
    {
        return $this->belongsTo(DeviseModel::class, 'devise_id');
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommandeFournisseur::class, 'commande_id');
    }

    public function receptions()
    {
        return $this->hasMany(ReceptionFournisseur::class, 'commande_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementFournisseur::class, 'commande_id');
    }
}
