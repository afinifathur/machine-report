<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default User
        User::firstOrCreate(
            ['email' => 'admin@mrm.local'],
            [
                'name' => 'System Executive',
                'password' => bcrypt('password'),
            ]
        );

        // Seed Master Departments
        $depts = [
            ['code' => 'MACHINING', 'name' => 'Machining', 'sort_order' => 10],
            ['code' => 'MAINTENANCE', 'name' => 'Maintenance', 'sort_order' => 20],
            ['code' => 'ASSEMBLY', 'name' => 'Assembly Center', 'sort_order' => 30],
            ['code' => 'WAREHOUSE', 'name' => 'Warehouse', 'sort_order' => 40],
            ['code' => 'QC', 'name' => 'QC', 'sort_order' => 50],
            ['code' => 'PPIC', 'name' => 'PPIC', 'sort_order' => 60],
        ];
        foreach ($depts as $d) {
            \App\Models\MasterDepartment::firstOrCreate(['code' => $d['code']], $d);
        }

        // Seed Master Machine Categories
        $cats = [
            ['code' => 'CNC', 'name' => 'CNC', 'sort_order' => 10],
            ['code' => 'LATHE', 'name' => 'Lathe', 'sort_order' => 20],
            ['code' => 'MILLING', 'name' => 'Milling', 'sort_order' => 30],
            ['code' => 'DRILLING', 'name' => 'Drilling', 'sort_order' => 40],
            ['code' => 'PUMP', 'name' => 'Pump', 'sort_order' => 50],
            ['code' => 'COMPRESSOR', 'name' => 'Compressor', 'sort_order' => 60],
            ['code' => 'ROBOT', 'name' => 'Robot', 'sort_order' => 70],
            ['code' => 'PRESS', 'name' => 'Press', 'sort_order' => 80],
        ];
        foreach ($cats as $c) {
            \App\Models\MasterMachineCategory::firstOrCreate(['code' => $c['code']], $c);
        }

        if (app()->environment('testing')) {
            $this->call(DummyMachineSeeder::class);
            $this->call(MaintenancePlanSeeder::class);
        } else {
            $this->call(RealMachineSeeder::class);
        }
    }
}
