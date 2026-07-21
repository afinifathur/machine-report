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
        'production_area_id',
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
     * Get machine documents (legacy upload support).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MachineDocument::class);
    }

    /**
     * Get machine document links (Library ISO references).
     */
    public function documentLinks(): HasMany
    {
        return $this->hasMany(MachineDocumentLink::class)->orderBy('sort_order', 'asc')->latest('created_at');
    }

    /**
     * Get machine photos.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(MachinePhoto::class)->orderBy('sort_order', 'asc')->latest('created_at');
    }

    /**
     * Get the production area.
     */
    public function productionArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasterProductionArea::class, 'production_area_id');
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
     * Check if machine optional physical details are complete.
     */
    public function getHasIdentitasAttribute(): bool
    {
        return !empty($this->manufacturer) &&
               !empty($this->model) &&
               !empty($this->vendor) &&
               !empty($this->serial_number) &&
               !empty($this->installation_date) &&
               !empty($this->commissioning_date);
    }

    /**
     * Check if machine has components.
     */
    public function getHasComponentsAttribute(): bool
    {
        return $this->components()->exists();
    }

    /**
     * Check if machine has any photo uploaded.
     */
    public function getHasPhotoAttribute(): bool
    {
        return $this->photos()->whereNotNull('file_path')->where('file_path', '!=', '')->exists();
    }

    /**
     * Check if machine has document links or legacy documents.
     */
    public function getHasManualAttribute(): bool
    {
        return $this->documentLinks()->exists() || $this->documents()->whereNotNull('file_name')->where('file_name', '!=', '')->exists();
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
     * Get completion progress of passport checklist.
     */
    public function getCompletionProgressAttribute(): array
    {
        $checklist = [
            'identitas' => $this->has_identitas,
            'sparepart' => $this->has_spareparts,
            'manual_book' => $this->has_manual,
            'foto' => $this->has_photo,
            'qr' => $this->has_qr,
            'komponen' => $this->has_components,
        ];

        $completed = count(array_filter($checklist));
        $total = count($checklist);

        return [
            'checklist' => $checklist,
            'completed' => $completed,
            'total' => $total,
        ];
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
