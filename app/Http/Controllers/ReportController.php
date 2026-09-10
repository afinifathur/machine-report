<?php

namespace App\Http\Controllers;

use App\Services\GeneralMaintenanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected GeneralMaintenanceReportService $reportService;

    public function __construct(GeneralMaintenanceReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the General Maintenance Report page.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('report.view'), 403);

        $filters = [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'department' => $request->input('department', 'all'),
        ];

        $reportData = $this->reportService->getReportData($filters);

        return view('reports.index', [
            'plans' => $reportData['plans'],
            'meta' => $reportData['meta'],
            'departments' => $reportData['departments'],
        ]);
    }

    /**
     * Export General Maintenance Report to Excel / CSV.
     */
    public function exportExcel(Request $request)
    {
        abort_unless(auth()->user()->can('report.view'), 403);

        $filters = [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'department' => $request->input('department', 'all'),
        ];

        $reportData = $this->reportService->getReportData($filters);
        $csvContent = $this->reportService->generateExcel($reportData['plans'], $reportData['meta']);

        $filename = 'Laporan_Umum_Maintenance_' . date('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export General Maintenance Report to PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()->can('report.view'), 403);

        $filters = [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'department' => $request->input('department', 'all'),
        ];

        $reportData = $this->reportService->getReportData($filters);
        $pdfContent = $this->reportService->generatePdf($reportData['plans'], $reportData['meta']);

        $filename = 'Laporan_Umum_Maintenance_' . date('Ymd') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
