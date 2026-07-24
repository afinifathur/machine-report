<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_case_id',
        'user_id',
        'stage',
        'decision',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ApprovalDecision::class,
        ];
    }

    public function procurementCase(): BelongsTo
    {
        return $this->belongsTo(ProcurementCase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
