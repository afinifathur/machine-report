<?php

namespace App\Models;

use App\Enums\MaintenancePlanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MaintenancePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'maintenance_template_id',
        'scheduled_date',
        'assigned_technician',
        'priority',
        'status',
        'generation_source',
        'notes',
        'type',
        'breakdown_number',
        'reported_at',
        'reported_by',
        'reported_department',
        'completed_at',
        'downtime_duration',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'type' => MaintenancePlanType::class,
        'reported_at' => 'datetime',
        'completed_at' => 'datetime',
        'downtime_duration' => 'integer',
    ];

    /**
     * Get the machine scheduled for maintenance.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    /**
     * Get the maintenance template/package (SOP knowledge) for this plan.
     */
    public function maintenanceTemplate(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTemplate::class, 'maintenance_template_id');
    }

    /**
     * Alias for maintenanceTemplate.
     */
    public function pmTemplate(): BelongsTo
    {
        return $this->maintenanceTemplate();
    }

    /**
     * Get the execution record for this plan, if any.
     */
    public function execution()
    {
        return $this->hasOne(MaintenanceExecution::class, 'maintenance_plan_id');
    }

    /**
     * Get spareparts consumed in the execution of this plan.
     */
    public function executionSpareparts(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaintenanceExecutionSparepart::class,
            MaintenanceExecution::class,
            'maintenance_plan_id',
            'execution_id',
            'id',
            'id'
        );
    }

    /**
     * Scope to filter PM plans.
     */
    public function scopePreventive($query)
    {
        return $query->where('type', MaintenancePlanType::PM);
    }

    /**
     * Scope to filter Corrective plans.
     */
    public function scopeCorrective($query)
    {
        return $query->where('type', MaintenancePlanType::CORRECTIVE);
    }

    /**
     * Scope to filter active breakdowns.
     */
    public function scopeActiveBreakdowns($query)
    {
        return $query->where('type', MaintenancePlanType::CORRECTIVE)
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Check if plan is preventive.
     */
    public function isPreventive(): bool
    {
        return $this->type === MaintenancePlanType::PM;
    }

    /**
     * Check if plan is corrective.
     */
    public function isCorrective(): bool
    {
        return $this->type === MaintenancePlanType::CORRECTIVE;
    }
}

