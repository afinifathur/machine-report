<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Enums\MaintenancePlanType;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Test that System Administrator has bypass access to everything.
     */
    public function test_system_administrator_bypasses_all_policies()
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin);

        // Can access dashboard
        $this->get(route('dashboard'))->assertStatus(200);

        // Can access machines index
        $this->get(route('machines.index'))->assertStatus(200);

        // Can access create machine page
        $this->get(route('machines.create'))->assertStatus(200);

        // Can access administration panel
        $this->get(route('admin.index'))->assertStatus(200);
    }

    /**
     * Test that Director has read-only access to dashboard and machines, but cannot create.
     */
    public function test_director_role_restrictions()
    {
        $director = User::factory()->create();
        $director->assignRole('Director');

        $this->actingAs($director);

        // Can view dashboard and machines list
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('machines.index'))->assertStatus(200);

        // Cannot create machines
        $this->get(route('machines.create'))->assertStatus(403);
        $this->post(route('machines.store'), [])->assertStatus(403);

        // Can access administration panel (due to employee.view)
        $this->get(route('admin.index'))->assertStatus(200);
        $this->post(route('admin.users.store'), [])->assertStatus(403);
    }

    /**
     * Test that Maintenance Manager can assign technicians and verify, but not CRUD machines.
     */
    public function test_maintenance_manager_permissions()
    {
        $manager = User::factory()->create();
        $manager->assignRole('Maintenance Manager');

        $this->actingAs($manager);

        // Can access planning board
        $this->get(route('planning.index'))->assertStatus(200);

        // Cannot create machines
        $this->get(route('machines.create'))->assertStatus(403);

        // Can access administration panel (due to employee.view)
        $this->get(route('admin.index'))->assertStatus(200);
        $this->post(route('admin.users.store'), [])->assertStatus(403);
    }

    /**
     * Test that Maintenance Administrator can create plans and CRUD machines.
     */
    public function test_maintenance_administrator_permissions()
    {
        $adminMtc = User::factory()->create();
        $adminMtc->assignRole('Maintenance Administrator');

        $this->actingAs($adminMtc);

        // Can view machines list and create
        $this->get(route('machines.index'))->assertStatus(200);
        $this->get(route('machines.create'))->assertStatus(200);

        // Can access administration panel (due to employee.view)
        $this->get(route('admin.index'))->assertStatus(200);
        $this->post(route('admin.users.store'), [])->assertStatus(403);
    }

    /**
     * Test that Maintenance Technician has restricted execution permissions.
     */
    public function test_maintenance_technician_permissions()
    {
        $tech = User::factory()->create();
        $tech->assignRole('Maintenance Technician');

        $this->actingAs($tech);

        // Cannot access planning board (view index)
        $this->get(route('planning.index'))->assertStatus(403);

        // Cannot create machines
        $this->get(route('machines.create'))->assertStatus(403);

        // Cannot access administration panel
        $this->get(route('admin.index'))->assertStatus(403);
    }

    /**
     * Test that Warehouse Administrator can access spareparts integration.
     */
    public function test_warehouse_administrator_permissions()
    {
        $whAdmin = User::factory()->create();
        $whAdmin->assignRole('Warehouse Administrator');

        $this->actingAs($whAdmin);

        // Can view spareparts integration page
        $this->get(route('spareparts.index'))->assertStatus(200);

        // Cannot access administration panel
        $this->get(route('admin.index'))->assertStatus(403);
    }

    /**
     * Test that guest users / unauthorized users are completely blocked.
     */
    public function test_unauthorized_users_are_blocked()
    {
        $user = User::factory()->create(); // No role

        $this->actingAs($user);

        $this->get(route('dashboard'))->assertStatus(403);
        $this->get(route('machines.index'))->assertStatus(403);
        $this->get(route('planning.index'))->assertStatus(403);
        $this->get(route('spareparts.index'))->assertStatus(403);
        $this->get(route('admin.index'))->assertStatus(403);
    }
}
