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
     * Get primary photo of the machine based on priority rules.
     */
    public function getPrimaryPhotoAttribute(): ?MachinePhoto
    {
        $photos = $this->photos;

        if ($photos->isEmpty()) {
            return null;
        }

        // 1. Priority 1: photo_type = 'reference'
        $reference = $photos->first(fn($p) => ($p->photo_type ?? '') === 'reference');
        if ($reference) {
            return $reference;
        }

        // 2. Priority 2: Legacy photo (type = 'overall')
        $overall = $photos->first(fn($p) => ($p->type ?? '') === 'overall');
        if ($overall) {
            return $overall;
        }

        // 3. Priority 3: First available photo
        return $photos->first();
    }

    /**
     * Get primary photo URL resolver.
     */
    public function getPrimaryPhotoUrlAttribute(): string
    {
        $photo = $this->primary_photo;
        if (!$photo || !$photo->file_path) {
            return '';
        }

        if (str_starts_with($photo->file_path, 'images/')) {
            return asset($photo->file_path);
        }

        return asset('storage/' . $photo->file_path);
    }

    /**
     * Helper method to get the primary photo instance.
     */
    public function primaryPhoto(): ?MachinePhoto
    {
        return $this->primary_photo;
    }

    /**
     * Helper method to get the primary photo URL.
     */
    public function primaryPhotoUrl(): string
    {
        return $this->primary_photo_url;
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
        if ($this->relationLoaded('photos')) {
            return $this->photos->contains(fn($p) => !empty($p->file_path));
        }
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

    /**
     * Get the latest active breakdown (unfinished corrective plan) for the machine.
     */
    public function activeBreakdown(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MaintenancePlan::class, 'machine_id')
            ->where('type', \App\Enums\MaintenancePlanType::CORRECTIVE)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('reported_at');
    }

    /**
     * Get completed corrective plans ordered newest first.
     */
    public function correctiveHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaintenancePlan::class, 'machine_id')
            ->where('type', \App\Enums\MaintenancePlanType::CORRECTIVE)
            ->where('status', 'completed')
            ->latest('completed_at');
    }
}

