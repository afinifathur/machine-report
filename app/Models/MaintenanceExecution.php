<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceExecution extends Model
{
    protected $fillable = [
        'maintenance_plan_id',
        'machine_id',
        'operator_name',
        'started_at',
        'completed_at',
        'overall_score',
        'notes',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'overall_score' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MaintenanceExecutionAnswer::class, 'execution_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MaintenanceExecutionPhoto::class, 'execution_id');
    }

    public function spareparts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaintenanceExecutionSparepart::class, 'execution_id');
    }
}

