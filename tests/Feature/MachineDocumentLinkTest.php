<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineDocumentLink;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Models\MasterProductionArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineDocumentLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@mrm.local',
        ]);

        $dept = MasterDepartment::create([
            'code' => 'MACHINING',
            'name' => 'Machining',
            'sort_order' => 10,
        ]);

        $cat = MasterMachineCategory::create([
            'code' => 'CNC',
            'name' => 'CNC',
            'sort_order' => 10,
        ]);

        $area = MasterProductionArea::create([
            'code' => 'MACHINING',
            'name' => 'Machining Area',
            'sort_order' => 10,
        ]);

        $this->machine = Machine::create([
            'code' => 'CNC-TEST-01',
            'name' => 'CNC Test Machine 01',
            'department' => 'MACHINING',
            'category' => 'CNC',
            'production_area' => 'MACHINING',
            'production_area_id' => $area->id,
            'criticality' => 'A',
            'operational_status' => 'running',
            'is_active' => true,
        ]);
    }

    /**
     * Test fetching linked documents index.
     */
    public function test_can_fetch_document_links_index(): void
    {
        $this->actingAs($this->user);

        MachineDocumentLink::create([
            'machine_id' => $this->machine->id,
            'title' => 'Manual Fanuc Oi-TF',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
            'description' => 'Official operational manual',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson(route('machines.documents.index', $this->machine->code));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('total_count', 1);
        $response->assertJsonPath('links.0.title', 'Manual Fanuc Oi-TF');
        $response->assertJsonPath('links.0.library_url', 'https://library.peroniks.id/documents/381');
    }

    /**
     * Test adding a document link.
     */
    public function test_can_add_document_link(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => 'Wiring Diagram Fanuc',
            'document_category' => 'electrical',
            'library_url' => 'https://library.peroniks.id/documents/102',
            'description' => 'Electrical schematic Rev 2',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('link.title', 'Wiring Diagram Fanuc');

        $this->assertDatabaseHas('machine_document_links', [
            'machine_id' => $this->machine->id,
            'title' => 'Wiring Diagram Fanuc',
            'document_category' => 'electrical',
            'library_url' => 'https://library.peroniks.id/documents/102',
        ]);

        // Verify checklist manual progress becomes completed
        $this->assertTrue($this->machine->fresh()->has_manual);
    }

    /**
     * Test editing a document link.
     */
    public function test_can_edit_document_link(): void
    {
        $this->actingAs($this->user);

        $link = MachineDocumentLink::create([
            'machine_id' => $this->machine->id,
            'title' => 'Old Title',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/500',
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson(route('machines.documents.update', [$this->machine->code, $link->id]), [
            'title' => 'Updated Title Fanuc',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/500',
            'description' => 'Updated description text',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('link.title', 'Updated Title Fanuc');

        $this->assertDatabaseHas('machine_document_links', [
            'id' => $link->id,
            'title' => 'Updated Title Fanuc',
            'description' => 'Updated description text',
        ]);
    }

    /**
     * Test deleting a document link.
     */
    public function test_can_delete_document_link(): void
    {
        $this->actingAs($this->user);

        $link = MachineDocumentLink::create([
            'machine_id' => $this->machine->id,
            'title' => 'PLC Backup Ladder',
            'document_category' => 'plc',
            'library_url' => 'https://library.peroniks.id/documents/999',
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson(route('machines.documents.destroy', [$this->machine->code, $link->id]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseMissing('machine_document_links', [
            'id' => $link->id,
        ]);

        $this->assertFalse($this->machine->fresh()->has_manual);
    }

    /**
     * Test URL validation and required fields.
     */
    public function test_validates_url_format_and_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => '',
            'document_category' => '',
            'library_url' => 'invalid-url-string',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'document_category', 'library_url']);
    }

    /**
     * Test duplicate URL prevention for the same machine.
     */
    public function test_prevents_duplicate_url_per_machine(): void
    {
        $this->actingAs($this->user);

        MachineDocumentLink::create([
            'machine_id' => $this->machine->id,
            'title' => 'Document 1',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => 'Document 2 Duplicate',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['library_url']);
    }

    /**
     * Test rendering empty state in machine passport view.
     */
    public function test_machine_passport_renders_empty_state_when_no_document_links_exist(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('machines.show', $this->machine->code));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Dokumen Yang Dihubungkan');
        $response->assertSee('Seluruh dokumen teknis dikelola melalui Library ISO.');
    }
}
