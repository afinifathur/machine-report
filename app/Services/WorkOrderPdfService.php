<?php

namespace App\Services;

use App\Models\MaintenancePlan;
use App\Services\MaintenanceReadinessService;
use App\Integrations\WMS\Services\MachineSparepartService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkOrderPdfService
{
    protected MaintenanceReadinessService $readinessService;
    protected MachineSparepartService $sparepartService;

    public function __construct(
        MaintenanceReadinessService $readinessService,
        MachineSparepartService $sparepartService
    ) {
        $this->readinessService = $readinessService;
        $this->sparepartService = $sparepartService;
    }

    /**
     * Generate inline PDF binary content.
     */
    public function generatePdf(MaintenancePlan $plan): string
    {
        $plan->load([
            'machine.documents',
            'maintenanceTemplate.checklists',
            'maintenanceTemplate.spareparts',
            'execution'
        ]);

        // Generate offline QR code linking to permanent Machine Passport
        $qrOptions = new QROptions([
            'outputBase64' => true,
            'scale' => 5,
            'eccLevel' => EccLevel::L,
        ]);
        $passportUrl = route('machines.show', $plan->machine->code);
        $qrCodeImage = (new QRCode($qrOptions))->render($passportUrl);

        // Get live readiness report
        $readiness = $this->readinessService->getReadinessReport($plan);

        // Get mapped spareparts view
        $sparepartsView = $this->sparepartService->getMachineSparepartsView($plan->machine);

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

        // Smart Display Truncation: max 10 items
        $displayedSpareparts = $sparepartsView;
        $additionalSparepartsCount = 0;
        if (count($sparepartsView) > 10) {
            $displayedSpareparts = array_slice($sparepartsView, 0, 9);
            $additionalSparepartsCount = count($sparepartsView) - 9;
        }

        // Normalize spareparts data for Blade template Presentation layer
        $normalizedSpareparts = [];
        foreach ($displayedSpareparts as $part) {
            $dto = $part['dto'] ?? null;
            $status = $part['status'] ?? [];
            $statusCode = is_array($status) ? ($status['code'] ?? 'unknown') : 'unknown';
            $statusLabel = is_array($status) ? ($status['label'] ?? 'Unknown') : 'Unknown';

            // Safe lookup for badge styles
            $badgeClass = 'badge-blue';
            if ($statusCode === 'critical') {
                $badgeClass = 'badge-red';
            } elseif ($statusCode === 'reorder') {
                $badgeClass = 'badge-orange';
            }

            $normalizedSpareparts[] = [
                'erp_code' => $dto ? ($dto->erpCode ?? '?') : '?',
                'name' => $dto ? ($dto->name ?? 'Unknown') : 'Unknown',
                'quantity_required' => $part['qty_per_machine'] ?? 1,
                'status_label' => $statusLabel,
                'status_badge_class' => $badgeClass,
            ];
        }

        // Render PDF
        $pdf = Pdf::loadView('planning.print_pdf', compact(
            'plan',
            'qrCodeImage',
            'readiness',
            'normalizedSpareparts',
            'additionalSparepartsCount'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }
}
