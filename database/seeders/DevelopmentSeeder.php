<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            DummyMachineSeeder::class,
            RealMachineSeeder::class,
            MaintenancePlanSeeder::class,
        ]);
    }
}
