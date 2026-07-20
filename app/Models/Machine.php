<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'code',
        'name',
        'department',
        'production_area',
        'production_area_id',
        'category',
        'criticality',
        'operational_status',
        'manufacturer',
        'model',
        'serial_number',
        'installation_date',
        'commissioning_date',
        'vendor',
        'qr_code_path',
        'is_active',
        'lifecycle_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'commissioning_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get structural component groups.
     */
    public function components(): HasMany
    {
        return $this->hasMany(MachineComponent::class);
    }

    /**
     * Get mapped warehouse spareparts.
     */
    public function requiredSpareparts(): HasMany
    {
        return $this->hasMany(MachineRequiredSparepart::class);
    }

    /**
     * Get machine documents (e.g. electrical drawings, manuals).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MachineDocument::class);
    }

    /**
     * Get machine photos.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(MachinePhoto::class);
    }

    /**
     * Get the user who registered this machine.
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the master production area.
     */
    public function masterProductionArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasterProductionArea::class, 'production_area_id');
    }

    /**
     * Check if machine has an overall photo uploaded.
     */
    public function getHasPhotoAttribute(): bool
    {
        return $this->photos()->where('type', 'overall')->whereNotNull('file_path')->where('file_path', '!=', '')->exists();
    }

    /**
     * Check if machine has a manual book uploaded.
     */
    public function getHasManualAttribute(): bool
    {
        return $this->documents()->where('type', 'manual_book')->whereNotNull('file_name')->where('file_name', '!=', '')->exists();
    }

    /**
     * Check if machine has a QR code path generated.
     */
    public function getHasQrAttribute(): bool
    {
        return !empty($this->qr_code_path);
    }

    /**
     * Check if machine has required spare parts mapped.
     */
    public function getHasSparepartsAttribute(): bool
    {
        return $this->requiredSpareparts()->exists();
    }

    /**
     * Accessor for simulated health score.
     * Calculated dynamically (not persisted) to support future Reliability Engine.
     */
    public function getHealthScoreAttribute(): int
    {
        return match (strtoupper($this->code)) {
            'CNC-08' => 38,
            'CNC-04' => 42,
            'ARM-12' => 58,
            'PMP-08' => 61,
            'DRL-19' => 85,
            default => match ($this->operational_status) {
                'breakdown' => 35,
                'maintenance' => 60,
                'stopped' => 70,
                'idle' => 80,
                'running' => 95,
                default => 100,
            }
        };
    }
}
