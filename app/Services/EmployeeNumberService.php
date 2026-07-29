<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class EmployeeNumberService
{
    /**
     * Resolve the next employee index and code for a given employee number.
     *
     * @param string $employeeNumber
     * @param int|null $excludeEmployeeId Exclude specific record when editing
     * @return array Contains ['employee_index' => int, 'employee_code' => string]
     * @throws ValidationException
     */
    public function generateNextCode(string $employeeNumber, ?int $excludeEmployeeId = null): array
    {
        $query = Employee::where('employee_number', $employeeNumber);
        
        if ($excludeEmployeeId) {
            $query->where('id', '!=', $excludeEmployeeId);
        }

        $existing = $query->get();

        // Rule: If another ACTIVE employee already owns employee_number, Reject validation
        $activeExists = $existing->contains(function ($emp) {
            return $emp->employment_status === \App\Enums\EmploymentStatus::ACTIVE;
        });

        if ($activeExists) {
            throw ValidationException::withMessages([
                'employee_number' => "Nomor karyawan {$employeeNumber} sudah digunakan oleh karyawan yang sedang aktif."
            ]);
        }

        if ($existing->isEmpty()) {
            return [
                'employee_index' => 1,
                'employee_code' => $employeeNumber,
            ];
        }

        // If only inactive employees own it, generate next suffix automatically
        $maxIndex = $existing->max('employee_index');
        $nextIndex = $maxIndex + 1;

        return [
            'employee_index' => $nextIndex,
            'employee_code' => $employeeNumber . '.' . $nextIndex,
        ];
    }
}
