<?php

namespace App\Models;

use App\Models\Articles\ArticleModel;
use Illuminate\Database\Eloquent\Model;

class DeviseModel extends Model
{
    protected $fillable = [
        'code',
        'libelle',
        'symbole',
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

    public function articles()
    {
        return $this->hasMany(ArticleModel::class, 'devise_id');
    }
}
