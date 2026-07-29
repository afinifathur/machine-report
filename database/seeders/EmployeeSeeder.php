<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterPosition;
use App\Enums\EmploymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Resolve or create MTC Department
        $mtcDepartment = MasterDepartment::firstOrCreate(
            ['code' => 'MTC'],
            [
                'name' => 'Maintenance',
                'is_active' => true,
                'sort_order' => 20
            ]
        );

        // 2. Resolve or create Positions
        $positions = [
            'OPERATOR' => MasterPosition::firstOrCreate(
                ['code' => 'OPR'],
                ['name' => 'Operator / Mekanik', 'is_active' => true, 'sort_order' => 60]
            ),
            'SUPERVISOR' => MasterPosition::firstOrCreate(
                ['code' => 'SPV'],
                ['name' => 'Supervisor', 'is_active' => true, 'sort_order' => 50]
            ),
            'WAKIL SUPERVISOR' => MasterPosition::firstOrCreate(
                ['code' => 'WSPV'],
                ['name' => 'Wakil Supervisor', 'is_active' => true, 'sort_order' => 55]
            ),
            'KEPALA BAGIAN' => MasterPosition::firstOrCreate(
                ['code' => 'KBG'],
                ['name' => 'Kepala Bagian', 'is_active' => true, 'sort_order' => 45]
            ),
        ];

        // 3. Employee raw data
        $employeesData = [
            ['number' => '7319', 'name' => 'EDWIN PERMADI', 'pos' => 'OPERATOR', 'start' => '2022-07-15'],
            ['number' => '7346', 'name' => 'DIKI YOGA SAPUTRA', 'pos' => 'SUPERVISOR', 'start' => '2022-09-05'],
            ['number' => '7376', 'name' => 'MOHAMMAD CHOIRUL HISAMMUDIN', 'pos' => 'OPERATOR', 'start' => '2023-01-20'],
            ['number' => '7400', 'name' => 'RUDI CAHYO SETYONO', 'pos' => 'OPERATOR', 'start' => '2023-05-05'],
            ['number' => '7419', 'name' => 'ERIK KURNIAWAN ARROSYID', 'pos' => 'OPERATOR', 'start' => '2023-07-11'],
            ['number' => '7666', 'name' => 'SUTRISNO', 'pos' => 'OPERATOR', 'start' => '2024-12-23'],
            ['number' => '7697', 'name' => 'SETIYO UTOMO', 'pos' => 'OPERATOR', 'start' => '2025-05-14'],
            ['number' => '7714', 'name' => 'FIRNANDA ARIANTO', 'pos' => 'OPERATOR', 'start' => '2025-07-11'],
            ['number' => '7727', 'name' => 'MUKHAMMAD IRVANI SYAIFULLOH', 'pos' => 'OPERATOR', 'start' => '2025-07-22'],
            ['number' => '7738', 'name' => 'FERI KRISTIAWAN', 'pos' => 'OPERATOR', 'start' => '2025-08-04'],
            ['number' => '7777', 'name' => 'JAMALLUDIN RIZQI', 'pos' => 'OPERATOR', 'start' => '2025-10-07'],
            ['number' => '7779', 'name' => 'SANDY ARYA KUSUMA', 'pos' => 'OPERATOR', 'start' => '2025-10-17'],
            ['number' => '7780', 'name' => 'MUKHAMMAD FATKHUR ROKHMAN', 'pos' => 'OPERATOR', 'start' => '2025-10-20'],
            ['number' => '7781', 'name' => 'PRISGA ALFANTO', 'pos' => 'OPERATOR', 'start' => '2025-10-20'],
            ['number' => '7787', 'name' => 'FIRMAN ARI ARDIANSYAH', 'pos' => 'OPERATOR', 'start' => '2025-11-05'],
            ['number' => '9523', 'name' => 'M. ZUBED AAQIL MUBAROK', 'pos' => 'OPERATOR', 'start' => '2019-04-25'],
            ['number' => '9918', 'name' => 'MOCHAMAD ARIFFIANTO', 'pos' => 'OPERATOR', 'start' => '2020-12-22'],
            ['number' => '9936', 'name' => 'WISNU SETYO PRABOWO', 'pos' => 'OPERATOR', 'start' => '2021-01-11'],
            ['number' => '9951', 'name' => 'ABDULLOH FAQIH', 'pos' => 'OPERATOR', 'start' => '2021-02-01'],
            ['number' => '0402', 'name' => 'ALEX JON WILIS', 'pos' => 'WAKIL SUPERVISOR', 'start' => '2017-03-30'],
            ['number' => '0941', 'name' => 'DENY ROMADHON', 'pos' => 'SUPERVISOR', 'start' => '2023-02-01'],
            ['number' => '0949', 'name' => 'TRI HARDI CAHYONO', 'pos' => 'KEPALA BAGIAN', 'start' => '2025-06-01'],
        ];

        // 4. Seed employees using updateOrCreate
        foreach ($employeesData as $emp) {
            $posModel = $positions[$emp['pos']] ?? $positions['OPERATOR'];
            
            Employee::updateOrCreate(
                [
                    'employee_number' => $emp['number'],
                    'employee_index' => 1
                ],
                [
                    'employee_code' => $emp['number'],
                    'full_name' => $emp['name'],
                    'department_id' => $mtcDepartment->id,
                    'position_id' => $posModel->id,
                    'employment_status' => EmploymentStatus::ACTIVE,
                    'employment_start_date' => Carbon::parse($emp['start']),
                    'employment_end_date' => null,
                    'is_assignable' => true,
                    'primary_skill' => null,
                    'level' => null,
                    'phone' => null,
                    'linked_user_id' => null,
                    'remarks' => null,
                ]
            );
        }
    }
}
