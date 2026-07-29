<x-layouts.app 
    title="Audit Kesiapan Perawatan | Sistem MRM"
    topbar-title="Audit Kesiapan PM"
>
    <x-breadcrumb :items="['Perencanaan' => route('planning.index'), 'Audit Kesiapan' => '']" />

    @php
        $statusText = $report['overall_status'];
        $blockers = $report['blockers'];
        $warnings = $report['warnings'];

        // Card styling based on readiness status
        $bannerClasses = match($statusText) {
            'Completed' => 'bg-green-50 border-green-300 text-green-900 dark:bg-green-950/20 dark:border-green-800 dark:text-green-300',
            'Waiting Review' => 'bg-blue-50 border-blue-300 text-blue-900 dark:bg-blue-950/20 dark:border-blue-800 dark:text-blue-300',
            'Ready' => 'bg-green-50 border-green-300 text-green-900 dark:bg-green-950/20 dark:border-green-800 dark:text-green-300',
            'Almost Ready' => 'bg-orange-50 border-orange-300 text-orange-950 dark:bg-orange-950/20 dark:border-orange-800 dark:text-orange-300',
            'Blocked' => 'bg-error-container border-error/30 text-on-error-container',
            'Reported' => 'bg-rose-50 border-rose-300 text-rose-950 dark:bg-rose-950/20 dark:border-rose-800 dark:text-rose-300',
            'Assigned' => 'bg-amber-50 border-amber-300 text-amber-950 dark:bg-amber-950/20 dark:border-amber-800 dark:text-amber-300',
        };

        $statusIcon = match($statusText) {
            'Completed' => 'task_alt',
            'Waiting Review' => 'rate_review',
            'Ready' => 'check_circle',
            'Almost Ready' => 'warning',
            'Blocked' => 'block',
            'Reported' => 'report',
            'Assigned' => 'engineering',
        };

        $statusTitle = match($statusText) {
            'Completed' => 'SELESAI (COMPLETED)',
            'Waiting Review' => 'MENUNGGU REVIEW (WAITING REVIEW)',
            'Ready' => 'SIAP EKSEKUSI',
            'Almost Ready' => 'HAMPIR SIAP',
            'Blocked' => 'TERBLOKIR (BLOCKED)',
            'Reported' => 'DILAPORKAN (REPORTED)',
            'Assigned' => 'DITUGASKAN (ASSIGNED)',
        };

        $statusSub = match($statusText) {
            'Completed' => 'Laporan pemeliharaan telah diserahkan oleh teknisi lapangan. Detail laporan dan hasil penilaian tertera di bawah.',
            'Waiting Review' => 'Pekerjaan pemeliharaan telah selesai dilakukan oleh teknisi dan sedang menunggu peninjauan/persetujuan Anda.',
            'Ready' => 'Semua prasyarat terpenuhi. Rencana pemeliharaan ini aman dan siap untuk dikonversi menjadi perintah kerja (work order).',
            'Almost Ready' => 'Rencana pemeliharaan ini dapat dilanjutkan, namun ada beberapa prasyarat minor yang belum lengkap.',
            'Blocked' => 'Rencana pemeliharaan tidak dapat dieksekusi saat ini karena adanya hambatan kritis pada kondisi mesin atau stok suku cadang.',
            'Reported' => 'Laporan kerusakan mesin telah didaftarkan ke sistem dan sedang menunggu penugasan teknisi.',
            'Assigned' => 'Teknisi pemeliharaan telah ditugaskan untuk melakukan perbaikan fisik pada mesin.',
        };

        $priorityLabel = match($plan->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
        };

        $priorityClass = match($plan->priority) {
            'low' => 'bg-surface-container text-on-surface-variant',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
            'critical' => 'bg-error-container text-on-error-container font-bold animate-pulse',
        };
    @endphp

    <!-- Header Details Card -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <div>
                <span class="text-label-md text-primary font-bold uppercase tracking-wider">
                    @if($plan->isCorrective())
                        Laporan Audit Kerusakan (Corrective)
                    @else
                        Laporan Audit Kesiapan PM
                    @endif
                </span>
                <h1 class="font-headline-md text-headline-md text-on-surface mt-1">
                    @if($plan->isCorrective())
                        Kerusakan Mesin: {{ $plan->breakdown_number }}
                    @else
                        Paket Perawatan: {{ $plan->maintenanceTemplate->name }}
                    @endif
                </h1>
                <p class="text-body-md text-on-surface-variant mt-1">
                    Mesin Sasaran: 
                    <a href="{{ route('machines.show', $plan->machine->code) }}" class="font-bold text-primary hover:underline">
                        {{ $plan->machine->code }} — {{ $plan->machine->name }}
                    </a>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-label-sm font-bold uppercase {{ $priorityClass }}">
                    Prioritas: {{ $priorityLabel }}
                </span>
                <span class="px-3 py-1 rounded-full text-label-sm font-bold uppercase bg-surface-container text-on-surface-variant">
                    Siklus: {{ $plan->isCorrective() ? 'Corrective' : $plan->maintenanceTemplate->maintenance_type }}
                </span>
            </div>
        </div>

        <hr class="border-outline-variant my-4" />

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-body-sm text-on-surface-variant">
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Jadwal Rencana</span>
                <span class="font-bold text-on-surface">{{ $plan->scheduled_date->format('d M Y') }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Estimasi Durasi</span>
                <span class="font-bold text-on-surface">
                    @if($plan->isCorrective())
                        {{ $plan->downtime_duration ?? '-' }} Menit (Downtime)
                    @else
                        {{ $plan->maintenanceTemplate->estimated_duration }} Menit
                    @endif
                </span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Teknisi Ditugaskan</span>
                <span class="font-bold text-on-surface">{{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Metode Pembuatan</span>
                <span class="font-bold text-on-surface">{{ $plan->generation_source }}</span>
            </div>
        </div>
    </div>

    <!-- Overall Readiness Banner -->
    <div class="border rounded-xl p-5 flex items-start gap-4 mb-8 shadow-sm {{ $bannerClasses }}">
        <span class="material-symbols-outlined text-[36px] mt-1 shrink-0" style="font-variation-settings: 'FILL' 1;">
            {{ $statusIcon }}
        </span>
        <div class="flex-1">
            <h3 class="font-headline-sm text-headline-sm font-bold mb-1">{{ $statusTitle }}</h3>
            <p class="text-body-md mb-4">{{ $statusSub }}</p>

            @if(count($blockers) > 0)
                <div class="mb-4">
                    <span class="text-label-sm font-bold uppercase text-error block mb-1">Masalah Kritis (Blockers):</span>
                    <ul class="list-disc pl-5 text-body-sm text-on-error-container space-y-1">
                        @foreach($blockers as $blocker)
                            <li>{{ $blocker }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(count($warnings) > 0)
                <div>
                    <span class="text-label-sm font-bold uppercase text-orange-800 dark:text-orange-400 block mb-1">Peringatan Persiapan (Warnings):</span>
                    <ul class="list-disc pl-5 text-body-sm text-on-surface-variant space-y-1">
                        @foreach($warnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Subsystem Auditing Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Grid 1: Kondisi Aset Mesin -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">1. Status Operasional Aset</h4>
                    <span class="material-symbols-outlined {{ $report['machine_ready'] ? 'text-green-500' : 'text-error' }}" style="font-variation-settings: 'FILL' 1;">
                        {{ $report['machine_ready'] ? 'check_circle' : 'cancel' }}
                    </span>
                </div>
                <h5 class="text-headline-sm font-headline-sm mb-2">
                    {{ $report['machine_ready'] ? 'Aset Operasional' : 'Aset Terganggu' }}
                </h5>
                <p class="text-body-sm text-on-surface-variant mb-4">
                    Mesin harus dalam keadaan 'Running' atau 'Idle' agar pemeliharaan preventif terjadwal berjalan lancar.
                </p>
            </div>
            <div class="p-3 bg-surface-container rounded-lg flex justify-between items-center text-body-sm">
                <span>Status Saat Ini:</span>
                <span class="font-bold {{ $report['machine_ready'] ? 'text-green-600' : 'text-error' }}">
                    {{ $report['machine_status_text'] }}
                </span>
            </div>
        </div>

        <!-- Grid 2: Penugasan Teknisi -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">2. Personel Pelaksana</h4>
                    <span class="material-symbols-outlined {{ $report['technician_assigned'] ? 'text-green-500' : 'text-orange-500' }}" style="font-variation-settings: 'FILL' 1;">
                        {{ $report['technician_assigned'] ? 'check_circle' : 'pending' }}
                    </span>
                </div>
                <h5 class="text-headline-sm font-headline-sm mb-2">
                    {{ $report['technician_assigned'] ? 'Teknisi Ditunjuk' : 'Belum Ditugaskan' }}
                </h5>
                <p class="text-body-sm text-on-surface-variant mb-4">
                    Penugasan nama teknisi penting agar penanggung jawab lapangan jelas saat rencana dimulai.
                </p>
            </div>
            <div class="p-3 bg-surface-container rounded-lg flex items-center gap-3 text-body-sm">
                <span class="material-symbols-outlined text-[20px] text-primary">engineering</span>
                <span class="font-bold text-on-surface">
                    {{ $plan->assigned_technician ?? 'Pilih Teknisi Pelaksana...' }}
                </span>
            </div>
        </div>

        <!-- Grid 3: Ketersediaan Dokumen Mandatori -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">3. Dokumen SOP & Buku Manual</h4>
                    <span class="material-symbols-outlined {{ $report['documents_available'] ? 'text-green-500' : 'text-orange-500' }}" style="font-variation-settings: 'FILL' 1;">
                        {{ $report['documents_available'] ? 'check_circle' : 'pending' }}
                    </span>
                </div>
                <h5 class="text-headline-sm font-headline-sm mb-2">
                    {{ $report['documents_available'] ? 'Buku Manual Tersedia' : 'Manual Tidak Ditemukan' }}
                </h5>
                <p class="text-body-sm text-on-surface-variant mb-4">
                    Akses digital ke dokumen petunjuk teknis/buku manual wajib tersedia bagi teknisi di area mesin.
                </p>
            </div>
            <div class="p-3 bg-surface-container rounded-lg flex justify-between items-center text-body-sm">
                <span>File Manual:</span>
                <span class="font-semibold text-on-surface truncate max-w-[150px]">
                    @php
                        $manualBook = $plan->machine->documents->firstWhere('type', 'manual_book');
                    @endphp
                    @if($manualBook)
                        {{ $manualBook->file_name }}
                    @else
                        Tidak Ada
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Detailed Checklist and Sparepart lists from PM Template -->
    @if(!$plan->isCorrective())
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        <!-- Left Side: Checklist SOP dari Paket Perawatan -->
        <div class="lg:col-span-6 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Daftar Checklist Tindakan</h3>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-primary/10 text-primary">
                    {{ $plan->maintenanceTemplate->checklists->count() }} Tugas
                </span>
            </div>
            <p class="text-body-sm text-on-surface-variant mb-4">Checklist ini didefinisikan dalam Paket Perawatan standar dan tidak dapat diubah di tingkat rencana.</p>

            <div class="space-y-4">
                @forelse($plan->maintenanceTemplate->checklists as $chk)
                    <div class="p-3 bg-surface-container-low border border-outline-variant rounded-lg flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">task_alt</span>
                        <div>
                            <h4 class="font-body-md text-body-md font-semibold text-on-surface">{{ $chk->title }}</h4>
                            @if($chk->description)
                                <p class="text-body-sm text-on-surface-variant mt-0.5">{{ $chk->description }}</p>
                            @endif
                            <span class="inline-block mt-2 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded {{ $chk->is_required ? 'bg-red-50 text-error' : 'bg-surface-container text-on-surface-variant' }}">
                                {{ $chk->is_required ? 'Wajib' : 'Opsional' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-body-md text-on-surface-variant italic">Belum ada tugas checklist diatur dalam Paket Perawatan.</p>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Spareparts Audit dari WMS -->
        <div class="lg:col-span-6 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Ketersediaan Suku Cadang (WMS)</h3>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold {{ $report['spareparts_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $report['spareparts_available'] ? 'Lengkap' : 'Ada Kurang' }}
                </span>
            </div>
            <p class="text-body-sm text-on-surface-variant mb-4">Suku cadang dicocokkan secara realtime dari data Warehouse Management System (WMS).</p>

            <div class="space-y-4">
                @forelse($report['sparepart_details'] as $sp)
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-body-md text-body-md font-bold text-on-surface">{{ $sp['name'] }}</h4>
                                <span class="mono text-xs text-primary font-semibold">{{ $sp['code'] }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $sp['is_sufficient'] ? 'bg-green-100 text-green-800' : 'bg-error-container text-on-error-container' }}">
                                {{ $sp['is_sufficient'] ? 'Cukup' : 'Stok Kurang' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-body-sm text-on-surface-variant mt-3 pt-2 border-t border-outline-variant/30">
                            <div>
                                <span class="block text-[10px] uppercase font-semibold text-on-surface-variant opacity-60">Dibutuhkan</span>
                                <span class="font-bold text-on-surface">{{ $sp['required'] }} Unit</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-semibold text-on-surface-variant opacity-60">Stok WMS</span>
                                <span class="font-bold {{ $sp['is_sufficient'] ? 'text-on-surface' : 'text-error' }}">{{ $sp['available'] }} Unit</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-semibold text-on-surface-variant opacity-60">Lokasi Rak</span>
                                <span class="font-bold text-on-surface truncate block">{{ $sp['location'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-surface-container border border-outline-variant rounded-xl text-on-surface-variant italic">
                        <span class="material-symbols-outlined text-[48px] opacity-40 mb-2">check_circle</span>
                        <p class="text-body-md">Pemeliharaan ini tidak membutuhkan penggantian suku cadang.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Notes & Action Footer -->
    @if($plan->notes)
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
            <h4 class="font-headline-sm text-headline-sm text-on-surface mb-2">Catatan Perencana</h4>
            <p class="text-body-md text-on-surface-variant italic leading-relaxed">
                "{{ $plan->notes }}"
            </p>
        </div>
    @endif

    <!-- Field Execution Results -->
    @if($plan->status === 'completed' && $plan->execution)
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
            <h3 class="font-headline-sm text-headline-sm text-on-surface border-b border-outline-variant pb-3 mb-4 uppercase tracking-wider font-extrabold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">task_alt</span>
                Laporan Eksekusi Perbaikan Lapangan
            </h3>
            
            @if($plan->isCorrective())
                <!-- Corrective Maintenance Results -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Metadata -->
                    <div class="md:col-span-2 grid grid-cols-2 gap-4 text-body-sm text-on-surface-variant">
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Nama Administrator (Verifikator)</span>
                            <span class="font-bold text-on-surface text-sm">{{ $plan->execution->operator_name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Waktu Verifikasi Selesai</span>
                            <span class="font-bold text-on-surface text-sm">
                                {{ $plan->execution->completed_at?->format('d M Y H:i') ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Rata-Rata Skor Kondisi</span>
                            <span class="font-bold text-on-surface text-sm flex items-center gap-1.5 mt-1">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black {{ $plan->execution->overall_score >= 4.0 ? 'bg-green-100 text-green-800' : ($plan->execution->overall_score >= 3.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($plan->execution->overall_score, 2) }} / 5.00
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Durasi Downtime</span>
                            <span class="font-bold text-on-surface text-sm">{{ $plan->downtime_duration ? $plan->downtime_duration . ' Menit' : '-' }}</span>
                        </div>
                    </div>

                    <!-- After Photo -->
                    <div class="bg-surface-container border border-outline-variant p-2 rounded-lg flex flex-col items-center justify-center min-h-[140px]">
                        @php
                            $afterPhoto = $plan->execution->photos->firstWhere('type', 'after');
                        @endphp
                        @if($afterPhoto && Storage::disk('public')->exists($afterPhoto->photo_path))
                            <img src="{{ asset('storage/' . $afterPhoto->photo_path) }}" alt="Foto Setelah Perbaikan" class="max-h-32 object-contain rounded shadow" />
                        @else
                            <div class="text-center text-slate-400 py-3">
                                <span class="material-symbols-outlined text-[36px] opacity-40">broken_image</span>
                                <p class="text-[10px] uppercase font-bold tracking-tight">Foto Tidak Ditemukan</p>
                            </div>
                        @endif
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Foto Setelah Perbaikan</span>
                    </div>
                </div>

                <!-- Before Photo (Optional) -->
                @php
                    $beforePhoto = $plan->execution->photos->firstWhere('type', 'before');
                @endphp
                @if($beforePhoto && Storage::disk('public')->exists($beforePhoto->photo_path))
                    <div class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-4 mb-6">
                        <img src="{{ asset('storage/' . $beforePhoto->photo_path) }}" alt="Foto Sebelum Perbaikan" class="max-h-24 object-contain rounded shadow" />
                        <div>
                            <span class="block text-xs uppercase font-bold text-on-surface-variant opacity-60">Foto Sebelum Perbaikan (Optional)</span>
                            <p class="text-xs text-slate-500 mt-1">Kondisi kerusakan awal mesin saat dilaporkan operator.</p>
                        </div>
                    </div>
                @endif

                <!-- Consumed Spareparts list -->
                @if($plan->execution->spareparts->count() > 0)
                    <div class="mb-6 border-t border-outline-variant/30 pt-4">
                        <h4 class="font-body-md text-body-md font-bold uppercase tracking-wider text-on-surface-variant mb-3">Suku Cadang yang Digunakan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($plan->execution->spareparts as $esp)
                                <div class="p-3.5 bg-surface-container-low border border-outline-variant rounded-lg flex justify-between items-center text-body-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span>
                                        <span class="font-bold text-slate-800 font-mono">{{ $esp->warehouse_item_code }}</span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-bold font-mono text-xs">
                                        Qty: {{ $esp->quantity }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Notes / Tindakan Korektif -->
                @if($plan->execution->notes)
                    @php
                        $reportData = null;
                        $cleanedNotes = str_replace("\r", "", $plan->execution->notes);
                        if (preg_match('/^\[REPORT:(.*)\]$/m', $cleanedNotes, $matches)) {
                            $reportData = json_decode($matches[1], true);
                        }
                    @endphp
                    @if($reportData)
                        <div class="bg-surface-container-low border border-outline-variant p-5 rounded-xl space-y-4 text-body-sm text-on-surface mb-6">
                            <div class="border-b border-outline-variant/30 pb-2">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-primary">Laporan Penyelesaian Perbaikan</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-on-surface-variant opacity-60">Verified By (Verifikator)</span>
                                    <span class="font-bold text-slate-800">{{ $reportData['verified_by'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-on-surface-variant opacity-60">Repair Performed By (Tim Perbaikan)</span>
                                    <span class="font-bold text-slate-800">{{ !empty($reportData['team']) ? implode(', ', $reportData['team']) : '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-on-surface-variant opacity-60">Tipe Perbaikan</span>
                                    <span class="font-bold text-slate-800">{{ ($reportData['repair_type'] ?? '') === 'Temporary' ? 'Sementara (Temporary)' : 'Permanen' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-on-surface-variant opacity-60">Status Operasional Hasil</span>
                                    <span class="font-bold text-slate-800">{{ ucfirst($reportData['actual_status'] ?? 'running') }}</span>
                                </div>
                            </div>
                            
                            <div class="bg-amber-50/70 p-3.5 rounded-xl border border-amber-200/40">
                                <span class="block text-[10px] uppercase font-bold text-amber-700">Masalah Tersisa (Remaining Issues)</span>
                                <p class="text-xs text-amber-900 mt-1 italic font-medium">
                                    {{ !empty($reportData['remaining_issues']) ? $reportData['remaining_issues'] : 'Tidak ada.' }}
                                </p>
                            </div>

                            <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-200/40">
                                <span class="block text-[10px] uppercase font-bold text-blue-700">Tindakan Lanjutan (Follow-up Action)</span>
                                <p class="text-xs text-blue-900 mt-1 italic font-medium">
                                    {{ !empty($reportData['follow_up']) ? $reportData['follow_up'] : '-' }}
                                </p>
                            </div>

                            @if(!empty($reportData['user_notes']))
                                <div class="border-t border-outline-variant/35 pt-3">
                                    <span class="block text-[10px] uppercase font-bold text-on-surface-variant opacity-60">Catatan Tambahan</span>
                                    <p class="italic text-on-surface text-xs mt-1">"{{ $reportData['user_notes'] }}"</p>
                                </div>
                            @endif
                        </div>
                    @else
                        @if(!str_contains($plan->execution->notes, '[REPORT:'))
                            <div class="bg-surface-container-low border border-outline-variant p-4 rounded-lg text-body-sm text-on-surface mb-6">
                                <span class="block text-xs uppercase font-bold text-on-surface-variant opacity-60 mb-1">Catatan Tambahan &amp; Tindakan Korektif</span>
                                <p class="italic text-on-surface">"{{ $plan->execution->notes }}"</p>
                            </div>
                        @endif
                    @endif
                @endif

            @else
                <!-- Original PM Results -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Metadata -->
                    <div class="md:col-span-2 grid grid-cols-2 gap-4 text-body-sm text-on-surface-variant">
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Teknisi Pelaksana</span>
                            <span class="font-bold text-on-surface text-sm">{{ $plan->execution->operator_name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Waktu Pelaksanaan</span>
                            <span class="font-bold text-on-surface text-sm">
                                {{ $plan->execution->started_at?->format('d M Y H:i') }} - {{ $plan->execution->completed_at?->format('H:i') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Rata-Rata Skor Kondisi</span>
                            <span class="font-bold text-on-surface text-sm flex items-center gap-1.5 mt-1">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black {{ $plan->execution->overall_score >= 4.0 ? 'bg-green-100 text-green-800' : ($plan->execution->overall_score >= 3.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($plan->execution->overall_score, 2) }} / 5.00
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase font-semibold opacity-60">Status Laporan</span>
                            <span class="font-bold text-on-surface text-sm">
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase bg-blue-100 text-blue-800">
                                    {{ $plan->execution->status === 'waiting_review' ? 'Menunggu Review' : 'Disetujui' }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- General Photo -->
                    <div class="bg-surface-container border border-outline-variant p-2 rounded-lg flex flex-col items-center justify-center min-h-[140px]">
                        @php
                            $genPhoto = $plan->execution->photos->firstWhere('type', 'general');
                        @endphp
                        @if($genPhoto && Storage::disk('public')->exists($genPhoto->photo_path))
                            <img src="{{ asset('storage/' . $genPhoto->photo_path) }}" alt="Foto Eksekusi" class="max-h-32 object-contain rounded shadow" />
                        @else
                            <div class="text-center text-slate-400 py-3">
                                <span class="material-symbols-outlined text-[36px] opacity-40">broken_image</span>
                                <p class="text-[10px] uppercase font-bold tracking-tight">Foto Tidak Ditemukan</p>
                            </div>
                        @endif
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Foto Bukti Lapangan</span>
                    </div>
                </div>

                <!-- Notes -->
                @if($plan->execution->notes)
                    <div class="bg-surface-container-low border border-outline-variant p-4 rounded-lg text-body-sm text-on-surface mb-6">
                        <span class="block text-xs uppercase font-bold text-on-surface-variant opacity-60 mb-1">Catatan Tambahan Teknisi</span>
                        <p class="italic text-on-surface">"{{ $plan->execution->notes }}"</p>
                    </div>
                @endif

                <!-- Answers List -->
                <div>
                    <h4 class="font-body-md text-body-md font-bold uppercase tracking-wider text-on-surface-variant mb-3">Hasil Evaluasi Checklist</h4>
                    <div class="space-y-3">
                        @foreach($plan->execution->answers as $ans)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-surface-container-low border border-outline-variant rounded-lg">
                                <div class="flex-1">
                                    <h5 class="font-body-sm text-body-sm font-bold text-on-surface leading-tight">
                                        {{ $ans->checklistItem->title }}
                                    </h5>
                                    @if($ans->remarks)
                                        <p class="text-xs text-red-600 mt-1 italic font-medium">Catatan Kerusakan: "{{ $ans->remarks }}"</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs text-on-surface-variant">Skor:</span>
                                    <span class="px-3 py-1.5 rounded-xl text-xs font-black text-white {{ $ans->score == 5 ? 'bg-green-600' : ($ans->score >= 3 ? 'bg-amber-500' : 'bg-red-600') }}">
                                        {{ $ans->score }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

    <div class="flex justify-end gap-3 mb-12">
        <a href="{{ route('planning.index') }}" class="bg-surface-container border border-outline-variant hover:bg-surface-container-high text-on-surface px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors">
            Kembali ke Papan Perencanaan
        </a>
        @if($plan->status !== 'completed')
            <a href="{{ route('planning.print', $plan->id) }}" target="_blank" class="bg-primary hover:bg-primary/95 text-on-primary px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[20px]">print</span>
                Cetak Perintah Kerja
            </a>
            <a href="{{ route('planning.execute', $plan->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                Eksekusi PM (Simulasi QR)
            </a>
        @endif
    </div>
</x-layouts.app>
