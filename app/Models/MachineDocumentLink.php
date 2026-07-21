<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineDocumentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'title',
        'document_category',
        'library_url',
        'description',
        'sort_order',
        'created_by',
    ];

    /**
     * Get the machine that owns the document link.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the user who registered this document link.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor for human-friendly category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->document_category) {
            'manual_book', 'manual' => 'Manual',
            'electrical_diagram', 'electrical' => 'Electrical',
            'hydraulic_diagram', 'hydraulic' => 'Hydraulic',
            'pneumatic_diagram', 'pneumatic' => 'Pneumatic',
            'plc_backup', 'plc' => 'PLC',
            'parameter_backup', 'parameter' => 'Parameter',
            'certificate' => 'Certificate',
            'vendor_document', 'vendor' => 'Vendor',
            default => ucfirst($this->document_category ?: 'Other'),
        };
    }
}
