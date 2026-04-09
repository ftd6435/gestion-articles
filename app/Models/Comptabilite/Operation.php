<?php

namespace App\Models\Comptabilite;

use App\Models\DeviseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operation extends Model
{
    use SoftDeletes;

    protected $table = 'operations';

    protected $fillable = [
        'type_operation_id',
        'devise_id',
        'reason',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function typeOperation()
    {
        return $this->belongsTo(TypeOperation::class, 'type_operation_id');
    }

    public function devise()
    {
        return $this->belongsTo(DeviseModel::class, 'devise_id');
    }
}
