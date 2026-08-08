<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Perawatan | {{ $plan->machine->code }}</title>
    <!-- Local tailwind.js for offline LAN support -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <!-- Google Fonts & Material Symbols -->
    <link href="{{ asset('css/material-symbols-outlined.css') }}" rel="stylesheet"/>
    <style>
        /* Touch-friendly styling refinements */
        .rating-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .rating-btn:active {
            transform: scale(0.9);
        }
        /* Sticky progress bar styling */
        .sticky-progress {
            position: sticky;
            top: 0;
            z-index: 50;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 sticky top-0 z-40 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
            <span class="text-sm font-bold tracking-tight text-slate-800">MRM Mobile Check</span>
        </div>
        <div class="text-xs text-slate-500 font-mono">
            {{ $plan->machine->code }}
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-lg w-full mx-auto p-4 flex flex-col justify-center">

        <!-- History Card (At Top) -->
        <div class="bg-slate-800 text-white rounded-2xl shadow-md p-4 mb-4 space-y-3">
            <div class="flex justify-between items-center border-b border-slate-700 pb-2">
                <div>
                    <h2 class="text-base font-black">{{ $historyCard['machine_name'] }}</h2>
                    <span class="text-xs font-mono text-slate-400 font-bold bg-slate-700 px-2 py-0.5 rounded">{{ $historyCard['machine_code'] }}</span>
                </div>
                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-1 rounded bg-blue-600/30 text-blue-400 border border-blue-500/20">
                    Paspor Mesin
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Terakhir Down</span>
                    <strong class="text-slate-200">{{ $historyCard['last_breakdown'] }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Terakhir PM</span>
                    <strong class="text-slate-200">{{ $historyCard['last_preventive'] }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Total Kerusakan</span>
                    <strong class="text-slate-200">{{ $historyCard['corrective_count'] }} Kali</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Rata-rata MTTR</span>
                    <strong class="text-slate-200">{{ $historyCard['average_mttr'] }}</strong>
                </div>
            </div>
        </div>

        @if($plan->isCorrective())
            <!-- PRE-EXECUTION STATE: Mulai Verifikasi Screen -->
            <div id="start-screen" class="bg-white rounded-2xl shadow-md border border-slate-200 p-6 text-center space-y-6">
                <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto shadow-inner animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                
                <div>
                    <h1 class="text-xl font-black text-slate-800">VERIFIKASI PERBAIKAN</h1>
                    <p class="text-xs text-rose-600 font-extrabold uppercase tracking-widest mt-1">Breakdown: {{ $plan->breakdown_number }}</p>
                    <div class="mt-4 bg-slate-50 p-3 rounded-lg border border-slate-100 text-left space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Teknisi Pelaksana:</span>
                            <span class="font-bold text-slate-800">{{ $plan->assigned_technician ?? 'Unassigned' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Dilaporkan Oleh:</span>
                            <span class="font-semibold text-slate-800">{{ $plan->reported_by }} (Dept. {{ $plan->reported_department }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Waktu Breakdown:</span>
                            <span class="font-semibold text-slate-800 font-mono">{{ $plan->reported_at->format('d M Y H:i') }} WIB</span>
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-start-inspection" class="w-full py-4 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-base rounded-xl shadow-lg shadow-rose-200 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Mulai Verifikasi</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>

            <!-- FORM EXECUTION STATE (Hidden by default) -->
            <form action="{{ route('planning.store-execute', $plan->id) }}" method="POST" enctype="multipart/form-data" id="execution-form" class="hidden space-y-5">
                @csrf

                @if ($errors->any())
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-800 space-y-1">
                        <p class="font-bold">Beberapa input tidak valid:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- PART 8: REPAIR SUMMARY CARD -->
                <div class="bg-slate-800 text-white rounded-2xl shadow-md p-5 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Riwayat Perbaikan (Read-only)</h3>
                    <div class="grid grid-cols-2 gap-3 text-[10px] font-semibold text-slate-300">
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Breakdown Number</span>
                            <span class="text-white">{{ $plan->breakdown_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Aset Mesin</span>
                            <span class="text-white">{{ $plan->machine->name }} ({{ $plan->machine->code }})</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Dilaporkan</span>
                            <span class="text-white">{{ $plan->reported_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Penugasan Awal</span>
                            <span class="text-white">{{ $plan->scheduled_date->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Waktu Verifikasi</span>
                            <span class="text-white">{{ now()->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Estimasi Downtime</span>
                            <span class="text-rose-400">
                                @php
                                    $min = now()->diffInMinutes($plan->reported_at);
                                    $hrs = floor($min / 60);
                                    $rem = $min % 60;
                                    echo $hrs > 0 ? "{$hrs} jam {$rem} m" : "{$rem} m";
                                @endphp
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Teknisi Ditunjuk</span>
                            <span class="text-white">{{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[9px] uppercase">Tim Pelaksana / Verifikator</span>
                            <span class="text-white" id="summary-teams">-</span>
                        </div>
                    </div>
                </div>

                <!-- PART 1: VERIFICATION & REPAIR TEAM -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                    <label for="operator_name" class="block text-xs font-bold uppercase text-slate-400">Verified By (Petugas Verifikator)</label>
                    <x-employee-autocomplete 
                        name="operator_name" 
                        id="operator_name" 
                        selected="{{ old('operator_name', auth()->user()->employee?->full_name ?? auth()->user()->name) }}"
                        required="true"
                        placeholder="Pilih nama verifikator..."
                    />
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                    <label class="block text-xs font-bold uppercase text-slate-400 font-bold">Repair Performed By (Tim Perbaikan)</label>
                    <p class="text-[10px] text-slate-400 leading-normal">Tambahkan satu atau lebih teknisi pelaksana perbaikan fisik:</p>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <x-employee-autocomplete 
                                name="temp_repair_performer" 
                                id="repair-performer-autocomplete" 
                                required="false"
                                placeholder="Cari nama teknisi pelaksana..."
                            />
                        </div>
                        <button type="button" id="btn-add-performer" class="px-4 py-3 bg-slate-800 hover:bg-slate-900 text-white font-extrabold text-xs rounded-xl transition-all self-stretch">
                            Tambah
                        </button>
                    </div>
                    <div id="repair-performed-container" class="flex flex-wrap gap-2 pt-2"></div>
                </div>

                <!-- PART 2: OPERATIONAL STATUS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                    <label class="block text-xs font-bold uppercase text-slate-400">Status Operasional Baru</label>
                    <input type="hidden" name="operational_status" id="operational_status" value="running" />
                    
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                            <input type="radio" name="actual_operational_status" value="running" checked class="mt-1 text-blue-600 focus:ring-blue-500" />
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Running</span>
                                <span class="block text-[9px] text-slate-400 leading-none mt-0.5">Machine operates normally.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                            <input type="radio" name="actual_operational_status" value="idle" class="mt-1 text-blue-600 focus:ring-blue-500" />
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Idle</span>
                                <span class="block text-[9px] text-slate-400 leading-none mt-0.5">Machine is operational but waiting for production.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                            <input type="radio" name="actual_operational_status" value="limited_operation" class="mt-1 text-blue-600 focus:ring-blue-500" />
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Limited Operation</span>
                                <span class="block text-[9px] text-slate-400 leading-none mt-0.5">Machine can operate with certain limitations.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                            <input type="radio" name="actual_operational_status" value="temporary_repair" class="mt-1 text-blue-600 focus:ring-blue-500" />
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Temporary Repair</span>
                                <span class="block text-[9px] text-slate-400 leading-none mt-0.5">Machine is usable, but permanent repair has not yet been completed.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- PART 3: MACHINE CONDITION -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
                    <label class="block text-xs font-bold uppercase text-slate-400">Kondisi Mesin Saat Ini (1-5)</label>
                    <div class="grid grid-cols-5 gap-2">
                        @for ($score = 1; $score <= 5; $score++)
                            @php
                                $desc = match($score) {
                                    1 => '1 - Machine requires overhaul or cannot reliably operate.',
                                    2 => '2 - Poor condition. Still operational but requires immediate attention.',
                                    3 => '3 - Fair condition. Temporary repair or several issues remain.',
                                    4 => '4 - Good condition. Minor wear only.',
                                    5 => '5 - Excellent condition. Machine performs normally.',
                                };
                                $colorClass = match($score) {
                                    1 => 'peer-checked:bg-rose-500 peer-checked:text-white peer-checked:ring-rose-200',
                                    2 => 'peer-checked:bg-amber-500 peer-checked:text-white peer-checked:ring-amber-200',
                                    3 => 'peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:ring-yellow-200',
                                    4 => 'peer-checked:bg-blue-500 peer-checked:text-white peer-checked:ring-blue-200',
                                    5 => 'peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:ring-emerald-200',
                                };
                            @endphp
                            <label class="cursor-pointer">
                                <input type="radio" 
                                       name="overall_score" 
                                       value="{{ $score }}" 
                                       class="sr-only peer condition-radio"
                                       data-desc="{{ $desc }}"
                                       required
                                       {{ old('overall_score', 5) == $score ? 'checked' : '' }}>
                                <div class="w-full py-3.5 text-center text-sm font-black text-slate-700 bg-slate-50 border border-slate-200 rounded-xl rating-btn peer-checked:ring-4 peer-checked:border-transparent transition-all {{ $colorClass }}">
                                    {{ $score }}
                                </div>
                            </label>
                        @endfor
                    </div>
                    <div id="condition-description-box" class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold text-slate-500 italic text-center">
                        5 - Excellent condition. Machine performs normally.
                    </div>
                    
                    <div class="space-y-2">
                        <label for="remaining_issues" class="block text-xs font-bold uppercase text-slate-400">Remaining Issues (Masalah Tersisa - Free Text)</label>
                        <textarea id="remaining_issues" rows="2" placeholder="Tuliskan temuan atau masalah tersisa (contoh: Hydraulic cylinder leaks slightly)..." class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <!-- PART 4: REPAIR TYPE -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
                    <label class="block text-xs font-bold uppercase text-slate-400">Repair Type (Jenis Perbaikan)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-center p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all font-bold text-xs text-slate-700">
                            <input type="radio" name="repair_type" value="Permanent" checked class="mr-2 text-blue-600 focus:ring-blue-500" />
                            Permanent Repair
                        </label>
                        <label class="flex items-center justify-center p-3 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all font-bold text-xs text-slate-700">
                            <input type="radio" name="repair_type" value="Temporary" class="mr-2 text-blue-600 focus:ring-blue-500" />
                            Temporary Repair
                        </label>
                    </div>
                    
                    <div id="follow-up-container" class="hidden space-y-2 pt-2">
                        <label for="follow_up_action" class="block text-xs font-bold uppercase text-slate-400">Follow-up Action (Tindakan Lanjutan)</label>
                        <textarea id="follow_up_action" rows="2" placeholder="Contoh: Ganti seal hidrolik saat PM berikutnya..." class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <!-- PART 5: SPAREPARTS AUTECOMPLETE -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                    <label class="block text-xs font-bold uppercase text-slate-400">Sparepart yang Diganti (Integrasi WMS)</label>
                    <p class="text-[10px] text-slate-400 leading-normal">Cari berdasarkan part number, brand, atau spesifikasi:</p>
                    
                    <x-wms-sparepart-autocomplete 
                        id="sparepart-autocomplete" 
                        machineCode="{{ $plan->machine->code }}" 
                        placeholder="Cari sparepart..." 
                    />
                    
                    <div class="pt-3">
                        <table class="w-full text-xs text-left text-slate-600">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold uppercase text-slate-400">
                                    <th class="pb-2">Suku Cadang</th>
                                    <th class="pb-2 w-16 text-center">Qty</th>
                                    <th class="pb-2 w-10 text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="spareparts-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- PART 6: MULTI PHOTO DOCUMENTATION -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-0.5">Dokumentasi Foto Lapangan</h3>
                        <p class="text-xs text-slate-400">Unggah dokumentasi sebelum/setelah perbaikan.</p>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Before Photos -->
                        <div class="space-y-2">
                            <span class="block text-[10px] font-bold uppercase text-slate-400">Sebelum Perbaikan (Optional - Sebelum)</span>
                            <div class="flex gap-2 overflow-x-auto pb-1" id="before-previews-list"></div>
                            <label class="flex items-center justify-center w-full py-3 border border-dashed border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                                <span class="material-symbols-outlined text-[18px] text-slate-400 mr-2">add_a_photo</span>
                                <span class="text-xs font-bold text-slate-500 uppercase">Tambah Foto Sebelum</span>
                                <input type="file" id="before_photo_picker" accept="image/*" class="hidden" multiple>
                            </label>
                            <input type="file" name="photo_before" id="photo_before" class="hidden">
                        </div>

                        <!-- After Photos -->
                        <div class="space-y-2">
                            <span class="block text-[10px] font-bold uppercase text-emerald-600">Sesudah Perbaikan (Required - Sesudah)</span>
                            <div class="flex gap-2 overflow-x-auto pb-1" id="after-previews-list"></div>
                            <label class="flex items-center justify-center w-full py-3 border border-dashed border-slate-200 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                                <span class="material-symbols-outlined text-[18px] text-emerald-600 mr-2">add_a_photo</span>
                                <span class="text-xs font-bold text-emerald-600 uppercase">Tambah Foto Sesudah</span>
                                <input type="file" id="after_photo_picker" accept="image/*" class="hidden" multiple>
                            </label>
                            <input type="file" name="photo" id="photo" class="hidden">
                        </div>
                    </div>
                </div>

                <!-- PART 7: VERIFICATION NOTES -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-2">
                    <label for="notes_original" class="block text-xs font-bold uppercase text-slate-400">Catatan Pemeliharaan / Temuan</label>
                    <textarea id="notes_original" rows="2" placeholder="Tuliskan catatan detail temuan lapangan..." class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    <input type="hidden" name="notes" id="notes" />
                </div>

                @php
                    $isOverdue = $plan->target_completion && now()->gt($plan->target_completion);
                    $delayDuration = '';
                    if ($isOverdue) {
                        $diff = now()->diff($plan->target_completion);
                        $parts = [];
                        if ($diff->d > 0) $parts[] = $diff->d . ' Days';
                        if ($diff->h > 0) $parts[] = $diff->h . ' Hours';
                        if ($diff->i > 0) $parts[] = $diff->i . ' Minutes';
                        $delayDuration = implode(' ', $parts);
                    }
                @endphp

                @if($plan->target_completion)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                        <h3 class="text-sm font-bold text-slate-800 mb-0.5">Analisis Waktu Target</h3>
                        <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-slate-600">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Target Selesai</span>
                                <span class="text-slate-800 font-bold text-sm">{{ $plan->target_completion->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Waktu Sekarang (Verifikasi)</span>
                                <span class="text-slate-800 font-bold text-sm">{{ now()->format('d M Y H:i') }}</span>
                            </div>
                        </div>

                        @if($isOverdue)
                            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2">
                                <span class="material-symbols-outlined text-rose-600 mt-0.5">warning</span>
                                <div>
                                    <strong class="font-bold">Terlambat dari Target (Overdue)</strong>
                                    <p class="mt-0.5 text-rose-700 font-medium">Late by: {{ $delayDuration }}</p>
                                </div>
                            </div>

                            <!-- Delay Analysis Section (Mandatory) -->
                            <div class="border-t border-slate-100 pt-3 space-y-3">
                                <h4 class="text-xs font-bold uppercase text-slate-400">Analisis Keterlambatan (Wajib)</h4>
                                
                                <div class="space-y-1">
                                    <label for="delay_reason_corrective" class="block text-[10px] font-bold uppercase text-slate-500">Alasan Keterlambatan</label>
                                    <select name="delay_reason" id="delay_reason_corrective" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Pilih Alasan Keterlambatan --</option>
                                        <option value="waiting_sparepart">Waiting Sparepart (Menunggu Suku Cadang)</option>
                                        <option value="waiting_production">Waiting Production (Menunggu Produksi)</option>
                                        <option value="waiting_vendor">Waiting Vendor (Menunggu Vendor)</option>
                                        <option value="waiting_approval">Waiting Approval (Menunggu Persetujuan)</option>
                                        <option value="additional_damage">Additional Damage Found (Kerusakan Tambahan Ditemukan)</option>
                                        <option value="manpower_shortage">Manpower Shortage (Kekurangan Personel)</option>
                                        <option value="power_failure">Power Failure (Kegagalan Listrik/Daya)</option>
                                        <option value="other">Other (Lainnya)</option>
                                    </select>
                                </div>

                                <div id="delay_notes_container_corrective" class="hidden space-y-1">
                                    <label for="delay_notes_corrective" class="block text-[10px] font-bold uppercase text-slate-500">Catatan Detail Keterlambatan</label>
                                    <textarea name="delay_notes" id="delay_notes_corrective" rows="2" placeholder="Jelaskan detail alasan keterlambatan..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const selectEl = document.getElementById('delay_reason_corrective');
                                    const notesContainer = document.getElementById('delay_notes_container_corrective');
                                    const notesTextarea = document.getElementById('delay_notes_corrective');

                                    if (selectEl) {
                                        selectEl.addEventListener('change', function() {
                                            if (this.value === 'other') {
                                                notesContainer.classList.remove('hidden');
                                                notesTextarea.setAttribute('required', 'required');
                                                notesTextarea.focus();
                                            } else {
                                                notesContainer.classList.add('hidden');
                                                notesTextarea.removeAttribute('required');
                                            }
                                        });
                                    }
                                });
                            </script>
                        @endif
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="pt-4 pb-8">
                    <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-base rounded-2xl shadow-lg shadow-emerald-100 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Selesaikan Verifikasi Perbaikan</span>
                    </button>
                </div>
            </form>

            <script>
                // State arrays for multiple images
                let beforePhotosList = [];
                let afterPhotosList = [];
                let repairPerformers = [];

                document.addEventListener('DOMContentLoaded', function() {
                    const startScreen = document.getElementById('start-screen');
                    const executionForm = document.getElementById('execution-form');
                    const btnStart = document.getElementById('btn-start-inspection');

                    btnStart.addEventListener('click', function() {
                        startScreen.classList.add('hidden');
                        executionForm.classList.remove('hidden');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });

                    // 1. Repair Performer Autocomplete Logic
                    const addPerformerBtn = document.getElementById('btn-add-performer');
                    const performerHidden = document.getElementById('repair-performer-autocomplete');
                    const performerSearch = document.getElementById('search-repair-performer-autocomplete');
                    const performerContainer = document.getElementById('repair-performed-container');

                    addPerformerBtn.addEventListener('click', () => {
                        const name = performerHidden.value;
                        if (name && !repairPerformers.includes(name)) {
                            repairPerformers.push(name);
                            updateRepairPerformersUI();
                        }
                        performerHidden.value = '';
                        if (performerSearch) performerSearch.value = '';
                    });

                    window.removePerformer = function(name) {
                        repairPerformers = repairPerformers.filter(p => p !== name);
                        updateRepairPerformersUI();
                    };

                    function updateRepairPerformersUI() {
                        performerContainer.innerHTML = '';
                        repairPerformers.forEach(name => {
                            const chip = document.createElement('div');
                            chip.className = 'repair-performed-chip bg-slate-100 text-slate-800 text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 border border-slate-200';
                            chip.setAttribute('data-name', name);
                            chip.innerHTML = `
                                <span>${name}</span>
                                <button type="button" onclick="removePerformer('${name}')" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                            `;
                            performerContainer.appendChild(chip);
                        });
                        document.getElementById('repair_performed_by_json').value = JSON.stringify(repairPerformers);
                        document.getElementById('summary-teams').textContent = repairPerformers.join(', ') || '-';
                    }

                    // 2. Operational Status Sync
                    const opStatusHidden = document.getElementById('operational_status');
                    const opStatusRadios = document.querySelectorAll('input[name="actual_operational_status"]');
                    opStatusRadios.forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            const val = e.target.value;
                            if (val === 'running' || val === 'limited_operation' || val === 'temporary_repair') {
                                opStatusHidden.value = 'running';
                            } else {
                                opStatusHidden.value = 'idle';
                            }
                        });
                    });

                    // 3. Condition Radio Details Box
                    const conditionRadios = document.querySelectorAll('.condition-radio');
                    const descBox = document.getElementById('condition-description-box');
                    conditionRadios.forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            descBox.textContent = e.target.getAttribute('data-desc');
                        });
                    });

                    // 4. Repair Type Hide/Show Follow-up
                    const repairTypeRadios = document.querySelectorAll('input[name="repair_type"]');
                    const followUpContainer = document.getElementById('follow-up-container');
                    repairTypeRadios.forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            if (e.target.value === 'Temporary') {
                                followUpContainer.classList.remove('hidden');
                            } else {
                                followUpContainer.classList.add('hidden');
                            }
                        });
                    });

                    // 5. Spareparts Autocomplete Integration
                    const spWrapper = document.getElementById('wrapper-sparepart-autocomplete');
                    const spTbody = document.getElementById('spareparts-tbody');

                    spWrapper.addEventListener('wms-sparepart-selected', (e) => {
                        const item = e.detail;
                        const code = item.code || '';
                        const name = item.name || '';
                        const brand = item.brand || '-';
                        addSparepartRow(code, name, brand);
                    });

                    function addSparepartRow(code, name, brand) {
                        // Prevent duplicate table rows
                        if (document.getElementById(`row-part-${code}`)) return;

                        const tr = document.createElement('tr');
                        tr.id = `row-part-${code}`;
                        tr.className = 'border-b border-slate-100';
                        tr.innerHTML = `
                            <td class="py-2.5">
                                <span class="block font-bold text-slate-800">${code}</span>
                                <span class="block text-[9px] text-slate-400">${name} (${brand})</span>
                                <input type="hidden" name="spareparts[${code}][checked]" value="1" />
                            </td>
                            <td class="py-2.5 text-center">
                                <input type="number" name="spareparts[${code}][qty]" value="1" min="1" class="w-12 p-1 border border-slate-200 text-center rounded text-xs font-bold text-slate-700" />
                            </td>
                            <td class="py-2.5 text-right">
                                <button type="button" onclick="document.getElementById('row-part-${code}').remove()" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                            </td>
                        `;
                        spTbody.appendChild(tr);
                    }

                    // 6. Photo Picker & Stitching Handlers
                    const beforePicker = document.getElementById('before_photo_picker');
                    const afterPicker = document.getElementById('after_photo_picker');

                    beforePicker.addEventListener('change', function() {
                        handleFileSelection(this.files, 'before');
                    });
                    afterPicker.addEventListener('change', function() {
                        handleFileSelection(this.files, 'after');
                    });

                    function handleFileSelection(files, type) {
                        const targetList = type === 'before' ? beforePhotosList : afterPhotosList;
                        Array.from(files).forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                targetList.push({
                                    file: file,
                                    dataUrl: e.target.result
                                });
                                renderPreviews(type);
                                stitchPhotos(type);
                            };
                            reader.readAsDataURL(file);
                        });
                    }

                    window.deletePhoto = function(type, idx) {
                        const targetList = type === 'before' ? beforePhotosList : afterPhotosList;
                        targetList.splice(idx, 1);
                        renderPreviews(type);
                        stitchPhotos(type);
                    };

                    function renderPreviews(type) {
                        const targetList = type === 'before' ? beforePhotosList : afterPhotosList;
                        const container = document.getElementById(type === 'before' ? 'before-previews-list' : 'after-previews-list');
                        container.innerHTML = '';
                        targetList.forEach((item, idx) => {
                            const div = document.createElement('div');
                            div.className = 'relative w-20 h-20 border border-slate-200 rounded-lg overflow-hidden shrink-0 group shadow-sm';
                            div.innerHTML = `
                                <img src="${item.dataUrl}" class="w-full h-full object-cover" />
                                <button type="button" onclick="deletePhoto('${type}', ${idx})" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold shadow hover:bg-red-700 transition-colors">&times;</button>
                            `;
                            container.appendChild(div);
                        });
                    }

                    function stitchPhotos(type) {
                        const targetList = type === 'before' ? beforePhotosList : afterPhotosList;
                        const finalInput = document.getElementById(type === 'before' ? 'photo_before' : 'photo');
                        
                        if (targetList.length === 0) {
                            finalInput.value = '';
                            return;
                        }

                        if (targetList.length === 1) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(targetList[0].file);
                            finalInput.files = dataTransfer.files;
                            return;
                        }

                        const imagesToLoad = [];
                        let loadedCount = 0;

                        targetList.forEach((item, idx) => {
                            const img = new Image();
                            img.onload = function() {
                                imagesToLoad[idx] = img;
                                loadedCount++;
                                if (loadedCount === targetList.length) {
                                    drawStitchedCanvas(imagesToLoad, type, finalInput);
                                }
                            };
                            img.src = item.dataUrl;
                        });
                    }

                    function drawStitchedCanvas(images, type, finalInput) {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        
                        let totalHeight = 0;
                        let maxWidth = 0;
                        images.forEach(img => {
                            totalHeight += img.height;
                            if (img.width > maxWidth) {
                                maxWidth = img.width;
                            }
                        });

                        canvas.width = maxWidth;
                        canvas.height = totalHeight;

                        let currentY = 0;
                        images.forEach(img => {
                            ctx.drawImage(img, 0, currentY);
                            currentY += img.height;
                        });

                        canvas.toBlob(function(blob) {
                            const file = new File([blob], `composite_${type}.jpg`, { type: 'image/jpeg' });
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            finalInput.files = dataTransfer.files;
                        }, 'image/jpeg', 0.85);
                    }

                    // 7. Form Submit Concat JSON Report
                    executionForm.addEventListener('submit', function(e) {
                        const verifiedBy = document.getElementById('operator_name').value;
                        const opStatusText = document.querySelector('input[name="actual_operational_status"]:checked')?.value || 'running';
                        const conditionScore = document.querySelector('input[name="overall_score"]:checked')?.value || '5';
                        const remainingIssues = document.getElementById('remaining_issues').value;
                        const repairType = document.querySelector('input[name="repair_type"]:checked')?.value || 'Permanent';
                        const followUpAction = document.getElementById('follow_up_action').value;
                        const userNotes = document.getElementById('notes_original').value;

                        const reportData = {
                            verified_by: verifiedBy,
                            team: repairPerformers,
                            actual_status: opStatusText,
                            condition_score: conditionScore,
                            remaining_issues: remainingIssues,
                            repair_type: repairType,
                            follow_up: followUpAction,
                            user_notes: userNotes
                        };

                        const formattedNotes = "[REPORT:" + JSON.stringify(reportData) + "]\n" + 
                                               "--- DETAIL HASIL VERIFIKASI PERBAIKAN ---\n" +
                                               "Tipe Perbaikan: " + (repairType === 'Temporary' ? 'Sementara (Temporary)' : 'Permanen') + "\n" +
                                               "Tim Teknisi: " + (repairPerformers.join(', ') || '-') + "\n" +
                                               "Masalah Tersisa: " + (remainingIssues || '-') + "\n" +
                                               "Tindakan Lanjutan: " + (followUpAction || '-') + "\n" +
                                               "Catatan Verifikasi: " + (userNotes || '-');

                        document.getElementById('notes').value = formattedNotes;
                    });
                });
            </script>

        @else
            <!-- PRE-EXECUTION STATE: Mulai Pemeriksaan Screen -->
            <div id="start-screen" class="bg-white rounded-2xl shadow-md border border-slate-200 p-6 text-center space-y-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c0-.621.504-1.125 1.125-1.125h15c.621 0 1.125.504 1.125 1.125v13.5c0 .621-.504 1.125-1.125 1.125h-15a1.125 1.125 0 0 1-1.125-1.125V5.25Z" />
                    </svg>
                </div>
                
                <div>
                    <h1 class="text-xl font-black text-slate-800">PEMERIKSAAN PM</h1>
                    <p class="text-xs text-slate-400 uppercase tracking-widest mt-1">SOP: {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : 'Tanpa SOP (Perencanaan Umum)' }}</p>
                    <div class="mt-4 bg-slate-50 p-3 rounded-lg border border-slate-100 text-left space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Mesin:</span>
                            <span class="font-bold text-slate-800">{{ $plan->machine->name }} ({{ $plan->machine->code }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Lokasi:</span>
                            <span class="font-semibold text-slate-800">{{ $plan->machine->department }} - Area {{ $plan->machine->production_area }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Rekomendasi Durasi:</span>
                            <span class="font-semibold text-slate-800 font-mono">{{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->estimated_duration : 120 }} Menit</span>
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-start-inspection" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-base rounded-xl shadow-lg shadow-blue-200 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Mulai Pemeriksaan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>

            <!-- FORM EXECUTION STATE (Hidden by default) -->
            <form action="{{ route('planning.store-execute', $plan->id) }}" method="POST" enctype="multipart/form-data" id="execution-form" class="hidden space-y-5">
                @csrf
                
                <!-- Started At Hidden Input -->
                <input type="hidden" name="started_at" id="started_at_input" value="">

                <!-- Floating Progress Tracker -->
                <div class="bg-white/95 backdrop-blur-md rounded-xl border border-slate-200 p-3 sticky top-[53px] z-30 shadow-sm flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                            <span>PROGRESS</span>
                            <span id="progress-text">0 / {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->checklists->count() : 0 }} Terisi</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-800 space-y-1">
                        <p class="font-bold">Beberapa input tidak valid:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Operator Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                    <div class="flex justify-between items-center">
                        <label for="operator_name" class="block text-xs font-bold uppercase text-slate-400">Pilih Nama Teknisi / Operator</label>
                        @if($plan->assigned_technician)
                            <span class="text-[10px] bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-extrabold">Ditugaskan: {{ $plan->assigned_technician }}</span>
                        @endif
                    </div>
                    <x-employee-autocomplete 
                        name="operator_name" 
                        id="operator_name" 
                        selected="{{ old('operator_name', $plan->assigned_technician) }}"
                        required="true"
                        placeholder="Pilih nama Anda..."
                    />
                </div>

                <!-- Checklist Cards -->
                <div class="space-y-4">
                    <span class="block text-xs font-bold uppercase text-slate-400 px-1">Daftar Checklist Tindakan</span>
                    
                    @if($plan->maintenanceTemplate)
                        @foreach ($plan->maintenanceTemplate->checklists as $index => $item)
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4 checklist-card transition-all duration-300" id="card-{{ $item->id }}">
                                
                                <!-- Header & Info -->
                                <div>
                                    <div class="flex justify-between items-start gap-2 mb-1.5">
                                        <h3 class="text-sm font-bold text-slate-800 leading-snug">{{ $item->title }}</h3>
                                        @if ($item->is_required)
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 rounded-md uppercase">Wajib</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-bold text-slate-400 bg-slate-50 border border-slate-200 rounded-md uppercase">Opsional</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 leading-normal">{{ $item->description ?? '-' }}</p>
                                </div>

                                <!-- 1-5 Score Buttons -->
                                <div class="space-y-1.5">
                                    <span class="block text-[10px] font-bold uppercase text-slate-400">Pilih Nilai Kondisi (1-5)</span>
                                    <div class="grid grid-cols-5 gap-2">
                                        @for ($score = 1; $score <= 5; $score++)
                                            @php
                                                $colorClass = match($score) {
                                                    1 => 'peer-checked:bg-rose-500 peer-checked:text-white peer-checked:ring-rose-200',
                                                    2 => 'peer-checked:bg-amber-500 peer-checked:text-white peer-checked:ring-amber-200',
                                                    3 => 'peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:ring-yellow-200',
                                                    4 => 'peer-checked:bg-blue-500 peer-checked:text-white peer-checked:ring-blue-200',
                                                    5 => 'peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:ring-emerald-200',
                                                };
                                            @endphp
                                            <label class="cursor-pointer">
                                                <input type="radio" 
                                                       name="answers[{{ $item->id }}][score]" 
                                                       value="{{ $score }}" 
                                                       class="sr-only peer score-radio" 
                                                       data-item-id="{{ $item->id }}"
                                                       required
                                                       {{ old("answers.{$item->id}.score") == $score ? 'checked' : '' }}>
                                                <div class="w-full py-3.5 text-center text-sm font-black text-slate-700 bg-slate-50 border border-slate-200 rounded-xl rating-btn peer-checked:ring-4 peer-checked:border-transparent transition-all {{ $colorClass }}">
                                                    {{ $score }}
                                                </div>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="flex justify-between text-[9px] text-slate-400 px-1 pt-0.5">
                                        <span class="font-bold text-rose-500">1 (Rusak Berat)</span>
                                        <span class="font-bold text-emerald-500">5 (Sangat Baik)</span>
                                    </div>
                                </div>

                                <!-- Conditional Remarks Input -->
                                <div id="remarks-container-{{ $item->id }}" class="hidden space-y-2">
                                    <label for="remarks-{{ $item->id }}" class="block text-xs font-bold uppercase text-rose-700">Catatan Kerusakan / Temuan</label>
                                    <textarea name="answers[{{ $item->id }}][remarks]" 
                                              id="remarks-{{ $item->id }}" 
                                              rows="2" 
                                              placeholder="Jelaskan detail kerusakan mesin..." 
                                              class="w-full p-3 bg-rose-50/50 border border-rose-200 rounded-xl text-xs text-slate-800 placeholder-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-500">{{ old("answers.{$item->id}.remarks") }}</textarea>
                                </div>

                            </div>
                        @endforeach
                    @else
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 text-center text-slate-500 text-xs">
                            Rencana PM ini tidak menggunakan paket SOP / checklist tindakan.
                        </div>
                    @endif
                </div>

                <!-- Mandatory Photo & Notes -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-0.5">Foto Bukti Pemeriksaan</h3>
                        <p class="text-xs text-slate-400">Unggah satu foto sebagai dokumentasi fisik pemeliharaan (Maks 10MB).</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all overflow-hidden" id="photo-preview-container">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-400" id="photo-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                                <p class="text-xs font-bold uppercase tracking-wider">Ketuk Untuk Ambil Foto</p>
                            </div>
                            <img id="photo-preview" class="hidden w-full h-full object-cover" alt="Preview foto" />
                            <input type="file" name="photo" id="photo" required accept="image/*" class="hidden">
                        </label>
                    </div>

                    <div class="space-y-1.5">
                        <label for="notes" class="block text-xs font-bold uppercase text-slate-400">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" id="notes" rows="2" placeholder="Tuliskan temuan atau catatan umum jika ada..." class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                @php
                    $isOverduePM = $plan->target_completion && now()->gt($plan->target_completion);
                    $delayDurationPM = '';
                    if ($isOverduePM) {
                        $diffPM = now()->diff($plan->target_completion);
                        $partsPM = [];
                        if ($diffPM->d > 0) $partsPM[] = $diffPM->d . ' Days';
                        if ($diffPM->h > 0) $partsPM[] = $diffPM->h . ' Hours';
                        if ($diffPM->i > 0) $partsPM[] = $diffPM->i . ' Minutes';
                        $delayDurationPM = implode(' ', $partsPM);
                    }
                @endphp

                @if($plan->target_completion)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                        <h3 class="text-sm font-bold text-slate-800 mb-0.5">Analisis Waktu Target</h3>
                        <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-slate-600">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Target Selesai</span>
                                <span class="text-slate-800 font-bold text-sm">{{ $plan->target_completion->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Waktu Sekarang (Verifikasi)</span>
                                <span class="text-slate-800 font-bold text-sm">{{ now()->format('d M Y H:i') }}</span>
                            </div>
                        </div>

                        @if($isOverduePM)
                            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2">
                                <span class="material-symbols-outlined text-rose-600 mt-0.5">warning</span>
                                <div>
                                    <strong class="font-bold">Terlambat dari Target (Overdue)</strong>
                                    <p class="mt-0.5 text-rose-700 font-medium">Late by: {{ $delayDurationPM }}</p>
                                </div>
                            </div>

                            <!-- Delay Analysis Section (Mandatory) -->
                            <div class="border-t border-slate-100 pt-3 space-y-3">
                                <h4 class="text-xs font-bold uppercase text-slate-400">Analisis Keterlambatan (Wajib)</h4>
                                
                                <div class="space-y-1">
                                    <label for="delay_reason_pm" class="block text-[10px] font-bold uppercase text-slate-500">Alasan Keterlambatan</label>
                                    <select name="delay_reason" id="delay_reason_pm" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Pilih Alasan Keterlambatan --</option>
                                        <option value="waiting_sparepart">Waiting Sparepart (Menunggu Suku Cadang)</option>
                                        <option value="waiting_production">Waiting Production (Menunggu Produksi)</option>
                                        <option value="waiting_vendor">Waiting Vendor (Menunggu Vendor)</option>
                                        <option value="waiting_approval">Waiting Approval (Menunggu Persetujuan)</option>
                                        <option value="additional_damage">Additional Damage Found (Kerusakan Tambahan Ditemukan)</option>
                                        <option value="manpower_shortage">Manpower Shortage (Kekurangan Personel)</option>
                                        <option value="power_failure">Power Failure (Kegagalan Listrik/Daya)</option>
                                        <option value="other">Other (Lainnya)</option>
                                    </select>
                                </div>

                                <div id="delay_notes_container_pm" class="hidden space-y-1">
                                    <label for="delay_notes_pm" class="block text-[10px] font-bold uppercase text-slate-500">Catatan Detail Keterlambatan</label>
                                    <textarea name="delay_notes" id="delay_notes_pm" rows="2" placeholder="Jelaskan detail alasan keterlambatan..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const selectEl = document.getElementById('delay_reason_pm');
                                    const notesContainer = document.getElementById('delay_notes_container_pm');
                                    const notesTextarea = document.getElementById('delay_notes_pm');

                                    if (selectEl) {
                                        selectEl.addEventListener('change', function() {
                                            if (this.value === 'other') {
                                                notesContainer.classList.remove('hidden');
                                                notesTextarea.setAttribute('required', 'required');
                                                notesTextarea.focus();
                                            } else {
                                                notesContainer.classList.add('hidden');
                                                notesTextarea.removeAttribute('required');
                                            }
                                        });
                                    }
                                });
                            </script>
                        @endif
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="pt-4 pb-8">
                    <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-base rounded-2xl shadow-lg shadow-emerald-100 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Kirim Laporan Perawatan</span>
                    </button>
                </div>

            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const startScreen = document.getElementById('start-screen');
                    const executionForm = document.getElementById('execution-form');
                    const btnStart = document.getElementById('btn-start-inspection');
                    const startedAtInput = document.getElementById('started_at_input');
                    const radios = document.querySelectorAll('.score-radio');
                    const photoInput = document.getElementById('photo');
                    const photoPreview = document.getElementById('photo-preview');
                    const photoPlaceholder = document.getElementById('photo-placeholder');
                    const progressBar = document.getElementById('progress-bar');
                    const progressText = document.getElementById('progress-text');

                    const totalItems = {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->checklists->count() : 0 }};

                    btnStart.addEventListener('click', function() {
                        const now = new Date();
                        const pad = (num) => String(num).padStart(2, '0');
                        const formattedTime = now.getFullYear() + '-' + 
                                              pad(now.getMonth() + 1) + '-' + 
                                              pad(now.getDate()) + ' ' + 
                                              pad(now.getHours()) + ':' + 
                                              pad(now.getMinutes()) + ':' + 
                                              pad(now.getSeconds());

                        if (startedAtInput) {
                            startedAtInput.value = formattedTime;
                        }

                        startScreen.classList.add('hidden');
                        executionForm.classList.remove('hidden');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });

                    radios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            const itemId = this.getAttribute('data-item-id');
                            const score = parseInt(this.value);
                            const card = document.getElementById('card-' + itemId);
                            const remarksContainer = document.getElementById('remarks-container-' + itemId);
                            const remarksTextarea = document.getElementById('remarks-' + itemId);

                            if (score === 1) {
                                card.classList.remove('border-slate-200', 'bg-white');
                                card.classList.add('border-rose-300', 'bg-rose-50/20');
                                remarksContainer.classList.remove('hidden');
                                remarksTextarea.setAttribute('required', 'required');
                                remarksTextarea.focus();
                            } else {
                                card.classList.remove('border-rose-300', 'bg-rose-50/20');
                                card.classList.add('border-slate-200', 'bg-white');
                                remarksContainer.classList.add('hidden');
                                remarksTextarea.removeAttribute('required');
                            }

                            updateProgress();
                        });
                    });

                    function updateProgress() {
                        if (!progressBar) return;
                        const checkedRadios = document.querySelectorAll('.score-radio:checked');
                        const answeredCount = checkedRadios.length;
                        const percentage = totalItems > 0 ? Math.round((answeredCount / totalItems) * 100) : 0;

                        progressBar.style.width = percentage + '%';
                        progressText.textContent = answeredCount + ' / ' + totalItems + ' Terisi';
                    }

                    photoInput.addEventListener('change', function() {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                photoPreview.src = e.target.result;
                                photoPreview.classList.remove('hidden');
                                photoPlaceholder.classList.add('hidden');
                            }
                            reader.readAsDataURL(file);
                        } else {
                            photoPreview.classList.add('hidden');
                            photoPlaceholder.classList.remove('hidden');
                        }
                    });

                    document.querySelectorAll('.score-radio:checked').forEach(radio => {
                        const itemId = radio.getAttribute('data-item-id');
                        const score = parseInt(radio.value);
                        if (score === 1) {
                            const card = document.getElementById('card-' + itemId);
                            const remarksContainer = document.getElementById('remarks-container-' + itemId);
                            const remarksTextarea = document.getElementById('remarks-' + itemId);
                            card.classList.remove('border-slate-200', 'bg-white');
                            card.classList.add('border-rose-300', 'bg-rose-50/20');
                            remarksContainer.classList.remove('hidden');
                            remarksTextarea.setAttribute('required', 'required');
                        }
                    });
                    updateProgress();
                });
            </script>
        @endif

    </main>

</body>
</html>
