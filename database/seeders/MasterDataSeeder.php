<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Models\MasterProductionArea;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default User
        if (!User::where('email', 'admin@mrm.local')->exists()) {
            User::create([
                'name' => 'System Executive',
                'email' => 'admin@mrm.local',
                'password' => bcrypt('password'),
            ]);
        }

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
            MasterDepartment::updateOrCreate(['code' => $d['code']], $d);
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
            MasterMachineCategory::updateOrCreate(['code' => $c['code']], $c);
        }

        // Seed Master Production Areas
        $areas = [
            ['code' => 'MACHINING', 'name' => 'Machining', 'sort_order' => 10],
            ['code' => 'ASSEMBLY', 'name' => 'Assembly', 'sort_order' => 20],
            ['code' => 'MAINTENANCE', 'name' => 'Maintenance', 'sort_order' => 30],
            ['code' => 'FOUNDRY', 'name' => 'Foundry', 'sort_order' => 40],
            ['code' => 'WAREHOUSE', 'name' => 'Warehouse', 'sort_order' => 50],
            ['code' => 'QC', 'name' => 'QC', 'sort_order' => 60],
            ['code' => 'PPIC', 'name' => 'PPIC', 'sort_order' => 70],
            ['code' => 'UTILITY', 'name' => 'Utility', 'sort_order' => 80],
        ];
        foreach ($areas as $a) {
            MasterProductionArea::updateOrCreate(['code' => $a['code']], $a);
        }
    }
}
