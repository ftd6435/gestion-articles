<?php

namespace App\Models;

use App\Models\Stock\CommandeFournisseur;
use Illuminate\Database\Eloquent\Model;

class FournisseurModel extends Model
{
    protected $table = "fournisseur_models";

    protected $fillable = [
        'name',
        'telephone',
        'adresse',
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

    public function commandes()
    {
        return $this->hasMany(CommandeFournisseur::class, 'fournisseur_id');
    }
}
