<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Services\ProcurementWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProcurementCaseController extends Controller
{
    use AuthorizesRequests;

    protected ProcurementWorkflowService $workflowService;

    public function __construct(ProcurementWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', ProcurementCase::class);

        $cases = ProcurementCase::with(['machine', 'creator'])
            ->latest()
            ->paginate(20);

        return view('procurements.index', compact('cases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ProcurementCase::class);

        $machines = Machine::orderBy('name')->get();

        return view('procurements.create', compact('machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ProcurementCase::class);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_name' => 'required|string|max:255',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'target_needed_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $user = auth()->user() ?? \App\Models\User::first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'User session not found.']);
        }

        $case = $this->workflowService->createDraft($validated, $user);

        return redirect()->route('procurements.show', $case->id)
            ->with('success', 'Draft pengadaan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcurementCase $procurement)
    {
        $this->authorize('view', $procurement);

        return view('procurements.show', compact('procurement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProcurementCase $procurement)
    {
        $this->authorize('update', $procurement);

        $machines = Machine::orderBy('name')->get();

        return view('procurements.edit', compact('procurement', 'machines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('update', $procurement);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_name' => 'required|string|max:255',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'target_needed_date' => 'required|date',
            'description' => 'required|string',
        ]);

        try {
            $this->workflowService->updateDraft($procurement, $validated);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Draft pengadaan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProcurementCase $procurement)
    {
        $this->authorize('delete', $procurement);

        try {
            $this->workflowService->deleteDraft($procurement);
            return redirect()->route('procurements.index')
                ->with('success', 'Draft pengadaan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit the procurement case to Kabag Maintenance.
     */
    public function submit(ProcurementCase $procurement)
    {
        $this->authorize('submit', $procurement);

        try {
            $this->workflowService->submit($procurement);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Pengadaan berhasil diajukan ke Kabag Maintenance.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve Stage 1 (Kabag).
     */
    public function approveStage1(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('approveStage1', $procurement);

        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->approveStage1($procurement, $user, $validated['note'] ?? null);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Persetujuan Stage 1 (Kabag) berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve Stage 2 (Direktur).
     */
    public function approveStage2(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('approveStage2', $procurement);

        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->approveStage2($procurement, $user, $validated['note'] ?? null);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Persetujuan Stage 2 (Direktur) berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Return for information.
     */
    public function returnInformation(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('returnForInformation', $procurement);

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->returnForInformation($procurement, $user, $validated['note']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Permintaan berhasil dikembalikan untuk informasi tambahan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update information (after NEED_INFO status).
     */
    public function updateInformation(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('updateInformation', $procurement);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_name' => 'required|string|max:255',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'target_needed_date' => 'required|date',
            'description' => 'required|string',
        ]);

        try {
            $this->workflowService->updateInformation($procurement, $validated);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Informasi pengadaan diperbarui dan diajukan ulang.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Input PO.
     */
    public function inputPo(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('inputPO', $procurement);

        $validated = $request->validate([
            'po_number' => 'required|string|max:255',
            'vendor_name' => 'required|string|max:255',
            'po_date' => 'required|date',
        ]);

        try {
            $this->workflowService->inputPO($procurement, $validated['po_number'], $validated['vendor_name'], $validated['po_date']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Informasi PO berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm Arrival.
     */
    public function confirmArrival(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('confirmArrival', $procurement);

        $validated = $request->validate([
            'rack_location' => 'required|string|max:255',
        ]);

        try {
            $this->workflowService->confirmArrival($procurement, $validated['rack_location']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Penerimaan barang berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm Pickup.
     */
    public function confirmPickup(ProcurementCase $procurement)
    {
        $this->authorize('confirmPickup', $procurement);

        try {
            $this->workflowService->confirmPickup($procurement);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Pengambilan barang dikonfirmasi. Workflow selesai.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel.
     */
    public function cancel(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('cancel', $procurement);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->cancel($procurement, $validated['reason'], $user);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Permintaan pengadaan telah dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
