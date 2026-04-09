<?php

namespace App\Models\Comptabilite;

use Illuminate\Database\Eloquent\Model;

class TypeOperation extends Model
{
    protected $table = 'type_operations';

    protected $fillable = [
        'name',
        'nature',
        'description',
    ];

    protected $casts = [
        'nature' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function operations()
    {
        return $this->hasMany(Operation::class, 'type_operation_id');
    }
}
