<x-layouts.app 
    title="Papan Perencanaan Perawatan | Sistem MRM"
    topbar-title="Perencanaan Perawatan"
>
    <x-breadcrumb :items="['Perencanaan' => '']" />

    @php
        // Dynamic Calendar Calculation
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;
        // ISO-8601 day of week: 1 (Monday) to 7 (Sunday)
        $startDayOfWeek = $startOfMonth->dayOfWeekIso; 

        // Indonesian Month names
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp

    <!-- Dashboard Header Info -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="font-headline-md text-headline-md text-on-surface">Papan Perencanaan PM</h1>
            <p class="text-body-md text-on-surface-variant">Kelola SOP (Paket Perawatan) dan jadwalkan inspeksi preventif berkala untuk menjamin keandalan manufaktur.</p>
        </div>
    </div>

    <!-- Top KPI Dashboard Grid -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-2.5 shadow-xs hover:shadow-sm transition-all">
            <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider truncate">Total Rencana</p>
                <h3 class="text-base font-black leading-tight">{{ $totalCount }}</h3>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-2.5 shadow-xs hover:shadow-sm transition-all border-l-4 border-l-green-500">
            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider truncate">Siap Eksekusi</p>
                <h3 class="text-base font-black leading-tight text-green-600">{{ $readyCount }}</h3>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-2.5 shadow-xs hover:shadow-sm transition-all border-l-4 border-l-orange-500">
            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">pending</span>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider truncate">Hampir Siap</p>
                <h3 class="text-base font-black leading-tight text-orange-600">{{ $almostReadyCount }}</h3>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-2.5 shadow-xs hover:shadow-sm transition-all border-l-4 border-l-error">
            <div class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center shrink-0 animate-pulse">
                <span class="material-symbols-outlined text-[18px]">block</span>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider truncate">Terblokir</p>
                <h3 class="text-base font-black leading-tight text-error">{{ $blockedCount }}</h3>
            </div>
        </div>
    </section>

    <!-- Priority Alerts Area -->
    @php
        $todayBlocked = $todayPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Blocked');
    @endphp
    @if($todayBlocked->count() > 0)
        <div class="mb-8 p-4 bg-error-container text-on-error-container rounded-xl border border-error flex items-start gap-4 shadow-sm">
            <span class="material-symbols-outlined text-[28px] mt-0.5" style="font-variation-settings: 'FILL' 1;">emergency</span>
            <div class="flex-1">
                <h4 class="font-headline-sm text-headline-sm text-error font-bold mb-1">Perhatian: Ada Rencana Terjadwal Hari Ini yang Terblokir!</h4>
                <p class="text-body-md mb-2">Sebanyak {{ $todayBlocked->count() }} rencana pemeliharaan preventif yang dijadwalkan hari ini terhambat oleh masalah ketersediaan suku cadang atau kondisi mesin.</p>
                <div class="space-y-1.5 mt-2 bg-white/20 p-3 rounded-lg">
                    @foreach($todayBlocked as $plan)
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-body-sm">
                            <span class="font-semibold">{{ $plan->machine->code }} — {{ $plan->maintenanceTemplate->name }}</span>
                            <span class="text-xs bg-error text-white font-bold px-2 py-0.5 rounded uppercase mt-1 sm:mt-0">
                                @if(in_array($plan->machine->operational_status, ['breakdown', 'maintenance']))
                                    Mesin Down
                                @else
                                    Stok WMS Kosong
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filtering Panel & Board Toggles -->
    <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm mb-8">
        <form method="GET" action="{{ route('planning.index') }}" class="flex flex-col gap-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-3 relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant">search</span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari mesin, catatan..." 
                        class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-4 py-2 text-body-md focus:outline-none focus:border-primary"
                    />
                </div>

                <!-- Type Filter -->
                <div class="md:col-span-2">
                    <select name="type_filter" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:outline-none focus:border-primary">
                        <option value="">Semua Tipe</option>
                        <option value="pm" {{ request('type_filter') === 'pm' ? 'selected' : '' }}>Preventive (PM)</option>
                        <option value="corrective" {{ request('type_filter') === 'corrective' ? 'selected' : '' }}>Corrective (CM)</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="md:col-span-2">
                    <select name="priority" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:outline-none focus:border-primary">
                        <option value="">Semua Prioritas</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Rendah</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Tinggi</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Kritis</option>
                    </select>
                </div>

                <!-- Readiness Status Filter -->
                <div class="md:col-span-2">
                    <select name="readiness_status" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:outline-none focus:border-primary">
                        <option value="">Semua Audit Kesiapan</option>
                        <option value="Ready" {{ request('readiness_status') === 'Ready' ? 'selected' : '' }}>Siap Eksekusi</option>
                        <option value="Almost Ready" {{ request('readiness_status') === 'Almost Ready' ? 'selected' : '' }}>Hampir Siap</option>
                        <option value="Blocked" {{ request('readiness_status') === 'Blocked' ? 'selected' : '' }}>Terblokir</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="md:col-span-2">
                    <select name="status" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:outline-none focus:border-primary">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                        <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <!-- Submit / Reset Actions -->
                <div class="md:col-span-1 flex gap-2">
                    <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-lg text-body-md font-bold flex-1 flex justify-center items-center">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'priority', 'readiness_status', 'status', 'type_filter']))
                        <a href="{{ route('planning.index') }}" class="bg-surface-container border border-outline-variant hover:bg-surface-container-high px-3 py-2 rounded-lg flex items-center justify-center" title="Reset Filter">
                            <span class="material-symbols-outlined">restart_alt</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tabs Toggle: Timeline Board vs Calendar Board -->
    <div class="tabs-container bg-surface-container-low border border-outline-variant p-1 rounded-xl flex gap-1 mb-6 max-w-md">
        <button id="tab-btn-timeline" class="flex-1 py-2 text-center text-label-md font-semibold rounded-lg bg-surface-container-lowest text-primary shadow-sm transition-all focus:outline-none" onclick="switchView('timeline')">
            <span class="flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">view_timeline</span>
                Tampilan Daftar
            </span>
        </button>
        <button id="tab-btn-calendar" class="flex-1 py-2 text-center text-label-md font-semibold rounded-lg text-on-surface-variant hover:bg-surface-container-lowest/50 transition-all focus:outline-none" onclick="switchView('calendar')">
            <span class="flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                Tampilan Kalender
            </span>
        </button>
    </div>

    <!-- VIEW 1: TIMELINE / CARD LIST VIEW -->
    <div id="view-timeline" class="space-y-4">
        @php
            $sortPlans = function($collection) {
                return $collection->sort(function($a, $b) {
                    // 1. Priority (Critical, High, Medium, Low)
                    $priorityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
                    $priorityA = $priorityOrder[$a->priority] ?? 0;
                    $priorityB = $priorityOrder[$b->priority] ?? 0;
                    if ($priorityA !== $priorityB) {
                        return $priorityB <=> $priorityA; // descending (Critical first)
                    }
                    
                    // 2. Status (Reported, Assigned, Ready, Blocked)
                    $statusOrder = [
                        'Reported' => 5,
                        'Assigned' => 4,
                        'Ready' => 3,
                        'Almost Ready' => 2,
                        'Blocked' => 1
                    ];
                    $statusA = $statusOrder[$a->readiness['overall_status']] ?? 0;
                    $statusB = $statusOrder[$b->readiness['overall_status']] ?? 0;
                    if ($statusA !== $statusB) {
                        return $statusB <=> $statusA; // descending (Reported first)
                    }
                    
                    // 3. Scheduled Time (ascending)
                    $timeA = $a->scheduled_date->timestamp;
                    $timeB = $b->scheduled_date->timestamp;
                    if ($timeA !== $timeB) {
                        return $timeA <=> $timeB;
                    }
                    
                    return $a->id <=> $b->id;
                });
            };

            $todayDate = \Carbon\Carbon::today();
            $tomorrowDate = \Carbon\Carbon::tomorrow();

            // Filter out completed plans for List View
            $activePlans = $plans->filter(fn($p) => $p->status !== 'completed');

            // Group into Today, Tomorrow, Upcoming
            $todayPlansList = $sortPlans($activePlans->filter(fn($p) => $p->scheduled_date->startOfDay()->lte($todayDate)));
            $tomorrowPlansList = $sortPlans($activePlans->filter(fn($p) => $p->scheduled_date->startOfDay()->eq($tomorrowDate)));
            $upcomingPlansList = $sortPlans($activePlans->filter(fn($p) => $p->scheduled_date->startOfDay()->gt($tomorrowDate)));
        @endphp

        @if($activePlans->count() > 0)
            <!-- TODAY Section -->
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <button 
                    type="button" 
                    onclick="toggleSection('section-today')" 
                    class="w-full px-4 py-2.5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between hover:bg-slate-100/70 transition-all focus:outline-none"
                >
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        <span class="font-bold text-xs uppercase tracking-wider text-slate-700">Hari Ini / Terlambat (TODAY)</span>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-black">{{ $todayPlansList->count() }}</span>
                    </div>
                    <span id="section-today-chevron" class="material-symbols-outlined text-slate-400 text-[18px] transition-transform">expand_more</span>
                </button>
                <div id="section-today" class="p-3 space-y-2">
                    @forelse($todayPlansList as $plan)
                        @include('planning.partials.compact_card', ['plan' => $plan])
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400 italic">Tidak ada pekerjaan untuk hari ini.</div>
                    @endforelse
                </div>
            </div>

            <!-- TOMORROW Section -->
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <button 
                    type="button" 
                    onclick="toggleSection('section-tomorrow')" 
                    class="w-full px-4 py-2.5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between hover:bg-slate-100/70 transition-all focus:outline-none"
                >
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        <span class="font-bold text-xs uppercase tracking-wider text-slate-700">Besok (TOMORROW)</span>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-black">{{ $tomorrowPlansList->count() }}</span>
                    </div>
                    <span id="section-tomorrow-chevron" class="material-symbols-outlined text-slate-400 text-[18px] transition-transform flex items-center">expand_more</span>
                </button>
                <div id="section-tomorrow" class="p-3 space-y-2">
                    @forelse($tomorrowPlansList as $plan)
                        @include('planning.partials.compact_card', ['plan' => $plan])
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400 italic">Tidak ada pekerjaan untuk besok.</div>
                    @endforelse
                </div>
            </div>

            <!-- UPCOMING Section -->
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <button 
                    type="button" 
                    onclick="toggleSection('section-upcoming')" 
                    class="w-full px-4 py-2.5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between hover:bg-slate-100/70 transition-all focus:outline-none"
                >
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="font-bold text-xs uppercase tracking-wider text-slate-700">Akan Datang (UPCOMING)</span>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-black">{{ $upcomingPlansList->count() }}</span>
                    </div>
                    <span id="section-upcoming-chevron" class="material-symbols-outlined text-slate-400 text-[18px] transition-transform">expand_more</span>
                </button>
                <div id="section-upcoming" class="p-3 space-y-2">
                    @forelse($upcomingPlansList as $plan)
                        @include('planning.partials.compact_card', ['plan' => $plan])
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400 italic">Tidak ada pekerjaan mendatang.</div>
                    @endforelse
                </div>
            </div>
        @else
            <x-empty-state 
                title="Tidak Ada Rencana Perawatan" 
                description="Tidak ada data rencana perawatan preventif yang cocok dengan kriteria filter saat ini." 
                icon="search_off"
            />
        @endif
    </div>

    <!-- VIEW 2: CALENDAR VIEW GRID -->
    <div id="view-calendar" class="hidden">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
            <!-- Calendar Navigation Header -->
            <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant flex justify-between items-center">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">
                    {{ $namaBulan[$month] }} {{ $year }}
                </h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('planning.index', array_merge(request()->all(), ['month' => $prevMonth->month, 'year' => $prevMonth->year])) }}" class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </a>
                    <a href="{{ route('planning.index', array_merge(request()->all(), ['month' => now()->month, 'year' => now()->year])) }}" class="px-3 py-1 rounded border border-outline-variant text-label-md hover:bg-surface-container transition-colors">
                        Hari Ini
                    </a>
                    <a href="{{ route('planning.index', array_merge(request()->all(), ['month' => $nextMonth->month, 'year' => $nextMonth->year])) }}" class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                </div>
            </div>

            <!-- Calendar Grid Layout -->
            <div class="grid grid-cols-7 border-b border-outline-variant bg-surface-container-low text-center text-label-sm font-semibold text-on-surface-variant py-2">
                <div>Sen</div>
                <div>Sel</div>
                <div>Rab</div>
                <div>Kam</div>
                <div>Jum</div>
                <div>Sab</div>
                <div>Min</div>
            </div>

            <div class="grid grid-cols-7 bg-surface-bright divide-x divide-y divide-outline-variant border-collapse min-h-[350px]">
                <!-- Blank days offset -->
                @for($i = 1; $i < $startDayOfWeek; $i++)
                    <div class="bg-surface-container-low/30 min-h-[100px] p-2"></div>
                @endfor

                <!-- Month days -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $dayPlans = $plans->filter(fn($p) => $p->scheduled_date->format('Y-m-d') === $dateString);
                        $isToday = now()->format('Y-m-d') === $dateString;
                    @endphp
                    <div class="min-h-[100px] p-2 bg-surface-container-lowest relative group flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-label-md font-bold px-1.5 py-0.5 rounded-full {{ $isToday ? 'bg-primary text-on-primary' : 'text-on-surface-variant' }}">
                                {{ $day }}
                            </span>
                        </div>
                        <div class="flex-1 flex flex-col justify-end gap-1 overflow-hidden mt-1">
                            @foreach($dayPlans as $p)
                                @php
                                    $bgColor = $p->isCorrective() ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700';
                                    $rdDotColor = match($p->readiness['overall_status']) {
                                        'Completed' => 'bg-green-400',
                                        'Waiting Review' => 'bg-indigo-300',
                                        'Ready' => 'bg-green-500',
                                        'Almost Ready' => 'bg-amber-400',
                                        'Blocked' => 'bg-red-300',
                                        'Reported' => 'bg-slate-350',
                                        'Assigned' => 'bg-yellow-300',
                                        default => 'bg-white',
                                    };
                                    $typeBadge = $p->isCorrective() ? 'CM' : 'PM';
                                @endphp
                                <a 
                                    href="{{ route('planning.show', $p->id) }}" 
                                    class="text-[9px] text-white font-semibold truncate rounded px-1.5 py-0.5 flex items-center justify-between gap-1 shadow-sm transition-all hover:scale-[1.02] {{ $bgColor }}"
                                    title="{{ $p->machine->code }} - {{ $p->isCorrective() ? $p->breakdown_number : $p->maintenanceTemplate->name }} ({{ $p->readiness['overall_status'] }})"
                                >
                                    <span class="truncate">[{{ $typeBadge }}] {{ $p->machine->code }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $rdDotColor }}" title="Kesiapan: {{ $p->readiness['overall_status'] }}"></span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endfor

                <!-- Blank days at end of week -->
                @php
                    $lastDayOfWeek = \Carbon\Carbon::createFromDate($year, $month, $daysInMonth)->dayOfWeekIso;
                    $endOffset = 7 - $lastDayOfWeek;
                @endphp
                @for($i = 0; $i < $endOffset; $i++)
                    <div class="bg-surface-container-low/30 min-h-[100px] p-2"></div>
                @endfor
            </div>
        </div>

        <!-- Legend Card -->
        <div class="mt-4 p-4 border border-outline-variant rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-3 text-label-sm font-semibold text-on-surface-variant">
            <div class="flex flex-wrap gap-6 items-center border-b border-outline-variant pb-2">
                <span class="text-xs uppercase text-slate-400 font-bold block w-full mb-1">Tipe Pekerjaan (Warna Kalender)</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-red-600"></span> Corrective (CM)</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-600"></span> Preventive (PM)</span>
            </div>
            <div class="flex flex-wrap gap-6 items-center">
                <span class="text-xs uppercase text-slate-400 font-bold block w-full mb-1">Status Kesiapan & Eksekusi (Bulatan Status)</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Selesai / Siap Eksekusi</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-300"></span> Menunggu Review</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Hampir Siap / Ditugaskan</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-300"></span> Terblokir (Hambatan / Mesin Down)</span>
            </div>
        </div>
    </div>

    <!-- UI State Navigation Script -->
    <script>
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const chevron = document.getElementById(sectionId + '-chevron');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            } else {
                section.classList.add('hidden');
                if (chevron) chevron.style.transform = 'rotate(-90deg)';
            }
        }

        function switchView(view) {
            const btnTimeline = document.getElementById('tab-btn-timeline');
            const btnCalendar = document.getElementById('tab-btn-calendar');
            const viewTimeline = document.getElementById('view-timeline');
            const viewCalendar = document.getElementById('view-calendar');

            if (view === 'timeline') {
                btnTimeline.classList.add('bg-surface-container-lowest', 'text-primary', 'shadow-sm');
                btnTimeline.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-lowest/50');
                
                btnCalendar.classList.remove('bg-surface-container-lowest', 'text-primary', 'shadow-sm');
                btnCalendar.classList.add('text-on-surface-variant', 'hover:bg-surface-container-lowest/50');
                
                viewTimeline.classList.remove('hidden');
                viewCalendar.classList.add('hidden');
                localStorage.setItem('mrm_planning_view', 'timeline');
            } else {
                btnCalendar.classList.add('bg-surface-container-lowest', 'text-primary', 'shadow-sm');
                btnCalendar.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-lowest/50');
                
                btnTimeline.classList.remove('bg-surface-container-lowest', 'text-primary', 'shadow-sm');
                btnTimeline.classList.add('text-on-surface-variant', 'hover:bg-surface-container-lowest/50');
                
                viewCalendar.classList.remove('hidden');
                viewTimeline.classList.add('hidden');
                localStorage.setItem('mrm_planning_view', 'calendar');
            }
        }

        // Restore view preferences from session storage
        document.addEventListener('DOMContentLoaded', () => {
            const savedView = localStorage.getItem('mrm_planning_view');
            if (savedView === 'calendar' || {{ request()->has('month') ? 'true' : 'false' }}) {
                switchView('calendar');
            } else {
                switchView('timeline');
            }
        });
    </script>
</x-layouts.app>
