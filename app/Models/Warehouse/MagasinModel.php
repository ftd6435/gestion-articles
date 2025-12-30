<?php

namespace App\Models\Warehouse;

use App\Models\Stock\LigneReceptionFournisseur;
use App\Models\User;
use App\Models\Ventes\LigneVenteClient;
use Illuminate\Database\Eloquent\Model;

class MagasinModel extends Model
{
    protected $fillable = [
        'code_magasin',
        'nom',
        'localisation',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
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

    /**
     * Scope: Only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope: Only inactive categories
     */
    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function etageres()
    {
        return $this->hasMany(EtagereModel::class, 'magasin_id');
    }

    public function ligneReceptions()
    {
        return $this->hasMany(LigneReceptionFournisseur::class, 'magasin_id');
    }

    public function ligneVentes()
    {
        return $this->hasMany(LigneVenteClient::class, 'magasin_id');
    }
}
