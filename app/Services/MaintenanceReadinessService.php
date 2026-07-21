<?php

namespace App\Services;

use App\Models\MaintenancePlan;
use App\Repositories\WarehouseRepositoryInterface;

class MaintenanceReadinessService
{
    protected WarehouseRepositoryInterface $warehouseRepository;

    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
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

        // 2. Template Available
        $templateAvailable = $template && $template->is_active;

        // 3. Checklist Available
        $checklistAvailable = $template && $template->checklists->count() > 0;

        // 4. Required Spareparts Available
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

        // 7. Determine Overall Readiness Status
        // Blocked: Machine is down, template missing, or spareparts are insufficient
        // Ready: All 6 checks are green
        // Almost Ready: Machine and parts are ready, but checklist, documents, or technician is missing/pending
        if (!$templateAvailable || !$machineReady || !$sparepartsAvailable) {
            $overallStatus = 'Blocked'; // Terblokir
        } elseif ($checklistAvailable && $documentsAvailable && $technicianAssigned) {
            $overallStatus = 'Ready'; // Siap
        } else {
            $overallStatus = 'Almost Ready'; // Hampir Siap
        }

        // 8. Compile Blockers and Warnings
        $blockers = [];
        $warnings = [];

        if (!$machineReady) {
            $blockers[] = "Mesin {$machine->code} sedang dalam kondisi " . strtolower($machineStatusText) . ".";
        }
        if (!$templateAvailable) {
            $blockers[] = "Paket Perawatan (SOP) tidak aktif atau tidak ditemukan.";
        }
        foreach ($insufficientParts as $part) {
            $blockers[] = "Stok WMS kurang untuk {$part['code']} ({$part['name']}): dibutuhkan {$part['required']}, tersedia {$part['available']}.";
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
            'blockers' => $blockers,
            'warnings' => $warnings,
        ];
    }
}
