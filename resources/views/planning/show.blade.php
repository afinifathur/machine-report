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

    @if($plan->isCancelled())
        <div class="mb-6 p-6 bg-error-container border border-error/30 text-on-error-container rounded-xl shadow-sm flex items-start gap-4">
            <span class="material-symbols-outlined text-[36px] shrink-0 text-error" style="font-variation-settings: 'FILL' 1;">
                cancel
            </span>
            <div class="space-y-2">
                <h3 class="font-headline-sm text-headline-sm font-black uppercase text-error">STATUS: CANCELLED</h3>
                
                <div>
                    <span class="block text-xs uppercase font-semibold opacity-70">Alasan Pembatalan / Reason:</span>
                    <p class="text-body-md font-bold italic">{{ $plan->cancellation_reason }}</p>
                </div>

                @if($plan->replacementPlan)
                    <div>
                        <span class="block text-xs uppercase font-semibold opacity-70">Laporan Pengganti / Replacement Report:</span>
                        <p class="text-body-md">
                            @if($plan->replacementPlan->isCorrective())
                                <a href="{{ route('breakdowns.show', $plan->replacementPlan->id) }}" class="font-bold text-primary hover:underline underline-offset-4">
                                    {{ $plan->replacementPlan->breakdown_number }}
                                </a>
                            @else
                                <a href="{{ route('preventive.show', $plan->replacementPlan->id) }}" class="font-bold text-primary hover:underline underline-offset-4">
                                    {{ $plan->replacementPlan->work_order_number }}
                                </a>
                            @endif
                        </p>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-error/20 text-xs opacity-80">
                    <div>
                        <span class="uppercase font-semibold">Dibatalkan Oleh:</span>
                        <span class="font-bold">{{ $plan->cancelledByUser->name ?? 'System' }}</span>
                    </div>
                    <div>
                        <span class="uppercase font-semibold">Tanggal Batal:</span>
                        <span class="font-bold font-mono">{{ $plan->cancelled_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                        Paket Perawatan: {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : 'Tanpa SOP (Perencanaan Umum)' }}
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
                    Siklus: {{ $plan->isCorrective() ? 'Corrective' : ($plan->maintenanceTemplate ? $plan->maintenanceTemplate->maintenance_type : 'Preventive') }}
                </span>
            </div>
        </div>

        <hr class="border-outline-variant my-4" />

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-body-sm text-on-surface-variant">
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
                        {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->estimated_duration : 120 }} Menit
                    @endif
                </span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Teknisi Ditugaskan</span>
                <span class="font-bold text-on-surface">{{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Target Selesai</span>
                <span class="font-bold text-on-surface font-mono text-primary">{{ $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-' }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-semibold text-on-surface-variant opacity-60">Metode Pembuatan</span>
                <span class="font-bold text-on-surface">{{ $plan->generation_source }}</span>
            </div>
        </div>
    </div>

    @if($plan->status !== 'completed' && $plan->status !== 'cancelled')
        <!-- Adjust target completion and plan details form -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">edit_calendar</span>
                Sesuaikan Target Selesai &amp; Detail Perencanaan
            </h3>
            <form action="{{ route('planning.update', $plan->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="target_completion" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Target Waktu Penyelesaian</label>
                        <input type="datetime-local" name="target_completion" id="target_completion" 
                               value="{{ $plan->target_completion ? $plan->target_completion->format('Y-m-d\TH:i') : '' }}"
                               class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary font-mono text-primary font-bold" />
                    </div>

                    <div>
                        <label for="assigned_technician" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Teknisi Pelaksana</label>
                        <x-employee-autocomplete 
                            name="assigned_technician" 
                            id="assigned_technician" 
                            selected="{{ old('assigned_technician', $plan->assigned_technician) }}"
                            required="false"
                            placeholder="Cari nama teknisi..."
                        />
                    </div>

                    <div>
                        <label for="priority" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Prioritas</label>
                        <select name="priority" id="priority" class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary font-bold">
                            <option value="low" {{ $plan->priority === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                            <option value="medium" {{ $plan->priority === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                            <option value="high" {{ $plan->priority === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                            <option value="critical" {{ $plan->priority === 'critical' ? 'selected' : '' }}>Kritis (Critical)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan atau instruksi khusus untuk teknisi..."
                              class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary italic text-slate-700">{{ $plan->notes }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    @if($plan->canBeCancelled())
                        <button type="button" onclick="openCancellationModal()" class="bg-error hover:bg-error/95 text-on-error px-5 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow">
                            Batalkan Rencana
                        </button>
                    @endif
                    <button type="submit" class="bg-primary hover:bg-primary/95 text-on-primary px-5 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($plan->status !== 'cancelled')
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
    @endif

    <!-- Subsystem Auditing Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
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
                    Mesin harus dalam keadaan 'Running' atau 'Idle' agar pemeliharaan preventif berjalan lancar.
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

        <!-- Grid 4: Kesiapan Suku Cadang Terpetakan -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">4. Suku Cadang Terpetakan</h4>
                    <span class="material-symbols-outlined {{ $report['sparepart_readiness_ready'] ? 'text-green-500' : 'text-orange-500' }}" style="font-variation-settings: 'FILL' 1;">
                        {{ $report['sparepart_readiness_ready'] ? 'check_circle' : 'warning' }}
                    </span>
                </div>
                <h5 class="text-headline-sm font-headline-sm mb-2">
                    {{ $report['sparepart_readiness_ready'] ? 'Suku Cadang Siap' : 'Perlu Perhatian' }}
                </h5>
                <p class="text-body-sm text-on-surface-variant mb-4">
                    Audit ketersediaan stok suku cadang mesin yang telah terpetakan di database.
                </p>
            </div>
            <div class="p-3 bg-surface-container rounded-lg flex justify-between items-center text-body-sm">
                <span>Kesiapan Stok:</span>
                <span class="font-bold {{ $report['sparepart_readiness_ready'] ? 'text-green-600' : 'text-orange-600' }}">
                    {{ $report['sparepart_readiness_ready'] ? 'Terpenuhi (Siap)' : 'Ada Kurang / Reorder' }}
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
                    {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->checklists->count() : 0 }} Tugas
                </span>
            </div>
            <p class="text-body-sm text-on-surface-variant mb-4">Checklist ini didefinisikan dalam Paket Perawatan standar dan tidak dapat diubah di tingkat rencana.</p>

            <div class="space-y-4">
                @if($plan->maintenanceTemplate)
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
                @else
                    <p class="text-body-md text-on-surface-variant italic text-center py-4">Rencana PM ini tidak menggunakan paket SOP / checklist.</p>
                @endif
            </div>
        </div>

        <!-- Right Side: Spareparts Audit dari WMS -->
        <div class="lg:col-span-6 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Ketersediaan Suku Cadang (WMS)</h3>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold {{ $report['spareparts_available'] === 'N/A' ? 'bg-slate-100 text-slate-800' : ($report['spareparts_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                    {{ $report['spareparts_available'] === 'N/A' ? 'N/A (Tanpa SOP)' : ($report['spareparts_available'] ? 'Lengkap' : 'Ada Kurang') }}
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

    <!-- Section: Mapped Spareparts Audit (Sparepart Readiness Panel) -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
        <div class="flex justify-between items-center border-b border-outline-variant pb-4 mb-4">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">4. Audit Stok Suku Cadang Terpetakan (WMS)</h3>
                <p class="text-body-sm text-on-surface-variant mt-1">Ketersediaan realtime dari Warehouse Management System (WMS) berdasarkan daftar suku cadang mesin ini.</p>
            </div>
            <span class="px-3 py-1 rounded text-xs font-bold {{ $report['sparepart_readiness_ready'] ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                {{ $report['sparepart_readiness_ready'] ? 'Semua Stok Siap' : 'Perlu Perhatian' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-sm">
                <thead>
                    <tr class="border-b border-outline-variant text-on-surface-variant font-bold">
                        <th class="pb-3 pr-4">Kode ERP</th>
                        <th class="pb-3 px-4">Nama Suku Cadang</th>
                        <th class="pb-3 px-4 text-center">Dibutuhkan</th>
                        <th class="pb-3 px-4 text-center">Stok WMS</th>
                        <th class="pb-3 pl-4 text-right">Status Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse($report['mapped_spareparts'] as $part)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 pr-4 font-mono font-bold text-primary">{{ $part['code'] }}</td>
                            <td class="py-3.5 px-4 font-medium text-on-surface">{{ $part['name'] }}</td>
                            <td class="py-3.5 px-4 text-center font-semibold">{{ $part['required'] }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $part['available'] >= $part['required'] ? 'text-on-surface' : 'text-error' }}">
                                {{ $part['available'] }}
                            </td>
                            <td class="py-3.5 pl-4 text-right">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 {{ $part['badge_class'] }}">
                                    <span>{{ $part['icon'] }}</span>
                                    <span>{{ $part['status'] }}</span>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-on-surface-variant italic">
                                Tidak ada suku cadang terpetakan untuk mesin ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
            
            @php
                $isPlanDelayed = $plan->actual_completion && $plan->target_completion && $plan->actual_completion->gt($plan->target_completion);
                $reasonLabels = [
                    'waiting_sparepart' => 'Waiting Sparepart (Menunggu Suku Cadang)',
                    'waiting_production' => 'Waiting Production (Menunggu Produksi)',
                    'waiting_vendor' => 'Waiting Vendor (Menunggu Vendor)',
                    'waiting_approval' => 'Waiting Approval (Menunggu Persetujuan)',
                    'additional_damage' => 'Additional Damage Found (Kerusakan Tambahan)',
                    'manpower_shortage' => 'Manpower Shortage (Kekurangan Personel)',
                    'power_failure' => 'Power Failure (Mati Listrik/Daya)',
                    'other' => 'Other (Lainnya)',
                ];
                $displayReason = $reasonLabels[$plan->delay_reason] ?? $plan->delay_reason;
            @endphp

            @if($isPlanDelayed)
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-5 mb-6 text-body-sm text-slate-800">
                    <h4 class="font-headline-sm text-headline-sm font-bold text-rose-800 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                        Analisis Keterlambatan Perawatan (Delay Analysis)
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-semibold text-slate-600 mb-4">
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Target Selesai</span>
                            <span class="font-bold text-slate-800">{{ $plan->target_completion->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Realisasi Selesai</span>
                            <span class="font-bold text-slate-800">{{ $plan->actual_completion->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Durasi Keterlambatan</span>
                            <span class="font-bold text-rose-600">
                                @php
                                    $diff = $plan->actual_completion->diff($plan->target_completion);
                                    $parts = [];
                                    if ($diff->d > 0) $parts[] = $diff->d . ' Hari';
                                    if ($diff->h > 0) $parts[] = $diff->h . ' Jam';
                                    if ($diff->i > 0) $parts[] = $diff->i . ' Menit';
                                    echo implode(' ', $parts) ?: 'Kurang dari 1 Menit';
                                @endphp
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Alasan Utama</span>
                            <span class="font-bold text-slate-800 uppercase">{{ $displayReason }}</span>
                        </div>
                    </div>
                    @if($plan->delay_notes)
                        <div class="bg-white p-3 rounded-lg border border-rose-100 text-xs">
                            <strong class="block mb-1 text-[10px] uppercase font-bold text-slate-400">Catatan Detail:</strong>
                            <p class="italic text-slate-700">"{{ $plan->delay_notes }}"</p>
                        </div>
                    @endif
                </div>
            @endif

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
        @if($plan->status !== 'cancelled')
            @if($plan->status === 'completed')
                <a href="{{ route('planning.report', $plan) }}" target="_blank" class="bg-primary hover:bg-primary/95 text-on-primary px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[20px]">print</span>
                    Print Hasil Kerja
                </a>
            @else
                <a href="{{ route('planning.print', $plan) }}" target="_blank" class="bg-primary hover:bg-primary/95 text-on-primary px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[20px]">print</span>
                    Cetak Perintah Kerja
                </a>
                <a href="{{ route('planning.execute', $plan->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg text-body-md font-bold transition-colors shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                    Eksekusi PM (Simulasi QR)
                </a>
            @endif
        @endif
    </div>

    @if($plan->canBeCancelled())
        <!-- Cancellation Modal -->
        <div id="cancellation-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCancellationModal()"></div>

                <!-- Center elements -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block align-middle bg-surface-container-lowest border border-outline-variant rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('planning.cancel', $plan->id) }}" method="POST" id="cancellation-form">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4 text-error">
                                <span class="material-symbols-outlined text-[32px]">warning</span>
                                <h3 class="text-headline-sm font-bold text-on-surface" id="modal-title">Konfirmasi Pembatalan</h3>
                            </div>
                            
                            <p class="text-body-md text-on-surface-variant mb-4">
                                Apakah Anda yakin ingin membatalkan rencana perawatan ini? Tindakan ini tidak dapat dibatalkan.
                            </p>

                            <div class="space-y-4">
                                <!-- Reason -->
                                <div>
                                    <label for="cancellation_reason" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Alasan Pembatalan <span class="text-error">*</span></label>
                                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" required
                                              placeholder="Masukkan alasan pembatalan secara detail..."
                                              class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                                </div>

                                <!-- Replacement Autocomplete -->
                                <div class="relative">
                                    <label for="replacement_search" class="block text-xs font-bold uppercase text-on-surface-variant mb-1 font-semibold">Laporan Pengganti (Opsional)</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-3 text-on-surface-variant opacity-60 text-[20px]">search</span>
                                        <input type="text" id="replacement_search" placeholder="Cari nomor BD/PM pengganti..." autocomplete="off"
                                               class="w-full pl-10 pr-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary focus:outline-none"/>
                                    </div>
                                    <input type="hidden" name="replacement_id" id="replacement_id"/>
                                    
                                    <!-- Autocomplete dropdown list -->
                                    <div id="replacement-results" class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto bg-surface-container border border-outline-variant rounded-lg shadow-lg hidden">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-4 bg-surface-container border-t border-outline-variant flex justify-end gap-3">
                            <button type="button" onclick="closeCancellationModal()" class="px-4 py-2 bg-surface-container border border-outline hover:bg-surface-container-high rounded-lg text-sm font-bold text-on-surface transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-error hover:bg-error/95 rounded-lg text-sm font-bold text-on-error transition-colors shadow">
                                Ya, Batalkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openCancellationModal() {
                document.getElementById('cancellation-modal').classList.remove('hidden');
            }

            function closeCancellationModal() {
                document.getElementById('cancellation-modal').classList.add('hidden');
                document.getElementById('cancellation_reason').value = '';
                document.getElementById('replacement_id').value = '';
                document.getElementById('replacement_search').value = '';
                document.getElementById('replacement-results').innerHTML = '';
                document.getElementById('replacement-results').classList.add('hidden');
            }

            (function() {
                const searchInput = document.getElementById('replacement_search');
                const resultsContainer = document.getElementById('replacement-results');
                const hiddenInput = document.getElementById('replacement_id');
                const planType = "{{ $plan->type->value }}"; //pm or corrective

                if (!searchInput || !resultsContainer) return;

                let timeoutId = null;

                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    hiddenInput.value = ''; // clear previous selection
                    
                    if (timeoutId) clearTimeout(timeoutId);

                    if (query.length < 2) {
                        resultsContainer.innerHTML = '';
                        resultsContainer.classList.add('hidden');
                        return;
                    }

                    timeoutId = setTimeout(() => {
                        fetch(`{{ route('planning.autocomplete-replacements') }}?q=${encodeURIComponent(query)}&type=${planType}`)
                            .then(response => response.json())
                            .then(data => {
                                resultsContainer.innerHTML = '';
                                if (data.length === 0) {
                                    resultsContainer.innerHTML = `<div class="p-3 text-xs text-on-surface-variant italic">Tidak ditemukan rencana pengganti</div>`;
                                    resultsContainer.classList.remove('hidden');
                                    return;
                                }

                                data.forEach(item => {
                                    const option = document.createElement('div');
                                    option.className = 'p-3 hover:bg-primary-container hover:text-on-primary-container cursor-pointer text-sm font-medium border-b border-outline-variant last:border-0';
                                    option.textContent = item.text;
                                    option.addEventListener('click', function() {
                                        searchInput.value = item.text;
                                        hiddenInput.value = item.id;
                                        resultsContainer.classList.add('hidden');
                                    });
                                    resultsContainer.appendChild(option);
                                });
                                resultsContainer.classList.remove('hidden');
                            })
                            .catch(error => {
                                console.error('Error fetching replacements:', error);
                            });
                    }, 300);
                });

                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (e.target !== searchInput && e.target !== resultsContainer && !resultsContainer.contains(e.target)) {
                        resultsContainer.classList.add('hidden');
                    }
                });
            })();
        </script>
    @endif
</x-layouts.app>
