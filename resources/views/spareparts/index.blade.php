<x-layouts.app 
    title="Machine Sparepart Monitor | Sistem MRM"
    topbar-title="Machine Sparepart Monitor"
>
    <!-- Breadcrumbs & Page Header -->
    <div class="flex items-center justify-between mb-4">
        <x-breadcrumb :items="['Integrasi Sparepart' => '']" />
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
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-2 shadow-none mb-4">
        <form method="GET" action="{{ route('spareparts.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Search:</span>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ERP Code / Name..." class="bg-surface-bright border border-outline-variant px-2.5 py-1 pl-7 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px] w-[180px]">
                    <span class="material-symbols-outlined absolute left-2 top-1.5 text-on-surface-variant text-[16px]">search</span>
                </div>
            </div>

            <!-- Machine -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Machine:</span>
                <select name="machine" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All Machines --</option>
                    @foreach($allMachines as $m)
                        <option value="{{ $m->id }}" {{ request('machine') == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Status:</span>
                <select name="status" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All Statuses --</option>
                    <option value="critical" {{ request('status') === 'critical' ? 'selected' : '' }}>🔴 Critical</option>
                    <option value="reorder" {{ request('status') === 'reorder' ? 'selected' : '' }}>🟠 Reorder</option>
                    <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>🟢 Healthy</option>
                    <option value="overstock" {{ request('status') === 'overstock' ? 'selected' : '' }}>🔵 Overstock</option>
                    <option value="unknown" {{ request('status') === 'unknown' ? 'selected' : '' }}>⚪ Unknown</option>
                </select>
            </div>

            <!-- Criticality -->
            <div class="flex items-center gap-2">
                <span class="text-body-sm font-bold text-on-surface-variant whitespace-nowrap">Criticality:</span>
                <select name="criticality" class="bg-surface-bright border border-outline-variant px-2 py-1 rounded-lg text-body-sm focus:outline-none focus:border-primary h-[32px]">
                    <option value="">-- All --</option>
                    <option value="A" {{ request('criticality') === 'A' ? 'selected' : '' }}>Kelas A</option>
                    <option value="B" {{ request('criticality') === 'B' ? 'selected' : '' }}>Kelas B</option>
                    <option value="C" {{ request('criticality') === 'C' ? 'selected' : '' }}>Kelas C</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="px-4 bg-primary text-on-primary rounded-lg text-body-sm font-bold hover:bg-opacity-90 transition-colors shadow-none flex items-center gap-1.5 h-[32px]">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Apply
                </button>
                @if(request()->anyFilled(['search', 'machine', 'status', 'criticality']))
                    <a href="{{ route('spareparts.index') }}" class="px-3 border border-outline text-on-surface rounded-lg text-body-sm font-bold hover:bg-surface-container-low transition-colors flex items-center justify-center h-[32px]" title="Reset Filters">
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
            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" data-icon="list_alt">list_alt</span>
                Monitoring List
            </h3>
            
            <!-- Small Legend Widget -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-surface-container-low border border-outline-variant px-2.5 py-1 rounded-lg text-[10px] font-bold text-on-surface-variant">
                <span class="flex items-center gap-1"><span class="text-red-600">🔴</span> Crit (&le;50% Min)</span>
                <span class="flex items-center gap-1"><span class="text-amber-500">🟠</span> Reord (&lt;Min)</span>
                <span class="flex items-center gap-1"><span class="text-green-600">🟢</span> Healthy</span>
                <span class="flex items-center gap-1"><span class="text-blue-600">🔵</span> Over (&gt;Target)</span>
                <span class="flex items-center gap-1"><span class="text-gray-400">⚪</span> Unknown</span>
            </div>
        </div>

        @if(count($items) > 0)
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
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Criticality</th>
                            <th class="px-3 py-2 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider text-right w-[80px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($items as $item)
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

                                <!-- Criticality -->
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    @php
                                        $critColor = match($item['criticality']) {
                                            'A' => 'bg-red-50 text-red-700 border-red-200 font-extrabold',
                                            'B' => 'bg-amber-50 text-amber-750 border-amber-200 font-bold',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200 font-medium'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-md text-[10px] border uppercase {{ $critColor }}">
                                        Kelas {{ $item['criticality'] }}
                                    </span>
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
                @foreach($items as $item)
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
        @else
            <!-- Empty State -->
            <div class="py-12">
                <x-empty-state 
                    title="Tidak Ada Data Terpantau" 
                    description="Tidak ada suku cadang terhubung yang cocok dengan kriteria pencarian atau filter Anda." 
                    icon="search_off"
                />
            </div>
        @endif
    </div>
</x-layouts.app>
