<x-layouts.app 
    title="Detail Perawatan {{ $plan->isCorrective() ? $plan->breakdown_number : ($plan->work_order_number ?? $plan->machine->code) }} | Sistem MRM"
    topbar-title="Audit Kesiapan PM"
>
    <x-breadcrumb :items="['Perencanaan' => route('planning.index'), ($plan->isCorrective() ? $plan->breakdown_number : 'Audit Kesiapan PM') => '']" />

    @php
        $statusText = $report['overall_status'];
        $blockers = $report['blockers'] ?? [];
        $warnings = $report['warnings'] ?? [];

        $priorityLabel = match($plan->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
            default => ucfirst($plan->priority ?? 'Medium'),
        };

        $priorityBadgeClass = match($plan->priority) {
            'low' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
            'medium' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'high' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800',
            'critical' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 animate-pulse font-black',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };

        $statusTitle = match($statusText) {
            'Completed' => 'SELESAI (COMPLETED)',
            'Waiting Review' => 'MENUNGGU REVIEW (WAITING REVIEW)',
            'Ready' => 'SIAP EKSEKUSI',
            'Almost Ready' => 'HAMPIR SIAP',
            'Blocked' => 'TERBLOKIR (BLOCKED)',
            'Reported' => 'DILAPORKAN (REPORTED)',
            'Assigned' => 'DITUGASKAN (ASSIGNED)',
            default => strtoupper($statusText),
        };

        $statusBadgeClass = match($statusText) {
            'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'Waiting Review' => 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'Ready' => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'Almost Ready' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'Blocked' => 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            'Reported' => 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            'Assigned' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };

        // Parse Execution / Report JSON if available
        $reportData = null;
        $rawExecutionNotes = $plan->execution?->notes ?? '';
        if ($rawExecutionNotes) {
            $cleanedNotes = str_replace("\r", "", $rawExecutionNotes);
            if (preg_match('/\[REPORT:\s*({.*?})\s*\]/s', $cleanedNotes, $matches)) {
                $reportData = json_decode($matches[1], true);
            }
        }

        // Delay Calculation
        $isPlanDelayed = $plan->actual_completion && $plan->target_completion && $plan->actual_completion->gt($plan->target_completion);
        $delayMinutes = $isPlanDelayed ? (int) $plan->actual_completion->diffInMinutes($plan->target_completion) : 0;
        $delayDiff = $isPlanDelayed ? $plan->actual_completion->diff($plan->target_completion) : null;
        $delayFormatted = '';
        if ($delayDiff) {
            $delayParts = [];
            if ($delayDiff->d > 0) $delayParts[] = $delayDiff->d . ' Hari';
            if ($delayDiff->h > 0) $delayParts[] = $delayDiff->h . ' Jam';
            if ($delayDiff->i > 0) $delayParts[] = $delayDiff->i . ' Menit';
            $delayFormatted = implode(' ', $delayParts) ?: 'Kurang dari 1 Menit';
        }

        $delayReasonLabels = [
            'waiting_sparepart' => 'Waiting Sparepart (Menunggu Suku Cadang)',
            'waiting_production' => 'Waiting Production (Menunggu Produksi)',
            'waiting_vendor' => 'Waiting Vendor (Menunggu Vendor)',
            'waiting_approval' => 'Waiting Approval (Menunggu Persetujuan)',
            'additional_damage' => 'Additional Damage Found (Kerusakan Tambahan)',
            'manpower_shortage' => 'Manpower Shortage (Kekurangan Personel)',
            'power_failure' => 'Power Failure (Mati Listrik/Daya)',
            'other' => 'Other (Lainnya)',
        ];
        $displayDelayReason = $delayReasonLabels[$plan->delay_reason] ?? ($plan->delay_reason ? ucfirst(str_replace('_', ' ', $plan->delay_reason)) : '-');

        // Document / Manual Availability Canonical Source
        $hasManualDoc = !empty($report['documents_available']);
        $manualBookDoc = $plan->machine ? $plan->machine->documents->firstWhere('type', 'manual_book') : null;
        $manualDocName = $manualBookDoc ? $manualBookDoc->file_name : ($hasManualDoc ? 'Tersedia di Sistem' : 'Tidak Ada');
    @endphp

    {{-- CANCELLED BANNER --}}
    @if($plan->isCancelled())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-300 text-rose-900 rounded-lg shadow-sm flex items-start gap-3">
            <span class="material-symbols-outlined text-[28px] shrink-0 text-rose-600" style="font-variation-settings: 'FILL' 1;">
                cancel
            </span>
            <div class="flex-1 text-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold uppercase tracking-wider text-rose-800 text-xs">STATUS: CANCELLED (DIBATALKAN)</span>
                    <span class="text-xs text-rose-600">· {{ $plan->cancelled_at ? $plan->cancelled_at->format('d M Y H:i') : '-' }}</span>
                </div>
                <p class="font-semibold text-rose-950 mb-1">Alasan: "{{ $plan->cancellation_reason }}"</p>
                <div class="flex flex-wrap gap-4 text-xs text-rose-700 pt-1">
                    <span>Oleh: <strong>{{ $plan->cancelledByUser->name ?? 'System' }}</strong></span>
                    @if($plan->replacementPlan)
                        <span>Laporan Pengganti: 
                            <a href="{{ $plan->replacementPlan->isCorrective() ? route('breakdowns.show', $plan->replacementPlan->id) : route('preventive.show', $plan->replacementPlan->id) }}" class="font-bold underline">
                                {{ $plan->replacementPlan->isCorrective() ? $plan->replacementPlan->breakdown_number : $plan->replacementPlan->work_order_number }}
                            </a>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 1. PRIMARY HEADER: DIGITAL MAINTENANCE CASE --}}
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 pb-5 border-b border-outline-variant/60">
            <!-- Left: Machine & Problem Identity -->
            <div class="space-y-2 max-w-3xl">
                <!-- Machine Identity (Visually Dominant) -->
                <div class="flex items-baseline flex-wrap gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-on-surface tracking-tight">
                        <a href="{{ route('machines.show', $plan->machine->code) }}" class="hover:text-primary transition-colors">
                            {{ $plan->machine->name }}
                        </a>
                    </h1>
                    <span class="font-mono text-base font-bold text-on-surface-variant bg-surface-container px-2.5 py-0.5 rounded border border-outline-variant">
                        {{ $plan->machine->code }}
                    </span>
                </div>

                <!-- Reference & Type Subtitle -->
                <div class="flex items-center flex-wrap gap-2 text-xs font-semibold text-on-surface-variant">
                    <span class="font-mono font-bold text-primary">
                        {{ $plan->isCorrective() ? $plan->breakdown_number : ($plan->work_order_number ?? 'PM-SCHEDULE') }}
                    </span>
                    <span>&bull;</span>
                    <span class="uppercase tracking-wider">
                        {{ $plan->isCorrective() ? 'Corrective Maintenance' : ($plan->maintenanceTemplate ? $plan->maintenanceTemplate->maintenance_type : 'Preventive Maintenance') }}
                    </span>
                    @if(!$plan->isCorrective() && $plan->maintenanceTemplate)
                        <span>&bull;</span>
                        <span class="text-on-surface">{{ $plan->maintenanceTemplate->name }}</span>
                    @endif
                </div>

                <!-- Problem / Work Description Prominent Display -->
                <div class="pt-1">
                    <div class="bg-surface-container-low border-l-4 border-primary px-4 py-2.5 rounded-r-lg">
                        <p class="text-base font-bold text-on-surface leading-snug">
                            @if($plan->isCorrective())
                                "{{ $plan->notes ?? 'Kerusakan mesin dilaporkan tanpa deskripsi rinci.' }}"
                            @else
                                "{{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : ($plan->notes ?? 'Paket Perawatan Preventif Rutin') }}"
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Status & Priority Badges -->
            <div class="flex lg:flex-col items-end gap-2 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider border {{ $statusBadgeClass }}">
                        {{ $statusTitle }}
                    </span>
                    <span class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider border {{ $priorityBadgeClass }}">
                        Prioritas: {{ $priorityLabel }}
                    </span>
                </div>
                @if($plan->status === 'completed' && $plan->execution)
                    <div class="text-right text-xs text-on-surface-variant mt-1">
                        <span class="font-semibold">Skor:</span>
                        <span class="font-bold font-mono text-emerald-700 dark:text-emerald-400 text-sm ml-1">
                            {{ number_format($plan->execution->overall_score, 2) }} / 5.00
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Header Quick Metadata Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
            <div>
                <span class="block uppercase font-bold text-on-surface-variant opacity-60 text-[10px]">Teknisi Pelaksana</span>
                <span class="font-bold text-on-surface text-sm mt-0.5 flex items-center gap-1.5 truncate">
                    <span class="material-symbols-outlined text-[16px] text-primary">engineering</span>
                    {{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}
                </span>
            </div>
            <div>
                <span class="block uppercase font-bold text-on-surface-variant opacity-60 text-[10px]">Target Selesai</span>
                <span class="font-bold text-on-surface font-mono text-sm mt-0.5 block truncate">
                    {{ $plan->target_completion ? $plan->target_completion->format('d M Y H:i') : '-' }}
                </span>
            </div>
            <div>
                <span class="block uppercase font-bold text-on-surface-variant opacity-60 text-[10px]">Realisasi Selesai</span>
                <span class="font-bold {{ $isPlanDelayed ? 'text-rose-600' : 'text-on-surface' }} font-mono text-sm mt-0.5 block truncate">
                    {{ $plan->actual_completion ? $plan->actual_completion->format('d M Y H:i') : ($plan->execution?->completed_at ? $plan->execution->completed_at->format('d M Y H:i') : '-') }}
                </span>
            </div>
            <div>
                <span class="block uppercase font-bold text-on-surface-variant opacity-60 text-[10px]">
                    {{ $plan->isCorrective() ? 'Durasi Downtime' : 'Estimasi Durasi' }}
                </span>
                <span class="font-bold text-on-surface font-mono text-sm mt-0.5 block truncate">
                    @if($plan->isCorrective())
                        {{ $plan->downtime_duration ? $plan->downtime_duration . ' Menit' : '-' }}
                    @else
                        {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->estimated_duration : 120 }} Menit
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- 2. COMPACT READINESS (KESIAPAN) BAR --}}
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-primary">checklist_rtl</span>
                Kesiapan Pemeliharaan (Readiness Audit)
            </h2>
            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $report['overall_status'] === 'Ready' || $report['overall_status'] === 'Completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : ($report['overall_status'] === 'Blocked' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300') }}">
                {{ $statusTitle }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <!-- Item 1: Machine -->
            <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/60 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">1. Kondisi Mesin</span>
                    <span class="font-bold text-on-surface text-xs mt-0.5 block truncate">
                        {{ $report['machine_status_text'] }}
                    </span>
                </div>
                <span class="material-symbols-outlined text-[20px] {{ $report['machine_ready'] ? 'text-emerald-600' : 'text-rose-600' }}" style="font-variation-settings: 'FILL' 1;">
                    {{ $report['machine_ready'] ? 'check_circle' : 'cancel' }}
                </span>
            </div>

            <!-- Item 2: Technician -->
            <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/60 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">2. Teknisi Pelaksana</span>
                    <span class="font-bold text-on-surface text-xs mt-0.5 block truncate">
                        {{ $report['technician_assigned'] ? ($plan->assigned_technician ?? 'Ditugaskan') : 'Belum Ditugaskan' }}
                    </span>
                </div>
                <span class="material-symbols-outlined text-[20px] {{ $report['technician_assigned'] ? 'text-emerald-600' : 'text-amber-500' }}" style="font-variation-settings: 'FILL' 1;">
                    {{ $report['technician_assigned'] ? 'check_circle' : 'pending' }}
                </span>
            </div>

            <!-- Item 3: Manual / Documents -->
            <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/60 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">3. Dokumen &amp; Manual</span>
                    <span class="font-bold text-on-surface text-xs mt-0.5 block truncate" title="{{ $manualDocName }}">
                        {{ $hasManualDoc ? ($manualBookDoc ? 'Tersedia (' . Str::limit($manualBookDoc->file_name, 15) . ')' : 'Tersedia') : 'Tidak Ada' }}
                    </span>
                </div>
                <span class="material-symbols-outlined text-[20px] {{ $hasManualDoc ? 'text-emerald-600' : 'text-amber-500' }}" style="font-variation-settings: 'FILL' 1;">
                    {{ $hasManualDoc ? 'check_circle' : 'pending' }}
                </span>
            </div>

            <!-- Item 4: Spareparts -->
            <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/60 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">4. Stok Suku Cadang</span>
                    <span class="font-bold text-on-surface text-xs mt-0.5 block truncate">
                        {{ $report['sparepart_readiness_ready'] ? 'Stok Siap' : 'Perlu Perhatian' }}
                    </span>
                </div>
                <span class="material-symbols-outlined text-[20px] {{ $report['sparepart_readiness_ready'] ? 'text-emerald-600' : 'text-amber-500' }}" style="font-variation-settings: 'FILL' 1;">
                    {{ $report['sparepart_readiness_ready'] ? 'check_circle' : 'warning' }}
                </span>
            </div>
        </div>

        {{-- Blockers & Warnings inline alerts if present --}}
        @if(count($blockers) > 0 || count($warnings) > 0)
            <div class="mt-3 pt-3 border-t border-outline-variant/40 space-y-2 text-xs">
                @if(count($blockers) > 0)
                    <div class="p-2.5 rounded bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-2">
                        <span class="material-symbols-outlined text-rose-600 text-[18px] shrink-0 mt-0.5">block</span>
                        <div>
                            <strong class="font-bold uppercase text-[10px] text-rose-800 block">Hambatan Kritis (Blockers):</strong>
                            <ul class="list-disc pl-4 space-y-0.5 text-rose-950 font-medium">
                                @foreach($blockers as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(count($warnings) > 0)
                    <div class="p-2.5 rounded bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-[18px] shrink-0 mt-0.5">warning</span>
                        <div>
                            <strong class="font-bold uppercase text-[10px] text-amber-800 block">Peringatan Kesiapan (Warnings):</strong>
                            <ul class="list-disc pl-4 space-y-0.5 text-amber-950 font-medium">
                                @foreach($warnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ADJUST TARGET FORM (For Active/In-Progress Plans) --}}
    @if($plan->status !== 'completed' && $plan->status !== 'cancelled')
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-primary">edit_calendar</span>
                Sesuaikan Target Selesai &amp; Penugasan Teknisi
            </h3>
            <form action="{{ route('planning.update', $plan->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="target_completion" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Target Waktu Penyelesaian</label>
                        <input type="datetime-local" name="target_completion" id="target_completion" 
                               value="{{ $plan->target_completion ? $plan->target_completion->format('Y-m-d\TH:i') : '' }}"
                               class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-xs font-mono font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary" />
                    </div>

                    <div>
                        <label for="assigned_technician" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Teknisi Pelaksana</label>
                        <x-employee-autocomplete 
                            name="assigned_technician" 
                            id="assigned_technician" 
                            selected="{{ old('assigned_technician', $plan->assigned_technician) }}"
                            required="false"
                            placeholder="Cari nama teknisi..."
                        />
                    </div>

                    <div>
                        <label for="priority" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Prioritas</label>
                        <select name="priority" id="priority" class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-xs font-bold text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="low" {{ $plan->priority === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                            <option value="medium" {{ $plan->priority === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                            <option value="high" {{ $plan->priority === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                            <option value="critical" {{ $plan->priority === 'critical' ? 'selected' : '' }}>Kritis (Critical)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan atau instruksi khusus untuk teknisi..."
                              class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary italic">{{ $plan->notes }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    @if($plan->canBeCancelled())
                        <button type="button" onclick="openCancellationModal()" class="bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 px-4 py-2 rounded-lg text-xs font-bold transition-colors">
                            Batalkan Rencana
                        </button>
                    @endif
                    <button type="submit" class="bg-primary hover:bg-primary/95 text-on-primary px-5 py-2 rounded-lg text-xs font-bold transition-colors shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- TWO COLUMN MAIN DETAIL CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        <!-- Left Column: Problem & Repair Action Details (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            {{-- 3. KERUSAKAN (Problem Section - for Corrective) or CHECKLIST (for PM) --}}
            @if($plan->isCorrective())
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-rose-600">report_problem</span>
                        Detail Kerusakan Mesin (Problem Description)
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Deskripsi Masalah / Keluhan</span>
                            <p class="text-sm font-bold text-on-surface mt-1 leading-relaxed bg-surface-container-low p-3 rounded-lg border border-outline-variant/40">
                                {{ $plan->notes ?? 'Tidak ada deskripsi rinci.' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Dilaporkan Oleh</span>
                                <span class="font-bold text-on-surface mt-0.5 block">
                                    {{ $plan->reported_by ?? 'Operator Lapangan' }} 
                                    @if($plan->reported_department)
                                        <span class="text-on-surface-variant font-medium">({{ $plan->reported_department }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Waktu Lapor</span>
                                <span class="font-bold text-on-surface mt-0.5 block font-mono">
                                    {{ $plan->reported_at ? $plan->reported_at->format('d M Y H:i') : ($plan->created_at ? $plan->created_at->format('d M Y H:i') : '-') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- PM Checklist Tasks --}}
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3 border-b border-outline-variant/60 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-primary">task_alt</span>
                            Daftar Checklist Tindakan Standar
                        </h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary">
                            {{ $plan->maintenanceTemplate ? $plan->maintenanceTemplate->checklists->count() : 0 }} Tugas
                        </span>
                    </div>

                    <div class="space-y-2">
                        @if($plan->maintenanceTemplate && $plan->maintenanceTemplate->checklists->count() > 0)
                            @foreach($plan->maintenanceTemplate->checklists as $chk)
                                <div class="p-3 bg-surface-container-low border border-outline-variant/50 rounded-lg flex items-start gap-3">
                                    <span class="material-symbols-outlined text-primary text-[18px] mt-0.5 shrink-0">check_circle</span>
                                    <div class="flex-1 text-xs">
                                        <div class="flex items-baseline justify-between gap-2">
                                            <h4 class="font-bold text-on-surface">{{ $chk->title }}</h4>
                                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded {{ $chk->is_required ? 'bg-rose-100 text-rose-800' : 'bg-surface-container text-on-surface-variant' }}">
                                                {{ $chk->is_required ? 'Wajib' : 'Opsional' }}
                                            </span>
                                        </div>
                                        @if($chk->description)
                                            <p class="text-on-surface-variant mt-1">{{ $chk->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-xs text-on-surface-variant italic py-3 text-center">Rencana ini tidak menggunakan paket SOP / checklist standar.</p>
                        @endif
                    </div>
                </div>

                {{-- PM Template Required Spareparts (Realtime WMS check) --}}
                @if(!empty($report['sparepart_details']) && count($report['sparepart_details']) > 0)
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-3 border-b border-outline-variant/60 pb-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-primary">inventory</span>
                                Kebutuhan Suku Cadang Paket PM (WMS)
                            </h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $report['spareparts_available'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $report['spareparts_available'] ? 'Lengkap' : 'Ada Kurang' }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            @foreach($report['sparepart_details'] as $sp)
                                <div class="p-3 bg-surface-container-low border border-outline-variant/50 rounded-lg text-xs">
                                    <div class="flex items-baseline justify-between gap-2 mb-1">
                                        <h4 class="font-bold text-on-surface">{{ $sp['name'] }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sp['is_sufficient'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $sp['is_sufficient'] ? 'Cukup' : 'Stok Kurang' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-on-surface-variant pt-1 border-t border-outline-variant/30">
                                        <span class="font-mono text-primary font-bold">{{ $sp['code'] }}</span>
                                        <span>Dibutuhkan: <strong>{{ $sp['required'] }}</strong></span>
                                        <span>Stok WMS: <strong class="{{ $sp['is_sufficient'] ? 'text-on-surface' : 'text-rose-600' }}">{{ $sp['available'] }}</strong></span>
                                        <span>Rak: <strong>{{ $sp['location'] }}</strong></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            {{-- 4. PERBAIKAN (Maintenance Action Performed) --}}
            @if($plan->execution)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">build_circle</span>
                        Tindakan Perbaikan yang Dilakukan (Maintenance Action)
                    </h3>

                    @if($reportData)
                        <div class="space-y-3 text-xs">
                            <div class="border-b border-outline-variant/40 pb-2">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-primary">Laporan Penyelesaian Perbaikan</h4>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Verified By (Verifikator)</span>
                                    <span class="font-bold text-on-surface mt-0.5 block">
                                        {{ $reportData['verified_by'] ?? '-' }}
                                    </span>
                                </div>
                                <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Repair Performed By (Tim Perbaikan)</span>
                                    <span class="font-bold text-on-surface mt-0.5 block">
                                        {{ !empty($reportData['team']) ? implode(', ', $reportData['team']) : ($plan->assigned_technician ?? '-') }}
                                    </span>
                                </div>
                                <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Tipe Perbaikan</span>
                                    <span class="font-bold text-on-surface mt-0.5 block">
                                        {{ ($reportData['repair_type'] ?? '') === 'Temporary' ? 'Sementara (Temporary)' : 'Permanen' }}
                                    </span>
                                </div>
                                <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Status Operasional Hasil</span>
                                    <span class="font-bold text-on-surface mt-0.5 block">
                                        {{ ucfirst($reportData['actual_status'] ?? 'running') }}
                                    </span>
                                </div>
                            </div>

                            @if(!empty($reportData['action_performed']))
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Tindakan Lapangan</span>
                                    <p class="text-sm font-bold text-on-surface mt-1 leading-relaxed bg-surface-container-low p-3 rounded-lg border border-outline-variant/40">
                                        {{ $reportData['action_performed'] }}
                                    </p>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Masalah Tersisa (Remaining Issues)</span>
                                    <p class="font-semibold text-on-surface mt-0.5">
                                        {{ !empty($reportData['remaining_issues']) ? $reportData['remaining_issues'] : 'Tidak ada.' }}
                                    </p>
                                </div>
                                <div class="p-3 rounded-lg border bg-surface-container-low border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Tindakan Lanjutan (Follow-up Action)</span>
                                    <p class="font-semibold text-on-surface mt-0.5">
                                        {{ !empty($reportData['follow_up']) ? $reportData['follow_up'] : 'Tidak ada.' }}
                                    </p>
                                </div>
                            </div>

                            @if(!empty($reportData['user_notes']))
                                <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] uppercase font-bold text-on-surface-variant opacity-60 block">Catatan Verifikator</span>
                                    <p class="italic text-on-surface mt-0.5 font-medium">"{{ $reportData['user_notes'] }}"</p>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($plan->execution->notes)
                            <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/40 text-xs">
                                <span class="block text-xs uppercase font-bold text-on-surface-variant opacity-60 mb-1">Catatan Tambahan &amp; Tindakan Korektif</span>
                                <p class="italic text-on-surface leading-relaxed">"{{ $plan->execution->notes }}"</p>
                            </div>
                        @else
                            <p class="text-xs text-on-surface-variant italic">Tindakan perbaikan telah diselesaikan sesuai instruksi kerja standar.</p>
                        @endif
                    @endif
                </div>
            @endif

            {{-- 5. PHOTO EVIDENCE --}}
            @php
                $beforePhoto = $plan->execution?->photos?->firstWhere('type', 'before');
                $afterPhoto = $plan->execution?->photos?->firstWhere('type', 'after');
                $generalPhoto = $plan->execution?->photos?->firstWhere('type', 'general');
                $hasAnyPhoto = ($beforePhoto && Storage::disk('public')->exists($beforePhoto->photo_path)) ||
                               ($afterPhoto && Storage::disk('public')->exists($afterPhoto->photo_path)) ||
                               ($generalPhoto && Storage::disk('public')->exists($generalPhoto->photo_path));
            @endphp

            @if($plan->execution && $hasAnyPhoto)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">photo_camera</span>
                        Bukti Foto Lapangan (Photo Evidence)
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Before Photo -->
                        <div class="border border-outline-variant/60 rounded-lg p-3 bg-surface-container-low flex flex-col items-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant opacity-70 mb-2">Sebelum Perbaikan (Before)</span>
                            @if($beforePhoto && Storage::disk('public')->exists($beforePhoto->photo_path))
                                <a href="{{ asset('storage/' . $beforePhoto->photo_path) }}" target="_blank" class="block">
                                    <img src="{{ asset('storage/' . $beforePhoto->photo_path) }}" alt="Foto Sebelum Perbaikan" class="max-h-48 rounded object-contain shadow-sm hover:opacity-95 transition-opacity" />
                                </a>
                            @else
                                <div class="py-8 text-center text-on-surface-variant opacity-40">
                                    <span class="material-symbols-outlined text-[32px]">no_photography</span>
                                    <p class="text-[10px] uppercase font-bold mt-1">Tidak Ada Foto Before</p>
                                </div>
                            @endif
                        </div>

                        <!-- After Photo / General Photo -->
                        <div class="border border-outline-variant/60 rounded-lg p-3 bg-surface-container-low flex flex-col items-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant opacity-70 mb-2">Setelah Perbaikan (After)</span>
                            @php
                                $displayAfter = $afterPhoto && Storage::disk('public')->exists($afterPhoto->photo_path) ? $afterPhoto : ($generalPhoto && Storage::disk('public')->exists($generalPhoto->photo_path) ? $generalPhoto : null);
                            @endphp
                            @if($displayAfter)
                                <a href="{{ asset('storage/' . $displayAfter->photo_path) }}" target="_blank" class="block">
                                    <img src="{{ asset('storage/' . $displayAfter->photo_path) }}" alt="Foto Setelah Perbaikan" class="max-h-48 rounded object-contain shadow-sm hover:opacity-95 transition-opacity" />
                                </a>
                            @else
                                <div class="py-8 text-center text-on-surface-variant opacity-40">
                                    <span class="material-symbols-outlined text-[32px]">no_photography</span>
                                    <p class="text-[10px] uppercase font-bold mt-1">Tidak Ada Foto After</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- 6. CHECKLIST ANSWERS & SCORES (For PM Executions) --}}
            @if(!$plan->isCorrective() && $plan->execution && $plan->execution->answers->count() > 0)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">fact_check</span>
                        Hasil Evaluasi Checklist Lapangan
                    </h3>

                    <div class="space-y-2">
                        @foreach($plan->execution->answers as $ans)
                            <div class="p-3 bg-surface-container-low border border-outline-variant/50 rounded-lg flex items-center justify-between gap-3 text-xs">
                                <div class="flex-1">
                                    <h4 class="font-bold text-on-surface leading-tight">{{ $ans->checklistItem->title }}</h4>
                                    @if($ans->remarks)
                                        <p class="text-[11px] text-rose-600 mt-1 italic font-medium">Catatan: "{{ $ans->remarks }}"</p>
                                    @endif
                                </div>
                                <span class="px-2.5 py-1 rounded text-xs font-mono font-black text-white shrink-0 {{ $ans->score == 5 ? 'bg-emerald-600' : ($ans->score >= 3 ? 'bg-amber-500' : 'bg-rose-600') }}">
                                    Skor: {{ $ans->score }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Results, Delay, Spareparts & Secondary Metadata (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            {{-- 7. HASIL PERBAIKAN (Execution Result Summary) --}}
            @if($plan->status === 'completed' && $plan->execution)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
                        Hasil Perbaikan (Result)
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="text-on-surface-variant font-medium">Status Pekerjaan</span>
                            <span class="font-bold uppercase text-emerald-700 dark:text-emerald-300">COMPLETED</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="text-on-surface-variant font-medium">Condition Score</span>
                            <span class="font-bold font-mono text-sm {{ $plan->execution->overall_score >= 4.0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ number_format($plan->execution->overall_score, 2) }} / 5.00
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="text-on-surface-variant font-medium">Actual Downtime</span>
                            <span class="font-bold font-mono text-on-surface">
                                {{ $plan->downtime_duration ? $plan->downtime_duration . ' Menit' : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="text-on-surface-variant font-medium">Diverifikasi Oleh</span>
                            <span class="font-bold text-on-surface truncate max-w-[150px]">
                                {{ $reportData['verified_by'] ?? ($plan->execution->operator_name ?? 'Supervisor / Kabag') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="text-on-surface-variant font-medium">Waktu Selesai</span>
                            <span class="font-bold font-mono text-on-surface text-[11px]">
                                {{ $plan->execution->completed_at ? $plan->execution->completed_at->format('d M Y H:i') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 8. ANALISIS KETERLAMBATAN (Delay Analysis) --}}
            @if($plan->status === 'completed')
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] {{ $isPlanDelayed ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $isPlanDelayed ? 'schedule' : 'check_circle' }}
                        </span>
                        Analisis Waktu &amp; Delay
                    </h3>

                    @if($isPlanDelayed)
                        <div class="space-y-2.5 text-xs">
                            <div class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-950">
                                <span class="text-[10px] font-bold uppercase text-rose-700 block">Durasi Keterlambatan</span>
                                <span class="font-bold font-mono text-sm text-rose-700 block mt-0.5">
                                    {{ $delayMinutes }} Menit <span class="text-xs font-normal">({{ $delayFormatted }})</span>
                                </span>
                            </div>
                            <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">Alasan Keterlambatan</span>
                                <span class="font-bold text-on-surface mt-0.5 block">{{ $displayDelayReason }}</span>
                            </div>
                            @if($plan->delay_notes)
                                <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40">
                                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block">Catatan Delay</span>
                                    <p class="italic text-on-surface mt-0.5">"{{ $plan->delay_notes }}"</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 text-xs font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">task_alt</span>
                            Selesai Tepat Waktu (On Time)
                        </div>
                    @endif
                </div>
            @endif

            {{-- 9. CONSUMED SPAREPARTS (If Completed) --}}
            @if($plan->execution && $plan->execution->spareparts->count() > 0)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 flex items-center gap-1.5 border-b border-outline-variant/60 pb-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">inventory_2</span>
                        Suku Cadang Digunakan
                    </h3>

                    <div class="space-y-2 text-xs">
                        @foreach($plan->execution->spareparts as $esp)
                            <div class="p-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-primary block">{{ $esp->warehouse_item_code }}</span>
                                    <span class="text-[11px] text-on-surface-variant truncate block max-w-[150px]">{{ $esp->warehouse_item_name ?? 'Item WMS' }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface font-mono font-bold text-xs">
                                    {{ $esp->quantity }} pcs
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 10. SPAREPART READINESS / WMS MAPPED SPAREPARTS --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3 border-b border-outline-variant/60 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-primary">warehouse</span>
                        Stok Suku Cadang Terpetakan
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $report['sparepart_readiness_ready'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $report['sparepart_readiness_ready'] ? 'Siap' : 'Perhatian' }}
                    </span>
                </div>

                @if(!empty($report['mapped_spareparts']) && count($report['mapped_spareparts']) > 0)
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @foreach($report['mapped_spareparts'] as $part)
                            <div class="p-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg text-xs">
                                <div class="flex items-baseline justify-between gap-1 mb-1">
                                    <span class="font-bold text-on-surface truncate" title="{{ $part['name'] }}">{{ $part['name'] }}</span>
                                    <span class="font-mono font-bold text-primary text-[11px] shrink-0">{{ $part['code'] }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-on-surface-variant pt-1 border-t border-outline-variant/30">
                                    <span>Butuh: <strong>{{ $part['required'] }}</strong></span>
                                    <span>Stok: <strong class="{{ $part['available'] >= $part['required'] ? 'text-on-surface' : 'text-rose-600' }}">{{ $part['available'] }}</strong></span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $part['badge_class'] ?? '' }}">
                                        {{ $part['status'] ?? 'OK' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-on-surface-variant italic py-2 text-center">Tidak ada suku cadang terpetakan untuk mesin ini.</p>
                @endif
            </div>

            {{-- 11. CATATAN PERENCANA (Compact) --}}
            @if($plan->notes && !$plan->isCorrective())
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-sm">
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant opacity-60 block mb-1">Catatan Perencana</span>
                    <p class="text-xs text-on-surface italic leading-relaxed">
                        "{{ $plan->notes }}"
                    </p>
                </div>
            @endif

        </div>
    </div>

    {{-- BOTTOM ACTION FOOTER --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-sm mb-12">
        <a href="{{ route('planning.index') }}" class="bg-surface-container border border-outline-variant hover:bg-surface-container-high text-on-surface px-5 py-2.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Papan Perencanaan
        </a>

        <div class="flex flex-wrap items-center gap-2">
            @if($plan->status !== 'cancelled')
                @if($plan->status === 'completed')
                    <a href="{{ route('planning.report', $plan) }}" target="_blank" class="bg-primary hover:bg-primary/95 text-on-primary px-5 py-2.5 rounded-lg text-xs font-bold transition-colors shadow flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                        Print Hasil Kerja (PDF)
                    </a>
                @else
                    <a href="{{ route('planning.print', $plan) }}" target="_blank" class="bg-surface-container border border-outline-variant hover:bg-surface-container-high text-on-surface px-5 py-2.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                        Cetak Perintah Kerja
                    </a>
                    <a href="{{ route('planning.execute', $plan->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-colors shadow flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                        Eksekusi PM (Simulasi QR)
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- CANCELLATION MODAL & AUTOCOMPLETE SCRIPT --}}
    @if($plan->canBeCancelled())
        <div id="cancellation-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeCancellationModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-middle bg-surface-container-lowest border border-outline-variant rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('planning.cancel', $plan->id) }}" method="POST" id="cancellation-form">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3 text-rose-600">
                                <span class="material-symbols-outlined text-[28px]">warning</span>
                                <h3 class="text-base font-bold text-on-surface" id="modal-title">Konfirmasi Pembatalan Perawatan</h3>
                            </div>
                            
                            <p class="text-xs text-on-surface-variant mb-4 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan rencana perawatan ini? Tindakan ini tidak dapat dibatalkan.
                            </p>

                            <div class="space-y-4 text-xs">
                                <div>
                                    <label for="cancellation_reason" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Alasan Pembatalan <span class="text-rose-600">*</span></label>
                                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" required
                                              placeholder="Masukkan alasan pembatalan secara detail..."
                                              class="w-full p-2.5 bg-surface-container border border-outline-variant rounded-lg text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                                </div>

                                <div class="relative">
                                    <label for="replacement_search" class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Laporan Pengganti (Opsional)</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant opacity-60 text-[18px]">search</span>
                                        <input type="text" id="replacement_search" placeholder="Cari nomor BD/PM pengganti..." autocomplete="off"
                                               class="w-full pl-9 pr-4 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary focus:outline-none"/>
                                    </div>
                                    <input type="hidden" name="replacement_id" id="replacement_id"/>
                                    
                                    <div id="replacement-results" class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto bg-surface-container border border-outline-variant rounded-lg shadow-lg hidden">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-3 bg-surface-container border-t border-outline-variant flex justify-end gap-2">
                            <button type="button" onclick="closeCancellationModal()" class="px-4 py-2 bg-surface-container border border-outline-variant hover:bg-surface-container-high rounded-lg text-xs font-bold text-on-surface transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 rounded-lg text-xs font-bold text-white transition-colors shadow">
                                Ya, Batalkan Rencana
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
                const planType = "{{ $plan->type->value }}";

                if (!searchInput || !resultsContainer) return;

                let timeoutId = null;

                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    hiddenInput.value = '';
                    
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
                                    option.className = 'p-2.5 hover:bg-primary/10 cursor-pointer text-xs font-medium border-b border-outline-variant/40 last:border-0';
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

                document.addEventListener('click', function(e) {
                    if (e.target !== searchInput && e.target !== resultsContainer && !resultsContainer.contains(e.target)) {
                        resultsContainer.classList.add('hidden');
                    }
                });
            })();
        </script>
    @endif
</x-layouts.app>
