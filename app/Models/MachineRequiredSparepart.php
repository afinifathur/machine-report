<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineRequiredSparepart extends Model
{
    protected $fillable = [
        'machine_id',
        'warehouse_item_code',
        'qty_per_machine',
        'lead_time_days',
        'maintenance_criticality',
        'notes',
    ];

    protected $casts = [
        'qty_per_machine' => 'integer',
        'lead_time_days' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
