@extends('pdf.layout')

@section('title', 'Procurement Case Report - ' . $doc_number)

@section('content')
    <style>
        /* Section styling */
        .section-header {
            font-size: 8pt;
            font-weight: 800;
            color: #1e3a8a;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .timeline-table th {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            font-size: 6.5pt;
            text-transform: uppercase;
        }
        
        .timeline-table td {
            border: 1px solid #e5e7eb;
            font-size: 7pt;
            padding: 4px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 6pt;
        }
        .status-approved { background-color: #dcfce7; color: #15803d; }
        .status-pending { background-color: #f3f4f6; color: #4b5563; }
        .status-rejected { background-color: #fee2e2; color: #b91c1c; }
        .status-returned { background-color: #ffedd5; color: #c2410c; }

        /* Signature block */
        .sig-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .sig-table td {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
            vertical-align: top;
            background-color: #f8fafc;
        }
        .sig-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .sig-stamp {
            font-size: 8pt;
            font-weight: bold;
            padding: 3px;
            border-radius: 3px;
            margin: 4px auto;
            width: 80%;
            text-align: center;
        }
        .stamp-approved {
            border: 2px dashed #16a34a;
            color: #16a34a;
            background-color: #f0fdf4;
        }
        .stamp-pending {
            border: 2px dashed #94a3b8;
            color: #94a3b8;
            background-color: #f8fafc;
        }
        .stamp-rejected {
            border: 2px dashed #dc2626;
            color: #dc2626;
            background-color: #fef2f2;
        }
        
        .sig-meta {
            font-size: 6pt;
            color: #64748b;
            line-height: 1.2;
            margin-top: 4px;
        }

        /* Attachment preview cards */
        .img-grid-table td {
            width: 25%;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .img-thumbnail {
            max-width: 100%;
            max-height: 50px;
            object-fit: contain;
            border: 1px solid #cbd5e1;
            border-radius: 2px;
        }

        .doc-card {
            border: 1px solid #cbd5e1;
            background-color: #f1f5f9;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 7pt;
            margin-bottom: 3px;
            display: inline-block;
            width: 48%;
            margin-right: 1%;
            box-sizing: border-box;
            vertical-align: top;
        }
        .doc-label {
            font-weight: bold;
            color: #fff;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 5.5pt;
            margin-right: 3px;
            vertical-align: middle;
        }
        .label-pdf { background-color: #ef4444; }
        .label-xls { background-color: #22c55e; }
        .label-doc { background-color: #3b82f6; }
        .label-file { background-color: #6b7280; }
    </style>

    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td style="width: 75%; padding-left: 0;">
                <div class="header-logo">PT PERONI KARYA SENTRA</div>
                <div class="header-title">PROCUREMENT CASE REPORT</div>
                <div style="font-size: 6.5pt; color: #6b7280; margin-top: 2px;">Permanent Archive | ISO 9001 Evidence</div>
            </td>
            <td style="width: 25%; text-align: right; padding-right: 0; vertical-align: middle;">
                <img src="{{ $qrCodeImage }}" style="width: 50px; height: 50px;" alt="QR Link" />
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <table class="summary-table">
        <tr>
            <td class="bg-soft-blue" style="width: 18%;"><span class="summary-label">Case Number</span></td>
            <td style="width: 32%;"><span class="summary-value font-mono">{{ $case->case_number }}</span></td>
            <td class="bg-soft-blue" style="width: 18%;"><span class="summary-label">Priority</span></td>
            <td style="width: 32%;">
                <span class="summary-value @if(($case->urgency->value ?? $case->urgency) === 'urgent') text-red @endif">
                    {{ strtoupper($case->urgency->value ?? $case->urgency) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-soft-blue"><span class="summary-label">Current Status</span></td>
            <td><span class="summary-value font-mono">{{ strtoupper($case->status->value ?? $case->status) }}</span></td>
            <td class="bg-soft-blue"><span class="summary-label">Current Owner</span></td>
            <td><span class="summary-value">{{ $case->current_owner ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-soft-blue"><span class="summary-label">Target Needed</span></td>
            <td><span class="summary-value font-mono">{{ $case->target_needed_date ? $case->target_needed_date->format('d M Y') : '-' }}</span></td>
            <td class="bg-soft-blue"><span class="summary-label">Created Date</span></td>
            <td><span class="summary-value font-mono">{{ $case->created_at ? $case->created_at->format('d M Y H:i') : '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-soft-blue"><span class="summary-label">Machine Code</span></td>
            <td><span class="summary-value font-mono">{{ $case->machine->code ?? '-' }}</span></td>
            <td class="bg-soft-blue"><span class="summary-label">Department</span></td>
            <td><span class="summary-value">{{ $case->machine->department ?? '-' }}</span></td>
        </tr>
    </table>

    <!-- Section 1: Item Information -->
    <div class="section-header">1. Item Information</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Item Name</span></td>
            <td colspan="3"><span class="summary-value" style="font-size: 8pt; color: #1e3a8a;">{{ $case->item_name }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Category</span></td>
            <td style="width: 30%;"><span class="summary-value">{{ $case->category->name ?? '-' }}</span></td>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Operational Impact</span></td>
            <td style="width: 30%;">
                <span class="summary-value @if($case->machine_down) text-red @else text-green @endif">
                    {{ $case->machine_down ? '🔴 MACHINE DOWN / DOWNTIME' : '🟢 RUNNING / NON-DOWNTIME' }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Reason / Urgency</span></td>
            <td colspan="3"><span class="summary-value" style="font-weight: normal;">{{ $case->reason ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Description</span></td>
            <td colspan="3"><span class="summary-value" style="font-weight: normal; font-style: italic;">{{ $case->description }}</span></td>
        </tr>
    </table>

    <!-- Section 2: Approval Timeline -->
    <div class="section-header">2. Approval Timeline</div>
    <table class="timeline-table">
        <thead>
            <tr>
                <th style="width: 25%;">Workflow Stage</th>
                <th style="width: 20%;">Approval Status</th>
                <th style="width: 20%;">Signer / Authorized</th>
                <th style="width: 35%;">Action Notes</th>
            </tr>
        </thead>
        <tbody>
            {{-- Stage 1: Request Creation --}}
            <tr>
                <td class="font-bold">1. Admin Maintenance</td>
                <td><span class="status-badge status-approved">✔ SUBMITTED</span></td>
                <td>{{ $case->creator->name ?? '-' }}</td>
                <td>Request created and workflow initialized.</td>
            </tr>

            {{-- Stage 2: Kabag Approval --}}
            @php
                $kabagApp = $case->approvals->where('stage', 1)->first();
            @endphp
            <tr>
                <td class="font-bold">2. Kabag Maintenance</td>
                <td>
                    @if($kabagApp)
                        @if($kabagApp->decision === 'approved')
                            <span class="status-badge status-approved">✔ APPROVED</span>
                        @elseif($kabagApp->decision === 'rejected')
                            <span class="status-badge status-rejected">✘ REJECTED</span>
                        @else
                            <span class="status-badge status-returned">↩ NEED INFO</span>
                        @endif
                    @elseif($case->status->value === 'draft' || $case->status->value === 'need_info')
                        <span class="status-badge status-pending">DRAFT / WAITING</span>
                    @elseif($case->status->value === 'pending_kabag')
                        <span class="status-badge status-pending" style="background-color: #fef08a; color: #854d0e;">● PENDING</span>
                    @else
                        <span class="status-badge status-approved">✔ APPROVED BYPASS</span>
                    @endif
                </td>
                <td>{{ $kabagApp->user->name ?? ($kabagApp ? 'Kabag' : '-') }}</td>
                <td>{{ $kabagApp->note ?? ($case->status->value === 'pending_kabag' ? 'Awaiting review from Maintenance Manager.' : '-') }}</td>
            </tr>

            {{-- Stage 3: Director Approval --}}
            @php
                $dirApp = $case->approvals->where('stage', 2)->first();
            @endphp
            <tr>
                <td class="font-bold">3. Director</td>
                <td>
                    @if($dirApp)
                        @if($dirApp->decision === 'approved')
                            <span class="status-badge status-approved">✔ APPROVED</span>
                        @elseif($dirApp->decision === 'rejected')
                            <span class="status-badge status-rejected">✘ REJECTED</span>
                        @else
                            <span class="status-badge status-returned">↩ NEED INFO</span>
                        @endif
                    @elseif(in_array($case->status->value, ['draft', 'need_info', 'pending_kabag']))
                        <span class="status-badge status-pending">WAITING PREV</span>
                    @elseif($case->status->value === 'pending_dir')
                        <span class="status-badge status-pending" style="background-color: #fef08a; color: #854d0e;">● PENDING</span>
                    @else
                        <span class="status-badge status-approved">✔ APPROVED BYPASS</span>
                    @endif
                </td>
                <td>{{ $dirApp->user->name ?? ($dirApp ? 'Director' : '-') }}</td>
                <td>{{ $dirApp->note ?? ($case->status->value === 'pending_dir' ? 'Awaiting final approval from Executive Board.' : '-') }}</td>
            </tr>

            {{-- Stage 4: Purchasing --}}
            <tr>
                <td class="font-bold">4. Purchasing Process</td>
                <td>
                    @if($case->po_number)
                        <span class="status-badge status-approved">✔ PO INPUTTED</span>
                    @elseif(in_array($case->status->value, ['draft', 'need_info', 'pending_kabag', 'pending_dir']))
                        <span class="status-badge status-pending">WAITING APPROVAL</span>
                    @elseif($case->status->value === 'processing')
                        <span class="status-badge status-pending" style="background-color: #fef08a; color: #854d0e;">● PROCESSING</span>
                    @else
                        <span class="status-badge status-approved">✔ PROCESSED</span>
                    @endif
                </td>
                <td>Purchasing Dept</td>
                <td>
                    @if($case->po_number)
                        PO: {{ $case->po_number }} | Vendor: {{ $case->vendor_name ?? '-' }}
                    @else
                        -
                    @endif
                </td>
            </tr>

            {{-- Stage 5: Warehouse Delivery --}}
            <tr>
                <td class="font-bold">5. Warehouse Receiving</td>
                <td>
                    @if($case->rack_location)
                        <span class="status-badge status-approved">✔ ARRIVED</span>
                    @elseif(in_array($case->status->value, ['draft', 'need_info', 'pending_kabag', 'pending_dir', 'processing']))
                        <span class="status-badge status-pending">WAITING PO</span>
                    @elseif($case->status->value === 'waiting_delivery')
                        <span class="status-badge status-pending" style="background-color: #fef08a; color: #854d0e;">● IN TRANSIT</span>
                    @else
                        <span class="status-badge status-approved">✔ ARRIVED</span>
                    @endif
                </td>
                <td>Warehouse Dept</td>
                <td>
                    @if($case->rack_location)
                        Location: {{ $case->rack_location }}
                    @else
                        -
                    @endif
                </td>
            </tr>

            {{-- Stage 6: Completion / Pickup --}}
            <tr>
                <td class="font-bold">6. Completed / Closed</td>
                <td>
                    @if($case->status->value === 'closed')
                        <span class="status-badge status-approved">✔ CLOSED</span>
                    @elseif($case->status->value === 'cancelled')
                        <span class="status-badge status-rejected">✘ CANCELLED</span>
                    @elseif($case->status->value === 'ready_to_pickup')
                        <span class="status-badge status-pending" style="background-color: #fef08a; color: #854d0e;">● READY TO PICKUP</span>
                    @else
                        <span class="status-badge status-pending">IN PROGRESS</span>
                    @endif
                </td>
                <td>Admin MTC</td>
                <td>
                    @if($case->status->value === 'closed')
                        Sparepart picked up and request archived.
                    @elseif($case->status->value === 'cancelled')
                        Request cancelled.
                    @else
                        -
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Section 3: Attachment Gallery -->
    <div class="section-header">3. Attachment Gallery</div>
    @if(empty($attachments['images']) && empty($attachments['non_images']))
        <table style="border: 1px solid #cbd5e1; background-color: #f8fafc; margin-bottom: 5px;">
            <tr>
                <td style="padding: 10px; text-align: center; color: #94a3b8; font-style: italic;">
                    No attachments uploaded.
                </td>
            </tr>
        </table>
    @else
        {{-- Images Grid (defensive tables layout) --}}
        @if(!empty($attachments['images']))
            <table class="img-grid-table" style="margin-bottom: 5px;">
                <tr>
                    @foreach($attachments['images'] as $idx => $imgPath)
                        <td>
                            <img src="{{ $imgPath }}" class="img-thumbnail" alt="Attachment" />
                            <div style="font-size: 5pt; color: #64748b; margin-top: 2px;">Image {{ $idx + 1 }}</div>
                        </td>
                        @if(($idx + 1) % 4 == 0 && ($idx + 1) < count($attachments['images']))
                            </tr><tr>
                        @endif
                    @endforeach
                    
                    {{-- Fill remaining table cells if row is not full --}}
                    @php
                        $rem = count($attachments['images']) % 4;
                    @endphp
                    @if($rem > 0)
                        @for($i = 0; $i < (4 - $rem); $i++)
                            <td style="border: none; background: transparent;"></td>
                        @endfor
                    @endif
                </tr>
            </table>
            @if($attachments['additional_images_count'] > 0)
                <div style="font-size: 7pt; color: #b91c1c; font-weight: bold; margin-bottom: 6px; background-color: #fef2f2; padding: 4px; border: 1px solid #fee2e2; border-radius: 4px;">
                    + {{ $attachments['additional_images_count'] }} additional attachments. Please scan the QR Code on the header to view all files.
                </div>
            @endif
        @endif

        {{-- Non-Images List --}}
        @if(!empty($attachments['non_images']))
            <div style="margin-top: 2px;">
                @foreach($attachments['non_images'] as $doc)
                    <div class="doc-card">
                        <span class="doc-label label-{{ strtolower($doc['label']) }}">{{ $doc['label'] }}</span>
                        <span style="font-weight: bold; color: #334155;">{{ truncate_filename($doc['original_filename'], 28) }}</span>
                        <span style="color: #64748b; font-size: 6pt;">
                            ({{ $doc['file_size'] >= 1048576 ? number_format($doc['file_size']/1048576,1).'MB' : number_format($doc['file_size']/1024,0).'KB' }})
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @php
        // Simple file naming truncate function
        if (!function_exists('truncate_filename')) {
            function truncate_filename($filename, $limit = 30) {
                if (strlen($filename) <= $limit) return $filename;
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $name = pathinfo($filename, PATHINFO_FILENAME);
                return substr($name, 0, $limit - strlen($ext) - 5) . '...' . $ext;
            }
        }
    @endphp

    <!-- Section 4: Machine Information -->
    <div class="section-header">4. Machine Information</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Machine Name</span></td>
            <td style="width: 30%;"><span class="summary-value">{{ $case->machine->name ?? '-' }}</span></td>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Machine Code</span></td>
            <td style="width: 30%;"><span class="summary-value font-mono">{{ $case->machine->code ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Department</span></td>
            <td><span class="summary-value">{{ $case->machine->department ?? '-' }}</span></td>
            <td class="bg-gray"><span class="summary-label">Production Area</span></td>
            <td><span class="summary-value">{{ $case->machine->production_area ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Machine Status</span></td>
            <td colspan="3"><span class="summary-value font-mono">{{ strtoupper($case->machine->operational_status ?? '-') }}</span></td>
        </tr>
    </table>

    <!-- Section 5: Purchasing & Logistics Panel -->
    <div class="section-header">5. Purchasing & Logistics Panel</div>
    <table class="summary-table">
        <tr>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Vendor Name</span></td>
            <td style="width: 30%;"><span class="summary-value">{{ $case->vendor_name ?? '-' }}</span></td>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Quotation Ref</span></td>
            <td style="width: 30%;"><span class="summary-value">-</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">PO Number</span></td>
            <td><span class="summary-value font-mono">{{ $case->po_number ?? '-' }}</span></td>
            <td class="bg-gray"><span class="summary-label">PO Date</span></td>
            <td><span class="summary-value font-mono">{{ $case->po_date ? $case->po_date->format('d M Y') : '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Expected Arrival</span></td>
            <td><span class="summary-value">-</span></td>
            <td class="bg-gray"><span class="summary-label">Actual Arrival</span></td>
            <td><span class="summary-value font-mono">{{ $case->rack_location ? 'Confirmed' : '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Receiving Status</span></td>
            <td>
                <span class="summary-value font-mono">
                    @if($case->rack_location)
                        RECEIVED / STORED
                    @elseif($case->po_number)
                        WAITING FOR DELIVERY
                    @else
                        PENDING ORDER
                    @endif
                </span>
            </td>
            <td class="bg-gray"><span class="summary-label">Rack Location</span></td>
            <td><span class="summary-value font-mono text-blue">{{ $case->rack_location ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Purchasing Notes</span></td>
            <td colspan="3"><span class="summary-value" style="font-weight: normal;">-</span></td>
        </tr>
    </table>

    <!-- Section 6: Audit History -->
    <div class="section-header">6. Chronological Audit History</div>
    @if(empty($timeline))
        <table style="border: 1px solid #cbd5e1; background-color: #f8fafc; margin-bottom: 5px;">
            <tr>
                <td style="padding: 8px; text-align: center; color: #94a3b8; font-style: italic;">
                    No formal approvals processed yet.
                </td>
            </tr>
        </table>
    @else
        <table class="timeline-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Stage</th>
                    <th style="width: 15%;">Decision</th>
                    <th style="width: 35%;">Comment / Note</th>
                    <th style="width: 15%;">Datetime</th>
                    <th style="width: 10%;">User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($timeline as $item)
                    <tr>
                        <td class="font-bold">{{ $item['stage'] }}</td>
                        <td>
                            @if(strtolower($item['decision']) === 'approved')
                                <span class="status-badge status-approved">APPROVED</span>
                            @elseif(strtolower($item['decision']) === 'rejected')
                                <span class="status-badge status-rejected">REJECTED</span>
                            @else
                                <span class="status-badge status-returned">RETURNED</span>
                            @endif
                        </td>
                        <td>{{ $item['comment'] }}</td>
                        <td class="font-mono">{{ $item['datetime'] }}</td>
                        <td>{{ $item['user'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Section 7: Digital Approval Signatures -->
    <div class="section-header">7. Digital Authorization Signatures</div>
    <table class="sig-table">
        <tr>
            {{-- Admin Maintenance Signature --}}
            <td>
                <div class="sig-title">Admin Maintenance</div>
                @if($approvals['admin'])
                    <div class="sig-stamp stamp-approved">DIGITAL APPROVED</div>
                    <div style="font-size: 7pt; font-weight: bold; color: #1e3a8a;">{{ $approvals['admin']['name'] }}</div>
                    <div class="sig-meta">
                        Date: {{ $approvals['admin']['date'] }}<br/>
                        IP: {{ $approvals['admin']['ip'] }}
                    </div>
                @else
                    <div class="sig-stamp stamp-pending">PENDING</div>
                @endif
            </td>

            {{-- Kabag Maintenance Signature --}}
            <td>
                <div class="sig-title">Kabag Maintenance</div>
                @if($approvals['kabag'])
                    @if($approvals['kabag']['status'] === 'approved')
                        <div class="sig-stamp stamp-approved">DIGITAL APPROVED</div>
                    @else
                        <div class="sig-stamp stamp-rejected">REJECTED</div>
                    @endif
                    <div style="font-size: 7pt; font-weight: bold; color: #1e3a8a;">{{ $approvals['kabag']['name'] }}</div>
                    <div class="sig-meta">
                        Date: {{ $approvals['kabag']['date'] }}<br/>
                        IP: {{ $approvals['kabag']['ip'] }}
                        @if(isset($approvals['kabag']['note']))
                            <br/><span style="font-style: italic; color: #b91c1c;">"{{ $approvals['kabag']['note'] }}"</span>
                        @endif
                    </div>
                @else
                    <div class="sig-stamp stamp-pending">PENDING</div>
                @endif
            </td>

            {{-- Director Signature --}}
            <td>
                <div class="sig-title">Director</div>
                @if($approvals['director'])
                    @if($approvals['director']['status'] === 'approved')
                        <div class="sig-stamp stamp-approved">DIGITAL APPROVED</div>
                    @else
                        <div class="sig-stamp stamp-rejected">REJECTED</div>
                    @endif
                    <div style="font-size: 7pt; font-weight: bold; color: #1e3a8a;">{{ $approvals['director']['name'] }}</div>
                    <div class="sig-meta">
                        Date: {{ $approvals['director']['date'] }}<br/>
                        IP: {{ $approvals['director']['ip'] }}
                        @if(isset($approvals['director']['note']))
                            <br/><span style="font-style: italic; color: #b91c1c;">"{{ $approvals['director']['note'] }}"</span>
                        @endif
                    </div>
                @else
                    <div class="sig-stamp stamp-pending">PENDING</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Footer Block -->
    @include('pdf.partials.footer')
@endsection
