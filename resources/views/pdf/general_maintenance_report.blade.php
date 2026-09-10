<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>General Maintenance Report - {{ $meta['start_date_formatted'] }} - {{ $meta['end_date_formatted'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.25;
            color: #1f2937;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .uppercase { text-transform: uppercase; }
        
        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }
        .header-logo {
            font-size: 12pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }
        .header-sublogo {
            font-size: 7.5pt;
            color: #4b5563;
            font-weight: bold;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }
        .header-title {
            font-size: 13pt;
            font-weight: bold;
            color: #111827;
            text-align: center;
        }
        .header-subtitle {
            font-size: 8pt;
            color: #4b5563;
            text-align: center;
            font-weight: 600;
        }
        
        /* Metadata block */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 8pt;
            vertical-align: middle;
        }
        .meta-label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
        }
        
        /* Main Report Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .report-table th {
            background-color: #f1f5f9;
            border: 1px solid #94a3b8;
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: center;
        }
        .report-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .machine-code {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            color: #0f172a;
            font-size: 8pt;
        }
        .machine-name {
            font-size: 7pt;
            color: #475569;
            margin-top: 1px;
        }
        .repair-date {
            font-weight: bold;
            color: #0f172a;
            font-size: 8pt;
        }
        .repair-time {
            font-size: 6.5pt;
            color: #64748b;
            font-family: 'Courier New', Courier, monospace;
            margin-top: 1px;
        }
        
        /* Empty State */
        .empty-row td {
            padding: 18px;
            text-align: center;
            color: #64748b;
            font-style: italic;
            font-size: 8.5pt;
            background-color: #f8fafc;
        }
        
        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            border: 1px solid #94a3b8;
            text-align: center;
            vertical-align: top;
            padding: 0;
            background-color: #ffffff;
        }
        .signature-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #94a3b8;
            padding: 4px;
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
        }
        .signature-space {
            height: 48px;
        }
        .signature-footer {
            padding: 4px;
            font-size: 7.5pt;
            color: #334155;
            border-top: 1px dashed #cbd5e1;
        }
        
        /* Footer metadata */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }
        .footer-text {
            font-size: 6.5pt;
            color: #64748b;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <!-- Document Header -->
    <table class="header-table">
        <tr>
            <td style="width: 35%; vertical-align: middle; padding: 0;">
                <div class="header-logo">PT PERONI KARYA SENTRA</div>
                <div class="header-sublogo">FACTORY MAINTENANCE DIVISION</div>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: middle; padding: 0;">
                <div class="header-title">GENERAL MAINTENANCE REPORT</div>
                <div class="header-subtitle">Laporan Umum Pemeliharaan & Perbaikan Mesin</div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle; padding: 0;">
                <div style="font-size: 7pt; color: #64748b;">
                    Tanggal Cetak: <strong style="color: #1e293b;">{{ $meta['generated_at'] }}</strong><br>
                    Halaman: <strong style="color: #1e293b;">1 / 1</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Filter & Metadata Summary -->
    <table class="meta-table">
        <tr>
            <td style="width: 30%;">
                <div class="meta-label">Periode Laporan</div>
                <div class="meta-value">{{ $meta['start_date_formatted'] }} &mdash; {{ $meta['end_date_formatted'] }}</div>
            </td>
            <td style="width: 30%;">
                <div class="meta-label">Departemen</div>
                <div class="meta-value">{{ $meta['department_label'] }}</div>
            </td>
            <td style="width: 20%;">
                <div class="meta-label">Total Pemeliharaan</div>
                <div class="meta-value">{{ $meta['total_cases'] }} Kasus</div>
            </td>
            <td style="width: 20%; text-align: right;">
                <div class="meta-label">Klasifikasi Dokumen</div>
                <div class="meta-value">OPERATIONAL REPORT</div>
            </td>
        </tr>
    </table>

    <!-- Main Report Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 20%;">Mesin</th>
                <th style="width: 48%; text-align: left;">Deskripsi Masalah / Keluhan</th>
                <th style="width: 16%;">Diperbaiki</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($plans as $index => $plan)
                @php
                    $incidentDate = $plan->reported_at 
                        ? $plan->reported_at->format('d M Y') 
                        : ($plan->scheduled_date ? $plan->scheduled_date->format('d M Y') : $plan->created_at->format('d M Y'));

                    $machineCode = $plan->machine->code ?? '-';
                    $machineName = $plan->machine->name ?? '-';

                    $problemDescription = $plan->notes;
                    if (empty($problemDescription)) {
                        $problemDescription = $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : 'Paket Perawatan Preventif';
                    }

                    $repairDate = $plan->actual_completion 
                        ? $plan->actual_completion->format('d M Y') 
                        : ($plan->completed_at ? $plan->completed_at->format('d M Y') : '-');

                    $repairTime = $plan->actual_completion 
                        ? $plan->actual_completion->format('H:i') 
                        : ($plan->completed_at ? $plan->completed_at->format('H:i') : '');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-mono">{{ $incidentDate }}</td>
                    <td>
                        <div class="machine-code">{{ $machineCode }}</div>
                        <div class="machine-name">{{ $machineName }}</div>
                    </td>
                    <td>
                        <div style="color: #1e293b; line-height: 1.35;">{{ $problemDescription }}</div>
                    </td>
                    <td class="text-center">
                        <div class="repair-date">{{ $repairDate }}</div>
                        @if ($repairTime)
                            <div class="repair-time">{{ $repairTime }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="5">
                        Tidak ada data maintenance pada periode dan departemen yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Block -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div class="signature-header">ADMIN MAINTENANCE</div>
                <div class="signature-space"></div>
                <div class="signature-footer">
                    ( __________________________ )<br>
                    <span style="font-size: 6.5pt; color: #64748b;">Nama & Tanggal</span>
                </div>
            </td>
            <td style="width: 10%; border: none;"></td>
            <td class="signature-box">
                <div class="signature-header">KABAG MAINTENANCE</div>
                <div class="signature-space"></div>
                <div class="signature-footer">
                    ( __________________________ )<br>
                    <span style="font-size: 6.5pt; color: #64748b;">Nama & Tanggal</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%; padding: 0;">
                <div class="footer-text">
                    Dokumen resmi PT Peroni Karya Sentra &mdash; Factory Maintenance Division.<br>
                    Laporan ini dihasilkan secara otomatis oleh MRM System untuk keperluan operasional dan audit ISO.
                </div>
            </td>
            <td style="width: 40%; text-align: right; padding: 0;">
                <div class="footer-text">
                    Filter: {{ $meta['start_date'] }} s/d {{ $meta['end_date'] }} | Dept: {{ $meta['department'] }}<br>
                    Status: Verified Completed Records
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
