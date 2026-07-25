<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementAttachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'procurement_case_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ProcurementCase::class, 'procurement_case_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
