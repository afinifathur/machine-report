<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MasterDepartment;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneralMaintenanceReportService
{
    /**
     * Build the query for General Maintenance Report.
     */
    public function buildQuery(array $filters): Builder
    {
        $startDate = !empty($filters['start_date']) 
            ? Carbon::parse($filters['start_date'])->startOfDay() 
            : Carbon::now()->startOfMonth()->startOfDay();

        $endDate = !empty($filters['end_date']) 
            ? Carbon::parse($filters['end_date'])->endOfDay() 
            : Carbon::now()->endOfDay();

        $department = $filters['department'] ?? 'all';

        $query = MaintenancePlan::with(['machine', 'execution', 'maintenanceTemplate'])
            ->where('status', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('actual_completion', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->whereNull('actual_completion')
                         ->whereBetween('completed_at', [$startDate, $endDate]);
                  });
            });

        // Department Filter
        if (!empty($department) && $department !== 'all') {
            $query->whereHas('machine', function ($mq) use ($department) {
                $mq->where('department', $department)
                   ->orWhere('department', 'like', "%{$department}%");
            });
        }

        // Sort chronologically (oldest -> newest, deterministic with id)
        $query->orderBy(DB::raw('COALESCE(actual_completion, completed_at, scheduled_date)'), 'asc')
              ->orderBy('id', 'asc');

        return $query;
    }

    /**
     * Get processed report data and metadata for view and exports.
     */
    public function getReportData(array $filters): array
    {
        $startDate = !empty($filters['start_date']) 
            ? Carbon::parse($filters['start_date'])->startOfDay() 
            : Carbon::now()->startOfMonth()->startOfDay();

        $endDate = !empty($filters['end_date']) 
            ? Carbon::parse($filters['end_date'])->endOfDay() 
            : Carbon::now()->endOfDay();

        $department = $filters['department'] ?? 'all';

        $plans = $this->buildQuery($filters)->get();

        $departments = MasterDepartment::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $departmentLabel = 'Semua Departemen';
        if (!empty($department) && $department !== 'all') {
            $matchedDept = $departments->first(fn($d) => strcasecmp($d->name, $department) === 0 || strcasecmp($d->code, $department) === 0);
            $departmentLabel = $matchedDept ? $matchedDept->name : $department;
        }

        $meta = [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_date_formatted' => $startDate->format('d M Y'),
            'end_date_formatted' => $endDate->format('d M Y'),
            'department' => $department,
            'department_label' => $departmentLabel,
            'total_cases' => $plans->count(),
            'generated_at' => Carbon::now()->format('d M Y H:i'),
        ];

        return [
            'plans' => $plans,
            'meta' => $meta,
            'departments' => $departments,
        ];
    }

    /**
     * Generate Excel export stream content (CSV with UTF-8 BOM).
     */
    public function generateExcel(Collection $plans, array $meta): string
    {
        $output = fopen('php://temp', 'r+');

        // Add UTF-8 BOM for Microsoft Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Report Header Lines
        fputcsv($output, ['PT PERONI KARYA SENTRA']);
        fputcsv($output, ['FACTORY MAINTENANCE DIVISION']);
        fputcsv($output, ['GENERAL MAINTENANCE REPORT']);
        fputcsv($output, []);
        fputcsv($output, ['Periode:', $meta['start_date_formatted'] . ' s/d ' . $meta['end_date_formatted']]);
        fputcsv($output, ['Departemen:', $meta['department_label']]);
        fputcsv($output, ['Total Kasus:', $meta['total_cases']]);
        fputcsv($output, ['Tanggal Ekspor:', $meta['generated_at']]);
        fputcsv($output, []);

        // Table Headers
        fputcsv($output, [
            'No',
            'Tanggal',
            'Kode Mesin',
            'Nama Mesin',
            'Deskripsi Masalah / Keluhan',
            'Tanggal Diperbaiki',
            'Jam Diperbaiki'
        ]);

        $rowNumber = 1;
        foreach ($plans as $plan) {
            $incidentDate = $plan->reported_at 
                ? $plan->reported_at->format('d M Y') 
                : ($plan->scheduled_date ? $plan->scheduled_date->format('d M Y') : $plan->created_at->format('d M Y'));

            $machineCode = $plan->machine->code ?? '-';
            $machineName = $plan->machine->name ?? '-';

            // Canonical problem description
            $problemDescription = $plan->notes;
            if (empty($problemDescription)) {
                $problemDescription = $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : 'Paket Perawatan Preventif';
            }

            $completionDate = $plan->actual_completion 
                ? $plan->actual_completion->format('d M Y') 
                : ($plan->completed_at ? $plan->completed_at->format('d M Y') : '-');

            $completionTime = $plan->actual_completion 
                ? $plan->actual_completion->format('H:i') 
                : ($plan->completed_at ? $plan->completed_at->format('H:i') : '-');

            fputcsv($output, [
                $rowNumber++,
                $incidentDate,
                $machineCode,
                $machineName,
                $problemDescription,
                $completionDate,
                $completionTime
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate PDF output.
     */
    public function generatePdf(Collection $plans, array $meta): string
    {
        $pdf = Pdf::loadView('pdf.general_maintenance_report', compact('plans', 'meta'));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        return $pdf->output();
    }
}
