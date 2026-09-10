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
            border: 1px solid #475569;
            color: #0f172a;
            background-color: #f1f5f9;
        }
        .stamp-pending {
            border: 1px dashed #cbd5e1;
            color: #64748b;
            background-color: #ffffff;
        }
        .stamp-rejected {
            border: 1px solid #94a3b8;
            color: #334155;
            background-color: #f1f5f9;
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
            <td class="bg-soft-blue"><span class="summary-label">Machine</span></td>
            <td><span class="summary-value font-mono">{{ $case->machine ? ($case->machine->code . ' / ' . $case->machine->name) : '-' }}</span></td>
            <td class="bg-soft-blue"><span class="summary-label">Department</span></td>
            <td><span class="summary-value">{{ $case->machine ? ($case->machine->department . ' / ' . $case->machine->production_area) : '-' }}</span></td>
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
                    {{ $case->machine_down ? 'MACHINE DOWN / DOWNTIME' : 'RUNNING / NON-DOWNTIME' }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Procurement Type</span></td>
            <td style="width: 30%;">
                <span class="summary-value font-mono" style="font-weight: bold;">
                    @if($case->sourcing_type === 'import')
                        IMPORT
                    @elseif($case->sourcing_type === 'local')
                        LOCAL
                    @else
                        -
                    @endif
                </span>
            </td>
            <td class="bg-gray" style="width: 20%;"><span class="summary-label">Quantity Required</span></td>
            <td style="width: 30%;">
                <span class="summary-value font-mono" style="font-weight: bold;">
                    {{ $case->quantity_required ? $case->quantity_required . ' Unit' : '-' }}
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

    <!-- Section 2: Approval Summary -->
    <div class="section-header">2. Approval Summary</div>
    <table class="summary-table" style="margin-bottom: 5px;">
        <tr>
            <td class="bg-gray" style="width: 25%;"><span class="summary-label">Submitted</span></td>
            <td style="width: 25%;">
                @if($approvals['admin'])
                    <span class="summary-value font-mono">{{ $approvals['admin']['date'] }}</span>
                @else
                    <span class="summary-value font-mono">-</span>
                @endif
            </td>
            <td class="bg-gray" style="width: 25%;"><span class="summary-label">Current Status</span></td>
            <td style="width: 25%;">
                <span class="summary-value font-mono">{{ strtoupper($case->status->value ?? $case->status) }}</span>
            </td>
        </tr>
        <tr>
            <td class="bg-gray"><span class="summary-label">Approved by Kabag</span></td>
            <td>
                @if($approvals['kabag'] && $approvals['kabag']['status'] === 'approved')
                    <span class="summary-value font-mono">{{ $approvals['kabag']['date'] }}</span>
                @else
                    <span class="summary-value font-mono">-</span>
                @endif
            </td>
            <td class="bg-gray"><span class="summary-label">Approved by Director</span></td>
            <td>
                @if($approvals['director'] && $approvals['director']['status'] === 'approved')
                    <span class="summary-value font-mono">{{ $approvals['director']['date'] }}</span>
                @elseif($approvals['director'] && $approvals['director']['status'] === 'skipped')
                    <span class="summary-value font-mono" style="color: #64748b;">SKIPPED (DELEGATED)</span>
                @else
                    <span class="summary-value font-mono">-</span>
                @endif
            </td>
        </tr>
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
            @foreach (array_chunk($attachments['images'], 3) as $chunkRow => $chunk)
                <table style="width: 100%; border-collapse: separate; border-spacing: 4px; border: none; margin-bottom: 2px;">
                    <tr>
                        @foreach ($chunk as $idxInChunk => $imgPath)
                            <td style="width: 32%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #cbd5e1; background: #f8fafc; padding: 4px; border-radius: 4px; height: 120px; text-align: center; vertical-align: middle;">
                                    <table style="width: 100%; height: 112px; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle; border: none; padding: 0; height: 112px;">
                                                <img src="{{ $imgPath }}" style="max-width: 100%; max-height: 112px; vertical-align: middle; object-fit: contain;" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        @endforeach
                        @for ($i = count($chunk); $i < 3; $i++)
                            <td style="width: 32%; text-align: center; border: none; vertical-align: top; padding: 0;">
                                <div style="border: 1px solid #cbd5e1; background: #f8fafc; padding: 4px; border-radius: 4px; height: 120px;"></div>
                            </td>
                        @endfor
                    </tr>
                </table>
            @endforeach
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

    <!-- Section 4: Digital Approval Signatures -->
    <div class="section-header">4. Digital Authorization Signatures</div>
    <table class="sig-table">
        <tr>
            {{-- Admin Maintenance Signature --}}
            <td>
                <div class="sig-title">Admin Maintenance</div>
                @if($approvals['admin'])
                    <div class="sig-stamp stamp-approved">DIGITAL APPROVED</div>
                    <div style="font-size: 7pt; font-weight: bold; color: #1f2937;">{{ $approvals['admin']['name'] }}</div>
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
                    <div style="font-size: 7pt; font-weight: bold; color: #1f2937;">{{ $approvals['kabag']['name'] }}</div>
                    <div class="sig-meta">
                        Date: {{ $approvals['kabag']['date'] }}<br/>
                        IP: {{ $approvals['kabag']['ip'] }}
                        @if(isset($approvals['kabag']['note']))
                            <br/><span style="font-style: italic; color: #475569;">"{{ $approvals['kabag']['note'] }}"</span>
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
                        @php
                            $directorQrUrl = route('procurements.show', $case->id);
                            $qrOptions = new \chillerlan\QRCode\QROptions([
                                'outputBase64' => true,
                                'scale' => 2,
                                'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
                            ]);
                            $directorQrCode = (new \chillerlan\QRCode\QRCode($qrOptions))->render($directorQrUrl);
                        @endphp
                        <table style="width: 100%; border: none; margin: 4px 0; padding: 0;">
                            <tr style="border: none;">
                                <td style="width: 40%; border: none; padding: 0; text-align: center; vertical-align: middle;">
                                    <img src="{{ $directorQrCode }}" style="width: 48px; height: 48px; display: block; margin: 0 auto;" alt="Verify" />
                                    <div style="font-size: 4.5pt; font-weight: bold; color: #475569; margin-top: 1px; text-transform: uppercase; letter-spacing: 0.2px; text-align: center; line-height: 1.1;">SCAN TO VERIFY</div>
                                </td>
                                <td style="width: 60%; border: none; padding-left: 6px; text-align: left; vertical-align: top;">
                                    <div style="font-size: 7.5pt; font-weight: bold; color: #1f2937;">Direktur</div>
                                    <div style="font-size: 6.5pt; font-weight: bold; color: #475569;">{{ $approvals['director']['name'] }}</div>
                                    <div class="sig-meta" style="margin-top: 2px;">
                                        Status: <span style="font-weight: bold; color: #0f172a;">APPROVED</span><br/>
                                        Date: {{ $approvals['director']['date'] }}<br/>
                                        IP: {{ $approvals['director']['ip'] }}
                                        @if(isset($approvals['director']['note']))
                                            <br/><span style="font-style: italic; color: #475569;">"{{ $approvals['director']['note'] }}"</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    @elseif($approvals['director']['status'] === 'skipped')
                        <div class="sig-stamp stamp-pending" style="border: 1px dashed #94a3b8; color: #475569; background-color: #f8fafc;">SKIPPED</div>
                        <div style="font-size: 6.5pt; font-weight: bold; color: #475569; text-transform: uppercase; margin-top: 2px;">NOT REQUIRED</div>
                        <div class="sig-meta" style="margin-top: 2px;">
                            Delegated Authority by:<br/>
                            <strong>{{ $approvals['director']['name'] }}</strong><br/>
                            Date: {{ $approvals['director']['date'] }}
                        </div>
                    @else
                        <div class="sig-stamp stamp-rejected">REJECTED</div>
                        <div style="font-size: 7pt; font-weight: bold; color: #1f2937;">{{ $approvals['director']['name'] }}</div>
                        <div class="sig-meta">
                            Date: {{ $approvals['director']['date'] }}<br/>
                            IP: {{ $approvals['director']['ip'] }}
                            @if(isset($approvals['director']['note']))
                                <br/><span style="font-style: italic; color: #475569;">"{{ $approvals['director']['note'] }}"</span>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="sig-stamp stamp-pending">PENDING</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Footer Block -->
    @include('pdf.partials.footer')
@endsection
