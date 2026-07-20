<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterProductionArea;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RealMachineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealMachineSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_machine_seeder_runs_successfully_and_is_idempotent(): void
    {
        // 1. Run seeder first time
        $this->seed(RealMachineSeeder::class);

        $totalCount = Machine::count();
        $this->assertGreaterThan(0, $totalCount);

        // Verify no duplicate codes
        $distinctCodesCount = Machine::distinct()->count('code');
        $this->assertEquals($totalCount, $distinctCodesCount, 'Machine codes must be unique and have no duplicates.');

        // 2. Run seeder second time to verify idempotency
        $this->seed(RealMachineSeeder::class);
        $this->assertEquals($totalCount, Machine::count(), 'Seeder must be idempotent and not create duplicates on rerun.');

        // 3. Verify Production Areas were created correctly
        $this->assertGreaterThan(0, MasterProductionArea::count());
        $this->assertDatabaseHas('master_production_areas', ['name' => 'BAHAN BAKU']);
        $this->assertDatabaseHas('master_production_areas', ['name' => 'COR FLANGE']);
        $this->assertDatabaseHas('master_production_areas', ['name' => 'NETTO FLANGE']);
        $this->assertDatabaseHas('master_production_areas', ['name' => 'UMUM']);

        // 4. Verify category automatic classification
        $press = Machine::where('code', 'A-PS.01')->first();
        $this->assertNotNull($press);
        $this->assertEquals('Press', $press->category);
        $this->assertEquals('BAHAN BAKU', $press->production_area);
        $this->assertNotNull($press->production_area_id);

        $bubut = Machine::where('code', 'N-BC.80')->first();
        $this->assertNotNull($bubut);
        $this->assertEquals('Lathe', $bubut->category);

        $ballMill = Machine::where('code', 'Y-BA.01')->first();
        $this->assertNotNull($ballMill);
        $this->assertEquals('BALL MILL 01', $ballMill->name);
        $this->assertEquals('UMUM', $ballMill->production_area);

        // 5. Verify every machine is ACTIVE and is_active is true
        $inactiveCount = Machine::where('lifecycle_status', '!=', 'ACTIVE')->orWhere('is_active', false)->count();
        $this->assertEquals(0, $inactiveCount, 'Every machine seeded by RealMachineSeeder must be ACTIVE.');

        // 6. Verify Machine Passport loads correctly for seeded machine
        $response = $this->get(route('machines.show', 'A-PS.01'));
        $response->assertStatus(200);
        $response->assertSee('PRESS 1');
        $response->assertSee('A-PS.01');
    }

    public function test_production_seeder_executes_successfully(): void
    {
        // Seed both DatabaseSeeder and RealMachineSeeder
        $this->seed(DatabaseSeeder::class);
        $this->seed(RealMachineSeeder::class);

        $this->assertGreaterThan(0, Machine::count());
        $this->assertDatabaseHas('machines', ['code' => 'Y-BA.01']);
        $this->assertDatabaseHas('machines', ['code' => 'A-PS.01']);
    }
}
