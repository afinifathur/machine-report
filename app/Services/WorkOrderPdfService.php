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
            $pa = $statusPriority[$a['status']] ?? 99;
            $pb = $statusPriority[$b['status']] ?? 99;
            return $pa <=> $pb;
        });

        // Smart Display Truncation: max 10 items
        $displayedSpareparts = $sparepartsView;
        $additionalSparepartsCount = 0;
        if (count($sparepartsView) > 10) {
            $displayedSpareparts = array_slice($sparepartsView, 0, 9);
            $additionalSparepartsCount = count($sparepartsView) - 9;
        }

        // Render PDF
        $pdf = Pdf::loadView('planning.print_pdf', compact(
            'plan',
            'qrCodeImage',
            'readiness',
            'displayedSpareparts',
            'additionalSparepartsCount'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }
}
