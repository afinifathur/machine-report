@extends('pdf.layout')

@section('title', 'Completion Report ' . $doc_number)

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
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
        <tr>
            <td style="width: 50%; padding: 0 4px 4px 0; border: none; vertical-align: top;">
                <div style="border: 1px solid #e5e7eb; background-color: #f9fafb; padding: 4px 6px; text-align: left;">
                    <div style="font-weight: bold; font-size: 7.5pt; color: #4b5563;">&bull; Mesin</div>
                    <div style="font-size: 7.5pt; color: #1f2937; margin-top: 1px;">
                        {{ $readiness['machine_status_text'] ?? 'Running (Siap)' }}
                    </div>
                </div>
            </td>
            <td style="width: 50%; padding: 0 0 4px 4px; border: none; vertical-align: top;">
                <div style="border: 1px solid #e5e7eb; background-color: #f9fafb; padding: 4px 6px; text-align: left;">
                    <div style="font-weight: bold; font-size: 7.5pt; color: #4b5563;">&bull; Teknisi</div>
                    <div style="font-size: 7.5pt; color: #1f2937; margin-top: 1px;">
                        {{ $plan->assigned_technician ?? 'Unassigned' }}
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 4px 4px 0 0; border: none; vertical-align: top;">
                <div style="border: 1px solid #e5e7eb; background-color: #f9fafb; padding: 4px 6px; text-align: left;">
                    <div style="font-weight: bold; font-size: 7.5pt; color: #4b5563;">&bull; Manual</div>
                    <div style="font-size: 7.5pt; color: #1f2937; margin-top: 1px;">
                        {{ ($readiness['documents_available'] ?? false) ? 'Available' : 'Not Available' }}
                    </div>
                </div>
            </td>
            <td style="width: 50%; padding: 4px 0 0 4px; border: none; vertical-align: top;">
                <div style="border: 1px solid #e5e7eb; background-color: #f9fafb; padding: 4px 6px; text-align: left;">
                    <div style="font-weight: bold; font-size: 7.5pt; color: #4b5563;">&bull; Sparepart</div>
                    <div style="font-size: 7.5pt; color: #1f2937; margin-top: 1px;">
                        {{ ($readiness['sparepart_readiness_ready'] ?? false) ? 'Ready' : 'Low Stock' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

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
    @php
        $beforePhotos = [];
        $afterPhotos = [];
        if ($plan->execution) {
            foreach ($plan->execution->photos as $photo) {
                $path = public_path('storage/' . $photo->photo_path);
                if (file_exists($path)) {
                    if ($photo->type === 'before') {
                        $beforePhotos[] = $path;
                    } elseif ($photo->type === 'after' || $photo->type === 'general') {
                        $afterPhotos[] = $path;
                    }
                }
            }
        }

        // Fallback to resolved ones if empty
        if (empty($beforePhotos) && !empty($photos['before'])) {
            $beforePhotos[] = $photos['before'];
        }
        if (empty($afterPhotos) && !empty($photos['after'])) {
            $afterPhotos[] = $photos['after'];
        }

        $hasBefore = count($beforePhotos) > 0;
        $hasAfter = count($afterPhotos) > 0;
        $photoHeight = ($hasBefore && $hasAfter) ? 180 : 220;
        $innerCellHeight = $photoHeight - 12;
    @endphp

    @if (!$hasBefore && !$hasAfter)
        <table style="width: 100%; border: 1px solid #d1d5db; margin-bottom: 4px; background-color: #f9fafb; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; padding: 15px 0; color: #9ca3af; font-style: italic; font-size: 7.5pt; border: none;">
                    No Photo Available
                </td>
            </tr>
        </table>
    @else
        {{-- Before Repair Section --}}
        @if ($hasBefore)
            <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin-top: 4px; margin-bottom: 2px; color: #4b5563;">Before Repair</div>
            @foreach (array_chunk($beforePhotos, 3) as $chunk)
                <table style="width: 100%; border-collapse: separate; border-spacing: 6px; border: none; margin-bottom: 4px;">
                    <tr>
                        @foreach ($chunk as $photo)
                            <td style="width: 31%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 6px; border-radius: 4px; height: {{ $photoHeight }}px; text-align: center; vertical-align: middle;">
                                    <table style="width: 100%; height: {{ $innerCellHeight }}px; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle; border: none; padding: 0; height: {{ $innerCellHeight }}px;">
                                                <img src="{{ $photo }}" style="max-width: 100%; max-height: {{ $innerCellHeight }}px; vertical-align: middle;" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="font-size: 7pt; font-weight: bold; margin-top: 4px; color: #4b5563; text-align: center;">Before Repair</div>
                            </td>
                        @endforeach
                        @for ($i = count($chunk); $i < 3; $i++)
                            <td style="width: 31%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 6px; border-radius: 4px; height: {{ $photoHeight }}px;"></div>
                                <div style="font-size: 7pt; font-weight: bold; margin-top: 4px; color: transparent; text-align: center;">&nbsp;</div>
                            </td>
                        @endfor
                    </tr>
                </table>
            @endforeach
        @else
            <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin-top: 4px; margin-bottom: 2px; color: #4b5563;">Before Repair</div>
            <div style="font-size: 7.5pt; color: #6b7280; font-style: italic; margin-bottom: 4px;">No photo documented.</div>
        @endif

        {{-- After Repair Section --}}
        @if ($hasAfter)
            <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin-top: 4px; margin-bottom: 2px; color: #4b5563;">After Repair</div>
            @foreach (array_chunk($afterPhotos, 3) as $chunk)
                <table style="width: 100%; border-collapse: separate; border-spacing: 6px; border: none; margin-bottom: 4px;">
                    <tr>
                        @foreach ($chunk as $photo)
                            <td style="width: 31%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 6px; border-radius: 4px; height: {{ $photoHeight }}px; text-align: center; vertical-align: middle;">
                                    <table style="width: 100%; height: {{ $innerCellHeight }}px; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle; border: none; padding: 0; height: {{ $innerCellHeight }}px;">
                                                <img src="{{ $photo }}" style="max-width: 100%; max-height: {{ $innerCellHeight }}px; vertical-align: middle;" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="font-size: 7pt; font-weight: bold; margin-top: 4px; color: #4b5563; text-align: center;">After Repair</div>
                            </td>
                        @endforeach
                        @for ($i = count($chunk); $i < 3; $i++)
                            <td style="width: 31%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 6px; border-radius: 4px; height: {{ $photoHeight }}px;"></div>
                                <div style="font-size: 7pt; font-weight: bold; margin-top: 4px; color: transparent; text-align: center;">&nbsp;</div>
                            </td>
                        @endfor
                    </tr>
                </table>
            @endforeach
        @else
            <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin-top: 4px; margin-bottom: 2px; color: #4b5563;">After Repair</div>
            <div style="font-size: 7.5pt; color: #6b7280; font-style: italic; margin-bottom: 4px;">No photo documented.</div>
        @endif
    @endif

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
