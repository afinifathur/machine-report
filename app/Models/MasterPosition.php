<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPosition extends Model
{
    protected $table = 'master_positions';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}
