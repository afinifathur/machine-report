@php
    $overallPhoto = $machine->photos->first();
    $photoUrl = ($overallPhoto && $overallPhoto->file_path) ? asset('storage/' . $overallPhoto->file_path) : null;
@endphp

<x-layouts.app 
    title="Paspor Mesin - {{ $machine->code }} | Sistem MRM"
    topbar-title="Manajemen Keandalan Mesin"
    :subnav="['Ikhtisar' => '#', 'Riwayat Medis' => route('machines.show', $machine->code), 'Sparepart' => '#', 'Dokumen' => '#']"
    active-subnav="Riwayat Medis"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Daftar Mesin' => route('machines.index'), $machine->code => '']" />

    <!-- Identity & Actions Header -->
    <div class="bg-surface-container-lowest border border-outline-variant p-8 mb-8 flex flex-col md:flex-row gap-8 items-start rounded-xl shadow-sm">
        <div class="w-full md:w-64 lg:w-72 xl:w-80 shrink-0 aspect-square rounded-xl overflow-hidden border border-outline-variant relative group bg-surface-container flex items-center justify-center p-2">
            @if($photoUrl)
                <img class="w-full h-full object-contain rounded transition-transform duration-300 group-hover:scale-[1.02]" alt="{{ $machine->name }} {{ $machine->code }}" src="{{ $photoUrl }}" loading="lazy"/>
            @else
                <div class="text-center p-6 text-on-surface-variant flex flex-col items-center justify-center gap-2 w-full h-full select-none">
                    <span class="material-symbols-outlined text-[48px] text-outline">image_not_supported</span>
                    <p class="font-body-md text-sm font-semibold">Foto Mesin Belum Diunggah</p>
                    <p class="text-xs opacity-75">Dokumen Belum Diunggah</p>
                </div>
            @endif
        </div>
        
        <div class="flex-1 space-y-4 w-full">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-1">{{ $machine->name }} - <span class="mono">{{ $machine->code }}</span></h2>
                    <div class="flex flex-wrap gap-2 text-on-surface-variant font-body-md items-center text-sm">
                        <span><strong class="font-semibold text-on-surface">Manufacturer:</strong> {{ $machine->manufacturer }}</span>
                        <span class="opacity-30">|</span>
                        <span><strong class="font-semibold text-on-surface">Model:</strong> {{ $machine->model }}</span>
                        <span class="opacity-30">|</span>
                        <span><strong class="font-semibold text-on-surface">Kategori:</strong> {{ $machine->category }}</span>
                        <span class="opacity-30">|</span>
                        <span><strong class="font-semibold text-on-surface">Departemen:</strong> {{ $machine->department }}</span>
                    </div>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2 shrink-0">
                    <x-status-badge :type="$machine->criticality" />
                    
                    @if($machine->lifecycle_status !== 'ACTIVE')
                        <x-status-badge type="{{ $machine->lifecycle_status === 'INACTIVE' ? 'warning' : 'low' }}" label="{{ $machine->lifecycle_status === 'INACTIVE' ? 'Nonaktif' : 'Pensiun' }}" />
                    @endif
                    
                    @if($machine->qr_code_path)
                        <div class="p-2 bg-white border border-outline-variant rounded shadow-sm flex items-center justify-center" title="QR Code Placeholder">
                            <img class="w-12 h-12" alt="QR Code" src="{{ asset($machine->qr_code_path) }}"/>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3 pt-4 border-t border-outline-variant">
                @if($activePlan)
                    <x-button variant="primary" icon="qr_code_scanner" href="{{ route('planning.execute', $activePlan->id) }}">
                        Eksekusi Perawatan
                    </x-button>
                    <x-button variant="secondary" icon="visibility" href="{{ route('planning.show', $activePlan->id) }}">
                        Audit Kesiapan
                    </x-button>
                @else
                    <x-button variant="primary" icon="calendar_today" href="{{ route('planning.index') }}">
                        Buat Jadwal Perawatan
                    </x-button>
                @endif
                <x-button variant="secondary" icon="stethoscope" href="{{ route('breakdowns.index') }}">
                    Laporkan Kerusakan
                </x-button>
                <x-button variant="secondary" icon="edit" href="{{ route('machines.edit', $machine->code) }}">
                    Edit Paspor
                </x-button>
                <x-button variant="secondary" icon="ios_share" href="#">
                    Ekspor Riwayat
                </x-button>
            </div>
        </div>
    </div>

    <!-- Health & Diagnostic Dashboard (Bento Grid) -->
    <div class="grid grid-cols-12 gap-6 mb-8">
        <!-- Left Side Bento (Health Gauge & Checklist) -->
        <div class="col-span-12 md:col-span-4 space-y-6">
            <!-- Health Gauge Section (Compact CMMS Strip ~105px) -->
            <div class="bg-surface-container-lowest border border-outline-variant/60 p-4 rounded-xl shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-label-md text-[11px] text-on-surface-variant uppercase tracking-wider font-bold">Kesehatan Mesin</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        {{ $machine->health_score }}% • OPTIMAL
                    </span>
                </div>
                <div class="w-full space-y-1.5 mt-1">
                    <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ min(100, max(0, $machine->health_score)) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-on-surface-variant font-medium">
                        <span>Target Operasional</span>
                        <span class="font-bold text-on-surface">85% - 100%</span>
                    </div>
                </div>
            </div>

            <!-- Machine Identity Checklist Card (Compact Audit Grid ~105px) -->
            <div class="bg-surface-container-lowest border border-outline-variant/60 p-4 rounded-xl shadow-2xs">
                @php
                    $progress = $machine->completion_progress;
                @endphp
                <div class="flex items-center justify-between mb-2 pb-2 border-b border-outline-variant/40">
                    <h4 class="font-label-md text-[11px] text-primary uppercase tracking-wider font-bold">Kelengkapan Paspor</h4>
                    <span id="checklist-progress-text" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                        {{ $progress['completed'] }}/{{ $progress['total'] }} Complete
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-1 text-xs text-left my-1.5">
                    <!-- Identitas -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('identitas')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-identitas">
                            @if($machine->has_identitas)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">Identitas</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">Identitas</span>
                            @endif
                        </div>
                    </div>

                    <!-- Sparepart -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('sparepart')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-sparepart">
                            @if($machine->has_spareparts)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">Sparepart</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">Sparepart</span>
                            @endif
                        </div>
                    </div>

                    <!-- Manual Book -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('manual')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-manual_book">
                            @if($machine->has_manual)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">Manual</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">Manual</span>
                            @endif
                        </div>
                    </div>

                    <!-- Foto Mesin -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('photo')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-foto">
                            @if($machine->has_photo)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">Foto</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">Foto</span>
                            @endif
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('qr')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-qr">
                            @if($machine->has_qr)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">QR Code</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">QR Code</span>
                            @endif
                        </div>
                    </div>

                    <!-- Komponen -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-surface-container-low px-1.5 py-1 rounded transition-colors group" onclick="navigateChecklist('components')">
                        <div class="flex items-center gap-1 min-w-0" id="checklist-icon-komponen">
                            @if($machine->has_components)
                                <span class="material-symbols-outlined text-green-600 font-bold text-[14px] shrink-0">check_circle</span>
                                <span class="text-on-surface font-semibold text-[11px] truncate">Komponen</span>
                            @else
                                <span class="material-symbols-outlined text-outline font-bold text-[14px] shrink-0">radio_button_unchecked</span>
                                <span class="text-on-surface-variant text-[11px] truncate">Komponen</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden mt-2">
                    <div id="checklist-progress-bar" class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $progress['total'] > 0 ? ($progress['completed'] / $progress['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Detailed Diagnostics Tabs Content -->
        <div class="col-span-12 md:col-span-8 bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col shadow-sm min-h-[400px]">
            <!-- Tab Headers -->
            <div class="border-b border-outline-variant px-6 flex space-x-8 overflow-x-auto" id="passport-tabs">
                <button data-target="panel-overview" class="tab-btn py-4 font-body-md text-body-md text-primary font-bold border-b-2 border-primary whitespace-nowrap">Ikhtisar</button>
                <button data-target="panel-components" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Komponen Mesin</button>
                <button data-target="panel-spareparts" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Kebutuhan Sparepart</button>
                <button data-target="panel-documents" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Dokumen</button>
                <button data-target="panel-photos" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Foto</button>
                <button data-target="panel-history" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Riwayat Mesin</button>
            </div>
            
            <div class="flex-1 overflow-y-auto max-h-[420px] hide-scrollbar">
                
                <!-- Panel 1: Overview -->
                <div id="panel-overview" class="tab-panel p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Identity Info Block -->
                        <div>
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-3">Identitas Fisik</h4>
                            <div class="space-y-2 text-body-md">
                                <p><strong class="font-semibold">Serial Number:</strong> <span class="mono">{{ $machine->serial_number }}</span></p>
                                <p><strong class="font-semibold">Manufacturer:</strong> {{ $machine->manufacturer }}</p>
                                <p><strong class="font-semibold">Model:</strong> {{ $machine->model }}</p>
                                <p><strong class="font-semibold">Vendor:</strong> {{ $machine->vendor }}</p>
                                <p><strong class="font-semibold">Tanggal Instalasi:</strong> {{ $machine->installation_date ? $machine->installation_date->format('d M Y') : '-' }}</p>
                                <p><strong class="font-semibold">Tanggal Commissioning:</strong> {{ $machine->commissioning_date ? $machine->commissioning_date->format('d M Y') : '-' }}</p>
                            </div>
                        </div>

                        <!-- Reliability Status Block -->
                        <div>
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-3">Indikator Keandalan</h4>
                            <div class="space-y-2 text-body-md">
                                <p class="flex items-center gap-2">
                                    <strong class="font-semibold">Status Mesin:</strong>
                                    <x-status-badge :type="$machine->operational_status" />
                                </p>
                                <p class="flex items-center gap-2">
                                    <strong class="font-semibold">Tingkat Prioritas:</strong>
                                    <x-status-badge :type="$machine->criticality" />
                                </p>
                                <p><strong class="font-semibold">Perawatan Terakhir:</strong> 10 Okt 2023 <span class="text-xs text-on-surface-variant italic">(Lubrikasi Rutin)</span></p>
                                <p><strong class="font-semibold">Jadwal Berikutnya:</strong> 05 Nov 2023 <span class="text-xs text-on-surface-variant italic">(Ultrasound Bearing)</span></p>
                                <p><strong class="font-semibold">Jam Operasional:</strong> 8.420 jam <span class="text-xs text-on-surface-variant italic">(Meteran Simulasi)</span></p>
                            </div>
                        </div>

                        <!-- QR Code Block -->
                        <div>
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-3">QR Code Paspor</h4>
                            <div id="qr-code-card" class="p-4 rounded-xl border border-outline-variant bg-surface-container-low flex flex-col items-center justify-center text-center transition-all duration-300">
                                @if($machine->qr_code_path)
                                    <!-- QR Code Image Preview Container (Click to open shared viewer) -->
                                    <div class="bg-white p-3 rounded-lg border border-outline-variant shadow-sm mb-3 cursor-pointer hover:border-primary hover:shadow transition group relative" 
                                         onclick="openAssetViewer('qr', '{{ asset($machine->qr_code_path) }}', '{{ $machine->code }}', '{{ $machine->name }}', '{{ route('machines.show', $machine->code) }}', '{{ $machine->updated_at ? $machine->updated_at->format('d M Y') : date('d M Y') }}')" 
                                         title="Klik untuk membuka QR Code Viewer">
                                        <img id="qr-preview-img" src="{{ asset($machine->qr_code_path) }}" alt="QR Code {{ $machine->code }}" class="w-28 h-28 object-contain" />
                                        <div class="absolute inset-0 bg-primary/5 rounded-lg opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                            <span class="bg-surface/90 text-primary p-1.5 rounded-full shadow-sm material-symbols-outlined text-sm">zoom_in</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full mb-3">
                                        <span class="material-symbols-outlined text-[14px]">verified</span>
                                        <span>✓ QR Permanen</span>
                                    </div>

                                    <p class="text-[11px] text-on-surface-variant mb-3">
                                        Dibuat: <span class="font-medium text-on-surface">{{ $machine->updated_at ? $machine->updated_at->format('d M Y') : date('d M Y') }}</span>
                                    </p>

                                    <!-- Permanent Action Buttons (No Regenerate Button) -->
                                    <div class="flex flex-wrap gap-1.5 justify-center w-full">
                                        <button onclick="openAssetViewer('qr', '{{ asset($machine->qr_code_path) }}', '{{ $machine->code }}', '{{ $machine->name }}', '{{ route('machines.show', $machine->code) }}', '{{ $machine->updated_at ? $machine->updated_at->format('d M Y') : date('d M Y') }}')"
                                                class="px-2.5 py-1 bg-surface-container border border-outline-variant text-on-surface text-xs font-medium rounded hover:bg-surface-container-high transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            Buka
                                        </button>

                                        <a href="{{ route('machines.qr.download', $machine->code) }}" 
                                           class="px-2.5 py-1 bg-surface-container border border-outline-variant text-on-surface text-xs font-medium rounded hover:bg-surface-container-high transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">download</span>
                                            Download PNG
                                        </a>

                                        <button onclick="copyQrImage('{{ asset($machine->qr_code_path) }}', '{{ route('machines.qr.download', $machine->code) }}')"
                                                class="px-2.5 py-1 bg-surface-container border border-outline-variant text-on-surface text-xs font-medium rounded hover:bg-surface-container-high transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                                            Copy Image
                                        </button>

                                        <button onclick="copyPassportLink('{{ route('machines.show', $machine->code) }}')"
                                                class="px-2.5 py-1 bg-surface-container border border-outline-variant text-on-surface text-xs font-medium rounded hover:bg-surface-container-high transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">link</span>
                                            Copy Link
                                        </button>

                                        <a href="{{ route('machines.qr.print', $machine->code) }}" target="_blank"
                                           class="px-2.5 py-1 bg-surface-container border border-outline-variant text-on-surface text-xs font-medium rounded hover:bg-surface-container-high transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">print</span>
                                            Print
                                        </a>
                                    </div>
                                @else
                                    <!-- Legacy Migration Helper (Only shown when qr_code_path is empty) -->
                                    <div class="w-24 h-24 bg-surface-container border border-dashed border-outline-variant rounded-lg flex flex-col items-center justify-center text-on-surface-variant/40 mb-2">
                                        <span class="material-symbols-outlined text-[32px]">qr_code_2</span>
                                        <span class="text-[10px] mt-1 font-semibold">Belum Ada QR</span>
                                    </div>
                                    <p class="text-xs text-on-surface-variant mb-2">QR Code digunakan untuk identifikasi mesin dan akses cepat.</p>
                                    <form action="{{ route('machines.qr.generate', $machine->code) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-primary text-on-primary text-xs font-semibold rounded hover:bg-primary-container transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">add_location_alt</span>
                                            Buat QR
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Components -->
                <div id="panel-components" class="tab-panel p-6 hidden">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Subsistem Struktural</h4>
                    <p class="text-body-sm text-on-surface-variant mb-4">
                        Modul mekanis dan elektrikal utama yang dipetakan untuk identitas mesin ini. Teknisi melakukan penggantian di tingkat ini.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($machine->components as $component)
                            <div class="flex items-center gap-3 p-3 bg-surface-container border border-outline-variant rounded-lg">
                                <span class="material-symbols-outlined text-primary">settings_applications</span>
                                <span class="font-body-md font-semibold text-on-surface">{{ $component->name }}</span>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-6 text-on-surface-variant">
                                Belum ada komponen yang dipetakan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel 3: Spareparts (Warehouse integration) -->
                <div id="panel-spareparts" class="tab-panel p-6 hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 pb-2.5 border-b border-outline-variant/60">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-[18px]">settings_input_component</span>
                            </div>
                            <div>
                                <h4 class="font-label-md text-xs text-primary uppercase tracking-wider font-bold">Kebutuhan Sparepart</h4>
                                <p class="text-[11px] text-on-surface-variant">Live stock & referensi pemetaan sparepart untuk perawatan mesin.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button id="btn-open-sparepart-modal" class="px-2.5 py-1 bg-primary text-on-primary rounded-lg font-semibold text-xs flex items-center gap-1 hover:bg-primary-container transition-colors shadow-2xs">
                                <span class="material-symbols-outlined text-[15px]">add</span> Tambah Mapping
                            </button>
                            <span class="inline-flex items-center gap-1 text-[11px] text-on-surface-variant font-bold bg-surface-container px-2 py-0.5 rounded-lg border border-outline-variant/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> WMS Connected
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-2xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-surface-container-low/80 font-bold uppercase tracking-wider text-[10px] text-on-surface-variant border-b border-outline-variant/60">
                                <tr>
                                    <th scope="col" class="py-2 px-3">Sparepart</th>
                                    <th scope="col" class="py-2 px-3">Stock</th>
                                    <th scope="col" class="py-2 px-3">Shared</th>
                                    <th scope="col" class="py-2 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="spareparts-list" class="divide-y divide-outline-variant/40">
                                @forelse($sparepartsView as $item)
                                    @php
                                        /** @var \App\Integrations\WMS\DTOs\SparepartItemDTO $dto */
                                        $dto = $item['dto'];
                                        $status = $item['status'];
                                        $unitUpper = strtoupper(trim($dto->unit));
                                        $isPcs = ($unitUpper === 'PCS' || empty($unitUpper));
                                        $unitDisplay = $isPcs ? '' : ' ' . $dto->unit;
                                    @endphp
                                    <tr class="hover:bg-surface-container-low/60 transition-colors sparepart-row h-[44px]" data-mapping-id="{{ $item['mapping_id'] }}">
                                        <td class="py-1.5 px-3 max-w-[280px] lg:max-w-[360px]">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-on-surface text-xs truncate" title="{{ $dto->name }}">{{ $dto->name }}</span>
                                                <span class="mono font-bold text-[10px] px-1 py-0.2 rounded bg-surface-container border border-outline-variant/60 text-on-surface-variant shrink-0" title="ERP Code">{{ $dto->erpCode }}</span>
                                            </div>
                                        </td>
                                        <td class="py-1.5 px-3 whitespace-nowrap">
                                            @if($dto->isOffline)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full border border-gray-300 dark:border-gray-700">
                                                    ⚪ WMS Offline
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold mono border {{ $status['badge_class'] }}">
                                                    <span>{{ $status['icon'] }}</span>
                                                    <span>{{ $dto->stock }}{{ $unitDisplay }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-3 whitespace-nowrap">
                                            @if($item['shared_count'] > 0)
                                                <button type="button" onclick="openSharedMachinesModal({{ json_encode($item['shared_machines']) }}, '{{ addslashes($dto->name) }}')" class="px-2 py-0.5 bg-surface-container border border-outline-variant/60 text-on-surface text-[11px] font-bold rounded-md hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-all inline-flex items-center gap-1" title="Lihat {{ $item['shared_count'] }} paspor mesin lain yang menggunakan sparepart ini">
                                                    <span class="material-symbols-outlined text-[13px] text-primary">groups</span>
                                                    <span>👥{{ $item['shared_count'] }}</span>
                                                </button>
                                            @else
                                                <span class="text-on-surface-variant/40 text-[11px] font-medium">-</span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-3 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-1">
                                                @if($item['open_wms_url'])
                                                    <a href="{{ $item['open_wms_url'] }}" target="_blank" class="p-1 text-primary hover:bg-primary/10 rounded-md transition-colors" title="Open Warehouse">
                                                        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                                    </a>
                                                @endif
                                                <button type="button" class="p-1 text-error hover:bg-error-container/20 rounded-md transition-colors btn-delete-mapping" data-url="{{ route('machines.spareparts.destroy', [$machine->code, $item['mapping_id']]) }}" title="Hapus Mapping">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="spareparts-empty-state">
                                        <td colspan="4" class="text-center py-6 text-on-surface-variant text-xs italic">
                                            Belum ada kebutuhan sparepart yang dipetakan untuk mesin ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-right text-[10px] text-on-surface-variant italic">
                        "Data live stock terhubung langsung dari Warehouse Management System (WMS)."
                    </div>
                </div>

                <!-- Panel 4: Documents (Library ISO Link Integration) -->
                <div id="panel-documents" class="tab-panel p-6 hidden space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-outline-variant/60 pb-5">
                        <div>
                            <div class="flex items-center gap-3">
                                <h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">Dokumentasi Mesin</h4>
                                <span id="doc-links-total-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    {{ $machine->documentLinks->count() }} Dokumen
                                </span>
                            </div>
                            <p class="text-body-sm text-on-surface-variant mt-1">
                                Dokumen teknis resmi dikelola melalui <strong class="text-primary font-semibold">Library ISO</strong>. MRM menyimpan referensi tautan dokumen terverifikasi.
                            </p>
                        </div>
                        <button type="button" id="btn-link-document" onclick="openAddDocLinkModal()" class="px-4 py-2 bg-primary text-on-primary rounded-xl font-bold text-xs hover:bg-primary-container transition-all flex items-center gap-2 shrink-0 self-start md:self-auto shadow-xs">
                            <span class="material-symbols-outlined text-[18px]">link</span> Hubungkan Dokumen
                        </button>
                    </div>

                    <!-- Document Links Grid -->
                    <div id="doc-links-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Loaded dynamically via loadDocLinks() -->
                    </div>

                    <!-- Empty state -->
                    <div id="doc-links-empty-state" class="hidden py-12 flex flex-col items-center justify-center text-center bg-surface-container-low border border-dashed border-outline-variant rounded-2xl p-8">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-3">
                            <span class="material-symbols-outlined text-[28px]">folder_off</span>
                        </div>
                        <h5 class="font-body-lg font-bold text-on-surface mb-1">Belum Ada Dokumen Yang Dihubungkan</h5>
                        <p class="text-body-sm text-on-surface-variant max-w-md mb-4">
                            Seluruh dokumen teknis dikelola melalui Library ISO.
                        </p>
                        <button type="button" id="btn-link-document-empty" onclick="openAddDocLinkModal()" class="px-4 py-2 bg-primary text-on-primary rounded-xl font-bold text-xs hover:bg-primary-container transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">link</span> Hubungkan Dokumen
                        </button>
                    </div>
                </div>

                <!-- Panel 5: Photos Dynamic Gallery -->
                <div id="panel-photos" class="tab-panel p-6 hidden space-y-6">
                    <!-- Gallery Header & Controls -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-outline-variant/60 pb-5">
                        <div>
                            <div class="flex items-center gap-3">
                                <h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">Foto Dokumentasi</h4>
                                <span id="gallery-total-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    {{ $machine->photos->count() }} Foto
                                </span>
                            </div>
                            <p id="gallery-latest-upload" class="text-xs text-on-surface-variant mt-1">
                                Terakhir diupload: {{ $machine->photos->isNotEmpty() ? $machine->photos->first()->formatted_upload_date : '-' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Instant Search Input -->
                            <div class="relative w-full sm:w-48">
                                <input id="gallery-search-input" type="text" placeholder="Cari foto..." class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-8 pr-3 py-1.5 text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">search</span>
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="relative">
                                <select id="gallery-sort-select" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-1.5 text-xs font-semibold text-on-surface focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                                    <option value="newest">Terbaru</option>
                                    <option value="oldest">Terlama</option>
                                    <option value="title_asc">Judul A–Z</option>
                                    <option value="title_desc">Judul Z–A</option>
                                </select>
                            </div>

                            <!-- Tambah Foto Button -->
                            <button type="button" onclick="openAddPhotoModal()" class="px-3.5 py-1.5 bg-primary text-on-primary font-semibold text-xs rounded-lg flex items-center gap-1.5 hover:bg-primary-container transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">add_a_photo</span> Tambah Foto
                            </button>
                        </div>
                    </div>

                    <!-- Category Filter Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-2" id="gallery-category-pills">
                        <button type="button" data-category="all" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-primary text-on-primary transition-all shadow-sm">Semua</button>
                        <button type="button" data-category="reference" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Reference</button>
                        <button type="button" data-category="name_plate" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Name Plate</button>
                        <button type="button" data-category="inspection" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Inspection</button>
                        <button type="button" data-category="breakdown" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Breakdown</button>
                        <button type="button" data-category="repair" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Repair</button>
                        <button type="button" data-category="other" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all">Other</button>
                    </div>

                    <!-- Gallery Grid Container -->
                    <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 min-h-[220px]">
                        <!-- Rendered via JS -->
                    </div>

                    <!-- Empty State Container -->
                    <div id="gallery-empty-state" class="hidden text-center py-12 px-4 bg-surface-container-lowest border border-dashed border-outline-variant rounded-xl flex flex-col items-center justify-center space-y-3">
                        <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[36px]">photo_library</span>
                        </div>
                        <h4 class="font-headline-sm text-headline-sm font-bold text-on-surface">Belum ada dokumentasi foto.</h4>
                        <p class="text-body-sm text-on-surface-variant max-w-md">
                            Tambahkan foto pertama untuk mulai membangun paspor visual mesin ini.
                        </p>
                        <button type="button" onclick="openAddPhotoModal()" class="mt-2 px-4 py-2 bg-primary text-on-primary font-semibold text-xs rounded-lg inline-flex items-center gap-2 hover:bg-primary-container transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add_a_photo</span> Tambah Foto
                        </button>
                    </div>

                    <!-- Gallery Pagination Container -->
                    <div id="gallery-pagination" class="flex justify-between items-center pt-4 border-t border-outline-variant text-xs text-on-surface-variant hidden">
                        <!-- Rendered via JS -->
                    </div>
                </div>

                <!-- Panel 6: Medical History (Timeline) -->
                <div id="panel-history" class="tab-panel p-6 hidden">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Riwayat Perawatan Mesin (PLACEHOLDER)</h4>
                    
                    <div class="relative pl-10 border-l-2 border-outline-variant space-y-8 ml-2">
                        <!-- Entry 1: Breakdown -->
                        <div class="relative">
                            <div class="absolute -left-[51px] top-1.5 w-6 h-6 rounded-full bg-error flex items-center justify-center ring-4 ring-white">
                                <span class="material-symbols-outlined text-[14px] text-white" style="font-variation-settings: 'FILL' 1;">emergency</span>
                            </div>
                            <div class="bg-surface-container-low border border-outline-variant p-4 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">25 Okt 2023</p>
                                        <h4 class="font-headline-sm text-headline-sm text-error">Kerusakan - Spindle Overheating</h4>
                                    </div>
                                    <x-status-badge type="critical" label="Tingkat Kritis Tinggi" />
                                </div>
                                <p class="text-body-sm text-on-surface-variant">Kegagalan kipas pendingin menyebabkan lonjakan suhu. Spindle utama mati untuk melindungi keselarasan struktural.</p>
                            </div>
                        </div>

                        <!-- Entry 2: Maintenance -->
                        <div class="relative">
                            <div class="absolute -left-[51px] top-1.5 w-6 h-6 rounded-full bg-primary flex items-center justify-center ring-4 ring-white">
                                <span class="material-symbols-outlined text-[14px] text-white" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                            </div>
                            <div class="bg-surface-container-low border border-outline-variant p-4 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">10 Okt 2023</p>
                                        <h4 class="font-headline-sm text-headline-sm text-on-surface">Perawatan Terjadwal - Lubrikasi Total</h4>
                                    </div>
                                    <x-status-badge type="success" label="Rutin" />
                                </div>
                                <p class="text-body-sm text-on-surface-variant">Semua saluran hidrolik dibersihkan. Tekanan sistem optimal dipulihkan. Melumasi kembali grup bearing.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 7: Health Analysis -->
                <div id="panel-health" class="tab-panel p-6 hidden">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Analisis Keandalan & Diagnosis</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="p-4 border border-outline-variant rounded-xl bg-surface-container-lowest">
                            <h5 class="font-body-md font-bold mb-2">Parameter Kondisi (Placeholder)</h5>
                            <ul class="space-y-2 text-body-sm text-on-surface-variant">
                                <li class="flex justify-between"><span>Amplitudo Getaran</span><span class="mono text-on-surface font-semibold">2.4 mm/s (Optimal)</span></li>
                                <li class="flex justify-between"><span>Deviasi Termal</span><span class="mono text-on-surface font-semibold">+12°C (Perlu Perhatian)</span></li>
                                <li class="flex justify-between"><span>Output Akustik</span><span class="mono text-on-surface font-semibold">78 dB (Optimal)</span></li>
                                <li class="flex justify-between"><span>Gelombang Tegangan</span><span class="mono text-on-surface font-semibold">Bersih (Optimal)</span></li>
                            </ul>
                        </div>

                        <div class="p-4 border border-outline-variant rounded-xl bg-surface-container-lowest">
                            <h5 class="font-body-md font-bold mb-2">Ringkasan Keandalan</h5>
                            <p class="text-body-sm text-on-surface-variant italic leading-relaxed">
                                "Mesin menunjukkan sedikit ketidakstabilan termal pada grup spindle komponen utama. Tekanan pelumasan stabil. Tidak ada penyesuaian struktural yang disarankan saat ini, pemeriksaan dijadwalkan untuk rotasi berikutnya."
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Data Visualization Mockups Layer -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant">Telemetri Langsung</h5>
                <span class="text-[10px] text-error font-bold flex items-center gap-1 animate-pulse"><span class="w-1.5 h-1.5 rounded-full bg-error"></span> LANGSUNG</span>
            </div>
            <div class="h-32 flex items-end gap-1">
                <div class="flex-1 bg-primary/25 h-[50%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[65%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[75%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[80%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[60%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[40%] rounded-t-sm"></div>
                <div class="flex-1 bg-primary/25 h-[55%] rounded-t-sm"></div>
            </div>
            <p class="text-label-sm text-on-surface-variant mt-2 text-center">Profil Suhu Spindle (°C)</p>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
            <h5 class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant mb-4">Rencana Perawatan Berikutnya</h5>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary">event</span>
                    <div>
                        <p class="font-body-md text-body-md font-semibold">Ultrasound Bearing</p>
                        <p class="text-label-sm text-on-surface-variant">Dijadwalkan untuk 05 Nov</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant opacity-40">event</span>
                    <div class="opacity-40">
                        <p class="font-body-md text-body-md font-semibold">Coolant Flush</p>
                        <p class="text-label-sm text-on-surface-variant">Dijadwalkan untuk 12 Des</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl flex flex-col items-center justify-center text-center shadow-sm">
            <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-2">clinical_notes</span>
            <h5 class="font-body-md text-body-md font-bold text-on-surface">Ringkasan Keandalan</h5>
            <p class="text-body-sm text-on-surface-variant mt-2 px-4 italic">"Parameter operasional dalam batas normal. Integritas lapisan pelumasan tinggi. Tidak diperlukan tindakan segera."</p>
            <p class="text-label-sm font-label-sm text-primary mt-3 font-semibold">— Reliability Engine</p>
        </div>
    </div>

    <!-- Add Sparepart Mapping Modal -->
    <div id="sparepart-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 w-full max-w-md shadow-2xl relative" onclick="event.stopPropagation()">
            <button id="btn-close-sparepart-modal" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4">Tambah Mapping Sparepart</h3>
            
            <div class="space-y-4">
                <div class="relative">
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Cari Sparepart</label>
                    <div class="relative">
                        <input id="sparepart-search-input" type="text" autocomplete="off" class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-3 pr-10 py-2 focus:ring-2 focus:ring-primary focus:outline-none text-body-md" placeholder="Ketik Nama atau Kode WMS... (min. 2 karakter)">
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div id="sparepart-search-results" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-surface-container-lowest border border-outline-variant rounded-lg shadow-lg hidden z-50">
                        <!-- Results will be injected here -->
                    </div>
                </div>

                <!-- Error Message Alert -->
                <div id="sparepart-error-alert" class="p-3 bg-error-container text-on-error-container rounded-lg text-body-sm hidden flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span id="error-message-text"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button id="btn-cancel-sparepart-modal" type="button" class="px-4 py-2 bg-surface-container text-on-surface rounded-lg font-semibold text-sm hover:brightness-95 transition-all">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Add Document Link -->
    <div id="modal-add-doc-link" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-xs">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 w-full max-w-md shadow-2xl relative" onclick="event.stopPropagation()">
            <button type="button" onclick="closeAddDocLinkModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">link</span> Hubungkan Dokumen Library ISO
            </h3>

            <form id="form-add-doc-link" onsubmit="submitAddDocLink(event)" class="space-y-4">
                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Kategori *</label>
                    <select id="add-doc-category" name="document_category" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="manual">Manual Book</option>
                        <option value="electrical">Diagram Elektrikal</option>
                        <option value="hydraulic">Diagram Hidrolik</option>
                        <option value="pneumatic">Diagram Pneumatik</option>
                        <option value="plc">PLC Backup / Program</option>
                        <option value="parameter">Parameter Backup</option>
                        <option value="certificate">Sertifikat / Kalibrasi</option>
                        <option value="vendor">Dokumen Vendor</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Judul Dokumen *</label>
                    <input id="add-doc-title" type="text" name="title" required placeholder="Contoh: Manual Fanuc Oi-TF, Wiring Diagram PLC" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">URL Library ISO *</label>
                    <input id="add-doc-url" type="url" name="library_url" required placeholder="https://library.peroniks.id/documents/381" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                    <p class="text-[11px] text-on-surface-variant mt-1">Salin dan tempelkan URL resmi dokumen dari Library ISO.</p>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Deskripsi / Catatan (Opsional)</label>
                    <textarea id="add-doc-description" name="description" rows="2" placeholder="Catatan tambahan..." class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none"></textarea>
                </div>

                <div id="add-doc-error" class="hidden p-3 bg-error-container text-on-error-container rounded-lg text-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span id="add-doc-error-text"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddDocLinkModal()" class="px-4 py-2 bg-surface-container text-on-surface rounded-lg font-semibold text-xs hover:brightness-95 transition-all">Batal</button>
                    <button type="submit" id="btn-submit-add-doc" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold text-xs hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Document Link -->
    <div id="modal-edit-doc-link" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-xs">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 w-full max-w-md shadow-2xl relative" onclick="event.stopPropagation()">
            <button type="button" onclick="closeEditDocLinkModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">edit</span> Edit Referensi Dokumen
            </h3>

            <form id="form-edit-doc-link" onsubmit="submitEditDocLink(event)" class="space-y-4">
                <input type="hidden" id="edit-doc-id">
                
                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Kategori *</label>
                    <select id="edit-doc-category" name="document_category" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="manual">Manual Book</option>
                        <option value="electrical">Diagram Elektrikal</option>
                        <option value="hydraulic">Diagram Hidrolik</option>
                        <option value="pneumatic">Diagram Pneumatik</option>
                        <option value="plc">PLC Backup / Program</option>
                        <option value="parameter">Parameter Backup</option>
                        <option value="certificate">Sertifikat / Kalibrasi</option>
                        <option value="vendor">Dokumen Vendor</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Judul Dokumen *</label>
                    <input id="edit-doc-title" type="text" name="title" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">URL Library ISO *</label>
                    <input id="edit-doc-url" type="url" name="library_url" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Deskripsi / Catatan (Opsional)</label>
                    <textarea id="edit-doc-description" name="description" rows="2" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none"></textarea>
                </div>

                <div id="edit-doc-error" class="hidden p-3 bg-error-container text-on-error-container rounded-lg text-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span id="edit-doc-error-text"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditDocLinkModal()" class="px-4 py-2 bg-surface-container text-on-surface rounded-lg font-semibold text-xs hover:brightness-95 transition-all">Batal</button>
                    <button type="submit" id="btn-submit-edit-doc" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold text-xs hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 1: Add Photo Modal -->
    <div id="modal-add-photo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-xs">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 w-full max-w-md shadow-2xl relative" onclick="event.stopPropagation()">
            <button type="button" onclick="closeAddPhotoModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_a_photo</span> Tambah Foto Dokumentasi
            </h3>

            <form id="form-add-photo" onsubmit="submitAddPhoto(event)" class="space-y-4">
                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Kategori Foto *</label>
                    <select id="add-photo-category" name="photo_type" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="reference">Reference (Tampilan Umum / Layout)</option>
                        <option value="name_plate">Name Plate (Plat Spesifikasi / Serial)</option>
                        <option value="inspection">Inspection (Komponen / Cabinet / Unit)</option>
                        <option value="breakdown">Breakdown (Kondisi Sebelum Perbaikan)</option>
                        <option value="repair">Repair (Kondisi Setelah Perbaikan)</option>
                        <option value="other">Other (Lainnya)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Judul Foto *</label>
                    <input id="add-photo-title" type="text" name="title" required placeholder="misal: Spindle Motor, PLC Cabinet, Front Side" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Catatan / Deskripsi (Opsional)</label>
                    <textarea id="add-photo-description" name="description" rows="2" placeholder="Catatan posisi atau keterangan tambahan..." class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Pilih / Ambil Foto *</label>
                    <input id="add-photo-file" type="file" name="file"
                           accept="image/*"
                           capture="environment"
                           required onchange="previewAddPhotoFile(this)"
                           class="w-full text-xs text-on-surface file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                    <p class="text-[11px] text-on-surface-variant mt-1">Kamera belakang akan terbuka langsung. Maks. 10 MB (otomatis dikompresi).</p>
                </div>

                <!-- Preview box -->
                <div id="add-photo-preview-container" class="hidden relative aspect-video rounded-lg overflow-hidden border border-outline-variant bg-black/5">
                    <img id="add-photo-preview-img" src="" alt="Preview" class="w-full h-full object-cover">
                </div>

                <div id="add-photo-error" class="hidden p-3 bg-error-container text-on-error-container rounded-lg text-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span id="add-photo-error-text"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddPhotoModal()" class="px-4 py-2 bg-surface-container text-on-surface rounded-lg font-semibold text-xs hover:brightness-95 transition-all">Batal</button>
                    <button type="submit" id="btn-submit-add-photo" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold text-xs hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">upload</span> Simpan Foto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Photo Modal -->
    <div id="modal-edit-photo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-xs">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 w-full max-w-md shadow-2xl relative" onclick="event.stopPropagation()">
            <button type="button" onclick="closeEditPhotoModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">edit</span> Edit Informasi Foto
            </h3>

            <form id="form-edit-photo" onsubmit="submitEditPhoto(event)" class="space-y-4">
                <input type="hidden" id="edit-photo-id">
                
                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Kategori Foto *</label>
                    <select id="edit-photo-category" name="photo_type" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="reference">Reference</option>
                        <option value="name_plate">Name Plate</option>
                        <option value="inspection">Inspection</option>
                        <option value="breakdown">Breakdown</option>
                        <option value="repair">Repair</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Judul Foto *</label>
                    <input id="edit-photo-title" type="text" name="title" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-body-sm font-semibold text-on-surface-variant mb-1">Catatan / Deskripsi (Opsional)</label>
                    <textarea id="edit-photo-description" name="description" rows="2" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:outline-none"></textarea>
                </div>

                <div id="edit-photo-error" class="hidden p-3 bg-error-container text-on-error-container rounded-lg text-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span id="edit-photo-error-text"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditPhotoModal()" class="px-4 py-2 bg-surface-container text-on-surface rounded-lg font-semibold text-xs hover:brightness-95 transition-all">Batal</button>
                    <button type="submit" id="btn-submit-edit-photo" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold text-xs hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span> Simpan Perubahan
                    </button>
                </div>
    <!-- Modal Shared Machines Lookup -->
    <div id="modal-shared-machines" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-xs">
        <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-2xl max-w-md w-full mx-4 shadow-2xl space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-outline-variant/60">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                    <div>
                        <h3 class="font-bold text-xs text-on-surface">Shared Machine Passports</h3>
                        <p id="shared-machines-modal-title" class="text-[11px] text-on-surface-variant font-medium truncate max-w-[260px]"></p>
                    </div>
                </div>
                <button type="button" onclick="closeSharedMachinesModal()" class="p-1 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-lg transition-all">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <p class="text-[11px] text-on-surface-variant">Sparepart ini juga digunakan oleh paspor mesin berikut:</p>

            <div id="shared-machines-modal-list" class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                <!-- Dynamic List -->
            </div>

            <div class="flex justify-end pt-2 border-t border-outline-variant/60">
                <button type="button" onclick="closeSharedMachinesModal()" class="px-3 py-1 bg-surface-container text-on-surface rounded-lg font-bold text-xs hover:brightness-95 transition-all">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Lightbox Fullscreen Viewer -->
    <div id="lightbox-modal" class="fixed inset-0 z-[9999] hidden bg-black/95 flex flex-col justify-between select-none" onclick="closeLightbox()">
        <!-- Top Header Bar -->
        <div class="px-6 py-4 flex items-center justify-between text-white border-b border-white/10 bg-black/40 backdrop-blur-md z-20" onclick="event.stopPropagation()">
            <div class="flex items-center gap-4">
                <span id="lightbox-counter" class="text-sm font-bold tracking-wider text-white/80 bg-white/10 px-3 py-1 rounded-full border border-white/15">
                    1 / 1
                </span>
                <span id="lightbox-category" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/30 text-primary border border-primary/40 uppercase tracking-wider">
                    Category
                </span>
            </div>

            <!-- Zoom Controls & Close -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="zoomLightbox(-0.25)" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-all" title="Zoom Out (-)">
                    <span class="material-symbols-outlined text-[22px]">zoom_out</span>
                </button>
                <button type="button" onclick="resetLightboxZoom()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-all" title="Reset Zoom">
                    <span class="material-symbols-outlined text-[22px]">center_focus_strong</span>
                </button>
                <button type="button" onclick="zoomLightbox(0.25)" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-all" title="Zoom In (+)">
                    <span class="material-symbols-outlined text-[22px]">zoom_in</span>
                </button>
                <div class="w-px h-6 bg-white/20 mx-1"></div>
                <button type="button" onclick="closeLightbox()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-all" title="Tutup (Esc)">
                    <span class="material-symbols-outlined text-[26px]">close</span>
                </button>
            </div>
        </div>

        <!-- Center Display Area (Image & Prev/Next Arrows) -->
        <div class="relative flex-1 flex items-center justify-center overflow-hidden p-4" onclick="event.stopPropagation()">
            <!-- Prev Button -->
            <button type="button" id="lightbox-btn-prev" onclick="prevLightboxPhoto()" class="absolute left-4 z-20 p-3 text-white/80 hover:text-white bg-black/50 hover:bg-black/80 rounded-full transition-all shadow-lg border border-white/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-[32px]">chevron_left</span>
            </button>

            <!-- Image Container with Zoom & Drag -->
            <div id="lightbox-img-wrapper" class="relative max-w-full max-h-full flex items-center justify-center cursor-grab active:cursor-grabbing transition-transform duration-150" onwheel="handleLightboxWheel(event)" ondblclick="handleLightboxDblClick(event)">
                <img id="lightbox-img" src="" alt="Photo" class="max-w-[85vw] max-h-[75vh] object-contain rounded shadow-2xl transition-opacity duration-200">
            </div>

            <!-- Next Button -->
            <button type="button" id="lightbox-btn-next" onclick="nextLightboxPhoto()" class="absolute right-4 z-20 p-3 text-white/80 hover:text-white bg-black/50 hover:bg-black/80 rounded-full transition-all shadow-lg border border-white/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-[32px]">chevron_right</span>
            </button>
        </div>

        <!-- Bottom Metadata Bar -->
        <div class="px-6 py-4 text-white border-t border-white/10 bg-black/60 backdrop-blur-md z-20" onclick="event.stopPropagation()">
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 id="lightbox-title" class="font-headline-sm text-headline-sm font-bold text-white">Title</h3>
                    <p id="lightbox-description" class="text-xs text-white/70 mt-1 italic"></p>
                </div>
                <div id="lightbox-qr-actions" class="hidden flex flex-wrap gap-2 items-center">
                    <a id="lightbox-btn-download" href="#" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span> Download PNG
                    </a>
                    <button type="button" id="lightbox-btn-copy-img" onclick="copyQrImageFromLightbox()" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span> Copy Image
                    </button>
                    <button type="button" id="lightbox-btn-copy-link" onclick="copyPassportLinkFromLightbox()" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">link</span> Copy Link
                    </button>
                    <a id="lightbox-btn-print" href="#" target="_blank" class="px-3 py-1.5 bg-primary text-on-primary font-semibold text-xs rounded-lg transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">print</span> Print
                    </a>
                </div>
                <div id="lightbox-photo-meta" class="text-right text-xs text-white/60 shrink-0">
                    <p>Diunggah oleh: <span id="lightbox-uploader" class="font-semibold text-white/90">Admin</span></p>
                    <p>Tanggal: <span id="lightbox-date" class="font-semibold text-white/90">-</span></p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');

            // Bind document link buttons event listeners
            const btnLinkDoc = document.getElementById('btn-link-document');
            if (btnLinkDoc) {
                btnLinkDoc.addEventListener('click', function(e) {
                    e.preventDefault();
                    openAddDocLinkModal();
                });
            }
            const btnLinkDocEmpty = document.getElementById('btn-link-document-empty');
            if (btnLinkDocEmpty) {
                btnLinkDocEmpty.addEventListener('click', function(e) {
                    e.preventDefault();
                    openAddDocLinkModal();
                });
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    // Remove active classes from all buttons
                    tabButtons.forEach(b => {
                        b.classList.remove('text-primary', 'font-bold', 'border-b-2', 'border-primary');
                        b.classList.add('text-on-surface-variant');
                    });

                    // Add active classes to clicked button
                    btn.classList.add('text-primary', 'font-bold', 'border-b-2', 'border-primary');
                    btn.classList.remove('text-on-surface-variant');

                    // Hide all panels
                    tabPanels.forEach(p => p.classList.add('hidden'));

                    // Show target panel
                    const targetId = btn.getAttribute('data-target');
                    const targetPanel = document.getElementById(targetId);
                    if (targetPanel) {
                        targetPanel.classList.remove('hidden');
                    }
                });
            });

            // Sparepart Mapping Interactive Logic
            const modal = document.getElementById('sparepart-modal');
            const btnOpen = document.getElementById('btn-open-sparepart-modal');
            const btnClose = document.getElementById('btn-close-sparepart-modal');
            const btnCancel = document.getElementById('btn-cancel-sparepart-modal');
            const searchInput = document.getElementById('sparepart-search-input');
            const searchResults = document.getElementById('sparepart-search-results');
            const errorAlert = document.getElementById('sparepart-error-alert');
            const errorText = document.getElementById('error-message-text');

            // Open Modal
            btnOpen?.addEventListener('click', () => {
                modal.classList.remove('hidden');
                searchInput.value = '';
                searchResults.innerHTML = '';
                searchResults.classList.add('hidden');
                errorAlert.classList.add('hidden');
                searchInput.focus();
            });

            // Close Modal
            const closeModal = () => {
                modal.classList.add('hidden');
            };
            btnClose?.addEventListener('click', closeModal);
            btnCancel?.addEventListener('click', closeModal);

            // Close on click outside content
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            // Search input autocomplete handler
            let debounceTimer;
            searchInput?.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    searchResults.innerHTML = '';
                    searchResults.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('machines.spareparts.search', $machine->code) }}?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="p-3 text-body-sm text-on-surface-variant italic">Tidak ada sparepart ditemukan</div>';
                                searchResults.classList.remove('hidden');
                                return;
                            }

                            data.forEach(item => {
                                const itemCode = item.code || item.erpCode || item.erp_code || '';
                                const itemName = item.name || 'Unknown Sparepart';
                                const itemBrand = item.brand || '-';
                                const itemLocation = item.location || '-';
                                const itemStock = item.stock !== undefined ? item.stock : 0;

                                const row = document.createElement('div');
                                row.className = 'p-3 hover:bg-surface-container cursor-pointer transition-colors border-b border-outline-variant last:border-b-0 flex justify-between items-center';
                                row.innerHTML = `
                                    <div>
                                        <div class="font-body-md font-bold text-on-surface">${itemName}</div>
                                        <div class="text-xs text-on-surface-variant flex flex-wrap gap-2 mt-0.5">
                                            <span>ERP: <strong class="mono text-on-surface">${itemCode}</strong></span>
                                            <span>Brand: <strong>${itemBrand}</strong></span>
                                            <span>Rak: <strong class="mono">${itemLocation}</strong></span>
                                        </div>
                                    </div>
                                    <div class="text-right flex items-center gap-2">
                                        <span class="text-xs font-bold mono ${itemStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'}">Stok: ${itemStock}</span>
                                        <span class="material-symbols-outlined text-primary text-[20px]">add_circle</span>
                                    </div>
                                `;
                                row.addEventListener('click', () => {
                                    mapSparepart(itemCode);
                                });
                                searchResults.appendChild(row);
                            });
                            searchResults.classList.remove('hidden');
                        })
                        .catch(err => {
                            console.error('Error fetching autocomplete:', err);
                        });
                }, 300);
            });

            function mapSparepart(code) {
                errorAlert.classList.add('hidden');
                
                fetch(`{{ route('machines.spareparts.store', $machine->code) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ warehouse_item_code: code })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Gagal menambahkan mapping.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(err => {
                    errorText.textContent = err.message;
                    errorAlert.classList.remove('hidden');
                });
            }

            function appendSparepartRow(item) {
                const list = document.getElementById('spareparts-list');
                const emptyState = document.getElementById('spareparts-empty-state');
                if (emptyState) {
                    emptyState.remove();
                }

                const row = document.createElement('tr');
                row.className = 'hover:bg-surface-container-low/60 transition-colors sparepart-row h-[44px]';
                row.setAttribute('data-mapping-id', item.mapping_id);

                const unitUpper = (item.unit || '').trim().toUpperCase();
                const isPcs = (!unitUpper || unitUpper === 'PCS');
                const unitStr = isPcs ? '' : ' ' + item.unit;

                let stockBadge = '';
                if (item.isOffline) {
                    stockBadge = `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full border border-gray-300 dark:border-gray-700">⚪ WMS Offline</span>`;
                } else if (item.stock > 0) {
                    stockBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold mono border bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20">🟢 ${item.stock}${unitStr}</span>`;
                } else {
                    stockBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold mono border bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20">🔴 0${unitStr}</span>`;
                }

                const destroyUrl = `{{ url('/machines/' . $machine->code . '/spareparts') }}/${item.mapping_id}`;

                row.innerHTML = `
                    <td class="py-1.5 px-3 max-w-[280px] lg:max-w-[360px]">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-on-surface text-xs truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</span>
                            <span class="mono font-bold text-[10px] px-1 py-0.2 rounded bg-surface-container border border-outline-variant/60 text-on-surface-variant shrink-0" title="ERP Code">${escapeHtml(item.code || '-')}</span>
                        </div>
                    </td>
                    <td class="py-1.5 px-3 whitespace-nowrap">
                        ${stockBadge}
                    </td>
                    <td class="py-1.5 px-3 whitespace-nowrap">
                        <span class="text-on-surface-variant/40 text-[11px] font-medium">-</span>
                    </td>
                    <td class="py-1.5 px-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <button type="button" class="p-1 text-error hover:bg-error-container/20 rounded-md transition-colors btn-delete-mapping" data-url="${destroyUrl}" title="Hapus Mapping">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </td>
                `;

                row.querySelector('.btn-delete-mapping').addEventListener('click', function() {
                    handleDeleteMapping(this);
                });

                list.appendChild(row);
            }

            function handleDeleteMapping(button) {
                if (!confirm('Apakah Anda yakin ingin menghapus mapping sparepart ini?')) {
                    return;
                }

                const url = button.getAttribute('data-url');
                const row = button.closest('.sparepart-row');

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Gagal menghapus mapping.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        row.remove();
                        
                        const list = document.getElementById('spareparts-list');
                        if (list.children.length === 0) {
                            list.innerHTML = `
                                <tr id="spareparts-empty-state">
                                    <td colspan="4" class="text-center py-6 text-on-surface-variant text-xs italic">
                                        Belum ada kebutuhan sparepart yang dipetakan untuk mesin ini.
                                    </td>
                                </tr>
                            `;
                        }
                    }
                })
                .catch(err => {
                    alert(err.message);
                });
            }

            // Bind delete event listeners for existing elements
            document.querySelectorAll('.btn-delete-mapping').forEach(btn => {
                btn.addEventListener('click', function() {
                    handleDeleteMapping(this);
                });
            });
        });

        // Shared Machines Modal handlers
        window.openSharedMachinesModal = function(machines, name) {
            const listContainer = document.getElementById('shared-machines-modal-list');
            document.getElementById('shared-machines-modal-title').textContent = name;
            
            listContainer.innerHTML = '';
            if (Array.isArray(machines)) {
                machines.forEach(m => {
                    const item = document.createElement('a');
                    item.href = `/machines/${encodeURIComponent(m.code)}`;
                    item.target = '_blank';
                    item.className = 'p-2 rounded-lg border border-outline-variant/60 hover:border-primary/40 hover:bg-primary/5 transition-all flex items-center justify-between group';
                    item.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[16px]">precision_manufacturing</span>
                            <div>
                                <p class="mono font-bold text-xs text-primary group-hover:underline">${escapeHtml(m.code)}</p>
                                <p class="text-[11px] text-on-surface-variant line-clamp-1">${escapeHtml(m.name)}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-[14px] text-on-surface-variant group-hover:text-primary">arrow_forward</span>
                    `;
                    listContainer.appendChild(item);
                });
            }
            
            document.getElementById('modal-shared-machines').classList.remove('hidden');
        };

        window.closeSharedMachinesModal = function() {
            document.getElementById('modal-shared-machines').classList.add('hidden');
        };

        // Global functions for checklist navigation and upload placeholder
        window.navigateChecklist = function(item) {
            if (item === 'identitas') {
                window.location.href = "{{ route('machines.edit', $machine->code) }}";
            } else if (item === 'sparepart') {
                const tabBtn = document.querySelector('[data-target="panel-spareparts"]');
                if (tabBtn) tabBtn.click();
                setTimeout(() => {
                    const addMappingBtn = document.getElementById('btn-open-sparepart-modal');
                    if (addMappingBtn) {
                        addMappingBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        addMappingBtn.click();
                    }
                }, 100);
            } else if (item === 'manual') {
                const tabBtn = document.querySelector('[data-target="panel-documents"]');
                if (tabBtn) tabBtn.click();
                setTimeout(() => {
                    const docCard = document.getElementById('doc-card-manual_book');
                    if (docCard) {
                        docCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        docCard.classList.add('bg-primary-container/20', 'border-primary');
                        setTimeout(() => {
                            docCard.classList.remove('bg-primary-container/20', 'border-primary');
                        }, 2000);
                    }
                }, 100);
            } else if (item === 'photo') {
                const tabBtn = document.querySelector('[data-target="panel-photos"]');
                if (tabBtn) tabBtn.click();
                setTimeout(() => {
                    const panel = document.getElementById('panel-photos');
                    if (panel) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            } else if (item === 'qr') {
                const tabBtn = document.querySelector('[data-target="panel-overview"]');
                if (tabBtn) tabBtn.click();
                setTimeout(() => {
                    const qrCard = document.getElementById('qr-code-card');
                    if (qrCard) {
                        qrCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        qrCard.classList.add('bg-primary-container/20', 'border-primary');
                        setTimeout(() => {
                            qrCard.classList.remove('bg-primary-container/20', 'border-primary');
                        }, 2000);
                    }
                }, 100);
            } else if (item === 'components') {
                const tabBtn = document.querySelector('[data-target="panel-components"]');
                if (tabBtn) tabBtn.click();
            }
        };

        window.showUploadPlaceholder = function() {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none';
                document.body.appendChild(toastContainer);
            }
            
            const toast = document.createElement('div');
            toast.className = 'bg-surface-container-highest text-on-surface border border-outline-variant shadow-lg rounded-lg px-4 py-3 flex items-center gap-3 pointer-events-auto max-w-sm';
            toast.innerHTML = `
                <span class="material-symbols-outlined text-amber-500">warning</span>
                <div class="flex-1 text-sm font-semibold">
                    🚧 Fitur Pembuatan QR Code otomatis akan tersedia pada Phase berikutnya.
                </div>
                <button class="text-on-surface-variant hover:text-on-surface ml-2" onclick="this.parentElement.remove()">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            `;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        };

        // Checklist live update helper
        window.updateChecklist = function(progress) {
            const checklistConfigs = {
                'manual_book': { id: 'checklist-icon-manual_book', label: 'Manual Book' },
                'foto': { id: 'checklist-icon-foto', label: 'Foto Mesin' },
                'identitas': { id: 'checklist-icon-identitas', label: 'Identitas' },
                'sparepart': { id: 'checklist-icon-sparepart', label: 'Sparepart' },
                'qr': { id: 'checklist-icon-qr', label: 'QR Code' },
                'komponen': { id: 'checklist-icon-komponen', label: 'Komponen' }
            };

            for (const [key, config] of Object.entries(checklistConfigs)) {
                const element = document.getElementById(config.id);
                if (element) {
                    const isCompleted = progress.checklist[key];
                    if (isCompleted) {
                        element.innerHTML = `
                            <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                            <span class="text-on-surface font-semibold">${config.label}</span>
                        `;
                    } else {
                        element.innerHTML = `
                            <span class="material-symbols-outlined text-outline font-bold">radio_button_unchecked</span>
                            <span class="text-on-surface-variant">${config.label}</span>
                        `;
                    }
                }
            }

            // Update Progress Bar
            const progressText = document.getElementById('checklist-progress-text');
            if (progressText) {
                progressText.textContent = `${progress.completed} dari ${progress.total} Selesai`;
            }

            const progressBar = document.getElementById('checklist-progress-bar');
            if (progressBar) {
                const pct = progress.total > 0 ? (progress.completed / progress.total) * 100 : 0;
                progressBar.style.width = `${pct}%`;
            }
        };

        // Document upload triggers and AJAX actions
        window.triggerDocUpload = function(type) {
            document.getElementById(`input-doc-${type}`).click();
        };

        window.performDocUpload = function(type) {
            const fileInput = document.getElementById(`input-doc-${type}`);
            if (!fileInput.files.length) return;

            const file = fileInput.files[0];
            
            // Client-side validation
            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExt)) {
                alert('Format file dokumen tidak didukung. Gunakan pdf, doc, docx, xls, xlsx, zip, atau rar.');
                fileInput.value = '';
                return;
            }
            if (file.size > 50 * 1024 * 1024) {
                alert('Ukuran file dokumen maksimal adalah 50 MB.');
                fileInput.value = '';
                return;
            }

            const loadingOverlay = document.getElementById(`doc-loading-${type}`);
            if (loadingOverlay) loadingOverlay.classList.remove('hidden');

            const formData = new FormData();
            formData.append('type', type);
            formData.append('file', file);

            fetch(`{{ route('machines.documents.store', $machine->code) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal mengunggah dokumen.');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Update Card UI to completed state
                    const card = document.getElementById(`doc-card-${type}`);
                    card.className = "p-4 rounded-xl border transition-all duration-300 bg-green-50/40 border-green-200 shadow-sm flex flex-col justify-between min-h-[140px] relative";

                    document.getElementById(`doc-filename-${type}`).textContent = data.document.file_name;
                    document.getElementById(`doc-filename-${type}`).title = data.document.file_name;
                    document.getElementById(`doc-size-${type}`).textContent = data.document.formatted_size;
                    document.getElementById(`doc-date-${type}`).textContent = data.document.formatted_upload_date;

                    document.getElementById(`doc-details-${type}`).classList.remove('hidden');
                    document.getElementById(`doc-actions-${type}`).classList.remove('hidden');
                    document.getElementById(`doc-upload-placeholder-${type}`).classList.add('hidden');

                    // Update action URLs
                    document.getElementById(`doc-view-${type}`).href = data.document.file_path;
                    
                    const downloadUrl = `{{ url('/machines/' . $machine->code . '/documents') }}/${type}/download`;
                    document.getElementById(`doc-download-${type}`).href = downloadUrl;

                    // Update Checklist
                    updateChecklist(data.completion_progress);
                }
            })
            .catch(err => {
                alert(err.message);
            })
            .finally(() => {
                if (loadingOverlay) loadingOverlay.classList.add('hidden');
                fileInput.value = '';
            });
        };

        window.deleteDocument = function(type) {
            if (!confirm('Apakah Anda yakin ingin menghapus file dokumen ini?')) {
                return;
            }

            const loadingOverlay = document.getElementById(`doc-loading-${type}`);
            if (loadingOverlay) loadingOverlay.classList.remove('hidden');

            fetch(`{{ url('/machines/' . $machine->code . '/documents') }}/${type}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal menghapus dokumen.');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Update Card UI to incomplete state
                    const card = document.getElementById(`doc-card-${type}`);
                    card.className = "p-4 rounded-xl border transition-all duration-300 bg-surface-container border-dashed border-outline-variant opacity-80 flex flex-col justify-between min-h-[140px] relative";

                    document.getElementById(`doc-details-${type}`).classList.add('hidden');
                    document.getElementById(`doc-actions-${type}`).classList.add('hidden');
                    document.getElementById(`doc-upload-placeholder-${type}`).classList.remove('hidden');

                    document.getElementById(`doc-filename-${type}`).textContent = '';
                    document.getElementById(`doc-size-${type}`).textContent = '';
                    document.getElementById(`doc-date-${type}`).textContent = '';

                    // Update Checklist
                    updateChecklist(data.completion_progress);
                }
            })
            .catch(err => {
                alert(err.message);
            })
            .finally(() => {
                if (loadingOverlay) loadingOverlay.classList.add('hidden');
            });
        };

        // Gallery AJAX & UI logic
        let currentGalleryPhotos = [];
        let activeGalleryCategory = 'all';
        let activeGallerySearch = '';
        let activeGallerySort = 'newest';
        let lightboxIndex = 0;
        let lightboxZoomScale = 1.0;

        function loadGalleryPhotos(page = 1) {
            const grid = document.getElementById('gallery-grid');
            const emptyState = document.getElementById('gallery-empty-state');
            if (!grid) return;

            grid.innerHTML = `
                <div class="col-span-full py-12 flex flex-col items-center justify-center text-on-surface-variant gap-2">
                    <span class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></span>
                    <span class="text-xs font-semibold">Memuat dokumentasi foto...</span>
                </div>
            `;
            if (emptyState) emptyState.classList.add('hidden');

            const url = `{{ route('machines.photos.index', $machine->code) }}?category=${activeGalleryCategory}&search=${encodeURIComponent(activeGallerySearch)}&sort=${activeGallerySort}&page=${page}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Gagal memuat foto.');

                    currentGalleryPhotos = data.photos || [];

                    const badge = document.getElementById('gallery-total-badge');
                    if (badge) badge.textContent = `${data.total_count} Foto`;

                    const latest = document.getElementById('gallery-latest-upload');
                    if (latest) latest.textContent = `Terakhir diupload: ${data.latest_upload}`;

                    if (currentGalleryPhotos.length === 0) {
                        grid.innerHTML = '';
                        if (emptyState) emptyState.classList.remove('hidden');
                        renderGalleryPagination(data.pagination);
                        return;
                    }

                    if (emptyState) emptyState.classList.add('hidden');
                    grid.innerHTML = '';

                    currentGalleryPhotos.forEach((photo, idx) => {
                        const card = document.createElement('div');
                        card.className = "bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-xs hover:shadow-md transition-all group flex flex-col justify-between";
                        
                        const thumbUrl = photo.thumbnail_url || photo.full_url;
                        const categoryLabel = photo.category_label || photo.photo_type || 'Other';
                        
                        card.innerHTML = `
                            <div class="relative aspect-4/3 bg-surface-bright overflow-hidden cursor-pointer" onclick="openLightbox(${idx})">
                                <img src="${thumbUrl}" alt="${photo.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                <div class="absolute top-2 left-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-xs">
                                        ${categoryLabel}
                                    </span>
                                </div>
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-[28px]">zoom_in</span>
                                </div>
                            </div>
                            <div class="p-3 space-y-1">
                                <h5 class="font-body-md font-bold text-on-surface text-sm truncate" title="${photo.title}">${photo.title}</h5>
                                <div class="flex justify-between items-center text-[11px] text-on-surface-variant">
                                    <span>${photo.formatted_upload_date || ''}</span>
                                <div class="flex items-center gap-1">
                                        <button type="button" onclick="rotatePhoto(${photo.id}, 'left')" class="p-1 hover:bg-surface-container rounded text-on-surface-variant transition-colors" title="Putar Kiri 90°">
                                            <span class="material-symbols-outlined text-[16px]">rotate_left</span>
                                        </button>
                                        <button type="button" onclick="rotatePhoto(${photo.id}, 'right')" class="p-1 hover:bg-surface-container rounded text-on-surface-variant transition-colors" title="Putar Kanan 90°">
                                            <span class="material-symbols-outlined text-[16px]">rotate_right</span>
                                        </button>
                                        <button type="button" onclick="openEditPhotoModal(${photo.id})" class="p-1 hover:bg-surface-container rounded text-primary transition-colors" title="Edit Metadata">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </button>
                                        <button type="button" onclick="confirmDeletePhoto(${photo.id})" class="p-1 hover:bg-surface-container rounded text-error transition-colors" title="Hapus Foto">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);
                    });

                    renderGalleryPagination(data.pagination);
                })
                .catch(err => {
                    grid.innerHTML = `<div class="col-span-full py-8 text-center text-error text-xs">${err.message}</div>`;
                });
        }

        function renderGalleryPagination(pg) {
            const container = document.getElementById('gallery-pagination');
            if (!container) return;

            if (!pg || pg.last_page <= 1) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            container.innerHTML = `
                <span>Menampilkan halaman ${pg.current_page} dari ${pg.last_page} (${pg.total} foto)</span>
                <div class="flex gap-2">
                    <button type="button" ${pg.current_page === 1 ? 'disabled' : ''} onclick="loadGalleryPhotos(${pg.current_page - 1})" class="px-3 py-1 bg-surface-container rounded border border-outline-variant hover:bg-surface-container-high disabled:opacity-40 disabled:pointer-events-none">Sebelumnya</button>
                    <button type="button" ${pg.current_page === pg.last_page ? 'disabled' : ''} onclick="loadGalleryPhotos(${pg.current_page + 1})" class="px-3 py-1 bg-surface-container rounded border border-outline-variant hover:bg-surface-container-high disabled:opacity-40 disabled:pointer-events-none">Berikutnya</button>
                </div>
            `;
        }

        // Category pills click handler
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.category-pill').forEach(p => {
                    p.className = "category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant hover:bg-surface-container-high transition-all";
                });
                this.className = "category-pill px-3.5 py-1.5 rounded-full text-xs font-semibold bg-primary text-on-primary transition-all shadow-sm";
                activeGalleryCategory = this.getAttribute('data-category');
                loadGalleryPhotos(1);
            });
        });

        // Search handler
        let gallerySearchDebounce;
        document.getElementById('gallery-search-input')?.addEventListener('input', function() {
            clearTimeout(gallerySearchDebounce);
            gallerySearchDebounce = setTimeout(() => {
                activeGallerySearch = this.value.trim();
                loadGalleryPhotos(1);
            }, 300);
        });

        // Sort handler
        document.getElementById('gallery-sort-select')?.addEventListener('change', function() {
            activeGallerySort = this.value;
            loadGalleryPhotos(1);
        });

        // Add Photo Modal
        window.openAddPhotoModal = function() {
            const modal = document.getElementById('modal-add-photo');
            const form = document.getElementById('form-add-photo');
            const errAlert = document.getElementById('add-photo-error');
            const prevContainer = document.getElementById('add-photo-preview-container');
            if (form) form.reset();
            if (errAlert) errAlert.classList.add('hidden');
            if (prevContainer) prevContainer.classList.add('hidden');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeAddPhotoModal = function() {
            const modal = document.getElementById('modal-add-photo');
            if (modal) modal.classList.add('hidden');
        };

        window.previewAddPhotoFile = function(input) {
            const prevContainer = document.getElementById('add-photo-preview-container');
            const prevImg = document.getElementById('add-photo-preview-img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    prevImg.src = e.target.result;
                    prevContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.submitAddPhoto = function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-add-photo');
            const errAlert = document.getElementById('add-photo-error');
            const errText = document.getElementById('add-photo-error-text');
            const fileInput = document.getElementById('add-photo-file');

            if (!fileInput.files.length) return;

            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span> Mengunggah...`;
            if (errAlert) errAlert.classList.add('hidden');

            const formData = new FormData(document.getElementById('form-add-photo'));

            fetch(`{{ route('machines.photos.store', $machine->code) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengunggah foto.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    closeAddPhotoModal();
                    loadGalleryPhotos(1);
                    if (data.completion_progress) updateChecklist(data.completion_progress);
                }
            })
            .catch(err => {
                if (errText) errText.textContent = err.message;
                if (errAlert) errAlert.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px]">upload</span> Simpan Foto`;
            });
        };

        // Edit Photo Modal
        window.openEditPhotoModal = function(id) {
            const photo = currentGalleryPhotos.find(p => p.id === id);
            if (!photo) return;

            document.getElementById('edit-photo-id').value = photo.id;
            document.getElementById('edit-photo-category').value = photo.photo_type || photo.type || 'other';
            document.getElementById('edit-photo-title').value = photo.title || '';
            document.getElementById('edit-photo-description').value = photo.description || '';

            const errAlert = document.getElementById('edit-photo-error');
            if (errAlert) errAlert.classList.add('hidden');

            document.getElementById('modal-edit-photo').classList.remove('hidden');
        };

        window.closeEditPhotoModal = function() {
            document.getElementById('modal-edit-photo').classList.add('hidden');
        };

        window.submitEditPhoto = function(e) {
            e.preventDefault();
            const photoId = document.getElementById('edit-photo-id').value;
            const btn = document.getElementById('btn-submit-edit-photo');
            const errAlert = document.getElementById('edit-photo-error');
            const errText = document.getElementById('edit-photo-error-text');

            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span> Menyimpan...`;
            if (errAlert) errAlert.classList.add('hidden');

            const payload = {
                title: document.getElementById('edit-photo-title').value,
                photo_type: document.getElementById('edit-photo-category').value,
                description: document.getElementById('edit-photo-description').value,
            };

            fetch(`{{ url('/machines/' . $machine->code . '/photos') }}/${photoId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal memperbarui metadata foto.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    closeEditPhotoModal();
                    loadGalleryPhotos(1);
                }
            })
            .catch(err => {
                if (errText) errText.textContent = err.message;
                if (errAlert) errAlert.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px]">save</span> Simpan Perubahan`;
            });
        };

        // Delete Photo
        window.confirmDeletePhoto = function(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus foto dokumentasi ini?')) return;

            fetch(`{{ url('/machines/' . $machine->code . '/photos') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menghapus foto.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    loadGalleryPhotos(1);
                    if (data.completion_progress) updateChecklist(data.completion_progress);
                }
            })
            .catch(err => alert(err.message));
        };

        // Rotate Photo (↺ left / ↻ right)
        window.rotatePhoto = function(id, direction) {
            fetch(`{{ url('/machines/' . $machine->code . '/photos') }}/${id}/rotate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ direction })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal merotasi foto.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Refresh gallery to show rotated thumbnails
                    loadGalleryPhotos(1);
                }
            })
            .catch(err => alert(err.message));
        };

        // Global Toast Notification Helper
        window.showToast = function(message) {
            let toast = document.getElementById('global-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'global-toast';
                toast.className = 'fixed bottom-6 right-6 z-[10000] bg-slate-900 text-white text-xs font-semibold px-4 py-3 rounded-lg shadow-xl border border-slate-700 flex items-center gap-2 transition-all duration-300 opacity-0 transform translate-y-2 pointer-events-none';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span> <span>${message}</span>`;
            toast.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
            }, 3000);
        };

        window.copyPassportLink = function(url) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Link Paspor berhasil disalin!');
                }).catch(err => {
                    fallbackCopyText(url);
                });
            } else {
                fallbackCopyText(url);
            }
        };

        function fallbackCopyText(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showToast('Link Paspor berhasil disalin!');
            } catch (err) {
                alert('Gagal menyalin link: ' + text);
            }
            document.body.removeChild(textArea);
        }

        window.copyQrImage = async function(imgUrl, downloadUrl) {
            try {
                if (!navigator.clipboard || !window.ClipboardItem) {
                    throw new Error('Clipboard API tidak didukung');
                }
                const response = await fetch(imgUrl);
                const blob = await response.blob();
                await navigator.clipboard.write([
                    new ClipboardItem({ [blob.type]: blob })
                ]);
                showToast('Gambar QR berhasil disalin ke clipboard!');
            } catch (err) {
                console.warn('Fallback ke download:', err);
                showToast('Clipboard API tidak didukung. Memulai download PNG...');
                window.location.href = downloadUrl;
            }
        };

        let currentQrData = null;

        // Shared Asset Viewer (Photo & QR Modes)
        window.openAssetViewer = function(mode, imgUrl, machineCode, machineName, passportUrl, createdDate) {
            const modal = document.getElementById('lightbox-modal');
            if (!modal) return;

            if (mode === 'qr') {
                currentQrData = { imgUrl, machineCode, machineName, passportUrl, createdDate };
                resetLightboxZoom();

                const counter = document.getElementById('lightbox-counter');
                if (counter) counter.textContent = '1 / 1';

                const category = document.getElementById('lightbox-category');
                if (category) category.textContent = '✓ QR PERMANEN';

                const img = document.getElementById('lightbox-img');
                if (img) img.src = imgUrl;

                const title = document.getElementById('lightbox-title');
                if (title) title.textContent = `QR Code Paspor — ${machineName} (${machineCode})`;

                const desc = document.getElementById('lightbox-description');
                if (desc) desc.textContent = `URL Paspor Digital: ${passportUrl}`;

                const qrActions = document.getElementById('lightbox-qr-actions');
                const photoMeta = document.getElementById('lightbox-photo-meta');
                if (qrActions) qrActions.classList.remove('hidden');
                if (photoMeta) photoMeta.classList.add('hidden');

                const btnPrev = document.getElementById('lightbox-btn-prev');
                const btnNext = document.getElementById('lightbox-btn-next');
                if (btnPrev) btnPrev.style.display = 'none';
                if (btnNext) btnNext.style.display = 'none';

                const btnDownload = document.getElementById('lightbox-btn-download');
                if (btnDownload) btnDownload.href = `/machines/${machineCode}/qr/download`;

                const btnPrint = document.getElementById('lightbox-btn-print');
                if (btnPrint) btnPrint.href = `/machines/${machineCode}/qr/print`;

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        window.copyQrImageFromLightbox = function() {
            if (currentQrData) {
                copyQrImage(currentQrData.imgUrl, `/machines/${currentQrData.machineCode}/qr/download`);
            }
        };

        window.copyPassportLinkFromLightbox = function() {
            if (currentQrData) {
                copyPassportLink(currentQrData.passportUrl);
            }
        };

        // Lightbox Actions (Photo Mode)
        window.openLightbox = function(index) {
            if (!currentGalleryPhotos.length) return;
            lightboxIndex = index;
            resetLightboxZoom();
            updateLightboxUI();

            const qrActions = document.getElementById('lightbox-qr-actions');
            const photoMeta = document.getElementById('lightbox-photo-meta');
            if (qrActions) qrActions.classList.add('hidden');
            if (photoMeta) photoMeta.classList.remove('hidden');

            document.getElementById('lightbox-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.closeLightbox = function() {
            const modal = document.getElementById('lightbox-modal');
            if (modal) modal.classList.add('hidden');
            document.body.style.overflow = '';
        };

        function updateLightboxUI() {
            const photo = currentGalleryPhotos[lightboxIndex];
            if (!photo) return;

            const counter = document.getElementById('lightbox-counter');
            if (counter) counter.textContent = `${lightboxIndex + 1} / ${currentGalleryPhotos.length}`;

            const category = document.getElementById('lightbox-category');
            if (category) category.textContent = photo.category_label || photo.photo_type || 'Other';

            const img = document.getElementById('lightbox-img');
            if (img) img.src = photo.full_url || photo.file_path;

            const title = document.getElementById('lightbox-title');
            if (title) title.textContent = photo.title || 'Foto Mesin';

            const desc = document.getElementById('lightbox-description');
            if (desc) desc.textContent = photo.description || '';

            const uploader = document.getElementById('lightbox-uploader');
            if (uploader) uploader.textContent = photo.uploader ? photo.uploader.name : 'Admin';

            const date = document.getElementById('lightbox-date');
            if (date) date.textContent = photo.formatted_upload_date || '-';

            const btnPrev = document.getElementById('lightbox-btn-prev');
            if (btnPrev) btnPrev.style.display = currentGalleryPhotos.length > 1 ? 'flex' : 'none';

            const btnNext = document.getElementById('lightbox-btn-next');
            if (btnNext) btnNext.style.display = currentGalleryPhotos.length > 1 ? 'flex' : 'none';
        }

        window.nextLightboxPhoto = function() {
            if (currentGalleryPhotos.length <= 1) return;
            lightboxIndex = (lightboxIndex + 1) % currentGalleryPhotos.length;
            resetLightboxZoom();
            updateLightboxUI();
        };

        window.prevLightboxPhoto = function() {
            if (currentGalleryPhotos.length <= 1) return;
            lightboxIndex = (lightboxIndex - 1 + currentGalleryPhotos.length) % currentGalleryPhotos.length;
            resetLightboxZoom();
            updateLightboxUI();
        };

        window.zoomLightbox = function(delta) {
            lightboxZoomScale = Math.min(Math.max(0.5, lightboxZoomScale + delta), 3.0);
            applyLightboxZoom();
        };

        window.resetLightboxZoom = function() {
            lightboxZoomScale = 1.0;
            applyLightboxZoom();
        };

        function applyLightboxZoom() {
            const wrapper = document.getElementById('lightbox-img-wrapper');
            if (wrapper) {
                wrapper.style.transform = `scale(${lightboxZoomScale})`;
            }
        }

        window.handleLightboxWheel = function(e) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.15 : -0.15;
            zoomLightbox(delta);
        };

        window.handleLightboxDblClick = function(e) {
            if (lightboxZoomScale === 1.0) {
                zoomLightbox(0.75);
            } else {
                resetLightboxZoom();
            }
        };

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('lightbox-modal');
            if (!modal || modal.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowRight') {
                nextLightboxPhoto();
            } else if (e.key === 'ArrowLeft') {
                prevLightboxPhoto();
            } else if (e.key === '+' || e.key === '=') {
                zoomLightbox(0.25);
            } else if (e.key === '-') {
                zoomLightbox(-0.25);
            }
        });

        // Initialize gallery and doc links load on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            loadGalleryPhotos(1);
            loadDocLinks();
        });

        // ==========================================
        // Document Links Management (Library ISO Integration)
        // ==========================================
        let currentDocLinks = [];

        function loadDocLinks() {
            const grid = document.getElementById('doc-links-grid');
            const emptyState = document.getElementById('doc-links-empty-state');
            if (!grid) return;

            grid.innerHTML = `
                <div class="col-span-full py-8 flex flex-col items-center justify-center text-on-surface-variant gap-2">
                    <span class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></span>
                    <span class="text-xs font-semibold">Memuat referensi dokumen Library ISO...</span>
                </div>
            `;
            if (emptyState) emptyState.classList.add('hidden');

            const url = `{{ route('machines.documents.index', $machine->code) }}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Gagal memuat dokumen.');

                    currentDocLinks = data.links || [];

                    const badge = document.getElementById('doc-links-total-badge');
                    if (badge) badge.textContent = `${data.total_count} Dokumen`;

                    if (currentDocLinks.length === 0) {
                        grid.innerHTML = '';
                        if (emptyState) emptyState.classList.remove('hidden');
                        return;
                    }

                    if (emptyState) emptyState.classList.add('hidden');
                    grid.innerHTML = '';

                    currentDocLinks.forEach((link) => {
                        const card = document.createElement('div');
                        card.className = "bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-h-[140px]";
                        
                        let categoryIcon = 'description';
                        const cat = (link.document_category || '').toLowerCase();
                        if (cat.includes('electrical')) categoryIcon = 'bolt';
                        else if (cat.includes('hydraulic')) categoryIcon = 'water_drop';
                        else if (cat.includes('pneumatic')) categoryIcon = 'air';
                        else if (cat.includes('plc')) categoryIcon = 'developer_board';
                        else if (cat.includes('manual')) categoryIcon = 'menu_book';
                        else if (cat.includes('certificate')) categoryIcon = 'verified';

                        card.innerHTML = `
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[22px]">${categoryIcon}</span>
                                        <span class="font-body-md font-bold text-on-surface line-clamp-1">${escapeHtml(link.title)}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary border border-primary/20 shrink-0">
                                        ${escapeHtml(link.category_label)}
                                    </span>
                                </div>
                                ${link.description ? `<p class="text-xs text-on-surface-variant mt-2 line-clamp-2 italic">${escapeHtml(link.description)}</p>` : ''}
                            </div>

                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between gap-2">
                                <a href="${escapeHtml(link.library_url)}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1.5 shadow-2xs">
                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                    <span>Buka di Library ISO</span>
                                </a>

                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="openEditDocLinkModal(${link.id})" class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-all" title="Edit Referensi">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button type="button" onclick="deleteDocLink(${link.id}, '${escapeHtml(link.title)}')" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded-lg transition-all" title="Hapus Tautan">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);
                    });
                })
                .catch(err => {
                    grid.innerHTML = `
                        <div class="col-span-full py-8 text-center text-error text-xs font-semibold">
                            ${err.message || 'Terjadi kesalahan saat memuat dokumen.'}
                        </div>
                    `;
                });
        }

        window.openAddDocLinkModal = function() {
            document.getElementById('form-add-doc-link').reset();
            document.getElementById('add-doc-error').classList.add('hidden');
            document.getElementById('modal-add-doc-link').classList.remove('hidden');
        };

        window.closeAddDocLinkModal = function() {
            document.getElementById('modal-add-doc-link').classList.add('hidden');
        };

        window.submitAddDocLink = function(e) {
            e.preventDefault();
            const form = document.getElementById('form-add-doc-link');
            const formData = new FormData(form);
            const btn = document.getElementById('btn-submit-add-doc');
            const errBox = document.getElementById('add-doc-error');

            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-on-primary"></span> Menyimpan...`;
            errBox.classList.add('hidden');

            fetch(`{{ route('machines.documents.store', $machine->code) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                if (res.status !== 200 || !res.body.success) {
                    throw new Error(res.body.message || 'Gagal menyimpan tautan dokumen.');
                }
                closeAddDocLinkModal();
                loadDocLinks();
                if (typeof updateChecklistProgress === 'function' && res.body.completion_progress) {
                    updateChecklistProgress(res.body.completion_progress);
                }
            })
            .catch(err => {
                document.getElementById('add-doc-error-text').textContent = err.message;
                errBox.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px]">save</span> Simpan`;
            });
        };

        window.openEditDocLinkModal = function(id) {
            const link = currentDocLinks.find(l => l.id === id);
            if (!link) return;

            document.getElementById('edit-doc-id').value = link.id;
            document.getElementById('edit-doc-category').value = link.document_category;
            document.getElementById('edit-doc-title').value = link.title;
            document.getElementById('edit-doc-url').value = link.library_url;
            document.getElementById('edit-doc-description').value = link.description || '';
            document.getElementById('edit-doc-error').classList.add('hidden');
            document.getElementById('modal-edit-doc-link').classList.remove('hidden');
        };

        window.closeEditDocLinkModal = function() {
            document.getElementById('modal-edit-doc-link').classList.add('hidden');
        };

        window.submitEditDocLink = function(e) {
            e.preventDefault();
            const id = document.getElementById('edit-doc-id').value;
            const form = document.getElementById('form-edit-doc-link');
            const formData = new FormData(form);
            const btn = document.getElementById('btn-submit-edit-doc');
            const errBox = document.getElementById('edit-doc-error');

            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-on-primary"></span> Menyimpan...`;
            errBox.classList.add('hidden');

            fetch(`/machines/{{ $machine->code }}/documents/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                if (res.status !== 200 || !res.body.success) {
                    throw new Error(res.body.message || 'Gagal memperbarui referensi dokumen.');
                }
                closeEditDocLinkModal();
                loadDocLinks();
            })
            .catch(err => {
                document.getElementById('add-doc-error-text').textContent = err.message;
                errBox.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px]">save</span> Simpan Perubahan`;
            });
        };

        window.deleteDocLink = function(id, title) {
            if (!confirm(`Apakah Anda yakin ingin melepas referensi dokumen "${title}"?`)) return;

            fetch(`/machines/{{ $machine->code }}/documents/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Gagal melepas dokumen.');
                loadDocLinks();
                if (typeof updateChecklistProgress === 'function' && data.completion_progress) {
                    updateChecklistProgress(data.completion_progress);
                }
            })
            .catch(err => {
                alert(err.message || 'Gagal melepas dokumen.');
            });
        };
    </script>
    @endpush
</x-layouts.app>
