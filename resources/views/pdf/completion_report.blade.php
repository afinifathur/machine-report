@extends('pdf.layout')

@section('title', 'Completion Report ' . $doc_number)

@section('content')
    <!-- Header -->
    @include('pdf.partials.header')

    <!-- Details Section -->
    <div class="section-title">1. Document Details</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray"><span class="summary-label">Report Number</span></td>
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
            <td class="bg-gray"><span class="summary-label">Completion Date</span></td>
            <td><span class="summary-value font-mono text-green">{{ $completed_at }}</span></td>
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

    <!-- Root Cause Analysis -->
    <div class="section-title">3. Root Cause Analysis</div>
    <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
        <tr>
            <td style="padding: 4px; font-size: 7.5pt; color: #374151;">
                @if ($plan->isCorrective())
                    {{-- For Corrective, display description or notes if there's breakdown metadata --}}
                    {{ $plan->notes ?? '-' }}
                @else
                    {{-- Default placeholder or checklist details for PM --}}
                    -
                @endif
            </td>
        </tr>
    </table>

    <!-- Corrective / Maintenance Actions performed -->
    <div class="section-title">4. Maintenance Action Performed</div>
    <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
        <tr>
            <td style="padding: 4px; font-size: 7.5pt; color: #374151; font-style: italic;">
                "{{ $corrective_actions }}"
            </td>
        </tr>
    </table>

    <!-- Readiness Summary (Historical Evidence) -->
    <div class="section-title">5. Historical Audit Readiness (Before Execution)</div>
    @include('pdf.partials.readiness')

    <!-- Consumed Spareparts List -->
    <div class="section-title">6. Consumed Spareparts List</div>
    @if (count($consumedSpareparts) > 0)
        <table class="checklist-table" style="margin-bottom: 4px;">
            <thead>
                <tr>
                    <th style="width: 30%; text-align: left;">ERP Code</th>
                    <th style="width: 50%; text-align: left;">Sparepart Name</th>
                    <th style="width: 20%; text-align: center;">Quantity Consumed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($consumedSpareparts as $part)
                    <tr>
                        <td class="font-mono font-bold">{{ $part['erp_code'] }}</td>
                        <td>{{ $part['name'] }}</td>
                        <td class="text-center font-bold">{{ $part['quantity_used'] }} pcs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table style="border: 1px solid #d1d5db; background-color: #f9fafb; margin-bottom: 4px;">
            <tr>
                <td style="padding: 3px; text-align: center; color: #9ca3af; font-style: italic; font-size: 7pt;">
                    No spareparts were consumed during this maintenance execution.
                </td>
            </tr>
        </table>
    @endif

    <!-- Photo Evidence Section -->
    <div class="section-title">7. Photo Evidence</div>
    <table style="width: 100%; border: 1px solid #d1d5db; margin-bottom: 4px; background-color: #f9fafb;">
        <tr>
            <td style="width: 50%; text-align: center; border-right: 1px solid #d1d5db; padding: 4px; vertical-align: middle;">
                <div style="font-weight: bold; font-size: 6.5pt; text-transform: uppercase; margin-bottom: 2px; color: #4b5563;">Before Repair / PM</div>
                @if ($photos['before'])
                    <img src="{{ $photos['before'] }}" style="max-height: 55px; max-width: 100%; object-fit: contain; border-radius: 2px;" alt="Before Photo" />
                @else
                    <div style="padding: 10px 0; color: #9ca3af; font-style: italic; font-size: 7pt;">
                        No Photo Available
                    </div>
                @endif
            </td>
            <td style="width: 50%; text-align: center; padding: 4px; vertical-align: middle;">
                <div style="font-weight: bold; font-size: 6.5pt; text-transform: uppercase; margin-bottom: 2px; color: #4b5563;">After Repair / PM</div>
                @if ($photos['after'])
                    <img src="{{ $photos['after'] }}" style="max-height: 55px; max-width: 100%; object-fit: contain; border-radius: 2px;" alt="After Photo" />
                @else
                    <div style="padding: 10px 0; color: #9ca3af; font-style: italic; font-size: 7pt;">
                        No Photo Available
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Delay Analysis Section -->
    <div class="section-title">8. Delay Analysis</div>
    @if ($is_delayed)
        <table class="summary-table" style="border: 1px solid #fca5a5; margin-bottom: 4px;">
            <tr>
                <td class="bg-soft-blue" style="width: 25%;"><span class="summary-label">Target Completion</span></td>
                <td style="width: 25%;"><span class="summary-value font-mono">{{ $target_completion_formatted }}</span></td>
                <td class="bg-soft-blue" style="width: 25%;"><span class="summary-label">Actual Completion</span></td>
                <td style="width: 25%;"><span class="summary-value font-mono text-red">{{ $completed_at }}</span></td>
            </tr>
            <tr>
                <td class="bg-soft-blue"><span class="summary-label">Delay Duration</span></td>
                <td><span class="summary-value text-red font-mono">{{ $delay_duration }} Menit</span></td>
                <td class="bg-soft-blue"><span class="summary-label">Delay Reason</span></td>
                <td><span class="summary-value text-red">{{ $delay_reason_label }}</span></td>
            </tr>
            <tr>
                <td class="bg-soft-blue"><span class="summary-label">Delay Notes</span></td>
                <td colspan="3"><span class="summary-value italic text-red">"{{ $delay_notes }}"</span></td>
            </tr>
        </table>
    @else
        <table style="border: 1px solid #86efac; background-color: #f0fdf4; margin-bottom: 4px;">
            <tr>
                <td style="padding: 4px; text-align: center; color: #15803d; font-weight: bold; font-size: 7.5pt;">
                    ✓ COMPLETED ON TIME
                </td>
            </tr>
        </table>
    @endif

    <!-- Verification / Execution results -->
    <div class="section-title">9. Execution & Verification Details</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray"><span class="summary-label">Condition Score</span></td>
            <td><span class="summary-value badge badge-blue">{{ $score }} / 5.00</span></td>
            <td class="bg-gray"><span class="summary-label">Actual Downtime</span></td>
            <td><span class="summary-value">{{ $downtime }} Menit</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Verified By</span></td>
            <td><span class="summary-value">{{ $verified_by }}</span></td>
            <td class="bg-gray"><span class="summary-label">Verification Time</span></td>
            <td><span class="summary-value font-mono">{{ $verification_time }}</span></td>
        </tr>
    </table>

    <!-- Approval Grid -->
    <div class="section-title">10. Approval & Verification Signatures</div>
    @include('pdf.partials.approval')

    <!-- Footer metadata -->
    @include('pdf.partials.footer')
@endsection
