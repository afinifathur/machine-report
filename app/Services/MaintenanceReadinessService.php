<?php

namespace App\Services;

use App\Models\MaintenancePlan;
use App\Repositories\WarehouseRepositoryInterface;

class MaintenanceReadinessService
{
    protected WarehouseRepositoryInterface $warehouseRepository;
    protected \App\Integrations\WMS\Services\MachineSparepartService $machineSparepartService;

    public function __construct(
        WarehouseRepositoryInterface $warehouseRepository,
        \App\Integrations\WMS\Services\MachineSparepartService $machineSparepartService
    ) {
        $this->warehouseRepository = $warehouseRepository;
        $this->machineSparepartService = $machineSparepartService;
    }

    /**
     * Audit and generate a readiness report for a given maintenance plan.
     */
    public function getReadinessReport(MaintenancePlan $plan): array
    {
        if (in_array($plan->status, ['completed', 'waiting_review'])) {
            $plan->loadMissing('execution');
            $isWaitingReview = $plan->status === 'waiting_review' || 
                              ($plan->execution && $plan->execution->status === 'waiting_review');
            return [
                'plan_id' => $plan->id,
                'overall_status' => $isWaitingReview ? 'Waiting Review' : 'Completed',
                'machine_ready' => true,
                'machine_status_text' => 'Running (Siap)',
                'template_available' => true,
                'checklist_available' => true,
                'spareparts_available' => true,
                'sparepart_details' => [],
                'documents_available' => true,
                'technician_assigned' => true,
                'sparepart_readiness_ready' => true,
                'mapped_spareparts' => [],
                'blockers' => [],
                'warnings' => [],
            ];
        }

        // Ensure relations are loaded
        $plan->loadMissing(['machine.documents', 'maintenanceTemplate.checklists', 'maintenanceTemplate.spareparts']);

        $machine = $plan->machine;
        $template = $plan->maintenanceTemplate;

        // 1. Machine Ready
        $machineReady = $machine && !in_array($machine->operational_status, ['breakdown', 'maintenance']);
        $machineStatusText = $machine ? match ($machine->operational_status) {
            'breakdown' => 'Kerusakan (Down)',
            'maintenance' => 'Dalam Perawatan',
            'idle' => 'Idle (Siap)',
            'running' => 'Running (Siap)',
            default => ucfirst($machine->operational_status),
        } : 'Tidak Ditemukan';

        // 2. Template Available (PM only, always true for corrective)
        $templateAvailable = $plan->isCorrective() ? true : ($template && $template->is_active);

        // 3. Checklist Available (PM only, always true for corrective)
        $checklistAvailable = $plan->isCorrective() ? true : ($template && $template->checklists->count() > 0);

        // 4. Required Spareparts Available (Mandatory SOP - PM only)
        $sparepartsAvailable = true;
        $sparepartDetails = [];
        $insufficientParts = [];

        if ($template) {
            foreach ($template->spareparts as $reqPart) {
                $wmsDetails = $this->warehouseRepository->getItemDetails($reqPart->warehouse_item_code);
                $isSufficient = $wmsDetails['stock'] >= $reqPart->quantity;
                
                if (!$isSufficient) {
                    $sparepartsAvailable = false;
                    $insufficientParts[] = [
                        'code' => $reqPart->warehouse_item_code,
                        'name' => $wmsDetails['name'],
                        'required' => $reqPart->quantity,
                        'available' => $wmsDetails['stock'],
                    ];
                }

                $sparepartDetails[] = [
                    'code' => $reqPart->warehouse_item_code,
                    'name' => $wmsDetails['name'],
                    'required' => $reqPart->quantity,
                    'available' => $wmsDetails['stock'],
                    'location' => $wmsDetails['location'],
                    'is_sufficient' => $isSufficient,
                ];
            }
        } else {
            $sparepartsAvailable = false;
        }

        // 5. Required Documents Available
        $documentsAvailable = false;
        if ($machine) {
            $manualBook = $machine->documents->firstWhere('type', 'manual_book');
            $documentsAvailable = $manualBook && !empty($manualBook->file_name);
        }

        // 6. Technician Assigned
        $technicianAssigned = !empty($plan->assigned_technician);

        // 7. Mapped Spareparts Stock Status (Sparepart Readiness Audit)
        $mappedSpareparts = $machine ? $this->machineSparepartService->getMachineSparepartsView($machine) : [];
        $sparepartReadinessReady = true;
        $sparepartReadinessDetails = [];

        foreach ($mappedSpareparts as $item) {
            $code = $item['dto']->erpCode;
            $name = $item['dto']->name;
            $required = $item['qty_per_machine'];
            $available = $item['dto']->stock;
            $statusLabel = $item['status']['label'] ?? 'Unknown';
            $statusCode = $item['status']['code'] ?? 'unknown';

            $isPartReady = $available >= $required && !in_array($statusCode, ['critical', 'reorder']);
            if (!$isPartReady) {
                $sparepartReadinessReady = false;
            }

            $sparepartReadinessDetails[] = [
                'code' => $code,
                'name' => $name,
                'required' => $required,
                'available' => $available,
                'status' => $statusLabel,
                'status_code' => $statusCode,
                'badge_class' => $item['status']['badge_class'] ?? '',
                'icon' => $item['status']['icon'] ?? '',
                'is_ready' => $isPartReady
            ];
        }

        // 8. Determine Overall Readiness Status
        // Blocked only if machine is down or template is inactive/missing
        // Spareparts shortages are treated as warnings to provide visibility without blocking execution or demoting status
        if (!$templateAvailable || !$machineReady) {
            $overallStatus = 'Blocked'; // Terblokir
        } elseif ($checklistAvailable && $documentsAvailable && $technicianAssigned) {
            $overallStatus = 'Ready'; // Siap
        } else {
            $overallStatus = 'Almost Ready'; // Hampir Siap
        }

        // 9. Compile Blockers and Warnings
        $blockers = [];
        $warnings = [];

        if (!$machineReady) {
            $blockers[] = "Mesin {$machine->code} sedang dalam kondisi " . strtolower($machineStatusText) . ".";
        }
        if (!$templateAvailable) {
            $blockers[] = "Paket Perawatan (SOP) tidak aktif atau tidak ditemukan.";
        }

        // Spareparts shortages moved to warnings
        foreach ($insufficientParts as $part) {
            $warnings[] = "Stok WMS kurang untuk {$part['code']} ({$part['name']}): dibutuhkan {$part['required']}, tersedia {$part['available']}.";
        }
        if (!$sparepartReadinessReady) {
            $warnings[] = "Beberapa suku cadang mesin yang terpetakan berada dalam kondisi kritis atau perlu reorder.";
        }

        if ($templateAvailable && !$checklistAvailable) {
            $warnings[] = "Daftar tugas (checklist) tindakan belum diatur pada paket perawatan.";
        }
        if (!$documentsAvailable) {
            $warnings[] = "Buku manual (manual book) belum diunggah untuk mesin ini.";
        }
        if (!$technicianAssigned) {
            $warnings[] = "Teknisi pelaksana belum ditugaskan untuk rencana ini.";
        }

        return [
            'plan_id' => $plan->id,
            'overall_status' => $overallStatus,
            'machine_ready' => $machineReady,
            'machine_status_text' => $machineStatusText,
            'template_available' => $templateAvailable,
            'checklist_available' => $checklistAvailable,
            'spareparts_available' => $sparepartsAvailable,
            'sparepart_details' => $sparepartDetails,
            'documents_available' => $documentsAvailable,
            'technician_assigned' => $technicianAssigned,
            'sparepart_readiness_ready' => $sparepartReadinessReady,
            'mapped_spareparts' => $sparepartReadinessDetails,
            'blockers' => $blockers,
            'warnings' => $warnings,
        ];
    }
}
