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
        'target_completion',
        'actual_completion',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'replacement_id',
        'delay_reason',
        'delay_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'type' => MaintenancePlanType::class,
        'reported_at' => 'datetime',
        'completed_at' => 'datetime',
        'downtime_duration' => 'integer',
        'target_completion' => 'datetime',
        'actual_completion' => 'datetime',
        'delay_reason' => 'string',
        'delay_notes' => 'string',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            if (empty($plan->target_completion)) {
                $startDate = $plan->scheduled_date ? \Carbon\Carbon::parse($plan->scheduled_date) : now();
                $baseStart = $startDate->copy()->setTime(8, 0, 0);

                $duration = 120; // Default fallback for corrective (120 minutes)
                if ($plan->type && $plan->type->value === 'pm' && $plan->maintenanceTemplate) {
                    $duration = $plan->maintenanceTemplate->estimated_duration ?? 120;
                }

                $plan->target_completion = $baseStart->addMinutes($duration);
            }
        });
    }

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

    /**
     * Get the user who cancelled this plan.
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get the replacement plan for this cancelled plan.
     */
    public function replacementPlan(): BelongsTo
    {
        return $this->belongsTo(MaintenancePlan::class, 'replacement_id');
    }

    /**
     * Check if this plan has been cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if this plan can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'reported', 'assigned']) && !$this->relationLoaded('execution') ? !$this->execution()->exists() : !$this->execution;
    }

    /**
     * Get the formatted work order number.
     */
    public function getWorkOrderNumberAttribute(): string
    {
        $dateStr = $this->scheduled_date ? $this->scheduled_date->format('Ymd') : now()->format('Ymd');
        $typeStr = $this->isPreventive() ? 'PM' : 'CM';
        $seqStr = str_pad($this->id, 5, '0', STR_PAD_LEFT);
        return "{$typeStr}-{$dateStr}-{$seqStr}";
    }
}

