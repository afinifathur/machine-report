<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceExecution;
use App\Models\MaintenanceExecutionAnswer;
use App\Models\MaintenanceExecutionPhoto;
use App\Services\ImageCompressionService;
use App\Services\Maintenance\DowntimeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

class MaintenanceExecutionController extends Controller
{
    protected ImageCompressionService $compressionService;
    protected DowntimeCalculationService $downtimeService;
    protected \App\Services\Maintenance\MachineHistorySummaryService $historyService;

    // Predefined Operator List to ensure consistency (Version 1)
    protected array $operators = [
        'R. Miller',
        'S. Chen',
        'R. Thompson',
        'M. Fadil',
        'A. Hidayat',
        'B. Setiawan'
    ];

    public function __construct(
        ImageCompressionService $compressionService,
        DowntimeCalculationService $downtimeService,
        \App\Services\Maintenance\MachineHistorySummaryService $historyService
    ) {
        $this->compressionService = $compressionService;
        $this->downtimeService = $downtimeService;
        $this->historyService = $historyService;
    }

    /**
     * QR Entry point scanned from physical machine barcode.
     * Identifies the oldest pending/scheduled maintenance plan and redirects to execution.
     */
    public function qrEntry(string $machineCode)
    {
        $machine = Machine::where('code', $machineCode)->first();
        if (!$machine) {
            abort(404, 'Mesin tidak ditemukan.');
        }

        // Check if there is an active breakdown
        $activeBreakdown = MaintenancePlan::where('machine_id', $machine->id)
            ->where('type', \App\Enums\MaintenancePlanType::CORRECTIVE)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('reported_at')
            ->first();

        if ($activeBreakdown) {
            return redirect()->route('planning.execute', $activeBreakdown->id);
        }

        // Find oldest active pending plan (status: scheduled, approved, waiting_approval, or draft)
        $plan = MaintenancePlan::where('machine_id', $machine->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('scheduled_date', 'asc')
            ->first();

        if (!$plan) {
            return redirect()->route('machines.show', $machine->id)
                ->with('info', 'Tidak ada rencana perawatan aktif yang terjadwal untuk mesin ini.');
        }

        return redirect()->route('planning.execute', $plan->id);
    }

    /**
     * Get dynamic list of technicians merged with hardcoded defaults.
     */
    protected function getOperators(): array
    {
        return \App\Models\Employee::where('employment_status', \App\Enums\EmploymentStatus::ACTIVE)
            ->where('is_assignable', true)
            ->pluck('full_name')
            ->toArray();
    }

    /**
     * Show mobile checklist execution view.
     */
    public function create(MaintenancePlan $plan)
    {
        if ($plan->status === 'completed') {
            return redirect()->route('planning.show', $plan->id)
                ->with('warning', 'Pemeriksaan perawatan untuk rencana ini sudah diselesaikan.');
        }

        $plan->load(['machine', 'maintenanceTemplate.checklists']);
        
        if ($plan->isCorrective()) {
            $plan->load(['machine.requiredSpareparts']);
        }
        
        $operators = $this->getOperators();
        $historyCard = $this->historyService->getSummary($plan->machine);

        return view('planning.execute', compact('plan', 'operators', 'historyCard'));
    }

    /**
     * Store submitted execution checklist and answers.
     */
    public function store(Request $request, MaintenancePlan $plan)
    {
        if ($plan->status === 'completed') {
            return redirect()->route('planning.show', $plan->id)
                ->with('error', 'Rencana perawatan ini sudah selesai.');
        }

        if ($plan->isCorrective()) {
            return $this->storeCorrective($request, $plan);
        }

        // Create validator for PM
        $rules = [
            'operator_name' => 'required|string',
            'started_at' => 'required|date_format:Y-m-d H:i:s',
            'photo' => 'required|image|max:10240', // Max 10MB upload
            'notes' => 'nullable|string',
        ];

        if ($plan->target_completion && now()->gt($plan->target_completion)) {
            $rules['delay_reason'] = 'required|string|in:waiting_sparepart,waiting_production,waiting_vendor,waiting_approval,additional_damage,manpower_shortage,power_failure,other';
            $rules['delay_notes'] = 'required_if:delay_reason,other|nullable|string';
        }

        $validator = Validator::make($request->all(), $rules, [
            'delay_reason.required' => 'Alasan keterlambatan wajib diisi karena melewati target waktu penyelesaian.',
            'delay_notes.required_if' => 'Catatan keterlambatan wajib diisi jika memilih alasan Lainnya.',
        ]);

        // Evaluate answers and apply conditional validation rules for PM
        $validator->after(function ($validator) use ($request, $plan) {
            $answers = $request->input('answers', []);
            $checklistItems = $plan->maintenanceTemplate->checklists;

            foreach ($checklistItems as $item) {
                $ans = $answers[$item->id] ?? null;
                if (!$ans || !isset($ans['score'])) {
                    $validator->errors()->add("answers.{$item->id}.score", "Nilai pemeriksaan untuk '{$item->title}' wajib diisi.");
                    continue;
                }

                $score = (int) $ans['score'];
                if ($score < 1 || $score > 5) {
                    $validator->errors()->add("answers.{$item->id}.score", "Nilai harus di antara 1 dan 5.");
                }

                // Conditional validation: Score 1 requires remarks
                if ($score === 1 && empty(trim($ans['remarks'] ?? ''))) {
                    $validator->errors()->add("answers.{$item->id}.remarks", "Catatan kerusakan wajib diisi jika nilai pemeriksaan bernilai 1.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Process and compress image using ImageCompressionService
            $photoPath = $this->compressionService->compressAndStore($request->file('photo'));

            // 2. Calculate average score
            $answers = $request->input('answers', []);
            $totalScore = 0;
            $count = count($answers);
            foreach ($answers as $ans) {
                $totalScore += (int) $ans['score'];
            }
            $overallScore = $count > 0 ? $totalScore / $count : 5.00;

            // 3. Create execution log (status: waiting_review to preserve lifecycle split)
            $execution = MaintenanceExecution::create([
                'maintenance_plan_id' => $plan->id,
                'machine_id' => $plan->machine_id,
                'operator_name' => $request->input('operator_name'),
                'started_at' => $request->input('started_at'),
                'completed_at' => now(),
                'overall_score' => $overallScore,
                'notes' => $request->input('notes'),
                'status' => 'waiting_review',
            ]);

            // 4. Create photo record
            MaintenanceExecutionPhoto::create([
                'execution_id' => $execution->id,
                'type' => 'general',
                'photo_path' => $photoPath,
            ]);

            // 5. Store checklist answers
            foreach ($answers as $itemId => $ansData) {
                MaintenanceExecutionAnswer::create([
                    'execution_id' => $execution->id,
                    'checklist_item_id' => $itemId,
                    'score' => (int) $ansData['score'],
                    'remarks' => $ansData['remarks'] ?? null,
                ]);
            }

            // 6. Complete the plan so Planning Board updates immediately
            $actualCompletion = now();
            $delayReason = null;
            $delayNotes = null;

            if ($plan->target_completion && $actualCompletion->gt($plan->target_completion)) {
                $delayReason = $request->input('delay_reason');
                $delayNotes = $request->input('delay_notes');
            }

            $plan->update([
                'status' => 'completed',
                'completed_at' => $actualCompletion,
                'actual_completion' => $actualCompletion,
                'delay_reason' => $delayReason,
                'delay_notes' => $delayNotes,
            ]);

            DB::commit();

            return redirect()->route('planning.show', $plan->id)
                ->with('success', 'Laporan perawatan berhasil diserahkan dan disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan laporan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store submitted execution corrective verification.
     */
    protected function storeCorrective(Request $request, MaintenancePlan $plan)
    {
        $rules = [
            'operator_name' => 'required|string',
            'photo' => 'required|image|max:10240', // Required after photo
            'photo_before' => 'nullable|image|max:10240', // Optional before photo
            'operational_status' => 'required|string|in:running,idle',
            'overall_score' => 'required|integer|between:1,5',
            'notes' => 'nullable|string',
            'spareparts' => 'nullable|array',
        ];

        if ($plan->target_completion && now()->gt($plan->target_completion)) {
            $rules['delay_reason'] = 'required|string|in:waiting_sparepart,waiting_production,waiting_vendor,waiting_approval,additional_damage,manpower_shortage,power_failure,other';
            $rules['delay_notes'] = 'required_if:delay_reason,other|nullable|string';
        }

        $request->validate($rules, [
            'delay_reason.required' => 'Alasan keterlambatan wajib diisi karena melewati target waktu penyelesaian.',
            'delay_notes.required_if' => 'Catatan keterlambatan wajib diisi jika memilih alasan Lainnya.',
        ]);

        DB::beginTransaction();
        try {
            $completedAt = now();
            // Calculate downtime duration using service
            $downtimeDuration = $this->downtimeService->calculateMinutes($plan->reported_at, $completedAt);

            // Compress and store after photo
            $photoPathAfter = $this->compressionService->compressAndStore($request->file('photo'));

            // Compress and store before photo if available
            $photoPathBefore = null;
            if ($request->hasFile('photo_before')) {
                $photoPathBefore = $this->compressionService->compressAndStore($request->file('photo_before'));
            }

            // Create execution log (status completed since Admin verifies directly)
            $execution = MaintenanceExecution::create([
                'maintenance_plan_id' => $plan->id,
                'machine_id' => $plan->machine_id,
                'operator_name' => $request->input('operator_name'),
                'started_at' => $plan->reported_at,
                'completed_at' => $completedAt,
                'overall_score' => $request->input('overall_score'),
                'notes' => $request->input('notes'),
                'status' => 'completed',
            ]);

            // Save after photo
            MaintenanceExecutionPhoto::create([
                'execution_id' => $execution->id,
                'type' => 'after',
                'photo_path' => $photoPathAfter,
            ]);

            // Save before photo if uploaded
            if ($photoPathBefore) {
                MaintenanceExecutionPhoto::create([
                    'execution_id' => $execution->id,
                    'type' => 'before',
                    'photo_path' => $photoPathBefore,
                ]);
            }

            // Save replaced spareparts
            if ($request->filled('spareparts')) {
                foreach ($request->input('spareparts') as $itemCode => $partData) {
                    if (isset($partData['checked']) && $partData['checked'] == '1') {
                        \App\Models\MaintenanceExecutionSparepart::create([
                            'execution_id' => $execution->id,
                            'warehouse_item_code' => $itemCode,
                            'quantity' => $partData['qty'] ?? 1,
                        ]);
                    }
                }
            }

            // Update machine operational status
            $plan->machine->update([
                'operational_status' => $request->input('operational_status'),
            ]);

            // Complete the plan
            $delayReason = null;
            $delayNotes = null;

            if ($plan->target_completion && $completedAt->gt($plan->target_completion)) {
                $delayReason = $request->input('delay_reason');
                $delayNotes = $request->input('delay_notes');
            }

            $plan->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'downtime_duration' => $downtimeDuration,
                'actual_completion' => $completedAt,
                'delay_reason' => $delayReason,
                'delay_notes' => $delayNotes,
            ]);

            DB::commit();

            return redirect()->route('planning.show', $plan->id)
                ->with('success', 'Laporan verifikasi perbaikan berhasil diserahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan verifikasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Render printable Work Order briefing sheet (PDF).
     */
    public function print(MaintenancePlan $plan, \App\Services\MaintenancePdfService $pdfService)
    {
        $pdfContent = $pdfService->generateWorkOrder($plan);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="work_order_' . $plan->id . '.pdf"',
        ]);
    }

    /**
     * Render printable Maintenance Completion Report sheet (PDF).
     */
    public function report(MaintenancePlan $plan, \App\Services\MaintenancePdfService $pdfService)
    {
        $pdfContent = $pdfService->generateCompletionReport($plan);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="completion_report_' . $plan->id . '.pdf"',
        ]);
    }
}

