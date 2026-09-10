<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sparepart Monitor Report - {{ $meta['generated_at'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            line-height: 1.2;
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
            margin-bottom: 6px;
            padding-bottom: 3px;
        }
        .header-logo {
            font-size: 11pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }
        .header-sublogo {
            font-size: 7pt;
            color: #4b5563;
            font-weight: bold;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }
        .header-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
            text-align: center;
        }
        .header-subtitle {
            font-size: 7.5pt;
            color: #4b5563;
            text-align: center;
            font-weight: 600;
        }
        
        /* Metadata block */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            padding: 3px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .meta-label {
            font-size: 6.5pt;
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
            margin-bottom: 8px;
        }
        .report-table th {
            background-color: #f1f5f9;
            border: 1px solid #94a3b8;
            font-size: 7pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            padding: 4px 5px;
            text-align: center;
        }
        .report-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 7pt;
            vertical-align: top;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-critical { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-reorder { background-color: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
        .badge-healthy { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-overstock { background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-unknown { background-color: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
        
        /* Empty State */
        .empty-row td {
            padding: 16px;
            text-align: center;
            color: #64748b;
            font-style: italic;
            font-size: 8pt;
            background-color: #f8fafc;
        }
        
        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 40%;
            border: 1px solid #94a3b8;
            text-align: center;
            vertical-align: top;
            padding: 0;
            background-color: #ffffff;
        }
        .signature-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #94a3b8;
            padding: 3px;
            font-size: 7pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
        }
        .signature-space {
            height: 40px;
        }
        .signature-footer {
            padding: 3px;
            font-size: 7pt;
            color: #334155;
            border-top: 1px dashed #cbd5e1;
        }
        
        /* Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
        }
        .footer-text {
            font-size: 6pt;
            color: #64748b;
            line-height: 1.25;
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
                <div class="header-title">SPAREPART MONITOR REPORT</div>
                <div class="header-subtitle">Laporan Monitoring Stok & Ketersediaan Suku Cadang Mesin</div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle; padding: 0;">
                <div style="font-size: 6.5pt; color: #64748b;">
                    Tanggal Cetak: <strong style="color: #1e293b;">{{ $meta['generated_at'] }}</strong><br>
                    Klasifikasi: <strong style="color: #1e293b;">INVENTORY MONITOR</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Filter & Metadata Summary -->
    <table class="meta-table">
        <tr>
            <td style="width: 28%;">
                <div class="meta-label">Filter Mesin</div>
                <div class="meta-value">{{ $meta['machine_label'] }}</div>
            </td>
            <td style="width: 22%;">
                <div class="meta-label">Filter Status</div>
                <div class="meta-value">{{ $meta['status_label'] }}</div>
            </td>
            <td style="width: 18%;">
                <div class="meta-label">Criticality</div>
                <div class="meta-value">{{ $meta['criticality_label'] }}</div>
            </td>
            <td style="width: 18%;">
                <div class="meta-label">Pencarian</div>
                <div class="meta-value">{{ $meta['search'] ?: 'Semua Item' }}</div>
            </td>
            <td style="width: 14%; text-align: right;">
                <div class="meta-label">Total Terpantau</div>
                <div class="meta-value">{{ $meta['total_items'] }} Item</div>
            </td>
        </tr>
    </table>

    <!-- Main Report Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">ERP Code</th>
                <th style="width: 25%; text-align: left;">Nama Item / Kategori & Brand</th>
                <th style="width: 8%;">Stok</th>
                <th style="width: 7%;">Weekly Avg</th>
                <th style="width: 7%;">Lead Time</th>
                <th style="width: 7%;">Min Stock</th>
                <th style="width: 7%;">Target</th>
                <th style="width: 8%;">Coverage</th>
                <th style="width: 10%;">Last Audit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                @php
                    $statusCode = $item['status']['code'] ?? 'unknown';
                    $badgeClass = match($statusCode) {
                        'critical' => 'badge-critical',
                        'reorder' => 'badge-reorder',
                        'healthy' => 'badge-healthy',
                        'overstock' => 'badge-overstock',
                        default => 'badge-unknown',
                    };

                    $lastAudit = '-';
                    if (!empty($item['last_audit_at'])) {
                        $lastAudit = \Carbon\Carbon::parse($item['last_audit_at'])->format('d M Y');
                    }
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $item['status']['label'] ?? 'Unknown' }}</span>
                    </td>
                    <td class="font-mono text-center font-bold" style="color: #1e3a8a;">
                        {{ $item['erp_code'] }}
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $item['name'] }}</div>
                        <div style="font-size: 6.5pt; color: #64748b;">
                            {{ $item['category'] ?? 'General' }} &bull; {{ $item['brand'] ?? '-' }}
                        </div>
                    </td>
                    <td class="text-center font-bold">
                        {{ $item['stock'] }} {{ $item['unit'] }}
                    </td>
                    <td class="text-center font-mono">
                        {{ !is_null($item['weekly_average']) ? number_format($item['weekly_average'], 1) : '-' }}
                    </td>
                    <td class="text-center font-mono">
                        {{ $item['lead_time'] }} Hari
                    </td>
                    <td class="text-center font-mono" style="{{ !is_null($item['min_stock']) && $item['stock'] < $item['min_stock'] ? 'color: #b91c1c; font-weight: bold;' : '' }}">
                        {{ !is_null($item['min_stock']) ? number_format($item['min_stock'], 1) : '-' }}
                    </td>
                    <td class="text-center font-mono">
                        {{ !is_null($item['target_stock']) ? number_format($item['target_stock'], 1) : '-' }}
                    </td>
                    <td class="text-center">
                        <span style="color: #1e3a8a; font-weight: bold;">{{ $item['coverage'] }}</span> Mesin
                    </td>
                    <td class="text-center font-mono" style="font-size: 6.5pt;">
                        {{ $lastAudit }}
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="11">
                        Tidak ada sparepart yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Block -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div class="signature-header">ADMIN GUDANG / SPAREPART</div>
                <div class="signature-space"></div>
                <div class="signature-footer">
                    ( __________________________ )<br>
                    <span style="font-size: 6.5pt; color: #64748b;">Nama & Tanggal</span>
                </div>
            </td>
            <td style="width: 20%; border: none;"></td>
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
                    Data stok terintegrasi langsung dengan Warehouse Management System (WMS).
                </div>
            </td>
            <td style="width: 40%; text-align: right; padding: 0;">
                <div class="footer-text">
                    Status Dokumen: Realtime Live Inventory Audit<br>
                    Hal: 1 / 1
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
