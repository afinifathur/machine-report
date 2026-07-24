<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser; // Admin Maintenance
    protected User $kabagUser;  // Kabag Maintenance
    protected User $direkturUser; // Direktur
    protected User $purchasingUser; // Purchasing
    protected User $sparepartUser; // Admin Sparepart
    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run seeders to setup roles & permissions and mock machine data
        $this->seed();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        $this->kabagUser = User::where('email', 'kabagmtc@peroniks.com')->first() ?? User::factory()->create();
        $this->direkturUser = User::where('email', 'direktur@peroniks.com')->first() ?? User::factory()->create();
        $this->purchasingUser = User::where('email', 'purchasing@peroniks.com')->first() ?? User::factory()->create();
        $this->sparepartUser = User::where('email', 'adminsp@peroniks.com')->first() ?? User::factory()->create();

        // Fallback role assignment if seeder did not assign them
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }
        if (!$this->kabagUser->hasRole('Kabag Maintenance')) {
            $this->kabagUser->assignRole('Kabag Maintenance');
        }
        if (!$this->direkturUser->hasRole('Direktur')) {
            $this->direkturUser->assignRole('Direktur');
        }
        if (!$this->purchasingUser->hasRole('Purchasing')) {
            $this->purchasingUser->assignRole('Purchasing');
        }
        if (!$this->sparepartUser->hasRole('Admin Sparepart')) {
            $this->sparepartUser->assignRole('Admin Sparepart');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'TEST-01',
            'name' => 'Conveyor Mixer',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
            'manufacturer' => 'Siemens',
            'model' => 'Model A',
            'serial_number' => 'SN-112233',
            'installation_date' => '2026-01-01',
            'commissioning_date' => '2026-01-01',
            'vendor' => 'Vendor A',
        ]);
    }

    /**
     * Helper to create a case at a specific status.
     */
    protected function createCaseAtStatus(ProcurementStatus $status, string $owner): ProcurementCase
    {
        return ProcurementCase::create([
            'case_number' => 'PR-' . now()->format('Ymd') . '-' . sprintf('%04d', rand(1, 9999)),
            'machine_id' => $this->machine->id,
            'item_name' => 'Gearbox Reducer WPA 80',
            'urgency' => 'urgent',
            'status' => $status,
            'current_owner' => $owner,
            'description' => 'Torsional shaft fracture.',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);
    }

    /*
    ============================================================================
    HAPPY PATHS (END-TO-END WORKFLOW)
    ============================================================================
    */

    public function test_full_workflow_happy_path(): void
    {
        // 1. DRAFT is created
        $case = $this->createCaseAtStatus(ProcurementStatus::DRAFT, 'Admin Maintenance');

        // 2. Submit to Kabag
        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.submit', $case->id));
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::PENDING_KABAG, $case->fresh()->status);
        $this->assertEquals('Kabag Maintenance', $case->fresh()->current_owner);

        // 3. Kabag Approves (Stage 1) -> PENDING_DIR
        $this->actingAs($this->kabagUser);
        $response = $this->post(route('procurements.approve-stage-1', $case->id), ['note' => 'Technical spec is valid.']);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::PENDING_DIR, $case->fresh()->status);
        $this->assertEquals('Direktur', $case->fresh()->current_owner);

        // 4. Direktur Approves (Stage 2) -> PROCESSING
        $this->actingAs($this->direkturUser);
        $response = $this->post(route('procurements.approve-stage-2', $case->id), ['note' => 'Budget approved.']);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::PROCESSING, $case->fresh()->status);
        $this->assertEquals('Purchasing', $case->fresh()->current_owner);

        // 5. Purchasing inputs PO -> WAITING_DELIVERY
        $this->actingAs($this->purchasingUser);
        $response = $this->post(route('procurements.input-po', $case->id), [
            'po_number' => 'PO-2026-9901',
            'vendor_name' => 'PT. Delta Motor',
            'po_date' => now()->toDateString(),
        ]);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::WAITING_DELIVERY, $case->fresh()->status);
        $this->assertEquals('Purchasing', $case->fresh()->current_owner);
        $this->assertEquals('PO-2026-9901', $case->fresh()->po_number);

        // 6. Admin Sparepart confirms arrival -> READY_TO_PICKUP
        $this->actingAs($this->sparepartUser);
        $response = $this->post(route('procurements.confirm-arrival', $case->id), [
            'rack_location' => 'RAK-B-04',
        ]);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::READY_TO_PICKUP, $case->fresh()->status);
        $this->assertEquals('Admin Maintenance', $case->fresh()->current_owner);
        $this->assertEquals('RAK-B-04', $case->fresh()->rack_location);

        // 7. Admin Maintenance confirms pickup -> CLOSED
        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.confirm-pickup', $case->id));
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::CLOSED, $case->fresh()->status);
        $this->assertEquals('None', $case->fresh()->current_owner);
    }

    public function test_return_for_information_and_update_information_path(): void
    {
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_KABAG, 'Kabag Maintenance');

        // Kabag requests more info
        $this->actingAs($this->kabagUser);
        $response = $this->post(route('procurements.return-information', $case->id), [
            'note' => 'Please upload a clearer nameplate photo.'
        ]);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::NEED_INFO, $case->fresh()->status);
        $this->assertEquals('Admin Maintenance', $case->fresh()->current_owner);

        // Admin Maintenance updates and resubmits
        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.update-information', $case->id), [
            'machine_id' => $this->machine->id,
            'item_name' => 'WPA 80 Reducer (Verified)',
            'urgency' => 'urgent',
            'target_needed_date' => now()->addDays(3)->toDateString(),
            'description' => 'Updated with proper nameplate serial number SN-WPA-9912.',
        ]);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::PENDING_KABAG, $case->fresh()->status);
        $this->assertEquals('Kabag Maintenance', $case->fresh()->current_owner);
        $this->assertEquals('WPA 80 Reducer (Verified)', $case->fresh()->item_name);
    }

    public function test_cancel_request_path(): void
    {
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_DIR, 'Direktur');

        // Direktur decides to cancel the request
        $this->actingAs($this->direkturUser);
        $response = $this->post(route('procurements.cancel', $case->id), [
            'reason' => 'Emergency conveyor is already repaired by internal crew.'
        ]);
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::CANCELLED, $case->fresh()->status);
        $this->assertEquals('None', $case->fresh()->current_owner);
    }

    /*
    ============================================================================
    NEGATIVE TEST CASES (BUSINESS CONSTRAINTS)
    ============================================================================
    */

    public function test_negative_approve_stage_1_when_not_pending_kabag(): void
    {
        // Status is PENDING_DIR, not PENDING_KABAG
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_DIR, 'Direktur');

        $this->actingAs($this->kabagUser);
        $response = $this->post(route('procurements.approve-stage-1', $case->id), ['note' => 'Approving anyway.']);
        
        // Fails authorization / status check
        $response->assertStatus(403);
    }

    public function test_negative_approve_stage_2_by_unauthorized_user(): void
    {
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_DIR, 'Direktur');

        // Admin Maintenance attempts to approve Stage 2
        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.approve-stage-2', $case->id), ['note' => 'Stealing approval.']);

        $response->assertStatus(403);
    }

    public function test_negative_return_for_information_without_comment(): void
    {
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_KABAG, 'Kabag Maintenance');

        $this->actingAs($this->kabagUser);
        $response = $this->post(route('procurements.return-information', $case->id), [
            'note' => '' // Missing/empty comment
        ]);

        $response->assertSessionHasErrors(['note']);
        $this->assertEquals(ProcurementStatus::PENDING_KABAG, $case->fresh()->status);
    }

    public function test_negative_cancel_when_already_closed(): void
    {
        $case = $this->createCaseAtStatus(ProcurementStatus::CLOSED, 'None');

        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.cancel', $case->id), [
            'reason' => 'Cancel Closed Case.'
        ]);

        // Fails policy / state transition checks
        $response->assertStatus(403);
        $this->assertEquals(ProcurementStatus::CLOSED, $case->fresh()->status);
    }

    public function test_negative_confirm_pickup_before_ready_to_pickup(): void
    {
        // Status is WAITING_DELIVERY (not ready to pickup)
        $case = $this->createCaseAtStatus(ProcurementStatus::WAITING_DELIVERY, 'Purchasing');

        $this->actingAs($this->adminUser);
        $response = $this->post(route('procurements.confirm-pickup', $case->id));

        $response->assertStatus(403);
        $this->assertEquals(ProcurementStatus::WAITING_DELIVERY, $case->fresh()->status);
    }

    public function test_negative_input_po_before_processing(): void
    {
        // Status is PENDING_DIR (still needs Director approval before purchasing can process)
        $case = $this->createCaseAtStatus(ProcurementStatus::PENDING_DIR, 'Direktur');

        $this->actingAs($this->purchasingUser);
        $response = $this->post(route('procurements.input-po', $case->id), [
            'po_number' => 'PO-2026-ERROR',
            'vendor_name' => 'Bad Vendor',
            'po_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
        $this->assertEquals(ProcurementStatus::PENDING_DIR, $case->fresh()->status);
    }
}
