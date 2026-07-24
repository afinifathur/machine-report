<?php

namespace App\Models;

use App\Enums\ProcurementStatus;
use App\Enums\ProcurementUrgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_number',
        'machine_id',
        'item_name',
        'urgency',
        'status',
        'current_owner',
        'description',
        'target_needed_date',
        'vendor_name',
        'po_number',
        'po_date',
        'rack_location',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcurementStatus::class,
            'urgency' => ProcurementUrgency::class,
            'target_needed_date' => 'date',
            'po_date' => 'date',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }
}
