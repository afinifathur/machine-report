<?php

namespace App\Services;

/**
 * |--------------------------------------------------------------------------
 * | Maintenance PDF Infrastructure
 * |--------------------------------------------------------------------------
 * |
 * | This service is the centralized PDF generator
 * | for the Machine Report CMMS.
 * |
 * | Current Documents
 * | - Work Order (What should be done)
 * | - Completion Report (What was actually done)
 * |
 * | Planned Documents
 * | - Inspection Report
 * | - Calibration Report
 * | - Shutdown Report
 * | - Machine Passport Summary
 * |
 * | All maintenance documents must share
 * | identical branding, metadata, QR Code
 * | strategy, typography and paper settings.
 * |
 */

use App\Models\MaintenancePlan;
use App\Services\MaintenanceReadinessService;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MaintenancePdfService
{
    protected MaintenanceReadinessService $readinessService;
    protected MachineSparepartService $sparepartService;
    protected SparepartLookupRepositoryInterface $wmsLookupRepository;

    public function __construct(
        MaintenanceReadinessService $readinessService,
        MachineSparepartService $sparepartService,
        SparepartLookupRepositoryInterface $wmsLookupRepository
    ) {
        $this->readinessService = $readinessService;
        $this->sparepartService = $sparepartService;
        $this->wmsLookupRepository = $wmsLookupRepository;
    }

    /**
     * Generate Work Order PDF.
     */
    public function generateWorkOrder(MaintenancePlan $plan): string
    {
        $this->loadPlanRelations($plan);
        $context = $this->buildDocumentContext($plan, 'Work Order');

        // Readiness Audit Context
        $readiness = $this->readinessService->getReadinessReport($plan);

        // Mapped Spareparts list
        $sparepartsView = $this->sparepartService->getMachineSparepartsView($plan->machine);
        $normalizedSpareparts = $this->normalizeWorkOrderSpareparts($sparepartsView);

        // Smart Display Truncation: max 10 items
        $additionalSparepartsCount = 0;
        if (count($normalizedSpareparts) > 10) {
            $additionalSparepartsCount = count($normalizedSpareparts) - 9;
            $normalizedSpareparts = array_slice($normalizedSpareparts, 0, 9);
        }

        // Checklist tasks
        $checklists = [];
        if ($plan->isPreventive() && $plan->maintenanceTemplate) {
            foreach ($plan->maintenanceTemplate->checklists as $item) {
                $checklists[] = [
                    'title' => $item->title,
                    'description' => $item->description ?? '-',
                    'is_required' => $item->is_required ? 'Wajib' : 'Opsional',
                ];
            }
        }

        // Combine everything
        $data = array_merge($context, [
            'readiness' => $readiness,
            'normalizedSpareparts' => $normalizedSpareparts,
            'additionalSparepartsCount' => $additionalSparepartsCount,
            'checklists' => $checklists,
        ]);

        return $this->initializePdf('pdf.work_order', $data);
    }

    /**
     * Generate Completion Report PDF.
     */
    public function generateCompletionReport(MaintenancePlan $plan): string
    {
        $this->loadPlanRelations($plan);
        $context = $this->buildDocumentContext($plan, 'Completion Report');

        // Historical Readiness Summary (with TODO note as requested)
        $readiness = $this->readinessService->getReadinessReport($plan);

        // Consumed Spareparts only
        $consumedSpareparts = $this->resolveConsumedSpareparts($plan);

        // Photos mapping
        $photos = $this->resolveExecutionPhotos($plan);

        // Execution & Delay Details
        $execution = $plan->execution;
        $downtime = $plan->downtime_duration ?? ($execution && $execution->completed_at ? $execution->completed_at->diffInMinutes($execution->started_at) : 0);

        // Delay Analysis
        $isDelayed = false;
        $delayDuration = 0;
        if ($plan->target_completion && $plan->actual_completion && $plan->actual_completion->gt($plan->target_completion)) {
            $isDelayed = true;
            $delayDuration = $plan->actual_completion->diffInMinutes($plan->target_completion);
        }

        // Friendly delay reason string
        $delayReasonLabel = '-';
        if ($plan->delay_reason) {
            $reasons = [
                'waiting_sparepart' => 'Menunggu Suku Cadang (WMS)',
                'waiting_production' => 'Menunggu Produksi',
                'waiting_vendor' => 'Menunggu Kontraktor/Vendor',
                'waiting_approval' => 'Menunggu Persetujuan',
                'additional_damage' => 'Ditemukan Kerusakan Tambahan',
                'manpower_shortage' => 'Kekurangan Teknisi',
                'power_failure' => 'Gangguan Listrik/Utilitas',
                'other' => 'Lainnya'
            ];
            $delayReasonLabel = $reasons[$plan->delay_reason] ?? ucfirst($plan->delay_reason);
        }

        // Verification details
        $verifiedBy = 'Sistem / Admin';
        $verificationTime = $plan->completed_at ? $plan->completed_at->format('d M Y H:i') : '-';
        if ($execution && $execution->status === 'completed') {
            $verifiedBy = 'Supervisor / Kabag';
        }

        $data = array_merge($context, [
            'readiness' => $readiness,
            'consumedSpareparts' => $consumedSpareparts,
            'photos' => $photos,
            'score' => $execution ? number_format($execution->overall_score, 2) : '5.00',
            'downtime' => $downtime,
            'started_at' => $execution && $execution->started_at ? $execution->started_at->format('d M Y H:i') : '-',
            'completed_at' => $plan->completed_at ? $plan->completed_at->format('d M Y H:i') : '-',
            'is_delayed' => $isDelayed,
            'delay_duration' => $delayDuration,
            'delay_reason_label' => $delayReasonLabel,
            'delay_notes' => $plan->delay_notes ?? '-',
            'corrective_actions' => $execution->notes ?? 'Pekerjaan selesai dilakukan sesuai dengan petunjuk kerja.',
            'verified_by' => $verifiedBy,
            'verification_time' => $verificationTime,
        ]);

        return $this->initializePdf('pdf.completion_report', $data);
    }

    /**
     * Load core relations.
     */
    protected function loadPlanRelations(MaintenancePlan $plan): void
    {
        $plan->load([
            'machine.documents',
            'maintenanceTemplate.checklists',
            'maintenanceTemplate.spareparts',
            'execution.photos',
            'execution.spareparts',
        ]);
    }

    /**
     * Generate Machine Passport QR Code pointing ONLY to permanent Machine Passport.
     */
    protected function generateMachinePassportQr(MaintenancePlan $plan): string
    {
        $qrOptions = new QROptions([
            'outputBase64' => true,
            'scale' => 5,
            'eccLevel' => EccLevel::L,
        ]);
        $passportUrl = route('machines.show', $plan->machine->code);
        return (new QRCode($qrOptions))->render($passportUrl);
    }

    /**
     * Generate document metadata and general context.
     */
    protected function buildDocumentContext(MaintenancePlan $plan, string $docType): array
    {
        $dateStr = $plan->scheduled_date ? $plan->scheduled_date->format('Ymd') : now()->format('Ymd');
        $typeStr = $plan->isPreventive() ? 'PM' : 'CM';
        $seqStr = str_pad($plan->id, 5, '0', STR_PAD_LEFT);
        $docNumber = "MR-{$typeStr}-{$dateStr}-{$seqStr}";

        $qrCodeImage = $this->generateMachinePassportQr($plan);

        // Priority formatting
        $priorityLabel = 'Low';
        $priorityColor = '#2563eb'; // blue
        if ($plan->priority === 'critical') {
            $priorityLabel = 'Critical';
            $priorityColor = '#dc2626'; // red
        } elseif ($plan->priority === 'high') {
            $priorityLabel = 'High';
            $priorityColor = '#d97706'; // amber
        } elseif ($plan->priority === 'medium') {
            $priorityLabel = 'Medium';
            $priorityColor = '#4b5563'; // gray
        }

        return [
            'plan' => $plan,
            'doc_type' => $docType,
            'doc_number' => $docNumber,
            'qrCodeImage' => $qrCodeImage,
            'machine_code' => $plan->machine->code,
            'machine_name' => $plan->machine->name,
            'machine_dept' => $plan->machine->department ?? '-',
            'machine_loc' => $plan->machine->production_area ?? '-',
            'priority_label' => $priorityLabel,
            'priority_color' => $priorityColor,
            'type_label' => $plan->isPreventive() ? 'Preventive Maintenance' : 'Corrective Maintenance',
            'technician' => $plan->assigned_technician ?? 'Unassigned',
            'scheduled_date_formatted' => $plan->scheduled_date ? $plan->scheduled_date->format('d M Y') : '-',
            'target_completion_formatted' => $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-',
            'generation_time' => now()->format('d M Y H:i') . ' WIB',
            'print_time' => now()->format('d M Y H:i') . ' WIB',
            'revision' => 'Rev.0',
        ];
    }

    /**
     * Resolve actual consumed spareparts for completion report.
     */
    protected function resolveConsumedSpareparts(MaintenancePlan $plan): array
    {
        $execution = $plan->execution;
        if (!$execution) {
            return [];
        }

        $usedParts = $execution->spareparts;
        if ($usedParts->isEmpty()) {
            return [];
        }

        $codes = $usedParts->pluck('warehouse_item_code')->unique()->toArray();
        $wmsDetails = $this->wmsLookupRepository->getItemsDetails($codes);

        $consumed = [];
        foreach ($usedParts as $part) {
            $code = $part->warehouse_item_code;
            $dto = $wmsDetails[$code] ?? null;
            $consumed[] = [
                'erp_code' => $code,
                'name' => $dto ? ($dto->name ?? 'Unknown') : 'Unknown',
                'quantity_used' => $part->quantity,
            ];
        }

        return $consumed;
    }

    /**
     * Resolve execution photos safely using absolute system paths to prevent Dompdf resolution failure.
     */
    protected function resolveExecutionPhotos(MaintenancePlan $plan): array
    {
        $beforePhoto = null;
        $afterPhoto = null;

        $execution = $plan->execution;
        if ($execution) {
            $beforeRec = $execution->photos->firstWhere('type', 'before');
            $afterRec = $execution->photos->firstWhere('type', 'after') ?? $execution->photos->firstWhere('type', 'general');

            if ($beforeRec && $beforeRec->photo_path) {
                $path = public_path('storage/' . $beforeRec->photo_path);
                if (file_exists($path)) {
                    $beforePhoto = $path;
                }
            }
            if ($afterRec && $afterRec->photo_path) {
                $path = public_path('storage/' . $afterRec->photo_path);
                if (file_exists($path)) {
                    $afterPhoto = $path;
                }
            }
        }

        return [
            'before' => $beforePhoto,
            'after' => $afterPhoto,
        ];
    }

    /**
     * Normalizes the mapped spareparts list defensively.
     */
    protected function normalizeWorkOrderSpareparts(array $sparepartsView): array
    {
        // Sort spareparts: Critical -> Reorder -> Healthy
        $statusPriority = ['critical' => 1, 'reorder' => 2, 'healthy' => 3];
        usort($sparepartsView, function($a, $b) use ($statusPriority) {
            $statusA = $a['status'] ?? [];
            $codeA = is_array($statusA) ? ($statusA['code'] ?? 'unknown') : 'unknown';
            
            $statusB = $b['status'] ?? [];
            $codeB = is_array($statusB) ? ($statusB['code'] ?? 'unknown') : 'unknown';

            $pa = $statusPriority[$codeA] ?? 99;
            $pb = $statusPriority[$codeB] ?? 99;
            return $pa <=> $pb;
        });

        $normalized = [];
        foreach ($sparepartsView as $part) {
            $dto = $part['dto'] ?? null;
            $status = $part['status'] ?? [];
            $statusCode = is_array($status) ? ($status['code'] ?? 'unknown') : 'unknown';
            $statusLabel = is_array($status) ? ($status['label'] ?? 'Unknown') : 'Unknown';

            $badgeClass = 'badge-blue';
            if ($statusCode === 'critical') {
                $badgeClass = 'badge-red';
            } elseif ($statusCode === 'reorder') {
                $badgeClass = 'badge-orange';
            }

            $normalized[] = [
                'erp_code' => $dto ? ($dto->erpCode ?? '?') : '?',
                'name' => $dto ? ($dto->name ?? 'Unknown') : 'Unknown',
                'quantity_required' => $part['qty_per_machine'] ?? 1,
                'status_label' => $statusLabel,
                'status_badge_class' => $badgeClass,
            ];
        }

        return $normalized;
    }

    /**
     * Initialize Dompdf, set paper format and render view.
     */
    protected function initializePdf(string $view, array $data): string
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->output();
    }
}
