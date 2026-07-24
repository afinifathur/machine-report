<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;      // Admin Maintenance
    protected User $purchasingUser; // Purchasing
    protected User $direkturUser;   // Direktur
    protected Machine $machine;
    protected ProcurementCase $procurement;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles, permissions and helper users
        $this->seed();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        $this->purchasingUser = User::where('email', 'purchasing@peroniks.com')->first() ?? User::factory()->create();
        $this->direkturUser = User::where('email', 'direktur@peroniks.com')->first() ?? User::factory()->create();

        // Assign Spatie roles if not assigned by seeder
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }
        if (!$this->purchasingUser->hasRole('Purchasing')) {
            $this->purchasingUser->assignRole('Purchasing');
        }
        if (!$this->direkturUser->hasRole('Direktur')) {
            $this->direkturUser->assignRole('Direktur');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'MCH-01',
            'name' => 'Test Machine',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
        ]);

        $this->procurement = ProcurementCase::create([
            'case_number' => 'PR-TEST-9988',
            'machine_id' => $this->machine->id,
            'item_name' => 'WPA 80 Reducer',
            'urgency' => 'normal',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Replace reducer gearbox.',
            'target_needed_date' => now()->addDays(3)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('MRM System');
        $response->assertSee('Clinical Precision');
    }

    public function test_login_successful_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'adminmtc@peroniks.com',
            'password' => 'password', // Default password set in DatabaseSeeder
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->adminUser->id, Auth::id());
    }

    public function test_login_failed_with_incorrect_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'adminmtc@peroniks.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    public function test_logout_successful(): void
    {
        $this->actingAs($this->adminUser);
        $this->assertTrue(Auth::check());

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_open_procurements(): void
    {
        $response = $this->get(route('procurements.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_logged_in_user_can_open_dashboard(): void
    {
        $this->actingAs($this->adminUser);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_logged_in_user_can_open_procurements(): void
    {
        $this->actingAs($this->adminUser);
        $response = $this->get(route('procurements.index'));
        $response->assertStatus(200);
    }

    public function test_purchasing_cannot_approve_stage_1(): void
    {
        $this->actingAs($this->purchasingUser);

        $response = $this->post(route('procurements.approve-stage-1', $this->procurement->id), [
            'note' => 'I am purchasing trying to approve stage 1'
        ]);

        $response->assertStatus(403);
    }

    public function test_direktur_cannot_input_po(): void
    {
        // Set state to processing so input-po is logical state, but direktur role is unauthorized
        $this->procurement->update([
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing'
        ]);

        $this->actingAs($this->direkturUser);

        $response = $this->post(route('procurements.input-po', $this->procurement->id), [
            'po_number' => 'PO-FAKE-999',
            'vendor_name' => 'Direktur Vendor',
            'po_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    }
}
