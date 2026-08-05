@extends('pdf.layout')

@section('title', 'Work Order ' . $doc_number)

@section('content')
    @if($plan->status === 'cancelled')
        <div style="position: fixed; top: 35%; left: 15%; width: 70%; text-align: center; font-size: 80pt; color: #ffe4e6; transform: rotate(-35deg); font-weight: bold; z-index: -1000; pointer-events: none; text-transform: uppercase; border: 12px solid #ffe4e6; padding: 15px; border-radius: 30px; font-family: sans-serif;">
            CANCELLED
        </div>
    @endif

    <!-- Header -->
    @include('pdf.partials.header')

    @if ($plan->status === 'cancelled')
        <div style="border: 2px solid #dc2626; background-color: #fef2f2; padding: 6px; margin-bottom: 6px; border-radius: 4px;">
            <div style="font-size: 8pt; font-weight: bold; color: #b91c1c; text-transform: uppercase; margin-bottom: 2px;">
                Status: Cancelled (Dibatalkan)
            </div>
            <table style="width: 100%; border: none; margin: 0; padding: 0;">
                <tr style="border: none;">
                    <td style="width: 50%; padding: 1px 0; border: none; font-size: 7.5pt; vertical-align: top;">
                        <span style="font-weight: bold; color: #4b5563;">Alasan Pembatalan:</span> {{ $plan->cancellation_reason }}
                    </td>
                    <td style="width: 50%; padding: 1px 0; border: none; font-size: 7.5pt; vertical-align: top;">
                        <span style="font-weight: bold; color: #4b5563;">Dibatalkan Oleh:</span> {{ $plan->cancelledByUser->name ?? 'System' }}
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="width: 50%; padding: 1px 0; border: none; font-size: 7.5pt; vertical-align: top;">
                        @if ($plan->replacementPlan)
                            <span style="font-weight: bold; color: #4b5563;">Laporan Pengganti:</span> 
                            {{ $plan->replacementPlan->isCorrective() ? $plan->replacementPlan->breakdown_number : $plan->replacementPlan->work_order_number }}
                        @else
                            <span style="font-weight: bold; color: #4b5563;">Laporan Pengganti:</span> Tidak ada
                        @endif
                    </td>
                    <td style="width: 50%; padding: 1px 0; border: none; font-size: 7.5pt; vertical-align: top;">
                        <span style="font-weight: bold; color: #4b5563;">Tanggal Batal:</span> {{ $plan->cancelled_at ? $plan->cancelled_at->format('d M Y H:i') : '-' }}
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Details Section -->
    <div class="section-title">1. Document Details</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray"><span class="summary-label">Work Order No</span></td>
            <td><span class="summary-value font-mono">#{{ $doc_number }}</span></td>
            <td class="bg-gray"><span class="summary-label">Machine Code</span></td>
            <td><span class="summary-value font-mono">{{ $machine_code }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Machine Name</span></td>
            <td><span class="summary-value">{{ $machine_name }}</span></td>
            <td class="bg-gray"><span class="summary-label">Priority</span></td>
            <td>
                <span class="summary-value uppercase" style="color: {{ $priority_color }};">
                    {{ $priority_label }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Maintenance Type</span></td>
            <td><span class="summary-value">{{ $type_label }}</span></td>
            <td class="bg-gray"><span class="summary-label">Technician</span></td>
            <td><span class="summary-value">{{ $technician }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Schedule Date</span></td>
            <td><span class="summary-value">{{ $scheduled_date_formatted }}</span></td>
            <td class="bg-gray"><span class="summary-label">Target Completion</span></td>
            <td><span class="summary-value font-mono text-blue">{{ $target_completion_formatted }}</span></td>
        </tr>
    </table>

    <!-- Problem Description -->
    <div class="section-title">2. Problem Description</div>
    <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
        <tr>
            <td style="padding: 4px; font-size: 7.5pt;">
                @if ($plan->isCorrective())
                    <div style="font-size: 7pt; color: #4b5563; font-family: monospace; border-bottom: 1px solid #e5e7eb; padding-bottom: 1px; margin-bottom: 2px;">
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
    @include('pdf.partials.readiness')

    <!-- Repair Instruction / Checklist Section -->
    <div class="section-title">4. Repair Instruction</div>
    @if (count($checklists) > 0)
        <table class="checklist-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 35%; text-align: left;">Instruction Task</th>
                    <th style="width: 40%; text-align: left;">SOP / Details</th>
                    <th style="width: 10%; text-align: center;">Mandatory</th>
                    <th style="width: 10%; text-align: center;">Rating (1-5)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($checklists as $idx => $item)
                    <tr>
                        <td class="text-center font-mono" style="font-size: 7pt;">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $item['title'] }}</td>
                        <td style="color: #4b5563;">{{ $item['description'] }}</td>
                        <td class="text-center font-bold">
                            @if ($item['is_required'] === 'Wajib')
                                <span class="text-red">Wajib</span>
                            @else
                                <span style="color: #9ca3af;">Opsional</span>
                            @endif
                        </td>
                        <td class="text-center" style="font-size: 6.5pt; color: #9ca3af;">
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
                        <td colspan="4" class="text-center font-bold text-gray-500 italic" style="color: #6b7280; font-size: 7pt; padding: 2px;">
                            + {{ $additionalSparepartsCount }} additional spareparts. Scan QR Code to view the complete spareparts list.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
            <tr>
                <td style="padding: 3px; text-align: center; color: #9ca3af; font-style: italic; font-size: 7pt;">
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
            <td><span class="summary-value font-mono">{{ $target_completion_formatted }}</span></td>
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
    @include('pdf.partials.approval')

    <!-- Footer metadata -->
    @include('pdf.partials.footer')
@endsection
