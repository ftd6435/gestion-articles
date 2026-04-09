<?php

namespace App\Models;

use App\Models\Ventes\VenteModel;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
    protected $fillable = [
        'name',
        'telephone',
        'type',
        'email',
        'adresse',
        'status',
        'is_default',
        'image',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_default' => 'boolean',
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

    public function ventes()
    {
        return $this->hasMany(VenteModel::class, 'client_id');
    }

    /**
     * Get the default active client (used for "client inconnu").
     */
    public static function getDefaultClient()
    {
        return self::active()->where('is_default', true)->first();
    }
}
