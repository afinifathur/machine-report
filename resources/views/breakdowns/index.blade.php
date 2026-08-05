<x-layouts.app 
    title="Kerusakan & Downtime | Sistem MRM"
    topbar-title="Kerusakan & Downtime"
>
    <!-- Breadcrumbs & Page Header -->
    <div class="flex items-center justify-between mb-4">
        <x-breadcrumb :items="['Kerusakan' => '']" />
    </div>

    <!-- Header Actions Panel (Compact Layout Reference) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div>
            <h1 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-600 text-[22px]">emergency_home</span>
                Monitoring Kerusakan & Downtime
            </h1>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('planning.report-breakdown') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-1.5 rounded-lg text-body-sm font-bold flex items-center justify-center gap-1.5 h-[32px] shadow-none">
                <span class="material-symbols-outlined text-[16px]">add_alert</span>
                Lapor Breakdown
            </a>
            <a href="{{ route('breakdowns.index') }}" class="bg-surface-bright border border-outline text-on-surface hover:bg-surface-container-low px-3 py-1.5 rounded-lg text-body-sm font-bold flex items-center justify-center gap-1 h-[32px]">
                <span class="material-symbols-outlined text-[16px]">refresh</span>
                Refresh
            </a>
        </div>
    </div>

    <!-- Compact Statistics Cards Grid -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-4">
        <!-- Active Breakdown -->
        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-3 shadow-none border-l-2 border-l-rose-500">
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">warning</span>
            </div>
            <div>
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider leading-none">Active Breakdown</p>
                <h3 class="text-sm font-black text-rose-600 mt-1 leading-none">{{ $activeBreakdownsCount }} Tiket</h3>
            </div>
        </div>

        <!-- Machines Down -->
        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-3 shadow-none border-l-2 border-l-red-600">
            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">broken_image</span>
            </div>
            <div>
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider leading-none">Machines Down</p>
                <h3 class="text-sm font-black text-red-600 mt-1 leading-none">{{ $machinesDownCount }} Unit</h3>
            </div>
        </div>

        <!-- Average MTTR -->
        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-3 shadow-none border-l-2 border-l-blue-500">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">hourglass_empty</span>
            </div>
            <div>
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider leading-none">Average MTTR</p>
                <h3 class="text-sm font-black text-blue-600 mt-1 leading-none">{{ $avgMttr }} Menit</h3>
            </div>
        </div>

        <!-- Completed Today -->
        <div class="bg-surface-container-lowest border border-outline-variant p-2.5 rounded-xl flex items-center gap-3 shadow-none border-l-2 border-l-green-500">
            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
            </div>
            <div>
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider leading-none">Completed Today</p>
                <h3 class="text-sm font-black text-green-600 mt-1 leading-none">{{ $completedTodayCount }} Tiket</h3>
            </div>
        </div>
    </section>

    <!-- Compact Inline Filter Toolbar -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-2 shadow-none mb-4">
        <form method="GET" action="{{ route('breakdowns.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Search:</span>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="No. breakdown, catatan..." 
                        class="bg-surface-bright border border-outline-variant px-2.5 py-1 pl-7 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px] w-[180px]"
                    />
                    <span class="material-symbols-outlined absolute left-2 top-1.5 text-on-surface-variant text-[16px]">search</span>
                </div>
            </div>

            <!-- Priority -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Priority:</span>
                <select name="priority" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All --</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Rendah</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Sedang</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Tinggi</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Kritis</option>
                </select>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Status:</span>
                <select name="status" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All --</option>
                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Reported</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Machine -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Machine:</span>
                <select name="machine_id" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All --</option>
                    @foreach ($machines as $m)
                        <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="px-4 bg-primary text-on-primary rounded-lg text-body-sm font-bold hover:bg-opacity-90 transition-colors shadow-none flex items-center gap-1.5 h-[32px]">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Apply
                </button>
                @if(request()->anyFilled(['search', 'priority', 'status', 'machine_id']))
                    <a href="{{ route('breakdowns.index') }}" class="px-3 border border-outline text-on-surface rounded-lg text-body-sm font-bold hover:bg-surface-container-low transition-colors flex items-center justify-center h-[32px]" title="Reset Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- MAPPED LIST (Desktop Table vs Mobile Cards) -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-3.5 shadow-none">
        
        <!-- Table Title Header -->
        <div class="flex items-center gap-2 mb-3">
            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" data-icon="list_alt">list_alt</span>
                Monitoring List
            </h3>
        </div>

        <!-- DESKTOP/TABLET TABLE VIEW -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-bright border-b border-outline-variant">
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[120px]">No. Breakdown</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Mesin / Status</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[150px]">Waktu Dilaporkan</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Prioritas</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[110px] text-center">Status Tiket</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[120px]">Teknisi</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Downtime</th>
                        <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider text-right w-[110px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($plans as $plan)
                        @php
                            $priorityClass = match($plan->priority) {
                                'low' => 'bg-slate-100 text-slate-700',
                                'medium' => 'bg-blue-100 text-blue-800',
                                'high' => 'bg-orange-100 text-orange-850',
                                'critical' => 'bg-red-100 text-red-850 font-bold',
                            };

                            $statusClass = match($plan->status) {
                                'reported' => 'bg-rose-100 text-rose-850 border border-rose-200',
                                'assigned' => 'bg-amber-100 text-amber-850 border border-amber-200',
                                'completed' => 'bg-emerald-100 text-emerald-850 border border-emerald-200',
                                'cancelled' => 'bg-slate-150 text-slate-500 border border-slate-250',
                                default => 'bg-slate-100 text-slate-800 border border-slate-200',
                            };

                            $machineStatusClass = match($plan->machine->operational_status) {
                                'running' => 'bg-emerald-500',
                                'idle' => 'bg-yellow-500',
                                'breakdown' => 'bg-red-650 animate-pulse',
                                default => 'bg-slate-400',
                            };
                        @endphp
                        <tr class="hover:bg-surface-container-low transition-colors group">
                            <!-- Breakdown Number -->
                            <td class="px-3 py-1.5 whitespace-nowrap">
                                <span class="mono text-xs font-bold text-slate-800">{{ $plan->breakdown_number }}</span>
                            </td>

                            <!-- Machine / Status -->
                            <td class="px-3 py-1.5">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-on-surface leading-tight">{{ $plan->machine->name }}</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $machineStatusClass }}"></span>
                                        <span class="text-[9px] uppercase font-bold text-slate-400 leading-none">{{ $plan->machine->operational_status }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Reported Time -->
                            <td class="px-3 py-1.5 whitespace-nowrap">
                                <span class="font-mono text-slate-600 text-xs">{{ $plan->reported_at->format('d M Y H:i') }}</span>
                            </td>

                            <!-- Priority Badge -->
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <span class="inline-block text-[10px] uppercase px-1.5 py-0.5 rounded font-black {{ $priorityClass }}">
                                    {{ $plan->priority }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <span class="inline-block text-[10px] uppercase px-2 py-0.5 rounded-full font-black {{ $statusClass }}">
                                    {{ $plan->status }}
                                </span>
                            </td>

                            <!-- Assigned Technician -->
                            <td class="px-3 py-1.5 whitespace-nowrap">
                                <span class="text-xs text-slate-700 font-semibold">{{ $plan->assigned_technician ?? '-' }}</span>
                            </td>

                            <!-- Downtime duration -->
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <span class="text-xs font-bold text-slate-800">{{ $plan->downtime_duration ? $plan->downtime_duration . ' Min' : '-' }}</span>
                            </td>

                            <!-- Action -->
                            <td class="px-3 py-1.5 text-right whitespace-nowrap">
                                @if($plan->status === 'reported')
                                    <button type="button" 
                                            onclick="openAssignModal('{{ $plan->id }}', '{{ $plan->assigned_technician }}')"
                                            class="h-[26px] bg-primary hover:bg-primary-container text-on-primary px-2.5 rounded-md text-[10px] font-bold inline-flex items-center gap-1 shadow-none">
                                        <span class="material-symbols-outlined text-[13px]">engineering</span>
                                        Tugaskan
                                    </button>
                                @endif
                                @if($plan->status === 'assigned')
                                    <a href="{{ route('planning.execute', $plan->id) }}" 
                                       class="h-[26px] bg-orange-500 hover:bg-orange-600 text-white px-2.5 rounded-md text-[10px] font-bold inline-flex items-center gap-1 shadow-none">
                                        <span class="material-symbols-outlined text-[13px]">qr_code_scanner</span>
                                        Verifikasi
                                    </a>
                                @endif
                                @if($plan->status === 'completed' || $plan->status === 'cancelled')
                                    <a href="{{ route('planning.show', $plan->id) }}" 
                                       class="h-[26px] bg-slate-100 hover:bg-slate-200 text-slate-800 px-2.5 rounded-md text-[10px] font-bold inline-flex items-center gap-1 shadow-none"
                                       title="Detail">
                                        Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 italic">Tidak ada data kerusakan terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PHONE CARDS VIEW (Responsive layout for mobile) -->
        <div class="block md:hidden space-y-3">
            @forelse($plans as $plan)
                @php
                    $priorityClass = match($plan->priority) {
                        'low' => 'bg-slate-100 text-slate-700',
                        'medium' => 'bg-blue-100 text-blue-800',
                        'high' => 'bg-orange-100 text-orange-850',
                        'critical' => 'bg-red-100 text-red-850 font-bold',
                    };

                    $statusClass = match($plan->status) {
                        'reported' => 'bg-rose-100 text-rose-850 border border-rose-200',
                        'assigned' => 'bg-amber-100 text-amber-850 border border-amber-200',
                        'completed' => 'bg-emerald-100 text-emerald-850 border border-emerald-200',
                        'cancelled' => 'bg-slate-150 text-slate-500 border border-slate-250',
                        default => 'bg-slate-100 text-slate-800 border border-slate-200',
                    };
 
                    $machineStatusClass = match($plan->machine->operational_status) {
                        'running' => 'bg-emerald-500',
                        'idle' => 'bg-yellow-500',
                        'breakdown' => 'bg-red-650 animate-pulse',
                        default => 'bg-slate-400',
                    };
                @endphp
                <div class="bg-white rounded-xl border border-outline-variant p-3.5 space-y-2.5 shadow-none">
                    <div class="flex justify-between items-start">
                        <span class="font-mono font-bold text-xs text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded">{{ $plan->breakdown_number }}</span>
                        <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-black tracking-wider {{ $priorityClass }}">
                            {{ $plan->priority }}
                        </span>
                    </div>
 
                    <div class="space-y-0.5">
                        <h3 class="text-xs font-bold text-slate-850">{{ $plan->machine->name }}</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $machineStatusClass }}"></span>
                            <span class="text-[9px] uppercase font-bold text-slate-400 font-mono">{{ $plan->machine->code }} ({{ $plan->machine->operational_status }})</span>
                        </div>
                    </div>
 
                    <div class="grid grid-cols-2 gap-2 text-[11px] border-t border-slate-100 pt-2 text-slate-500">
                        <div>
                            <span class="block text-[8px] uppercase font-bold text-slate-400">Dilaporkan</span>
                            <span class="font-mono text-slate-600 text-[9px]">{{ $plan->reported_at->format('d/m/y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-[8px] uppercase font-bold text-slate-400">Teknisi</span>
                            <span class="font-semibold text-slate-750 text-[10px]">{{ $plan->assigned_technician ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[8px] uppercase font-bold text-slate-400">Downtime</span>
                            <span class="font-bold text-slate-800 text-[10px]">{{ $plan->downtime_duration ? $plan->downtime_duration . ' Min' : '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[8px] uppercase font-bold text-slate-400">Status Tiket</span>
                            <span class="inline-block text-[8px] uppercase px-2 rounded-full font-bold {{ $statusClass }}">{{ $plan->status }}</span>
                        </div>
                    </div>
 
                    <div class="pt-2 border-t border-slate-100 flex justify-end">
                        @if($plan->status === 'reported')
                            <button type="button" 
                                    onclick="openAssignModal('{{ $plan->id }}', '{{ $plan->assigned_technician }}')"
                                    class="w-full bg-primary hover:bg-primary-container text-on-primary py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1 shadow-none">
                                <span class="material-symbols-outlined text-[15px]">engineering</span>
                                Tugaskan Teknisi
                            </button>
                        @elseif($plan->status === 'assigned')
                            <a href="{{ route('planning.execute', $plan->id) }}" 
                               class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1 shadow-none">
                                <span class="material-symbols-outlined text-[15px]">qr_code_scanner</span>
                                Mulai Verifikasi Paspor
                            </a>
                        @elseif($plan->status === 'completed' || $plan->status === 'cancelled')
                            <a href="{{ route('planning.show', $plan->id) }}" 
                               class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1 border border-slate-200">
                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                Lihat Detail
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-[11px] text-slate-400 italic text-center py-6">Tidak ada data kerusakan terdaftar.</p>
            @endforelse
        </div>

    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $plans->links() }}
    </div>

    <!-- ASSIGN TECHNICIAN MODAL -->
    <div id="assign-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <!-- Backdrop blur -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeAssignModal()"></div>
        <!-- Modal core -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white border border-outline-variant p-5 shadow-xl transition-all w-full max-w-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary">engineering</span>
                    <h3 class="text-sm font-black text-slate-800">Tugaskan Teknisi</h3>
                </div>
                <p class="text-[11px] text-slate-400 mb-4 leading-normal">Pilih nama teknisi yang akan bertanggung jawab untuk melakukan perbaikan mesin ini.</p>
                <form id="assign-form" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label for="modal-assigned-technician" class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Pilih Nama Teknisi</label>
                        <x-employee-autocomplete 
                            name="assigned_technician" 
                            id="modal-assigned-technician" 
                            required="true"
                            placeholder="Pilih nama teknisi..."
                        />
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeAssignModal()" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary text-on-primary hover:bg-primary-container rounded-xl shadow-none transition-colors">Tugaskan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Javascript Modal Handlers -->
    <script>
        function openAssignModal(planId, currentTechnician) {
            const form = document.getElementById('assign-form');
            let urlTemplate = "{{ route('planning.assign-technician', ':id') }}";
            form.action = urlTemplate.replace(':id', planId);
            
            const hiddenInput = document.getElementById('modal-assigned-technician');
            hiddenInput.value = currentTechnician || '';
            
            const searchInput = document.getElementById('search-modal-assigned-technician');
            if (searchInput) {
                if (currentTechnician) {
                    const opt = Array.from(document.querySelectorAll('.option-modal-assigned-technician'))
                        .find(o => o.getAttribute('data-value') === currentTechnician);
                    if (opt) {
                        searchInput.value = opt.getAttribute('data-display');
                    } else {
                        searchInput.value = currentTechnician;
                    }
                } else {
                    searchInput.value = '';
                }
            }
            
            document.getElementById('assign-modal').classList.remove('hidden');
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').classList.add('hidden');
        }
    </script>
</x-layouts.app>
