<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EmploymentStatus;

class Employee extends Model
{
    protected $fillable = [
        'employee_number',
        'employee_index',
        'employee_code',
        'full_name',
        'department_id',
        'position_id',
        'employment_status',
        'employment_start_date',
        'employment_end_date',
        'is_assignable',
        'primary_skill',
        'level',
        'phone',
        'linked_user_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'employment_status' => EmploymentStatus::class,
        'employment_start_date' => 'date',
        'employment_end_date' => 'date',
        'is_assignable' => 'boolean',
        'employee_index' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function department()
    {
        return $this->belongsTo(MasterDepartment::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(MasterPosition::class, 'position_id');
    }
}
