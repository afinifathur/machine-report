<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineDocument extends Model
{
    protected $fillable = [
        'machine_id',
        'type',
        'file_name',
        'file_path',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($this->file_path)) {
            return '0 B';
        }
        
        $bytes = \Illuminate\Support\Facades\Storage::disk('public')->size($this->file_path);
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        
        return $bytes . ' B';
    }

    /**
     * Get the formatted upload date in Indonesian.
     */
    public function getFormattedUploadDateAttribute(): string
    {
        if (!$this->updated_at) {
            return '-';
        }
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $date = $this->updated_at;
        return $date->format('j') . ' ' . $months[$date->format('n')] . ' ' . $date->format('Y');
    }
}
