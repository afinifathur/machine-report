<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceExecutionSparepart extends Model
{
    protected $fillable = [
        'execution_id',
        'warehouse_item_code',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the maintenance execution associated with the sparepart.
     */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(MaintenanceExecution::class, 'execution_id');
    }
}
