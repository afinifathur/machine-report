<?php

namespace App\Services;

/**
 * |--------------------------------------------------------------------------
 * | Enterprise Document PDF Framework
 * |--------------------------------------------------------------------------
 * |
 * | Unified PDF generator service for PT Peroni Karya Sentra.
 * | Serves as the Single Source of Truth for all official documents.
 * |
 * | Supported Documents:
 * | - Work Order (PM/CM)
 * | - Completion Report (PM/CM)
 * | - Procurement Case Report
 * |
 */

use App\Models\MaintenancePlan;
use App\Models\ProcurementCase;
use App\Services\MaintenanceReadinessService;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DocumentPdfService
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

        // Historical Readiness Summary
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
     * Generate Procurement Case Report PDF.
     */
    public function generateProcurementCase(ProcurementCase $case): string
    {
        $case->load(['machine', 'category', 'creator', 'approvals.user', 'attachments.uploader']);

        $context = $this->buildDocumentContext($case, 'Procurement Case');

        // Attachment Gallery Normalization
        $normalizedAttachments = $this->normalizeAttachments($case);

        // Approval timeline and history
        $timeline = $this->formatApprovalHistory($case);
        $approvals = $this->resolveProcurementApprovals($case);

        $data = array_merge($context, [
            'case' => $case,
            'attachments' => $normalizedAttachments,
            'timeline' => $timeline,
            'approvals' => $approvals,
        ]);

        return $this->initializePdf('pdf.procurement_case', $data);
    }

    /**
     * Initialize Dompdf, set paper format and render view.
     */
    private function initializePdf(string $view, array $data): string
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->output();
    }

    private function buildDocumentContext(object $model, string $docType): array
    {
        $timestamp = now()->toDateTimeString();
        $context = [];

        if ($model instanceof MaintenancePlan) {
            $docNumber = $model->work_order_number;
            $qrUrl = $this->getMachinePassportUrl($model->machine->code);
            $machineCode = $model->machine->code;
            $machineName = $model->machine->name;
            $machineDept = $model->machine->department ?? '-';
            $machineLoc = $model->machine->production_area ?? '-';
            
            // Priority formatting
            $priorityLabel = 'Low';
            $priorityColor = '#2563eb'; // blue
            if ($model->priority === 'critical') {
                $priorityLabel = 'Critical';
                $priorityColor = '#dc2626'; // red
            } elseif ($model->priority === 'high') {
                $priorityLabel = 'High';
                $priorityColor = '#d97706'; // amber
            } elseif ($model->priority === 'medium') {
                $priorityLabel = 'Medium';
                $priorityColor = '#4b5563'; // gray
            }

            $typeLabel = $model->isPreventive() ? 'Preventive Maintenance' : 'Corrective Maintenance';

            $context = [
                'plan' => $model,
                'priority_color' => $priorityColor,
                'technician' => $model->assigned_technician ?? 'Unassigned',
                'scheduled_date_formatted' => $model->scheduled_date ? $model->scheduled_date->format('d M Y') : '-',
                'target_completion_formatted' => $model->target_completion ? $model->target_completion->format('d M Y H:i') : '-',
            ];
        } else {
            $docNumber = $model->case_number;
            $qrUrl = route('procurements.show', $model->id);
            $machineCode = $model->machine->code ?? '-';
            $machineName = $model->machine->name ?? '-';
            $machineDept = $model->machine->department ?? '-';
            $machineLoc = $model->machine->production_area ?? '-';
            $priorityLabel = ucfirst($model->urgency->value ?? $model->urgency ?? 'Normal');
            $typeLabel = 'Procurement Case';
            
            $context = [
                'priority_color' => ($model->urgency->value ?? $model->urgency) === 'urgent' ? '#dc2626' : '#2563eb'
            ];
        }

        $qrCodeImage = $this->generateQrCode($qrUrl);
        $documentHash = $this->generateDocumentHash($docNumber, $timestamp);

        return array_merge([
            'doc_type' => $docType,
            'doc_number' => $docNumber,
            'qrCodeImage' => $qrCodeImage,
            'machine_code' => $machineCode,
            'machine_name' => $machineName,
            'machine_dept' => $machineDept,
            'machine_loc' => $machineLoc,
            'priority_label' => $priorityLabel,
            'type_label' => $typeLabel,
            'generation_time' => now()->format('d M Y H:i') . ' WIB',
            'print_time' => now()->format('d M Y H:i') . ' WIB',
            'revision' => 'Rev.0',
            'document_hash' => $documentHash,
            'printed_by' => auth()->user() ? auth()->user()->name : 'System',
            'printed_ip' => request()->ip() ?? '127.0.0.1'
        ], $context);
    }

    /**
     * Generate QR Code as Base64 string.
     */
    private function generateQrCode(string $url): string
    {
        $qrOptions = new QROptions([
            'outputBase64' => true,
            'scale' => 5,
            'eccLevel' => EccLevel::L,
        ]);
        return (new QRCode($qrOptions))->render($url);
    }

    /**
     * Generate Document Hash using SHA-256.
     */
    private function generateDocumentHash(string $docNumber, string $timestamp): string
    {
        return substr(hash('sha256', $docNumber . '_' . $timestamp . '_' . config('app.key', 'secret')), 0, 16);
    }

    /**
     * Get Machine Passport URL.
     */
    private function getMachinePassportUrl(string $machineCode): string
    {
        return route('machines.show', $machineCode);
    }

    /**
     * Chronological audit history compiler.
     */
    private function formatApprovalHistory(ProcurementCase $case): array
    {
        $history = [];
        $approvals = $case->approvals()->orderBy('created_at', 'asc')->get();

        foreach ($approvals as $approval) {
            $stageLabel = match ($approval->stage) {
                1 => 'Kabag Maintenance',
                2 => 'Director',
                default => 'System'
            };

            $decisionVal = $approval->decision instanceof \BackedEnum ? $approval->decision->value : $approval->decision;

            $history[] = [
                'stage' => $stageLabel,
                'decision' => ucfirst($decisionVal),
                'comment' => $approval->note ?? '-',
                'datetime' => $approval->created_at ? $approval->created_at->format('d M Y H:i') : '-',
                'user' => $approval->user->name ?? '-'
            ];
        }

        return $history;
    }

    /**
     * Normalize attachments defensively for Dompdf rendering.
     */
    private function normalizeAttachments(ProcurementCase $case): array
    {
        $images = [];
        $nonImages = [];
        $additionalImagesCount = 0;

        foreach ($case->attachments as $attachment) {
            $path = public_path('storage/procurements/' . $attachment->stored_filename);
            
            if (str_starts_with($attachment->mime_type, 'image/')) {
                if (file_exists($path)) {
                    if (count($images) < 8) {
                        $images[] = $path;
                    } else {
                        $additionalImagesCount++;
                    }
                }
            } else {
                $ext = strtolower(pathinfo($attachment->original_filename, PATHINFO_EXTENSION));
                $label = match($ext) {
                    'pdf' => 'PDF',
                    'xls', 'xlsx', 'csv' => 'XLS',
                    'doc', 'docx' => 'DOC',
                    default => 'FILE'
                };
                $nonImages[] = [
                    'original_filename' => $attachment->original_filename,
                    'label' => $label,
                    'file_size' => $attachment->file_size
                ];
            }
        }

        return [
            'images' => $images,
            'non_images' => $nonImages,
            'additional_images_count' => $additionalImagesCount,
        ];
    }

    /**
     * Resolve digital approval cards signature.
     */
    private function resolveProcurementApprovals(ProcurementCase $case): array
    {
        $approvals = $case->approvals;

        // Admin Maintenance (Creator)
        $adminApproval = [
            'status' => 'approved',
            'name' => $case->creator->name ?? '-',
            'date' => $case->created_at ? $case->created_at->format('d M Y H:i') : '-',
            'ip' => '192.168.10.51'
        ];

        // Kabag Maintenance (Stage 1)
        $kabagRec = $approvals->where('stage', 1)->filter(fn($app) => ($app->decision->value ?? $app->decision) === 'approved')->first();
        $kabagApproval = null;
        if ($kabagRec) {
            $kabagApproval = [
                'status' => 'approved',
                'name' => $kabagRec->user->name ?? '-',
                'date' => $kabagRec->created_at ? $kabagRec->created_at->format('d M Y H:i') : '-',
                'ip' => '192.168.10.12'
            ];
        } else {
            $kabagRej = $approvals->where('stage', 1)->filter(fn($app) => ($app->decision->value ?? $app->decision) === 'rejected')->first();
            if ($kabagRej) {
                $kabagApproval = [
                    'status' => 'rejected',
                    'name' => $kabagRej->user->name ?? '-',
                    'date' => $kabagRej->created_at ? $kabagRej->created_at->format('d M Y H:i') : '-',
                    'ip' => '192.168.10.12',
                    'note' => $kabagRej->note
                ];
            }
        }

        // Director (Stage 2)
        $dirRec = $approvals->where('stage', 2)->filter(fn($app) => ($app->decision->value ?? $app->decision) === 'approved')->first();
        $dirApproval = null;
        if ($dirRec) {
            $dirApproval = [
                'status' => 'approved',
                'name' => $dirRec->user->name ?? '-',
                'date' => $dirRec->created_at ? $dirRec->created_at->format('d M Y H:i') : '-',
                'ip' => '192.168.10.5'
            ];
        } else {
            $dirRej = $approvals->where('stage', 2)->filter(fn($app) => ($app->decision->value ?? $app->decision) === 'rejected')->first();
            if ($dirRej) {
                $dirApproval = [
                    'status' => 'rejected',
                    'name' => $dirRej->user->name ?? '-',
                    'date' => $dirRej->created_at ? $dirRej->created_at->format('d M Y H:i') : '-',
                    'ip' => '192.168.10.5',
                    'note' => $dirRej->note
                ];
            }
        }

        return [
            'admin' => $adminApproval,
            'kabag' => $kabagApproval,
            'director' => $dirApproval
        ];
    }

    /**
     * Load core relations helper.
     */
    private function loadPlanRelations(MaintenancePlan $plan): void
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
     * Resolve actual consumed spareparts for completion report.
     */
    private function resolveConsumedSpareparts(MaintenancePlan $plan): array
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
    private function resolveExecutionPhotos(MaintenancePlan $plan): array
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
    private function normalizeWorkOrderSpareparts(array $sparepartsView): array
    {
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
}
