<x-layouts.app 
    title="Machine Sparepart Monitor | Sistem MRM"
    topbar-title="Machine Sparepart Monitor"
>
    <!-- Breadcrumbs & Observability Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <x-breadcrumb :items="['Integrasi Sparepart' => '']" />
        
        <!-- Observability Widget -->
        <div class="flex items-center gap-3 bg-surface-container-low border border-outline-variant px-4 py-2 rounded-xl text-body-sm shadow-sm">
            <span class="flex items-center gap-1 text-on-surface-variant font-medium">
                <span class="material-symbols-outlined text-[16px] text-primary" data-icon="sync">sync</span>
                Last Sync: <span class="font-bold text-on-surface">{{ $lastSyncTime }}</span>
            </span>
            <div class="h-4 w-px bg-outline-variant"></div>
            <span class="flex items-center gap-1.5 font-bold">
                WMS Data:
                @if($dataSourceMode === 'Live')
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-green-100 text-green-700 border border-green-200">LIVE</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-700 border border-amber-200">MOCK</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Top Dashboards: Sparepart Health & Machine Mapping Health -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- 1. Machine Mapping Health (Left 1/3) -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-headline-sm text-headline-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" data-icon="hub">hub</span>
                        Machine Mapping Health
                    </h3>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 bg-surface-container-low border border-outline-variant rounded-xl">
                        <span class="block text-[22px] font-extrabold text-on-surface">{{ $totalMachinesCount }}</span>
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Total Mesin</span>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-xl">
                        <span class="block text-[22px] font-extrabold text-green-700">{{ $mappedMachinesCount }}</span>
                        <span class="text-[10px] text-green-800 font-bold uppercase tracking-wider">Mapped</span>
                    </div>
                    <a href="{{ route('spareparts.unmapped-machines') }}" class="block p-3 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition-colors">
                        <span class="block text-[22px] font-extrabold text-red-600">{{ $unmappedMachinesCount }}</span>
                        <span class="text-[10px] text-red-800 font-bold uppercase tracking-wider underline">Unmapped</span>
                    </a>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-outline-variant flex justify-between items-center text-body-sm text-on-surface-variant">
                <span>Kelengkapan Mapping:</span>
                <span class="font-bold text-on-surface">
                    {{ $totalMachinesCount > 0 ? round(($mappedMachinesCount / $totalMachinesCount) * 100) : 0 }}%
                </span>
            </div>
        </div>

        <!-- 2. Sparepart Health Status Summary (Right 2/3) -->
        <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 shadow-sm">
            <h3 class="font-headline-sm text-headline-sm font-bold flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary" data-icon="inventory_2">inventory_2</span>
                Sparepart Stock Health Status
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <!-- Critical -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'critical']) }}" class="p-3 bg-red-50 hover:bg-red-100/70 border border-red-200 rounded-xl flex flex-col justify-between transition-all {{ request('status') === 'critical' ? 'ring-2 ring-red-500' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[18px]">🔴</span>
                        <span class="px-2 py-0.5 rounded bg-red-600 text-white font-bold text-[10px] uppercase">Crit</span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-[22px] font-extrabold text-red-700 leading-none">{{ $statusCounts['critical'] }}</span>
                        <span class="text-[11px] text-red-800 font-bold mt-1 block">Critical</span>
                    </div>
                </a>

                <!-- Reorder -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'reorder']) }}" class="p-3 bg-amber-50 hover:bg-amber-100/70 border border-amber-200 rounded-xl flex flex-col justify-between transition-all {{ request('status') === 'reorder' ? 'ring-2 ring-amber-500' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[18px]">🟠</span>
                        <span class="px-2 py-0.5 rounded bg-amber-500 text-white font-bold text-[10px] uppercase">Reord</span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-[22px] font-extrabold text-amber-700 leading-none">{{ $statusCounts['reorder'] }}</span>
                        <span class="text-[11px] text-amber-800 font-bold mt-1 block">Reorder</span>
                    </div>
                </a>

                <!-- Healthy -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'healthy']) }}" class="p-3 bg-emerald-50 hover:bg-emerald-100/70 border border-emerald-200 rounded-xl flex flex-col justify-between transition-all {{ request('status') === 'healthy' ? 'ring-2 ring-emerald-500' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[18px]">🟢</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px] uppercase">Ok</span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-[22px] font-extrabold text-emerald-700 leading-none">{{ $statusCounts['healthy'] }}</span>
                        <span class="text-[11px] text-emerald-800 font-bold mt-1 block">Healthy</span>
                    </div>
                </a>

                <!-- Overstock -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'overstock']) }}" class="p-3 bg-blue-50 hover:bg-blue-100/70 border border-blue-200 rounded-xl flex flex-col justify-between transition-all {{ request('status') === 'overstock' ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[18px]">🔵</span>
                        <span class="px-2 py-0.5 rounded bg-blue-600 text-white font-bold text-[10px] uppercase">Over</span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-[22px] font-extrabold text-blue-700 leading-none">{{ $statusCounts['overstock'] }}</span>
                        <span class="text-[11px] text-blue-800 font-bold mt-1 block">Overstock</span>
                    </div>
                </a>

                <!-- Unknown -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'unknown']) }}" class="p-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl flex flex-col justify-between transition-all {{ request('status') === 'unknown' ? 'ring-2 ring-gray-400' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[18px]">⚪</span>
                        <span class="px-2 py-0.5 rounded bg-gray-500 text-white font-bold text-[10px] uppercase">N/A</span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-[22px] font-extrabold text-gray-700 leading-none">{{ $statusCounts['unknown'] }}</span>
                        <span class="text-[11px] text-gray-800 font-bold mt-1 block">Unknown</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form Panel -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 shadow-sm mb-6">
        <form method="GET" action="{{ route('spareparts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Search Filter -->
            <div>
                <label class="block text-body-sm font-bold text-on-surface-variant mb-1">Search Item</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ERP Code / Item Name..." class="w-full bg-surface-bright border border-outline-variant px-3 py-2 pl-9 rounded-xl text-body-sm focus:outline-none focus:border-primary">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[18px]">search</span>
                </div>
            </div>

            <!-- Machine Filter -->
            <div>
                <label class="block text-body-sm font-bold text-on-surface-variant mb-1">Machine</label>
                <select name="machine" class="w-full bg-surface-bright border border-outline-variant px-3 py-2 rounded-xl text-body-sm focus:outline-none focus:border-primary">
                    <option value="">-- All Machines --</option>
                    @foreach($allMachines as $m)
                        <option value="{{ $m->id }}" {{ request('machine') == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-body-sm font-bold text-on-surface-variant mb-1">Category</label>
                <select name="category" class="w-full bg-surface-bright border border-outline-variant px-3 py-2 rounded-xl text-body-sm focus:outline-none focus:border-primary">
                    <option value="">-- All Categories --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-body-sm font-bold text-on-surface-variant mb-1">Stock Status</label>
                <select name="status" class="w-full bg-surface-bright border border-outline-variant px-3 py-2 rounded-xl text-body-sm focus:outline-none focus:border-primary">
                    <option value="">-- All Statuses --</option>
                    <option value="critical" {{ request('status') === 'critical' ? 'selected' : '' }}>🔴 Critical</option>
                    <option value="reorder" {{ request('status') === 'reorder' ? 'selected' : '' }}>🟠 Reorder</option>
                    <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>🟢 Healthy</option>
                    <option value="overstock" {{ request('status') === 'overstock' ? 'selected' : '' }}>🔵 Overstock</option>
                    <option value="unknown" {{ request('status') === 'unknown' ? 'selected' : '' }}>⚪ Unknown</option>
                </select>
            </div>

            <!-- Criticality Filter -->
            <div>
                <label class="block text-body-sm font-bold text-on-surface-variant mb-1">Criticality</label>
                <select name="criticality" class="w-full bg-surface-bright border border-outline-variant px-3 py-2 rounded-xl text-body-sm focus:outline-none focus:border-primary">
                    <option value="">-- All Criticalities --</option>
                    <option value="A" {{ request('criticality') === 'A' ? 'selected' : '' }}>Kelas A (Mesin Mati)</option>
                    <option value="B" {{ request('criticality') === 'B' ? 'selected' : '' }}>Kelas B (Produksi Terganggu)</option>
                    <option value="C" {{ request('criticality') === 'C' ? 'selected' : '' }}>Kelas C (Kurang Kritis)</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-2 md:col-span-5 flex justify-end gap-2 mt-2">
                @if(request()->anyFilled(['search', 'machine', 'category', 'status', 'criticality']))
                    <a href="{{ route('spareparts.index') }}" class="px-4 py-2 border border-outline text-on-surface rounded-xl text-body-sm font-bold hover:bg-surface-container-low transition-colors">
                        Reset Filters
                    </a>
                @endif
                <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-body-sm font-bold hover:bg-opacity-90 transition-colors shadow-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span>
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Mapped Spareparts List -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm">
        
        <!-- Header & Legend Table -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" data-icon="list_alt">list_alt</span>
                Monitoring List
            </h3>
            
            <!-- Small Legend Widget -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 bg-surface-container-low border border-outline-variant px-3 py-1.5 rounded-xl text-[11px] font-bold text-on-surface-variant">
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
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[120px]">Status</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[120px]">ERP Code</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Item Name</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[110px] text-center">Stock</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Weekly Avg</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Lead Time</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Min Stock</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Target</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Coverage</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-[90px] text-center">Criticality</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right w-[100px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($items as $item)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <!-- Status Badge -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-label-sm border font-bold flex items-center justify-center gap-1 {{ $item['status']['badge_class'] }}">
                                        <span>{{ $item['status']['icon'] }}</span>
                                        <span>{{ $item['status']['label'] }}</span>
                                    </span>
                                </td>
                                
                                <!-- ERP Code -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="mono text-body-sm font-bold text-primary">{{ $item['erp_code'] }}</span>
                                </td>

                                <!-- Item Name & Category -->
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-body-sm font-bold text-on-surface">{{ $item['name'] }}</span>
                                        <span class="text-[11px] text-on-surface-variant mt-0.5">{{ $item['category'] }} ({{ $item['brand'] }})</span>
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm font-bold text-on-surface">{{ $item['stock'] }} {{ $item['unit'] }}</span>
                                </td>

                                <!-- Weekly Avg -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm text-on-surface font-medium">
                                        {{ !is_null($item['weekly_average']) ? number_format($item['weekly_average'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Lead Time -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm text-on-surface font-medium">{{ $item['lead_time'] }} Hari</span>
                                </td>

                                <!-- Min Stock -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm font-bold {{ !is_null($item['min_stock']) && $item['stock'] < $item['min_stock'] ? 'text-red-600' : 'text-on-surface-variant' }}">
                                        {{ !is_null($item['min_stock']) ? number_format($item['min_stock'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Target -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm text-on-surface-variant font-medium">
                                        {{ !is_null($item['target_stock']) ? number_format($item['target_stock'], 1) : '-' }}
                                    </span>
                                </td>

                                <!-- Coverage -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="text-body-sm text-primary font-bold">{{ $item['coverage'] }} Mesin</span>
                                </td>

                                <!-- Criticality -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    @php
                                        $critColor = match($item['criticality']) {
                                            'A' => 'bg-red-100 text-red-800 border-red-200 font-extrabold',
                                            'B' => 'bg-amber-100 text-amber-800 border-amber-200 font-bold',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200 font-medium'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-label-sm border uppercase {{ $critColor }}">
                                        Kelas {{ $item['criticality'] }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('spareparts.show', $item['erp_code']) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary-container text-primary text-label-md font-bold hover:bg-primary hover:text-on-primary transition-all">
                                        Buka Detail
                                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Visible on small screens) -->
            <div class="block md:hidden space-y-4">
                @foreach($items as $item)
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-2xl flex flex-col justify-between gap-4">
                        <div class="flex items-start justify-between">
                            <div class="flex flex-col">
                                <span class="mono text-body-sm font-bold text-primary leading-none">{{ $item['erp_code'] }}</span>
                                <h4 class="text-body-md font-bold text-on-surface mt-1.5">{{ $item['name'] }}</h4>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="px-2 py-0.5 rounded-full text-[10px] border font-bold flex items-center gap-1 {{ $item['status']['badge_class'] }}">
                                <span>{{ $item['status']['icon'] }}</span>
                                <span>{{ $item['status']['label'] }}</span>
                            </span>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-2 text-body-sm py-2 border-y border-outline-variant border-dashed">
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
                            <a href="{{ route('spareparts.show', $item['erp_code']) }}" class="inline-flex items-center gap-1 px-4 py-2 bg-primary text-on-primary text-label-sm font-bold rounded-xl shadow-sm">
                                Detail
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
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
