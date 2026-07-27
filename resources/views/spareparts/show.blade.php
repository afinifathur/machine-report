<x-layouts.app 
    title="Sparepart Passport | Sistem MRM"
    topbar-title="Sparepart Passport"
>
    <!-- Breadcrumbs -->
    <div class="mb-6">
        <x-breadcrumb :items="['Integrasi Sparepart' => route('spareparts.index'), $dto->erpCode => '']" />
    </div>

    <!-- Main Grid: Passport Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Item Digital Identity Card (1/3 width) -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm">
            <div class="text-center pb-6 border-b border-outline-variant">
                <!-- Status Badge -->
                <div class="inline-flex mb-4">
                    <span class="px-3 py-1 rounded-full text-label-md border font-bold flex items-center gap-1.5 {{ $statusInfo['badge_class'] }}">
                        <span>{{ $statusInfo['icon'] }}</span>
                        <span>{{ $statusInfo['label'] }}</span>
                    </span>
                </div>
                
                <h2 class="mono font-headline-sm text-headline-sm font-extrabold text-primary leading-tight">{{ $dto->erpCode }}</h2>
                <h3 class="font-headline-sm text-headline-sm mt-2 font-bold text-on-surface leading-snug">{{ $dto->name }}</h3>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ $dto->category }} &bull; {{ $dto->brand }}</p>
            </div>

            <!-- Identity Attribute List -->
            <div class="mt-6 space-y-4">
                <div class="flex justify-between items-center text-body-sm py-1 border-b border-outline-variant border-dashed">
                    <span class="text-on-surface-variant">Rack Location</span>
                    <span class="font-bold text-on-surface">{{ $dto->location }}</span>
                </div>
                <div class="flex justify-between items-center text-body-sm py-1 border-b border-outline-variant border-dashed">
                    <span class="text-on-surface-variant">Primary Supplier</span>
                    <span class="font-bold text-on-surface">{{ $dto->supplier }}</span>
                </div>
                <div class="flex justify-between items-center text-body-sm py-1 border-b border-outline-variant border-dashed">
                    <span class="text-on-surface-variant">Barcode</span>
                    <span class="mono text-on-surface font-semibold">{{ $dto->barcode }}</span>
                </div>
                <div class="flex justify-between items-center text-body-sm py-1">
                    <span class="text-on-surface-variant">Last WMS Sync</span>
                    <span class="font-bold text-on-surface-variant text-[11px]">{{ $lastSyncTime }}</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-outline-variant">
                <div class="flex items-center justify-between text-body-sm">
                    <span class="text-on-surface-variant">Data Mode:</span>
                    @if($dataSourceMode === 'Live')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">LIVE WMS</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">MOCK WMS</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Stocks, Metrics, Machines (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Stock & Usage Metrics (Passport Right Panel) -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-none">
                <h3 class="font-headline-sm text-headline-sm font-bold flex items-center gap-2 mb-3.5 pb-2 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary" data-icon="bar_chart">bar_chart</span>
                    Stock Monitoring & Usage Metrics
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-2.5 text-body-sm">
                    <!-- Stock -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Stock</span>
                        <span class="font-bold text-on-surface">{{ $dto->stock }} {{ $dto->unit }}</span>
                    </div>

                    <!-- Weekly (28d) -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Weekly (28d)</span>
                        <span class="font-bold text-on-surface">
                            {{ !is_null($dto->weeklyAverage) ? number_format($dto->weeklyAverage, 2) : '-' }}
                        </span>
                    </div>

                    <!-- Monthly (90d) -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Monthly (90d)</span>
                        <span class="font-bold text-on-surface">
                            {{ !is_null($dto->monthlyAverage) ? number_format($dto->monthlyAverage, 2) : '-' }}
                        </span>
                    </div>

                    <!-- 6 Month (180d) -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">6 Month (180d)</span>
                        <span class="font-bold text-on-surface">
                            {{ !is_null($dto->sixMonthAverage) ? number_format($dto->sixMonthAverage, 2) : '-' }}
                        </span>
                    </div>

                    <!-- Trend -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Trend</span>
                        @if($dto->trend === 'Increasing')
                            <span class="font-bold text-green-600">↑ Increasing</span>
                        @elseif($dto->trend === 'Decreasing')
                            <span class="font-bold text-red-650">↓ Decreasing</span>
                        @elseif($dto->trend === 'Stable')
                            <span class="font-bold text-on-surface">→ Stable</span>
                        @else
                            <span class="font-medium text-on-surface-variant">-</span>
                        @endif
                    </div>

                    <!-- Minimum -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Minimum</span>
                        <span class="font-bold {{ !is_null($statusInfo['min_stock']) && $dto->stock < $statusInfo['min_stock'] ? 'text-red-650' : 'text-on-surface' }}">
                            {{ !is_null($statusInfo['min_stock']) ? number_format($statusInfo['min_stock'], 1) : '-' }}
                        </span>
                    </div>

                    <!-- Target -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Target</span>
                        <span class="font-bold text-on-surface">
                            {{ !is_null($statusInfo['target_stock']) ? number_format($statusInfo['target_stock'], 1) : '-' }}
                        </span>
                    </div>

                    <!-- Lead Time -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Lead Time</span>
                        <span class="font-bold text-on-surface">{{ $maxLeadTime }} Hari</span>
                    </div>

                    <!-- Coverage -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Coverage</span>
                        <span class="font-bold text-primary">{{ $mappings->count() }} Mesin</span>
                    </div>

                    <!-- Criticality -->
                    <div class="flex justify-between items-center py-1 border-b border-outline-variant border-dashed">
                        <span class="text-on-surface-variant font-medium">Criticality</span>
                        @php
                            $critLabel = match($criticalityVal) {
                                'A' => 'Kelas A',
                                'B' => 'Kelas B',
                                default => 'Kelas C'
                            };
                        @endphp
                        <span class="font-bold text-on-surface uppercase">{{ $critLabel }}</span>
                    </div>
                </div>
            </div>

            <!-- Connected Machines Section -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-none">
                <h3 class="font-headline-sm text-headline-sm font-bold flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary" data-icon="precision_manufacturing">precision_manufacturing</span>
                    Digunakan oleh Mesin (Coverage)
                </h3>
                <p class="text-body-sm text-on-surface-variant mb-4">Daftar mesin aktif yang membutuhkan pemakaian suku cadang ini secara periodik:</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($mappings as $map)
                        @if($map->machine)
                            <a href="{{ route('machines.show', $map->machine->code) }}" class="p-4 border border-outline-variant rounded-xl flex items-center justify-between hover:border-primary hover:shadow-sm transition-all group">
                                <div class="flex flex-col">
                                    <span class="mono text-body-sm font-bold text-primary">{{ $map->machine->code }}</span>
                                    <span class="text-body-md font-bold text-on-surface mt-1 group-hover:text-primary transition-colors">{{ $map->machine->name }}</span>
                                    <span class="text-[11px] text-on-surface-variant mt-0.5">Qty / Mesin: <span class="font-bold text-on-surface">{{ $map->qty_per_machine }} PCS</span></span>
                                </div>
                                <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors" data-icon="arrow_forward">arrow_forward</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- History Placeholder Section -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm">
                <h3 class="font-headline-sm text-headline-sm font-bold flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-on-surface-variant" data-icon="history">history</span>
                    Riwayat Pergantian Suku Cadang (MTBF/MTTR)
                </h3>
                <div class="p-4 bg-surface-container-low border border-outline-variant border-dashed rounded-xl text-center text-on-surface-variant italic text-body-sm">
                    Belum tersedia.
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
