<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MachinePhoto extends Model
{
    protected $fillable = [
        'machine_id',
        'title',
        'photo_type',
        'type',
        'description',
        'file_name',
        'file_path',
        'uploaded_by',
        'taken_at',
        'sort_order',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'thumbnail_url',
        'full_url',
        'category_label',
        'formatted_upload_date',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get category display label.
     */
    public function getCategoryLabelAttribute(): string
    {
        $type = $this->photo_type ?: $this->type;
        return match ($type) {
            'reference', 'overall' => 'Reference',
            'name_plate' => 'Name Plate',
            'inspection', 'electrical_cabinet', 'hydraulic_unit', 'control_panel' => 'Inspection',
            'breakdown', 'before_repair' => 'Breakdown',
            'repair', 'after_repair' => 'Repair',
            default => 'Other',
        };
    }

    /**
     * Get asset URL for thumbnail image.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }

        // Check if thumbnail exists under /thumbs/
        $dir = dirname($this->file_path);
        $filename = basename($this->file_path);
        $thumbRelPath = $dir . '/thumbs/' . $filename;

        if (Storage::disk('public')->exists($thumbRelPath)) {
            return asset('storage/' . $thumbRelPath);
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Get asset URL for full size compressed image.
     */
    public function getFullUrlAttribute(): string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : '';
    }

    /**
     * Get formatted upload date string.
     */
    public function getFormattedUploadDateAttribute(): string
    {
        $date = $this->created_at ?: $this->taken_at;
        return $date ? $date->format('d M Y') : '-';
    }
}
