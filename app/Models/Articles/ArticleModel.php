<?php

namespace App\Models\Articles;

use App\Models\Category;
use App\Models\DeviseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ArticleModel extends Model
{
    protected $fillable = [
        'reference',
        'category_id',
        'devise_id',
        'designation',
        'description',
        'prix_achat',
        'prix_vente',
        'unite',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function devise()
    {
        return $this->belongsTo(DeviseModel::class, 'devise_id');
    }
}
