<x-layouts.app 
    title="Machine Sparepart Monitor | Sistem MRM"
    topbar-title="Machine Sparepart Monitor"
>
    <!-- Breadcrumbs & Export Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <x-breadcrumb :items="['Integrasi Sparepart' => '']" />

        <!-- Export Actions (Top Right) -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <!-- Export Excel -->
            <a href="{{ route('spareparts.export.excel', request()->all()) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors"
               title="Export ke format Excel / CSV">
                <span class="material-symbols-outlined text-[16px]">table_view</span>
                <span>Export Excel</span>
            </a>

            <!-- Export PDF (Inline View) -->
            <a href="{{ route('spareparts.export.pdf', request()->all()) }}" 
               target="_blank" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors"
               title="Export / Cetak Dokumen PDF Resmi">
                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <!-- Status Ribbon (Workspace Header) -->
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 bg-surface-container-lowest border border-outline-variant px-4 py-2 rounded-xl text-body-sm font-bold text-on-surface shadow-sm mb-4">
        <span class="flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px] text-primary" data-icon="precision_manufacturing">precision_manufacturing</span>
            {{ $totalMachinesCount }} Mesin
        </span>
        <span class="text-outline-variant">|</span>
        <span class="text-green-700">Mapped {{ $mappedMachinesCount }}</span>
        <span class="text-outline-variant">|</span>
        <a href="{{ route('spareparts.unmapped-machines') }}" class="text-red-600 hover:underline">Unmapped {{ $unmappedMachinesCount }}</a>
        <span class="text-outline-variant">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'critical']) }}" class="hover:underline flex items-center gap-1"><span class="text-[12px]">🔴</span> Critical <span class="text-red-700">{{ $statusCounts['critical'] }}</span></a>
        <span class="text-outline-variant">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'reorder']) }}" class="hover:underline flex items-center gap-1"><span class="text-[12px]">🟠</span> Reorder <span class="text-amber-600">{{ $statusCounts['reorder'] }}</span></a>
        <span class="text-outline-variant">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'healthy']) }}" class="hover:underline flex items-center gap-1"><span class="text-[12px]">🟢</span> Healthy <span class="text-green-600">{{ $statusCounts['healthy'] }}</span></a>
        <span class="text-outline-variant">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'overstock']) }}" class="hover:underline flex items-center gap-1"><span class="text-[12px]">🔵</span> Overstock <span class="text-blue-650">{{ $statusCounts['overstock'] }}</span></a>
        <span class="text-outline-variant">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'unknown']) }}" class="hover:underline flex items-center gap-1"><span class="text-[12px]">⚪</span> Unknown <span class="text-gray-500">{{ $statusCounts['unknown'] }}</span></a>
        <span class="text-outline-variant">|</span>
        @if($dataSourceMode === 'Live')
            <span class="px-2 py-0.5 rounded text-[10px] bg-green-100 text-green-700 font-bold border border-green-200">LIVE</span>
        @else
            <span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-700 font-bold border border-amber-200">MOCK</span>
        @endif
        <span class="text-outline-variant">|</span>
        <span class="text-on-surface-variant font-normal flex items-center gap-1 text-[11px]">
            <span class="material-symbols-outlined text-[14px]">sync</span>
            Last Sync: <span class="font-bold text-on-surface">{{ $lastSyncTime }}</span>
        </span>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-3 shadow-none mb-4">
        <form method="GET" action="{{ route('spareparts.index') }}" id="sparepart-filter-form" class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Search:</span>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="ERP Code / Name..." 
                        class="bg-surface-bright border border-outline-variant px-2.5 py-1 pl-7 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[34px] w-[180px]"
                    >
                    <span class="material-symbols-outlined absolute left-2 top-2 text-on-surface-variant text-[16px]">search</span>
                </div>
            </div>

            <!-- Machine Autocomplete -->
            <div class="flex items-center gap-2 relative">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Machine:</span>
                <div class="relative w-[230px]" id="machine-autocomplete-container">
                    <!-- Hidden input to store machine ID / filter value -->
                    <input 
                        type="hidden" 
                        name="machine" 
                        id="machine-hidden-input" 
                        value="{{ request('machine', request('machine_id', '')) }}"
                    >
                    
                    <!-- Searchable input -->
                    <div class="relative flex items-center">
                        <input 
                            type="text" 
                            id="machine-autocomplete-input" 
                            autocomplete="off"
                            placeholder="Ketik kode / nama mesin..." 
                            value="{{ $selectedMachine ? ($selectedMachine->code . ' — ' . $selectedMachine->name) : (request('machine') && !is_numeric(request('machine')) ? request('machine') : '') }}"
                            class="w-full bg-surface-bright border border-outline-variant px-2.5 py-1 pl-7 pr-7 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[34px] truncate"
                        >
                        <span class="material-symbols-outlined absolute left-2 top-2 text-on-surface-variant text-[16px]">precision_manufacturing</span>
                        
                        <!-- Clear Button (×) -->
                        <button 
                            type="button" 
                            id="machine-clear-btn" 
                            class="{{ (request('machine') || request('machine_id')) ? '' : 'hidden' }} absolute right-2 top-2 text-on-surface-variant hover:text-error transition-colors"
                            title="Hapus filter mesin"
                        >
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div 
                        id="machine-autocomplete-dropdown" 
                        class="hidden absolute left-0 top-[38px] w-full bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg z-50 max-h-64 overflow-y-auto"
                    >
                        <div id="machine-dropdown-list" class="divide-y divide-outline-variant text-xs">
                            <!-- Items populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Status:</span>
                <select name="status" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[34px]">
                    <option value="">-- All Statuses --</option>
                    <option value="critical" {{ request('status') === 'critical' ? 'selected' : '' }}>🔴 Critical</option>
                    <option value="reorder" {{ request('status') === 'reorder' ? 'selected' : '' }}>🟠 Reorder</option>
                    <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>🟢 Healthy</option>
                    <option value="overstock" {{ request('status') === 'overstock' ? 'selected' : '' }}>🔵 Overstock</option>
                    <option value="unknown" {{ request('status') === 'unknown' ? 'selected' : '' }}>⚪ Unknown</option>
                </select>
            </div>

            <!-- Criticality Filter -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Criticality:</span>
                <select name="criticality" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[34px]">
                    <option value="">-- All --</option>
                    <option value="A" {{ request('criticality') === 'A' ? 'selected' : '' }}>Kelas A</option>
                    <option value="B" {{ request('criticality') === 'B' ? 'selected' : '' }}>Kelas B</option>
                    <option value="C" {{ request('criticality') === 'C' ? 'selected' : '' }}>Kelas C</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="px-4 bg-primary text-on-primary rounded-lg text-body-sm font-bold hover:bg-opacity-90 transition-colors shadow-none flex items-center gap-1.5 h-[34px]">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Apply
                </button>
                @if(request()->anyFilled(['search', 'machine', 'machine_id', 'status', 'criticality']))
                    <a href="{{ route('spareparts.index') }}" class="px-3 border border-outline text-on-surface rounded-lg text-body-sm font-bold hover:bg-surface-container-low transition-colors flex items-center justify-center h-[34px]" title="Reset Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Mapped Spareparts List -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-3.5 shadow-none">
        
        <!-- Header & Legend Table -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" data-icon="list_alt">list_alt</span>
                    Monitoring List
                </h3>
                <span class="text-xs text-on-surface-variant font-medium">({{ count($items) }} Terpantau)</span>
            </div>
            
            <!-- Small Legend Widget -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-surface-container-low border border-outline-variant px-2.5 py-1 rounded-lg text-[10px] font-bold text-on-surface-variant">
                <span class="flex items-center gap-1"><span class="text-red-600">🔴</span> Crit (&le;50% Min)</span>
                <span class="flex items-center gap-1"><span class="text-amber-500">🟠</span> Reord (&lt;Min)</span>
                <span class="flex items-center gap-1"><span class="text-green-600">🟢</span> Healthy</span>
                <span class="flex items-center gap-1"><span class="text-blue-600">🔵</span> Over (&gt;Target)</span>
                <span class="flex items-center gap-1"><span class="text-gray-400">⚪</span> Unknown</span>
            </div>
        </div>

        @if(count($paginatedItems) > 0)
            <!-- DESKTOP VIEW (Visible on large screens) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-bright border-b border-outline-variant">
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[110px]">Status</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[110px]">ERP Code</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Item Name</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Stock</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Weekly Avg</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Lead Time</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Min Stock</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Target</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Coverage</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[120px] text-center">Last Audit</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider text-right w-[80px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($paginatedItems as $item)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <!-- Status Badge -->
                                <td class="px-3 py-1.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] border font-bold flex items-center justify-center gap-1 {{ $item['status']['badge_class'] }}">
                                        <span>{{ $item['status']['icon'] }}</span>
                                        <span>{{ $item['status']['label'] }}</span>
                                    </span>
                                </td>
                                
                                <!-- ERP Code -->
                                <td class="px-3 py-1.5 whitespace-nowrap">
                                    <span class="mono text-xs font-bold text-primary">{{ $item['erp_code'] }}</span>
                                </td>

                                <!-- Item Name & Category -->
                                <td class="px-3 py-1.5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-on-surface leading-tight">{{ $item['name'] }}</span>
                                        <span class="text-[10px] text-on-surface-variant leading-none mt-0.5">{{ $item['category'] }} ({{ $item['brand'] }})</span>
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs font-bold text-on-surface">{{ $item['stock'] }} {{ $item['unit'] }}</span>
                                </td>

                                <!-- Weekly Avg -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs text-on-surface font-medium">
                                        {{ !is_null($item['weekly_average']) ? number_format($item['weekly_average'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Lead Time -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs text-on-surface font-medium">{{ $item['lead_time'] }} H</span>
                                </td>

                                <!-- Min Stock -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs font-bold {{ !is_null($item['min_stock']) && $item['stock'] < $item['min_stock'] ? 'text-red-650 font-black' : 'text-on-surface-variant' }}">
                                        {{ !is_null($item['min_stock']) ? number_format($item['min_stock'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Target -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs text-on-surface-variant font-medium">
                                        {{ !is_null($item['target_stock']) ? number_format($item['target_stock'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Coverage -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <span class="text-xs text-primary font-bold">{{ $item['coverage'] }} Mesin</span>
                                </td>

                                 <!-- Last Audit -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    @if($item['last_audit_at'])
                                        @php
                                            $lastAuditDate = \Carbon\Carbon::parse($item['last_audit_at']);
                                            $formattedDate = $lastAuditDate->format('d M Y');
                                            $daysAgo = (int)$lastAuditDate->diffInDays(\Carbon\Carbon::now());
                                            $ageStr = $daysAgo === 0 ? 'today' : ($daysAgo === 1 ? 'yesterday' : $daysAgo . ' days ago');
                                            
                                            if ($daysAgo <= 30) {
                                                $badgeClass = 'bg-green-50 text-green-700 border-green-200 font-bold';
                                            } elseif ($daysAgo <= 90) {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200 font-medium';
                                            } else {
                                                $badgeClass = 'bg-gray-50 text-gray-600 border-gray-200 font-normal';
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-on-surface">{{ $formattedDate }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] border mt-1 {{ $badgeClass }}">
                                                {{ $ageStr }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] border bg-gray-50 text-gray-500 border-gray-200 font-medium">
                                            Never
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="px-3 py-1.5 text-right whitespace-nowrap">
                                    <a href="{{ route('spareparts.show', $item['erp_code']) }}" class="inline-flex items-center gap-0.5 px-2 py-1 rounded-lg bg-primary-container text-primary text-[11px] font-bold hover:bg-primary hover:text-on-primary transition-all shadow-none">
                                        Detail
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Visible on small screens) -->
            <div class="block md:hidden space-y-2">
                @foreach($paginatedItems as $item)
                    <div class="p-3 bg-surface-container-low border border-outline-variant rounded-xl flex flex-col justify-between gap-3 shadow-none">
                        <div class="flex items-start justify-between">
                            <div class="flex flex-col">
                                <span class="mono text-xs font-bold text-primary leading-none">{{ $item['erp_code'] }}</span>
                                <h4 class="text-xs font-bold text-on-surface mt-1">{{ $item['name'] }}</h4>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="px-2 py-0.5 rounded-md text-[10px] border font-bold flex items-center gap-1 {{ $item['status']['badge_class'] }}">
                                <span>{{ $item['status']['icon'] }}</span>
                                <span>{{ $item['status']['label'] }}</span>
                            </span>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-2 text-[11px] py-1.5 border-y border-outline-variant border-dashed">
                            <div>
                                <span class="text-on-surface-variant">Stok WMS:</span>
                                <span class="font-bold text-on-surface ml-1">{{ $item['stock'] }} {{ $item['unit'] }}</span>
                            </div>
                            <div>
                                <span class="text-on-surface-variant">Coverage:</span>
                                <span class="font-bold text-primary ml-1">{{ $item['coverage'] }} Mesin</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-on-surface-variant">Last Audit:</span>
                                @if($item['last_audit_at'])
                                    @php
                                        $lastAuditDate = \Carbon\Carbon::parse($item['last_audit_at']);
                                        $formattedDate = $lastAuditDate->format('d M Y');
                                        $daysAgo = (int)$lastAuditDate->diffInDays(\Carbon\Carbon::now());
                                        $ageStr = $daysAgo === 0 ? 'today' : ($daysAgo === 1 ? 'yesterday' : $daysAgo . ' days ago');
                                    @endphp
                                    <span class="font-bold text-on-surface ml-1">{{ $formattedDate }} ({{ $ageStr }})</span>
                                @else
                                    <span class="font-bold text-on-surface-variant ml-1">Never</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('spareparts.show', $item['erp_code']) }}" class="inline-flex items-center gap-0.5 px-2 py-1 bg-primary text-on-primary text-[10px] font-bold rounded-lg shadow-none">
                                Detail
                                <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            @if($paginatedItems->hasPages())
                <div class="pt-3 border-t border-outline-variant">
                    {{ $paginatedItems->links() }}
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="py-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2">search_off</span>
                <h4 class="text-sm font-bold text-on-surface mb-1">Tidak ada sparepart yang sesuai dengan filter.</h4>
                <p class="text-xs text-on-surface-variant">Coba ubah kriteria pencarian, mesin, atau status untuk melihat data lain.</p>
            </div>
        @endif
    </div>

    <!-- Machine Autocomplete JavaScript -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('machine-autocomplete-input');
            const hiddenInput = document.getElementById('machine-hidden-input');
            const clearBtn = document.getElementById('machine-clear-btn');
            const dropdown = document.getElementById('machine-autocomplete-dropdown');
            const list = document.getElementById('machine-dropdown-list');
            const container = document.getElementById('machine-autocomplete-container');
            const form = document.getElementById('sparepart-filter-form');

            let debounceTimer = null;

            if (!input || !hiddenInput || !dropdown || !list) return;

            // Fetch and render machines
            function fetchMachines(searchTerm) {
                const url = `{{ route('spareparts.machines.autocomplete') }}?search=${encodeURIComponent(searchTerm)}`;
                
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    renderDropdown(data);
                })
                .catch(err => {
                    console.error('Failed to search machines:', err);
                });
            }

            function renderDropdown(machines) {
                list.innerHTML = '';

                if (machines.length === 0) {
                    list.innerHTML = `
                        <div class="p-3 text-center text-on-surface-variant italic text-xs">
                            Tidak ditemukan mesin yang cocok.
                        </div>
                    `;
                    dropdown.classList.remove('hidden');
                    return;
                }

                // Add "All Machines" option at top if input is empty
                if (!input.value.trim()) {
                    const allItem = document.createElement('div');
                    allItem.className = 'px-3 py-2 cursor-pointer hover:bg-surface-container-low transition-colors text-primary font-bold flex items-center gap-1.5';
                    allItem.innerHTML = `<span class="material-symbols-outlined text-[16px]">all_inclusive</span> Semua Mesin`;
                    allItem.addEventListener('click', function() {
                        selectMachine(null, '');
                    });
                    list.appendChild(allItem);
                }

                machines.forEach(machine => {
                    const item = document.createElement('div');
                    item.className = 'px-3 py-2 cursor-pointer hover:bg-surface-container-low transition-colors';
                    item.innerHTML = `
                        <div class="font-mono font-bold text-primary text-xs">${escapeHtml(machine.code)}</div>
                        <div class="text-on-surface-variant text-[11px] leading-tight">${escapeHtml(machine.name)} ${machine.department ? '(' + escapeHtml(machine.department) + ')' : ''}</div>
                    `;
                    item.addEventListener('click', function() {
                        selectMachine(machine.id, `${machine.code} — ${machine.name}`);
                    });
                    list.appendChild(item);
                });

                dropdown.classList.remove('hidden');
            }

            function selectMachine(id, displayLabel) {
                hiddenInput.value = id || '';
                input.value = displayLabel || '';
                dropdown.classList.add('hidden');
                
                if (id) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.innerText = text;
                return div.innerHTML;
            }

            // Input event with debounce
            input.addEventListener('input', function() {
                const term = input.value.trim();
                if (term === '') {
                    hiddenInput.value = '';
                    clearBtn.classList.add('hidden');
                } else {
                    clearBtn.classList.remove('hidden');
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchMachines(term);
                }, 250);
            });

            // Focus event
            input.addEventListener('focus', function() {
                fetchMachines(input.value.trim());
            });

            // Clear button event
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    selectMachine('', '');
                    dropdown.classList.add('hidden');
                    input.focus();
                });
            }

            // Click outside to close dropdown
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
