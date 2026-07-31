<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order | {{ $plan->machine->code }} | PT Peroni Karya Sentra</title>
    <!-- Use local tailwind.js for offline LAN deployment compatibility -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <style>
        @media print {
            body {
                background-color: #fff;
                color: #000;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-gray-150 font-sans text-gray-900 antialiased p-4 sm:p-6 print:p-0 print:bg-white">

    <!-- Print Control Banner (Hidden during print) -->
    <div class="no-print max-w-3xl mx-auto mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-sm font-bold text-gray-800">Pratinjau Cetak Lembar Kerja</h1>
            <p class="text-xs text-gray-500">Tekan tombol di sebelah kanan atau gunakan pintasan browser <kbd class="px-1.5 py-0.5 bg-gray-100 rounded border font-mono">Ctrl + P</kbd> untuk mencetak.</p>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('planning.show', $plan->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 text-xs font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                Kembali
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-xs font-bold rounded-lg text-white transition-colors shadow-sm">
                Cetak Work Order
            </button>
        </div>
    </div>

    <!-- Main Work Order Document Sheet -->
    <div class="max-w-3xl mx-auto bg-white border border-gray-300 rounded-xl shadow-sm p-8 print:border-0 print:shadow-none print:p-0">
        
        <!-- Header Separator line -->
        <div class="border-t-4 border-double border-gray-800 my-2 print:my-0"></div>

        <!-- Top Header Block -->
        <div class="text-center py-4">
            <h1 class="text-xl font-extrabold tracking-widest text-gray-900 uppercase">PT PERONI KARYA SENTRA</h1>
            <h2 class="text-md font-bold tracking-widest text-gray-600 uppercase mt-1">WORK ORDER</h2>
        </div>

        <!-- Double Separator -->
        <div class="border-b-2 border-gray-800 mb-6"></div>

        <!-- Metadata Section Grid -->
        <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm mb-6 pb-6 border-b border-dashed border-gray-300">
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">WO</span>
                <span class="font-mono font-bold text-gray-900 text-base">
                    #{{ $plan->isPreventive() ? 'PM' : 'CM' }}-{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">Machine</span>
                <span class="font-semibold text-gray-800">
                    {{ $plan->machine->code }} &mdash; {{ $plan->machine->name }}
                    <span class="text-xs text-gray-500 font-normal">({{ $plan->machine->category }})</span>
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">Priority</span>
                <span class="font-bold uppercase text-xs @if($plan->priority === 'critical') text-red-600 @elseif($plan->priority === 'high') text-orange-600 @else text-blue-600 @endif">
                    {{ $plan->priority === 'critical' ? 'Kritis (Critical)' : ($plan->priority === 'high' ? 'Tinggi (High)' : ($plan->priority === 'medium' ? 'Sedang (Medium)' : 'Rendah (Low)')) }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">Technician</span>
                <span class="font-semibold text-gray-800">
                    {{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">Schedule</span>
                <span class="font-medium text-gray-800">
                    {{ $plan->scheduled_date->format('d M Y') }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block uppercase font-bold">Target Finish</span>
                <span class="font-semibold text-blue-800 font-mono">
                    {{ $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-' }}
                </span>
            </div>
        </div>

        <!-- Problem Description Section -->
        <div class="mb-6 pb-6 border-b border-dashed border-gray-300">
            <h3 class="text-xs font-extrabold uppercase text-gray-500 tracking-wider mb-2">Problem Description</h3>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-sm">
                @if ($plan->isCorrective())
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-2 font-mono pb-2 border-b border-gray-200">
                        <span>No. Kerusakan: <strong class="text-gray-700">{{ $plan->breakdown_number }}</strong></span>
                        <span>Pelapor: <strong class="text-gray-700">{{ $plan->reported_by }} ({{ $plan->reported_department }})</strong></span>
                    </div>
                    <p class="text-gray-800 font-semibold italic">"{{ $plan->notes ?? 'Laporan kerusakan mesin lapangan.' }}"</p>
                @else
                    <p class="text-gray-800 italic">"{{ $plan->notes ?? 'Paket pemeliharaan berkala / tindakan pencegahan preventif.' }}"</p>
                @endif
            </div>
        </div>

        <!-- Repair Instruction Section -->
        <div class="mb-6 pb-6 border-b border-dashed border-gray-300">
            <h3 class="text-xs font-extrabold uppercase text-gray-500 tracking-wider mb-3">Repair Instruction</h3>
            
            @if($plan->isPreventive() && $plan->maintenanceTemplate)
                <div class="space-y-4">
                    <!-- Checklists Table -->
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs rounded overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-center font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                                <th scope="col" class="px-3 py-2 text-left font-bold text-gray-500 uppercase tracking-wider">Item Tindakan</th>
                                <th scope="col" class="px-3 py-2 text-left font-bold text-gray-500 uppercase tracking-wider">Panduan / Keterangan</th>
                                <th scope="col" class="px-3 py-2 text-center font-bold text-gray-500 uppercase tracking-wider w-14">Status</th>
                                <th scope="col" class="px-3 py-2 text-center font-bold text-gray-500 uppercase tracking-wider w-24">Skor (1-5)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($plan->maintenanceTemplate->checklists as $idx => $item)
                                <tr>
                                    <td class="px-3 py-2.5 text-center font-mono text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2.5 font-bold text-gray-800">{{ $item->title }}</td>
                                    <td class="px-3 py-2.5 text-gray-600">{{ $item->description ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-center font-bold">
                                        @if($item->is_required)
                                            <span class="text-red-700 bg-red-50 text-[9px] px-1.5 py-0.5 rounded border border-red-200 font-bold uppercase">Wajib</span>
                                        @else
                                            <span class="text-gray-400 text-[9px] uppercase">Opsional</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono text-gray-400">
                                        [1] [2] [3] [4] [5]
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-gray-400 italic">Tidak ada checklist instruksi terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Mapped Spareparts list if any -->
                    @if ($plan->maintenanceTemplate->spareparts->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase">Kebutuhan Suku Cadang SOP:</h4>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs rounded overflow-hidden">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-left font-bold text-gray-500 uppercase w-32">Kode Part</th>
                                        <th scope="col" class="px-3 py-2 text-left font-bold text-gray-500 uppercase">Nama Suku Cadang</th>
                                        <th scope="col" class="px-3 py-2 text-center font-bold text-gray-500 uppercase w-20">Qty</th>
                                        <th scope="col" class="px-3 py-2 text-center font-bold text-gray-500 uppercase w-24">Diambil</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 font-mono">
                                    @foreach ($plan->maintenanceTemplate->spareparts as $reqPart)
                                        @php
                                            $wms = app(\App\Repositories\WarehouseRepository::class)->getItemDetails($reqPart->warehouse_item_code);
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 font-bold">{{ $reqPart->warehouse_item_code }}</td>
                                            <td class="px-3 py-2 text-gray-700 font-sans font-medium">{{ $wms['name'] ?? 'Sparepart' }}</td>
                                            <td class="px-3 py-2 text-center font-bold text-gray-800">{{ $reqPart->quantity }} unit</td>
                                            <td class="px-3 py-2 text-center font-bold text-gray-400">[ &nbsp; ]</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @else
                <div class="border border-gray-300 rounded p-4 min-h-[100px] flex flex-col justify-between">
                    <span class="text-xs text-gray-400 uppercase font-semibold">Tulis petunjuk perbaikan atau tindakan corrective di bawah ini:</span>
                    <div class="border-b border-dashed border-gray-300 w-full mb-2"></div>
                    <div class="border-b border-dashed border-gray-300 w-full mb-2"></div>
                    <div class="border-b border-dashed border-gray-300 w-full"></div>
                </div>
            @endif
        </div>

        <!-- Audit Readiness Section -->
        <div class="mb-6 pb-6 border-b border-dashed border-gray-300">
            <h3 class="text-xs font-extrabold uppercase text-gray-500 tracking-wider mb-3">Audit Readiness</h3>
            
            <div class="grid grid-cols-4 gap-4 text-sm font-semibold">
                <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    @if ($readiness['machine_ready'])
                        <span class="text-green-600 text-lg font-bold">✓</span>
                        <span class="text-gray-800 font-bold">Machine</span>
                    @else
                        <span class="text-red-600 text-lg font-bold">✗</span>
                        <span class="text-gray-800 font-bold">Machine</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    @if ($readiness['technician_assigned'])
                        <span class="text-green-600 text-lg font-bold">✓</span>
                        <span class="text-gray-800 font-bold">Technician</span>
                    @else
                        <span class="text-red-600 text-lg font-bold">✗</span>
                        <span class="text-gray-800 font-bold">Technician</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    @if ($readiness['documents_available'])
                        <span class="text-green-600 text-lg font-bold">✓</span>
                        <span class="text-gray-800 font-bold">Manual</span>
                    @else
                        <span class="text-amber-500 text-lg font-bold">⚠</span>
                        <span class="text-gray-800 font-bold">Manual</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    @if ($readiness['sparepart_readiness_ready'])
                        <span class="text-green-600 text-lg font-bold">✓</span>
                        <span class="text-gray-800 font-bold">Sparepart</span>
                    @else
                        <span class="text-amber-500 text-lg font-bold">⚠</span>
                        <span class="text-gray-800 font-bold">Sparepart</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Execution Record Section -->
        <div class="mb-6 pb-6 border-b border-dashed border-gray-300">
            <h3 class="text-xs font-extrabold uppercase text-gray-500 tracking-wider mb-3">Execution Record</h3>
            
            <div class="grid grid-cols-3 gap-6 text-sm">
                @if ($plan->status === 'completed' && $plan->execution)
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Start</span>
                        <span class="font-mono font-bold text-gray-800">
                            {{ $plan->execution->started_at ? $plan->execution->started_at->format('d M Y H:i') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Finish</span>
                        <span class="font-mono font-bold text-gray-800">
                            {{ $plan->execution->completed_at ? $plan->execution->completed_at->format('d M Y H:i') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Actual Downtime</span>
                        <span class="font-mono font-bold text-gray-800">
                            {{ $plan->downtime_duration ?? ($plan->execution->completed_at ? $plan->execution->completed_at->diffInMinutes($plan->execution->started_at) : 0) }} Menit
                        </span>
                    </div>
                @else
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Start</span>
                        <span class="text-gray-450 border-b border-gray-400 w-full block mt-2 pt-1 font-mono text-center">Tgl: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Jam: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Finish</span>
                        <span class="text-gray-450 border-b border-gray-400 w-full block mt-2 pt-1 font-mono text-center">Tgl: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Jam: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block uppercase font-bold">Actual Downtime</span>
                        <span class="text-gray-450 border-b border-gray-400 w-full block mt-2 pt-1 font-mono text-center">___________________ Menit</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="mt-8">
            <div class="grid grid-cols-3 gap-6 text-center text-xs font-semibold">
                <div class="flex flex-col justify-between h-28 border border-gray-250 p-3 bg-gray-50 rounded-xl">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Technician</span>
                    <span class="font-mono text-gray-400 text-xs mt-2 block">(....................)</span>
                    <span class="text-[9px] font-bold text-gray-600 mt-1 uppercase truncate">{{ $plan->assigned_technician ?? 'Tanda Tangan Pelaksana' }}</span>
                </div>
                <div class="flex flex-col justify-between h-28 border border-gray-250 p-3 bg-gray-50 rounded-xl">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Supervisor</span>
                    <span class="font-mono text-gray-400 text-xs mt-2 block">(....................)</span>
                    <span class="text-[9px] font-bold text-gray-600 mt-1 uppercase">Tanda Tangan Pengawas</span>
                </div>
                <div class="flex flex-col justify-between h-28 border border-gray-250 p-3 bg-gray-50 rounded-xl">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">QC</span>
                    <span class="font-mono text-gray-400 text-xs mt-2 block">(....................)</span>
                    <span class="text-[9px] font-bold text-gray-600 mt-1 uppercase">Tanda Tangan Quality Control</span>
                </div>
            </div>
        </div>

        <!-- Footer Separator line -->
        <div class="border-b-4 border-double border-gray-800 my-6"></div>

    </div>

</body>
</html>
