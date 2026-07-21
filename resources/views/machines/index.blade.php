@php
    if (!function_exists('getSortUrl')) {
        function getSortUrl($column, $currentSortBy, $currentSortOrder) {
            $params = request()->query();
            $params['sort_by'] = $column;
            $params['sort_order'] = ($currentSortBy === $column && $currentSortOrder === 'asc') ? 'desc' : 'asc';
            return route('machines.index', $params);
        }
    }

    $critLabels = [
        'mission_critical' => 'Sangat Kritis',
        'high' => 'Tinggi',
        'medium' => 'Sedang',
        'low' => 'Rendah'
    ];

    $statusLabels = [
        'running' => 'Beroperasi',
        'idle' => 'Tidak Beroperasi',
        'maintenance' => 'Dalam Perawatan',
        'breakdown' => 'Rusak',
        'stopped' => 'Berhenti'
    ];

    // Define sorting order for lifecycle status
    $lifecycleOrder = [
        'ACTIVE' => 1,
        'INACTIVE' => 2,
        'RETIRED' => 3
    ];

    // Determine custom sort parameters
    $customSortBy = request()->input('sort_by');
    $customSortOrder = request()->input('sort_order', 'asc');
@endphp

<x-layouts.app 
    title="Daftar Mesin | Sistem MRM"
    topbar-title="Daftar Mesin"
    :subnav="['Aktif' => route('machines.index', ['status_filter' => 'ACTIVE']), 'Nonaktif' => route('machines.index', ['status_filter' => 'INACTIVE']), 'Pensiun' => route('machines.index', ['status_filter' => 'RETIRED']), 'Semua' => route('machines.index', ['status_filter' => 'ALL'])]"
    active-subnav="{{ $statusFilter === 'ACTIVE' ? 'Aktif' : ($statusFilter === 'INACTIVE' ? 'Nonaktif' : ($statusFilter === 'RETIRED' ? 'Pensiun' : 'Semua')) }}"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Daftar Mesin' => route('machines.index')]" />

    <x-page-header title="Daftar Mesin" subtitle="Total: {{ $machines->total() }} Unit Peralatan Terdaftar" class="mb-6">
        <x-slot name="right">
            <x-button variant="primary" icon="add" href="{{ route('machines.create') }}">
                Tambah Mesin Baru
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-12 gap-6">
        <!-- Main Registry Table -->
        <div class="col-span-12 bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
            
            <!-- Filters & Search Form -->
            <div class="px-4 py-3 border-b border-outline-variant bg-surface-container-low">
                <form method="GET" action="{{ route('machines.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-2.5 w-full">
                    <!-- Keep current sort parameter -->
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}"/>
                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}"/>
                    <input type="hidden" name="status_filter" value="{{ $statusFilter }}"/>

                    <!-- Search bar -->
                    <div class="flex-grow min-w-[200px]">
                        <label class="block text-label-sm font-label-sm text-on-surface-variant uppercase mb-1">Cari Kata Kunci</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                            <input name="search" value="{{ request('search') }}" class="w-full pl-9 pr-3 py-1.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" placeholder="Kode, Nama..." type="text"/>
                        </div>
                    </div>
                    
                    <!-- Department Filter -->
                    <div class="w-full lg:w-44">
                        <label class="block text-label-sm font-label-sm text-on-surface-variant uppercase mb-1">Departemen</label>
                        <select name="department" class="w-full px-2.5 py-1.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="w-full lg:w-40">
                        <label class="block text-label-sm font-label-sm text-on-surface-variant uppercase mb-1">Kategori</label>
                        <select name="category" class="w-full px-2.5 py-1.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Criticality Filter -->
                    <div class="w-full lg:w-40">
                        <label class="block text-label-sm font-label-sm text-on-surface-variant uppercase mb-1">Prioritas</label>
                        <select name="criticality" class="w-full px-2.5 py-1.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            <option value="">Semua Prioritas</option>
                            @foreach($criticalities as $crit)
                                <option value="{{ $crit }}" {{ request('criticality') === $crit ? 'selected' : '' }}>{{ $critLabels[$crit] ?? ucfirst($crit) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full lg:w-40">
                        <label class="block text-label-sm font-label-sm text-on-surface-variant uppercase mb-1">Status Mesin</label>
                        <select name="operational_status" class="w-full px-2.5 py-1.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            <option value="">Semua Status</option>
                            @foreach($operationalStatuses as $status)
                                <option value="{{ $status }}" {{ request('operational_status') === $status ? 'selected' : '' }}>{{ $statusLabels[$status] ?? ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-1.5 self-stretch lg:self-auto">
                        <button type="submit" class="bg-primary text-on-primary hover:bg-primary/90 px-3 py-1.5 rounded-lg flex items-center justify-center shadow-sm" title="Terapkan Filter">
                            <span class="material-symbols-outlined text-[20px]">filter_list</span>
                        </button>
                        @if(request()->anyFilled(['search', 'department', 'category', 'criticality', 'operational_status']))
                            <a href="{{ route('machines.index') }}" class="bg-surface-container border border-outline-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg flex items-center justify-center" title="Reset Filter">
                                <span class="material-symbols-outlined text-[20px]">filter_list_off</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Dynamic Machine Table List -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-bright border-b border-outline-variant">
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-16">Foto</th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <a href="{{ getSortUrl('code', $sortBy, $sortOrder) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Kode Mesin
                                    @if($sortBy === 'code')
                                        <span class="material-symbols-outlined text-[16px]">{{ $sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Nama Mesin</th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <a href="{{ getSortUrl('department', $sortBy, $sortOrder) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Departemen / Area
                                    @if($sortBy === 'department')
                                        <span class="material-symbols-outlined text-[16px]">{{ $sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <a href="{{ getSortUrl('criticality', $sortBy, $sortOrder) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Prioritas
                                    @if($sortBy === 'criticality')
                                        <span class="material-symbols-outlined text-[16px]">{{ $sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <a href="{{ getSortUrl('operational_status', $sortBy, $sortOrder) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Status Mesin
                                    @if($sortBy === 'operational_status')
                                        <span class="material-symbols-outlined text-[16px]">{{ $sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <a href="{{ getSortUrl('health', $sortBy, $sortOrder) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Kondisi
                                    @if($sortBy === 'health')
                                        <span class="material-symbols-outlined text-[16px]">{{ $sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status PM</th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status Sparepart</th>
                            <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($machines as $machine)
                            @php
                                $isMachineActive = $machine->lifecycle_status === 'ACTIVE';
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors border-b border-outline-variant @if(!$isMachineActive) opacity-60 text-slate-500 bg-surface-container-low/20 italic @endif">
                                <!-- Machine Photo -->
                                <td class="px-4 py-2.5">
                                    @php
                                        $primaryPhoto = $machine->photos->where('type', 'overall')->first();
                                    @endphp
                                    @if($primaryPhoto && $primaryPhoto->file_path)
                                        <img src="{{ asset($primaryPhoto->file_path) }}" alt="{{ $machine->name }}" class="w-8 h-8 object-cover rounded border border-outline-variant shadow-sm"/>
                                    @else
                                        <div class="w-8 h-8 rounded border border-outline-variant bg-surface-container flex items-center justify-center text-on-surface-variant" title="Dokumen Belum Diunggah">
                                            <span class="material-symbols-outlined text-[16px]">image_not_supported</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Code -->
                                <td class="px-4 py-2.5 mono text-body-sm font-bold @if($isMachineActive) text-primary @else text-slate-500 @endif">
                                    {{ $machine->code }}
                                </td>

                                <!-- Name -->
                                <td class="px-4 py-2.5 font-body-md font-semibold @if($isMachineActive) text-on-surface @else text-slate-500 @endif">
                                    {{ $machine->name }}
                                </td>

                                <!-- Department & Area -->
                                <td class="px-4 py-2.5 font-body-sm">
                                    <span class="font-semibold block @if($isMachineActive) text-on-surface @else text-slate-500 @endif">{{ $machine->department }}</span>
                                    <span class="@if($isMachineActive) text-on-surface-variant @else text-slate-500/80 @endif text-xs">
                                        @if($machine->productionArea)
                                            {{ $machine->productionArea->name }}@if($machine->production_area) - {{ $machine->production_area }}@endif
                                        @else
                                            {{ $machine->production_area }}
                                        @endif
                                    </span>
                                </td>

                                <!-- Criticality -->
                                <td class="px-4 py-2.5">
                                    <x-status-badge :type="!$isMachineActive ? 'low' : $machine->criticality" :label="!$isMachineActive ? ($critLabels[$machine->criticality] ?? $machine->criticality) : null" />
                                </td>

                                <!-- Operational Status -->
                                <td class="px-4 py-2.5">
                                    <x-status-badge :type="!$isMachineActive ? 'low' : $machine->operational_status" :label="!$isMachineActive ? ($statusLabels[$machine->operational_status] ?? $machine->operational_status) : null" />
                                </td>

                                <!-- Health Score (Calculated, not persisted) -->
                                <td class="px-4 py-2.5">
                                    <x-health-score :score="$machine->health_score" type="bar" size="max-w-[80px]" />
                                </td>

                                <!-- PM Status (Placeholder) -->
                                <td class="px-4 py-2.5">
                                    @php
                                        // Mocks based on operational status
                                        $pmType = match($machine->operational_status) {
                                            'breakdown' => 'danger',
                                            'maintenance' => 'warning',
                                            default => 'success'
                                        };
                                        $pmLabel = match($machine->operational_status) {
                                            'breakdown' => 'Terlambat',
                                            'maintenance' => 'Dalam Proses',
                                            default => 'Sesuai Jadwal'
                                        };
                                    @endphp
                                    @if($isMachineActive)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold py-0.5 px-2 rounded @if($pmType === 'danger') text-red-700 bg-red-50 @elseif($pmType === 'warning') text-blue-700 bg-blue-50 @else text-green-700 bg-green-50 @endif">
                                            {{ $pmLabel }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold py-0.5 px-2 rounded text-slate-600 bg-slate-100">
                                            {{ $pmLabel }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Sparepart Readiness (Placeholder) -->
                                <td class="px-4 py-2.5">
                                    @php
                                        // Mock readiness based on code
                                        $readinessLabel = match($machine->code) {
                                            'CNC-08' => 'Tersedia',
                                            'CNC-04' => 'Peringatan Stok',
                                            default => 'Tersedia'
                                        };
                                        $readinessColor = match($machine->code) {
                                            'CNC-04' => 'text-orange-700 bg-orange-50',
                                            default => 'text-green-700 bg-green-50'
                                        };
                                    @endphp
                                    @if($isMachineActive)
                                        <span class="inline-flex items-center text-xs font-semibold py-0.5 px-2 rounded {{ $readinessColor }}">
                                            {{ $readinessLabel }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs font-semibold py-0.5 px-2 rounded text-slate-600 bg-slate-100">
                                            {{ $readinessLabel }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Passport Button -->
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <x-button variant="secondary" icon="chevron_right" href="{{ route('machines.show', $machine->code) }}" class="p-1 px-2.5 text-xs font-semibold">
                                        Paspor
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <x-empty-state 
                                        title="Mesin Tidak Ditemukan"
                                        description="Kami tidak dapat menemukan mesin yang sesuai dengan pencarian atau filter Anda. Silakan ubah kata kunci pencarian atau filter."
                                        icon="precision_manufacturing"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-4 py-3 border-t border-outline-variant bg-surface-container-low">
                {{ $machines->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
