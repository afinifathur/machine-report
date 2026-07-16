@php
    $overallPhoto = $machine->photos->where('type', 'overall')->first();
    $photoUrl = ($overallPhoto && $overallPhoto->file_path) ? asset($overallPhoto->file_path) : null;
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
        <div class="w-full md:w-1/3 aspect-video rounded overflow-hidden border border-outline-variant relative group bg-surface-container flex items-center justify-center">
            @if($photoUrl)
                <img class="w-full h-full object-cover" alt="{{ $machine->name }} {{ $machine->code }}" src="{{ $photoUrl }}"/>
            @else
                <div class="text-center p-6 text-on-surface-variant flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-[48px]">image_not_supported</span>
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
                <x-button variant="primary" icon="medical_services" href="{{ route('maintenances.create') }}">
                    Catat Perawatan
                </x-button>
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
            <!-- Health Gauge Section -->
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl flex flex-col items-center justify-center text-center shadow-sm">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase mb-4 tracking-widest">Kesehatan Mesin</p>
                <x-health-score :score="$machine->health_score" type="circle" />
                
                <div class="mt-6 w-full space-y-2">
                    <div class="flex justify-between text-label-md font-label-md">
                        <span>Rentang Optimal</span>
                        <span class="text-on-surface font-semibold">85% - 100%</span>
                    </div>
                    <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-primary" style="width: 85%"></div>
                    </div>
                </div>
            </div>

            <!-- Machine Identity Checklist Card -->
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Kelengkapan Paspor Mesin</h4>
                <ul class="space-y-3 text-body-md text-left">
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                        <span class="text-on-surface font-semibold">Identitas</span>
                    </li>
                    <li class="flex items-center gap-3">
                        @if($machine->has_photo)
                            <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                            <span class="text-on-surface">Foto</span>
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant opacity-40">cancel</span>
                            <span class="text-on-surface-variant line-through opacity-60">Foto</span>
                        @endif
                    </li>
                    <li class="flex items-center gap-3">
                        @if($machine->has_manual)
                            <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                            <span class="text-on-surface">Manual Book</span>
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant opacity-40">cancel</span>
                            <span class="text-on-surface-variant line-through opacity-60">Manual Book</span>
                        @endif
                    </li>
                    <li class="flex items-center gap-3">
                        @if($machine->has_qr)
                            <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                            <span class="text-on-surface">QR Code</span>
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant opacity-40">cancel</span>
                            <span class="text-on-surface-variant line-through opacity-60">QR Code</span>
                        @endif
                    </li>
                    <li class="flex items-center gap-3">
                        @if($machine->has_spareparts)
                            <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                            <span class="text-on-surface">Mapping Sparepart</span>
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant opacity-40">cancel</span>
                            <span class="text-on-surface-variant line-through opacity-60">Mapping Sparepart</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>

        <!-- Detailed Diagnostics Tabs Content -->
        <div class="col-span-12 md:col-span-8 bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col shadow-sm min-h-[400px]">
            <!-- Tab Headers -->
            <div class="border-b border-outline-variant px-6 flex space-x-8 overflow-x-auto" id="passport-tabs">
                <button data-target="panel-overview" class="tab-btn py-4 font-body-md text-body-md text-primary font-bold border-b-2 border-primary whitespace-nowrap">Ikhtisar</button>
                <button data-target="panel-components" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Komponen Mesin</button>
                <button data-target="panel-spareparts" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Kebutuhan Sparepart (BACA-SAJA)</button>
                <button data-target="panel-documents" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Dokumen</button>
                <button data-target="panel-photos" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Foto</button>
                <button data-target="panel-history" class="tab-btn py-4 font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Riwayat Mesin</button>
            </div>
            
            <div class="flex-1 overflow-y-auto max-h-[420px] hide-scrollbar">
                
                <!-- Panel 1: Overview -->
                <div id="panel-overview" class="tab-panel p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Kebutuhan Sparepart</h4>
                        <span class="inline-flex items-center gap-1 text-[11px] text-on-surface-variant font-bold bg-surface-container px-2 py-0.5 rounded border border-outline-variant">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Terhubung ke WMS (BACA-SAJA)
                        </span>
                    </div>

                    <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 p-3 rounded-lg text-body-sm flex gap-3 items-start">
                        <span class="material-symbols-outlined text-[20px] mt-0.5">info</span>
                        <p>
                            <strong>Warehouse adalah Sumber Informasi Utama (Single Source of Truth):</strong> Detail stok dan status ketersediaan diambil langsung dari Warehouse Management System (WMS). Perubahan stok atau operasi transaksi tidak dapat dilakukan di sini.
                        </p>
                    </div>

                    <div class="divide-y divide-outline-variant">
                        @forelse($sparepartsDetails as $code => $part)
                            <div class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-body-md font-bold text-on-surface">{{ $part['name'] }}</p>
                                    <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Kode WMS: <span class="mono font-semibold">{{ $code }}</span></p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">Lokasi: <span class="mono">{{ $part['location'] }}</span> | Supplier: {{ $part['supplier'] }}</p>
                                </div>
                                <div class="text-right flex flex-col items-end gap-1">
                                    <span class="font-label-md text-label-md text-on-surface-variant font-bold">Jumlah Stok: <span class="mono text-on-surface">{{ $part['stock'] }}</span></span>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase @if($part['stock'] > 0) bg-green-50 text-green-700 @else bg-red-50 text-red-700 @endif">
                                        {{ $part['availability'] === 'Available' ? 'Tersedia' : 'Stok Habis' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-on-surface-variant">
                                Belum ada kebutuhan sparepart yang dipetakan untuk mesin ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel 4: Documents -->
                <div id="panel-documents" class="tab-panel p-6 hidden">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Dokumentasi Mesin</h4>
                    <p class="text-body-sm text-on-surface-variant mb-4">
                        Dokumen digital yang diperlukan untuk penanganan masalah. Sistem mendukung pengunggahan dokumen secara bertahap.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $docCategories = [
                                'manual_book' => 'Manual Book',
                                'electrical_diagram' => 'Diagram Elektrikal',
                                'hydraulic_diagram' => 'Diagram Hidrolik',
                                'parameter_backup' => 'Backup Parameter',
                                'vendor_document' => 'Dokumen Vendor'
                            ];
                        @endphp

                        @foreach($docCategories as $type => $label)
                            @php
                                $doc = $machine->documents->where('type', $type)->first();
                            @endphp

                            <div class="p-4 rounded-xl border {{ ($doc && $doc->file_path) ? 'bg-surface-container-lowest border-outline-variant' : 'bg-surface-container border-dashed border-outline-variant opacity-80' }} flex flex-col justify-between min-h-[120px]">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined {{ ($doc && $doc->file_path) ? 'text-primary' : 'text-on-surface-variant' }}">description</span>
                                        <span class="font-body-md font-bold text-on-surface">{{ $label }}</span>
                                    </div>
                                    @if($doc && $doc->file_path)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase text-green-700 bg-green-50 px-2 py-0.5 rounded">Diunggah</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase text-slate-600 bg-slate-100 px-2 py-0.5 rounded">Belum Lengkap</span>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    @if($doc && $doc->file_path)
                                        <span class="text-xs text-on-surface-variant truncate max-w-[150px] mono" title="{{ $doc->file_name }}">{{ $doc->file_name }}</span>
                                        <a href="#" class="text-primary hover:text-primary-container font-semibold text-xs flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">download</span> Unduh
                                        </a>
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">Belum Ada Dokumen</span>
                                        <button class="text-primary hover:text-primary-container font-semibold text-xs flex items-center gap-1" title="Unggah sekarang">
                                            <span class="material-symbols-outlined text-[16px]">upload</span> Unggah
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Panel 5: Photos -->
                <div id="panel-photos" class="tab-panel p-6 hidden">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider mb-4">Foto Dokumentasi Fisik</h4>
                    <p class="text-body-sm text-on-surface-variant mb-4">
                        Foto referensi lapangan bernilai tinggi untuk operasional dan perawatan. Dikompresi ke 100-200 KB untuk pengiriman cepat.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $photoCategories = [
                                'overall' => 'Keseluruhan Mesin',
                                'name_plate' => 'Name Plate',
                                'electrical_cabinet' => 'Electrical Cabinet',
                                'hydraulic_unit' => 'Hydraulic Unit'
                            ];
                        @endphp

                        @foreach($photoCategories as $type => $label)
                            @php
                                $photo = $machine->photos->where('type', $type)->first();
                            @endphp

                            <div class="border rounded-xl p-3 flex flex-col justify-between min-h-[160px] {{ ($photo && $photo->file_path) ? 'bg-surface-container-lowest border-outline-variant' : 'bg-surface-container border-dashed border-outline-variant opacity-80' }}">
                                <div class="text-label-sm font-label-sm uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ $label }}
                                </div>

                                <div class="flex-1 flex items-center justify-center bg-surface-bright rounded overflow-hidden border border-outline-variant relative group">
                                    @if($photo && $photo->file_path)
                                        <img src="{{ asset($photo->file_path) }}" alt="{{ $label }}" class="w-full h-24 object-cover"/>
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <a href="#" class="text-white text-xs font-semibold flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center p-3 text-on-surface-variant/60 flex flex-col items-center">
                                            <span class="material-symbols-outlined text-[24px]">photo_camera</span>
                                            <span class="text-[10px] mt-1 italic">Menunggu</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-2 flex justify-between items-center">
                                    <span class="text-[10px] text-on-surface-variant font-bold uppercase">{{ ($photo && $photo->file_path) ? 'Terverifikasi' : 'Belum Ada' }}</span>
                                    <button class="text-primary hover:text-primary-container text-xs font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">edit</span> Ubah
                                    </button>
                                </div>
                            </div>
                        @endforeach
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

    <!-- Contextual FAB -->
    <div class="fixed bottom-8 right-8 z-50">
        <a href="{{ route('maintenances.create') }}" class="w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-transform">
            <span class="material-symbols-outlined text-[28px]" data-icon="add">add</span>
        </a>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');

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
        });
    </script>
    @endpush
</x-layouts.app>
