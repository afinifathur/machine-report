<x-layouts.app 
    title="Morning Briefing | Sistem MRM"
    topbar-title="Morning Briefing"
    :subnav="['Ikhtisar' => route('dashboard'), 'Riwayat Medis' => '#', 'Sparepart' => '#', 'Dokumen' => '#']"
    active-subnav="Ikhtisar"
>
    <!-- Section 1: Morning Briefing Greeting Header -->
    <header class="mb-8 p-6 bg-gradient-to-r from-primary to-primary-container text-on-primary rounded-2xl shadow-sm border border-outline-variant relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="z-10">
            <span class="font-label-md text-label-md opacity-80 uppercase tracking-wider">Morning Briefing Control Room</span>
            <h1 class="font-headline-md text-headline-md mt-1 font-bold">{{ $greetingText }}</h1>
            <p class="font-body-md text-body-md mt-2 opacity-90">Gunakan panel ini untuk meninjau penugasan, menyelesaikan hambatan, dan memprioritaskan inspeksi hari ini.</p>
        </div>
        <div class="flex gap-3 z-10">
            <a href="{{ route('planning.index') }}" class="px-5 py-2.5 bg-surface-container-lowest text-primary rounded-xl font-bold hover:bg-surface-bright transition-colors shadow-sm inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]" data-icon="dashboard">dashboard</span>
                Papan Perencanaan
            </a>
        </div>
        <!-- Decorative subtle background shapes -->
        <div class="absolute -right-16 -top-16 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    </header>

    <!-- Section 2: Agenda Hari Ini -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Not Started Card -->
        <a href="{{ route('planning.index', ['status' => 'approved']) }}" class="group block bg-surface-container-lowest border border-outline-variant p-5 rounded-xl flex flex-col justify-between hover:border-orange-500 hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="text-on-surface-variant font-label-md text-label-md">Belum Dimulai</span>
                    <span class="text-[32px] font-headline-lg text-on-surface font-extrabold mt-1">{{ $counts['not_started'] }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 group-hover:bg-orange-100 transition-colors">
                    <span class="material-symbols-outlined" data-icon="schedule">schedule</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-body-sm text-on-surface-variant font-medium group-hover:text-orange-600 transition-colors">
                <span>Lihat Perintah Kerja</span>
                <span class="material-symbols-outlined text-[16px] ml-1" data-icon="arrow_forward">arrow_forward</span>
            </div>
        </a>

        <!-- In Progress Card -->
        <a href="{{ route('planning.index', ['status' => 'in_progress']) }}" class="group block bg-surface-container-lowest border border-outline-variant p-5 rounded-xl flex flex-col justify-between hover:border-yellow-600 hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="text-on-surface-variant font-label-md text-label-md">Sedang Berjalan</span>
                    <span class="text-[32px] font-headline-lg text-yellow-600 font-extrabold mt-1">{{ $counts['in_progress'] }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-100 transition-colors">
                    <span class="material-symbols-outlined font-bold" data-icon="play_arrow">play_arrow</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-body-sm text-on-surface-variant font-medium group-hover:text-yellow-600 transition-colors">
                <span>Pantau Progress</span>
                <span class="material-symbols-outlined text-[16px] ml-1" data-icon="arrow_forward">arrow_forward</span>
            </div>
        </a>

        <!-- Waiting Review Card -->
        <a href="{{ route('planning.index', ['readiness_status' => 'Waiting Review']) }}" class="group block bg-surface-container-lowest border border-outline-variant p-5 rounded-xl flex flex-col justify-between hover:border-blue-600 hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="text-on-surface-variant font-label-md text-label-md">Menunggu Review</span>
                    <span class="text-[32px] font-headline-lg text-blue-600 font-extrabold mt-1">{{ $counts['waiting_review'] }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
                    <span class="material-symbols-outlined" data-icon="rate_review">rate_review</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-body-sm text-on-surface-variant font-medium group-hover:text-blue-600 transition-colors">
                <span>Tinjau & Validasi</span>
                <span class="material-symbols-outlined text-[16px] ml-1" data-icon="arrow_forward">arrow_forward</span>
            </div>
        </a>

        <!-- Completed Card -->
        <a href="{{ route('planning.index', ['status' => 'completed']) }}" class="group block bg-surface-container-lowest border border-outline-variant p-5 rounded-xl flex flex-col justify-between hover:border-green-600 hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="text-on-surface-variant font-label-md text-label-md">Selesai Hari Ini</span>
                    <span class="text-[32px] font-headline-lg text-green-600 font-extrabold mt-1">{{ $counts['completed'] }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
                    <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-body-sm text-on-surface-variant font-medium group-hover:text-green-600 transition-colors">
                <span>Lihat Laporan Selesai</span>
                <span class="material-symbols-outlined text-[16px] ml-1" data-icon="arrow_forward">arrow_forward</span>
            </div>
        </a>
    </section>

    <!-- Bento Grid Content -->
    <div class="grid grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Operations and Activities (8 Cols) -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            
            <!-- Section 3: Hambatan Hari Ini -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-headline-sm text-headline-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-error" data-icon="warning">warning</span>
                        Hambatan Hari Ini
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-label-sm font-bold {{ count($blockers) > 0 ? 'bg-error-container text-error' : 'bg-green-50 text-green-700' }}">
                        {{ count($blockers) }} Hambatan
                    </span>
                </div>

                @if(count($blockers) > 0)
                    <div class="space-y-3">
                        @foreach($blockers as $blocker)
                            @php
                                $isCritical = $blocker['severity'] === 'critical';
                                $cardBorder = $isCritical ? 'border-l-4 border-l-error bg-error-container/10' : 'border-l-4 border-l-orange-500 bg-orange-500/5';
                                $badgeBg = $isCritical ? 'bg-error text-on-error' : 'bg-orange-500 text-white';
                            @endphp
                            <div class="p-4 border border-outline-variant rounded-lg flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all hover:shadow-sm {{ $cardBorder }}">
                                <div class="flex items-start gap-3">
                                    <span class="font-label-sm text-label-sm px-2 py-0.5 rounded font-bold uppercase {{ $badgeBg }}">
                                        {{ $blocker['type'] }}
                                    </span>
                                    <div class="flex flex-col">
                                        <p class="text-body-md font-semibold text-on-surface">{{ $blocker['reason'] }}</p>
                                        <span class="text-body-sm text-on-surface-variant mt-0.5">
                                            Mesin: <a href="{{ route('machines.show', $blocker['machine_code']) }}" class="text-primary font-bold hover:underline">{{ $blocker['machine_code'] }} ({{ $blocker['machine_name'] }})</a>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center self-end md:self-auto">
                                    <a href="{{ $blocker['action_url'] }}" class="px-4 py-1.5 text-label-md font-bold rounded-lg border border-outline hover:bg-surface-container-low transition-colors inline-flex items-center gap-1.5 text-primary">
                                        {{ $blocker['action_label'] }}
                                        <span class="material-symbols-outlined text-[16px]" data-icon="open_in_new">open_in_new</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        title="Tidak Ada Hambatan" 
                        description="Semua rencana perawatan hari ini siap dieksekusi. Tidak ada kendala mesin, spare part, teknisi, atau dokumen." 
                        icon="check_circle"
                    />
                @endif
            </div>

            <!-- Section 4: Mesin Prioritas -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-headline-sm text-headline-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" data-icon="priority_high">priority_high</span>
                        Prioritas Inspeksi & Pemantauan
                    </h3>
                    <span class="text-body-sm text-on-surface-variant">Diurutkan berdasarkan tingkat risiko operasional</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-bright border-b border-outline-variant">
                                <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Mesin</th>
                                <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kondisi</th>
                                <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Alasan Prioritas</th>
                                <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse($priorityMachines as $machine)
                                <tr class="hover:bg-surface-container-low transition-colors group">
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="mono text-body-sm font-semibold text-primary">{{ $machine->code }}</span>
                                            <span class="text-body-sm text-on-surface-variant mt-0.5">{{ $machine->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <x-health-score :score="$machine->health_score" type="bar" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($machine->priority_reasons as $reason)
                                                @php
                                                    $reasonColor = match($reason) {
                                                        'Kerusakan Aktif' => 'bg-error-container text-error border-error/20',
                                                        'PM Terblokir' => 'bg-orange-50 text-orange-700 border-orange-500/20',
                                                        'PM Terlambat' => 'bg-yellow-50 text-yellow-800 border-yellow-500/20',
                                                        'Inspeksi Rendah' => 'bg-error-container/50 text-error border-error/10',
                                                        default => 'bg-surface-container text-on-surface-variant border-outline-variant/35'
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-0.5 text-label-sm font-bold rounded-full border {{ $reasonColor }}">
                                                    {{ $reason }}
                                                </span>
                                            @endforeach
                                            @if(empty($machine->priority_reasons))
                                                <span class="px-2.5 py-0.5 text-label-sm font-bold rounded-full border bg-green-50 text-green-700 border-green-500/20">
                                                    Normal
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('machines.show', $machine->code) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-container text-primary text-label-md font-bold group-hover:bg-primary group-hover:text-on-primary transition-all">
                                            Buka Paspor
                                            <span class="material-symbols-outlined text-[16px]" data-icon="arrow_forward">arrow_forward</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-on-surface-variant italic">Tidak ada mesin terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 5: Aktivitas Hari Ini -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-headline-sm text-headline-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" data-icon="timeline">timeline</span>
                        Aktivitas Hari Ini
                    </h3>
                    <span class="text-body-sm text-on-surface-variant">Pembaruan aktivitas lapangan waktu nyata</span>
                </div>

                @if(count($timelineEvents) > 0)
                    <div class="relative pl-6 border-l border-outline-variant space-y-6 ml-3 py-2">
                        @foreach($timelineEvents as $event)
                            @php
                                $bulletColor = match($event['color']) {
                                    'red' => 'bg-error ring-error-container',
                                    'orange' => 'bg-orange-500 ring-orange-100',
                                    'yellow' => 'bg-yellow-500 ring-yellow-100',
                                    'amber' => 'bg-amber-500 ring-amber-100',
                                    'blue' => 'bg-blue-600 ring-blue-100',
                                    'green' => 'bg-green-600 ring-green-100',
                                    default => 'bg-outline ring-surface-container'
                                };
                            @endphp
                            <div class="relative">
                                <!-- Bullet point indicator -->
                                <span class="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full ring-4 {{ $bulletColor }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                </span>
                                
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-1">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="mono text-label-md font-bold text-on-surface-variant">{{ $event['time'] }}</span>
                                            <h4 class="text-body-md font-semibold text-on-surface">{{ $event['title'] }}</h4>
                                        </div>
                                        <p class="text-body-sm text-on-surface-variant mt-0.5">{{ $event['details'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        title="Belum Ada Aktivitas" 
                        description="Belum ada aktivitas terekam hari ini. Timeline akan diperbarui secara real-time saat teknisi memulai, menyelesaikan, atau melaporkan hambatan pekerjaan." 
                        icon="history"
                    />
                @endif
            </div>

            <!-- Section 7: Rekomendasi Tindakan -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-headline-sm text-headline-sm flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary" data-icon="assistant_navigation">assistant_navigation</span>
                    Rekomendasi Tindakan
                </h3>
                
                @if(count($recommendations) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($recommendations as $rec)
                            @php
                                $recColor = match($rec['color']) {
                                    'red' => 'border-l-4 border-l-error bg-error-container/5',
                                    'orange' => 'border-l-4 border-l-orange-500 bg-orange-500/5',
                                    'blue' => 'border-l-4 border-l-blue-600 bg-blue-600/5',
                                    'green' => 'border-l-4 border-l-green-600 bg-green-600/5',
                                    default => 'border-l-4 border-l-primary bg-primary/5'
                                };
                            @endphp
                            <div class="p-4 border border-outline-variant rounded-lg flex flex-col justify-between {{ $recColor }}">
                                <div>
                                    <div class="flex items-center gap-2 text-on-surface font-semibold">
                                        <span class="material-symbols-outlined text-primary" data-icon="{{ $rec['icon'] }}">{{ $rec['icon'] }}</span>
                                        <h4 class="text-body-md font-bold">{{ $rec['title'] }}</h4>
                                    </div>
                                    <p class="text-body-sm text-on-surface-variant mt-2">{{ $rec['description'] }}</p>
                                </div>
                                <div class="mt-4 text-right">
                                    <a href="{{ $rec['action_url'] }}" class="inline-flex items-center gap-1 text-label-md font-bold text-primary hover:underline">
                                        {{ $rec['action_label'] }}
                                        <span class="material-symbols-outlined text-[16px]" data-icon="arrow_forward">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 border border-green-500/20 bg-green-50 rounded-lg text-center text-green-700 font-medium">
                        Semua tugas operasional hari ini telah diselesaikan. Kerja bagus!
                    </div>
                @endif
            </div>

        </div> <!-- End Left Column -->

        <!-- Right Column: Operational Summary & Context (4 Cols) -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            
            <!-- Section 6: Ringkasan Operasional -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-headline-sm text-headline-sm mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" data-icon="insights">insights</span>
                    Ringkasan Operasional
                </h3>
                
                <div class="space-y-4">
                    <!-- Rata-rata Kondisi Mesin -->
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-xl flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-label-md text-on-surface-variant font-medium">Rerata Kondisi Mesin</span>
                            <span class="text-headline-md font-headline-md text-on-surface font-extrabold mt-1">{{ round($avgHealth) }}%</span>
                        </div>
                        <div class="w-12 h-12">
                            <svg class="w-full h-full -rotate-90" viewbox="0 0 36 36">
                                <circle cx="18" cy="18" fill="none" r="16" stroke="#e0e3e5" stroke-width="3"></circle>
                                <circle cx="18" cy="18" fill="none" r="16" stroke="{{ $avgHealth >= 80 ? '#22c55e' : ($avgHealth >= 60 ? '#f97316' : '#ba1a1a') }}" stroke-dasharray="{{ $avgHealth }}, 100" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                        </div>
                    </div>

                    <!-- Kepatuhan Perawatan (Compliance) -->
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-xl flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-label-md text-on-surface-variant font-medium">Kepatuhan Perawatan (PM)</span>
                            <span class="text-headline-md font-headline-md text-primary font-extrabold mt-1">{{ $complianceRate }}%</span>
                            <span class="text-label-sm text-on-surface-variant mt-1">Target Pabrik: 90%</span>
                        </div>
                        <div class="w-12 h-12">
                            <svg class="w-full h-full -rotate-90" viewbox="0 0 36 36">
                                <circle cx="18" cy="18" fill="none" r="16" stroke="#e0e3e5" stroke-width="3"></circle>
                                <circle cx="18" cy="18" fill="none" r="16" stroke="#1E40AF" stroke-dasharray="{{ $complianceRate }}, 100" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                        </div>
                    </div>

                    <!-- Downtime Bulanan -->
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-xl flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-label-md text-on-surface-variant font-medium">Downtime Bulan Ini</span>
                            <span class="text-headline-md font-headline-md text-on-surface font-extrabold mt-1">{{ number_format($simulatedDowntime, 1) }} Jam</span>
                            <span class="text-label-sm text-on-surface-variant mt-1">Rerata kerusakan mesin terdeteksi</span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant">
                            <span class="material-symbols-outlined" data-icon="timer">timer</span>
                        </div>
                    </div>

                    <!-- Sparepart Out of Stock Alert -->
                    <a href="{{ route('planning.index', ['readiness_status' => 'Blocked']) }}" class="block p-4 bg-surface-container-low border border-outline-variant rounded-xl flex items-center justify-between hover:border-error transition-colors">
                        <div class="flex flex-col">
                            <span class="text-label-md text-on-surface-variant font-medium">Peringatan Stok WMS</span>
                            <span class="text-headline-md font-headline-md {{ $outOfStockCount > 0 ? 'text-error font-extrabold' : 'text-green-600 font-bold' }} mt-1">{{ $outOfStockCount }} Item Kosong</span>
                            <span class="text-label-sm text-on-surface-variant mt-1">Butuh pemesanan ulang suku cadang</span>
                        </div>
                        <div class="w-10 h-10 rounded-full {{ $outOfStockCount > 0 ? 'bg-error-container text-error' : 'bg-green-50 text-green-600' }} flex items-center justify-center">
                            <span class="material-symbols-outlined" data-icon="inventory">inventory</span>
                        </div>
                    </a>
                </div>
            </div>

        </div> <!-- End Right Column -->

    </div>

    <!-- Scan-to-Service Footer Widget -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 bg-primary-container text-on-primary p-8 rounded-xl items-center">
        <div>
            <h2 class="font-headline-md text-headline-md mb-2">Scan-to-Service</h2>
            <p class="font-body-lg text-body-lg opacity-80 max-w-md">Gunakan aplikasi MRM Mobile untuk memindai QR Code mesin untuk mendapatkan diagnosis kondisi dan riwayat medis secara instan.</p>
            <div class="mt-6 flex gap-4">
                <button class="bg-surface-container-lowest text-primary px-6 py-2 rounded-lg font-bold">Unduh Aplikasi</button>
                <button class="border border-white border-opacity-30 text-white px-6 py-2 rounded-lg font-bold">Panduan Operator</button>
            </div>
        </div>
        <div class="flex justify-center md:justify-end">
            <div class="bg-white p-4 rounded-xl shadow-lg border-4 border-primary text-center">
                <img class="w-32 h-32 mx-auto" alt="A clean, minimalist high-contrast QR code centered on a white background with thin industrial blue border." src="{{ asset('images/qr-system-main-hub.png') }}"/>
                <p class="text-primary-fixed mt-2 mono font-bold text-label-sm">ID: SYSTEM-MAIN-HUB</p>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <a href="{{ route('planning.index') }}" class="w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-transform" title="Papan Perencanaan">
            <span class="material-symbols-outlined text-[28px]" data-icon="calendar_month">calendar_month</span>
        </a>
    </div>
</x-layouts.app>
