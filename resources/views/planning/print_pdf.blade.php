<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Work Order #WO-{{ $plan->isPreventive() ? 'PM' : 'CM' }}-{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
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
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        td, th {
            padding: 3px 5px;
            vertical-align: top;
        }
        
        /* Utilities */
        .border-all {
            border: 1px solid #d1d5db;
        }
        .border-b {
            border-bottom: 1px solid #e5e7eb;
        }
        .bg-gray {
            background-color: #f9fafb;
        }
        .bg-soft-blue {
            background-color: #f0f7ff;
        }
        .text-blue { color: #2563eb; }
        .text-amber { color: #d97706; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }
        
        /* Header Block */
        .header-table {
            border-bottom: 2px solid #2563eb;
            margin-bottom: 6px;
        }
        .header-logo {
            font-size: 11pt;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: 0.5px;
        }
        .header-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
        }
        .header-wo {
            font-size: 9.5pt;
            font-weight: bold;
            color: #374151;
        }
        
        .section-title {
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #4b5563;
            border-bottom: 1px solid #9ca3af;
            padding-bottom: 1.5px;
            margin-top: 5px;
            margin-bottom: 3px;
        }
        
        .summary-table td {
            width: 25%;
            padding: 2.5px 5px;
            border: 1px solid #e5e7eb;
        }
        .summary-label {
            font-size: 7pt;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-value {
            font-size: 8pt;
            font-weight: bold;
            color: #1f2937;
        }
        
        .readiness-item {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 7.5pt;
            text-align: center;
        }
        
        .instruction-box {
            border: 1px solid #d1d5db;
            padding: 5px;
            min-height: 45px;
        }
        
        .write-line {
            border-bottom: 1px dashed #d1d5db;
            margin-top: 13px;
            height: 1px;
        }
        
        .approval-table {
            margin-top: 6px;
        }
        .approval-table th {
            font-size: 7pt;
            color: #4b5563;
            text-transform: uppercase;
            font-weight: bold;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        .approval-table td {
            height: 38px;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 3px;
            font-size: 7.5pt;
        }
        
        .footer-text {
            font-size: 6.5pt;
            color: #9ca3af;
        }
        
        .checklist-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-weight: bold;
            font-size: 7pt;
            padding: 2px 4px;
        }
        .checklist-table td {
            border: 1px solid #e5e7eb;
            padding: 2px 4px;
            font-size: 7.5pt;
        }
        
        /* Small badges */
        .badge {
            display: inline-block;
            padding: 1px 3px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-red { background-color: #fee2e2; color: #b91c1c; }
        .badge-orange { background-color: #ffedd5; color: #c2410c; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 40%; vertical-align: middle; padding-left: 0;">
                <div class="header-logo">PT PERONI KARYA SENTRA</div>
                <div style="font-size: 7.5pt; color: #6b7280; font-weight: bold; margin-top: 1px;">FACTORY MAINTENANCE DIVISION</div>
            </td>
            <td style="width: 45%; text-align: center; vertical-align: middle;">
                <div class="header-title">MAINTENANCE WORK ORDER</div>
                <div class="header-wo">
                    WO-{{ $plan->isPreventive() ? 'PM' : 'CM' }}-{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </td>
            <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 0;">
                <img src="{{ $qrCodeImage }}" style="width: 45px; height: 45px;" alt="QR Code" />
            </td>
        </tr>
    </table>

    <!-- Summary / Information Section -->
    <div class="section-title">1. Document Details</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray"><span class="summary-label">Work Order No</span></td>
            <td><span class="summary-value font-mono">#{{ $plan->isPreventive() ? 'PM' : 'CM' }}-{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}</span></td>
            <td class="bg-gray"><span class="summary-label">Machine Code</span></td>
            <td><span class="summary-value font-mono">{{ $plan->machine->code }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Machine Name</span></td>
            <td><span class="summary-value">{{ $plan->machine->name }}</span></td>
            <td class="bg-gray"><span class="summary-label">Priority</span></td>
            <td>
                <span class="summary-value uppercase @if($plan->priority === 'critical') text-red @elseif($plan->priority === 'high') text-amber @else text-blue @endif">
                    {{ $plan->priority === 'critical' ? 'Critical' : ($plan->priority === 'high' ? 'High' : ($plan->priority === 'medium' ? 'Medium' : 'Low')) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Maintenance Type</span></td>
            <td>
                <span class="summary-value">
                    {{ $plan->isPreventive() ? ($plan->maintenanceTemplate->maintenance_type ?? 'PM') : 'Corrective' }}
                </span>
            </td>
            <td class="bg-gray"><span class="summary-label">Technician</span></td>
            <td><span class="summary-value">{{ $plan->assigned_technician ?? 'Unassigned' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Schedule Date</span></td>
            <td><span class="summary-value">{{ $plan->scheduled_date->format('d M Y') }}</span></td>
            <td class="bg-gray"><span class="summary-label">Target Completion</span></td>
            <td><span class="summary-value font-mono text-blue">{{ $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-' }}</span></td>
        </tr>
    </table>

    <!-- Problem Description -->
    <div class="section-title">2. Problem Description</div>
    <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
        <tr>
            <td style="padding: 5px; font-size: 8pt;">
                @if ($plan->isCorrective())
                    <div style="font-size: 7.5pt; color: #4b5563; font-family: monospace; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; margin-bottom: 3px;">
                        BREAKDOWN NO: {{ $plan->breakdown_number }} &nbsp;|&nbsp; REPORTER: {{ $plan->reported_by }} ({{ $plan->reported_department }}) &nbsp;|&nbsp; REPORTED AT: {{ $plan->reported_at ? $plan->reported_at->format('d M Y H:i') : '-' }}
                    </div>
                @endif
                <div style="font-style: italic; color: #374151; font-weight: bold;">
                    "{{ $plan->notes ?? 'Routine maintenance package or planned asset inspection.' }}"
                </div>
            </td>
        </tr>
    </table>

    <!-- Audit Readiness Summary -->
    <div class="section-title">3. Audit Readiness</div>
    <table style="margin-bottom: 4px;">
        <tr>
            <td style="width: 25%; padding: 0 3px 0 0;">
                <div class="readiness-item">
                    @if ($readiness['machine_ready'])
                        <span class="text-green">✓</span> Machine Ready
                    @else
                        <span class="text-red">✗</span> Machine Down
                    @endif
                </div>
            </td>
            <td style="width: 25%; padding: 0 3px;">
                <div class="readiness-item">
                    @if ($readiness['technician_assigned'])
                        <span class="text-green">✓</span> Technician Assigned
                    @else
                        <span class="text-red">✗</span> Technician Unassigned
                    @endif
                </div>
            </td>
            <td style="width: 25%; padding: 0 3px;">
                <div class="readiness-item">
                    @if ($readiness['documents_available'])
                        <span class="text-green">✓</span> Manual Available
                    @else
                        <span class="text-amber">⚠</span> Manual Not Available
                    @endif
                </div>
            </td>
            <td style="width: 25%; padding: 0 0 0 3px;">
                <div class="readiness-item">
                    @if ($readiness['sparepart_readiness_ready'])
                        <span class="text-green">✓</span> Sparepart Ready
                    @else
                        <span class="text-amber">⚠</span> Spareparts Low/Reorder
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Repair Instruction / Checklist Section -->
    <div class="section-title">4. Repair Instruction</div>
    @if ($plan->isPreventive() && $plan->maintenanceTemplate && $plan->maintenanceTemplate->checklists->isNotEmpty())
        <table class="checklist-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No</th>
                    <th style="width: 34%; text-align: left;">Instruction Task</th>
                    <th style="width: 40%; text-align: left;">SOP / Details</th>
                    <th style="width: 10%; text-align: center;">Mandatory</th>
                    <th style="width: 10%; text-align: center;">Rating (1-5)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plan->maintenanceTemplate->checklists as $idx => $item)
                    <tr>
                        <td class="text-center font-mono" style="font-size: 7.5pt;">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $item->title }}</td>
                        <td style="color: #4b5563;">{{ $item->description ?? '-' }}</td>
                        <td class="text-center font-bold">
                            @if ($item->is_required)
                                <span class="text-red">Wajib</span>
                            @else
                                <span style="color: #9ca3af;">Opsional</span>
                            @endif
                        </td>
                        <td class="text-center" style="font-size: 7pt; color: #9ca3af;">
                            [ ] [ ] [ ] [ ] [ ]
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="instruction-box">
            <div class="write-line"></div>
            <div class="write-line"></div>
            <div class="write-line"></div>
        </div>
    @endif

    <!-- Sparepart Section -->
    <div class="section-title">5. Required Spareparts List</div>
    @if (count($normalizedSpareparts) > 0)
        <table class="checklist-table" style="margin-bottom: 4px;">
            <thead>
                <tr>
                    <th style="width: 25%; text-align: left;">ERP Code</th>
                    <th style="width: 50%; text-align: left;">Sparepart Name</th>
                    <th style="width: 13%; text-align: center;">Qty</th>
                    <th style="width: 12%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($normalizedSpareparts as $part)
                    <tr>
                        <td class="font-mono font-bold">{{ $part['erp_code'] }}</td>
                        <td>{{ $part['name'] }}</td>
                        <td class="text-center font-bold">{{ $part['quantity_required'] }} pcs</td>
                        <td class="text-center">
                            <span class="badge {{ $part['status_badge_class'] }}">
                                {{ $part['status_label'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                @if ($additionalSparepartsCount > 0)
                    <tr>
                        <td colspan="4" class="text-center font-bold text-gray-500 italic" style="color: #6b7280; font-size: 7.5pt; padding: 3px;">
                            + {{ $additionalSparepartsCount }} additional spareparts. Scan QR Code to view the complete spareparts list.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
            <tr>
                <td style="padding: 4px; text-align: center; color: #9ca3af; font-style: italic; font-size: 7.5pt;">
                    No specific spareparts mapping required for this maintenance plan.
                </td>
            </tr>
        </table>
    @endif

    <!-- Execution Plan & Records -->
    <div class="section-title">6. Execution Record</div>
    <table class="summary-table" style="margin-bottom: 4px;">
        <tr>
            <td class="bg-gray"><span class="summary-label">Estimated Duration</span></td>
            <td><span class="summary-value">{{ $plan->maintenanceTemplate->estimated_duration ?? 120 }} Menit</span></td>
            <td class="bg-gray"><span class="summary-label">Actual Start Time</span></td>
            <td>
                <span class="summary-value font-mono">
                    @if ($plan->status === 'completed' && $plan->execution && $plan->execution->started_at)
                        {{ $plan->execution->started_at->format('d M Y H:i') }}
                    @else
                        Tgl: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Jam: &nbsp; &nbsp; &nbsp;
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Downtime Estimation</span></td>
            <td><span class="summary-value">{{ $plan->maintenanceTemplate->estimated_duration ?? 120 }} Menit</span></td>
            <td class="bg-gray"><span class="summary-label">Actual Finish Time</span></td>
            <td>
                <span class="summary-value font-mono">
                    @if ($plan->status === 'completed' && $plan->execution && $plan->execution->completed_at)
                        {{ $plan->execution->completed_at->format('d M Y H:i') }}
                    @else
                        Tgl: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Jam: &nbsp; &nbsp; &nbsp;
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Target Completion</span></td>
            <td><span class="summary-value font-mono">{{ $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-' }}</span></td>
            <td class="bg-gray"><span class="summary-label">Actual Downtime</span></td>
            <td>
                <span class="summary-value font-mono">
                    @if ($plan->status === 'completed' && $plan->execution)
                        {{ $plan->downtime_duration ?? ($plan->execution->completed_at ? $plan->execution->completed_at->diffInMinutes($plan->execution->started_at) : 0) }} Menit
                    @else
                        ____________________ Menit
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <!-- Approval Grid -->
    <div class="section-title">7. Approval & Verification</div>
    <table class="approval-table">
        <thead>
            <tr>
                <th style="width: 33.3%;">Pelaksana (Technician)</th>
                <th style="width: 33.3%;">Supervisor (SP)</th>
                <th style="width: 33.3%;">Kepala Bagian (Kabag)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-size: 7.5pt; font-weight: bold; color: #4b5563; margin-bottom: 2px;">
                        {{ $plan->assigned_technician ?? 'Tanda Tangan' }}
                    </div>
                </td>
                <td>
                    <div style="font-size: 7pt; color: #9ca3af; margin-bottom: 2px;">
                        (................................)
                    </div>
                </td>
                <td>
                    <div style="font-size: 7pt; color: #9ca3af; margin-bottom: 2px;">
                        (................................)
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Document Metadata and Footer -->
    <table style="margin-top: 6px; border-top: 1px solid #e5e7eb; padding-top: 2px;">
        <tr>
            <td style="width: 50%; padding-left: 0;">
                <div class="footer-text">
                    This document was automatically generated by Machine Report CMMS.<br/>
                    Document Number: <strong style="color: #4b5563;">WO-{{ $plan->isPreventive() ? 'PM' : 'CM' }}-{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}</strong> &nbsp;|&nbsp; Revision: <strong>Rev.0</strong>
                </div>
            </td>
            <td style="width: 50%; text-align: right; padding-right: 0;">
                <div class="footer-text">
                    Generated: <strong>{{ now()->format('d M Y H:i') }} WIB</strong><br/>
                    Printed: <strong>{{ now()->format('d M Y H:i') }} WIB</strong>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
