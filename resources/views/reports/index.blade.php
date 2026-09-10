<x-layouts.app 
    title="Laporan Umum Maintenance | MRM System"
    topbar-title="Laporan Umum"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Laporan' => '', 'Laporan Umum' => '']" />

    <!-- Page Header -->
    <div class="mb-5">
        <h1 class="font-headline-md text-headline-md font-bold text-on-surface">Laporan Umum Maintenance</h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Ringkasan operasional kasus perbaikan dan pemeliharaan mesin</p>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 md:p-5 shadow-sm mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-4">
            <!-- Start Date -->
            <div class="flex-1 min-w-[160px]">
                <label for="start_date" class="block text-xs font-semibold text-on-surface-variant mb-1.5 uppercase tracking-wide">Tanggal Mulai</label>
                <input 
                    type="date" 
                    id="start_date" 
                    name="start_date" 
                    value="{{ $meta['start_date'] }}"
                    class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest"
                    required
                >
            </div>

            <!-- End Date -->
            <div class="flex-1 min-w-[160px]">
                <label for="end_date" class="block text-xs font-semibold text-on-surface-variant mb-1.5 uppercase tracking-wide">Tanggal Selesai</label>
                <input 
                    type="date" 
                    id="end_date" 
                    name="end_date" 
                    value="{{ $meta['end_date'] }}"
                    class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest"
                    required
                >
            </div>

            <!-- Department Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="department" class="block text-xs font-semibold text-on-surface-variant mb-1.5 uppercase tracking-wide">Departemen</label>
                <select 
                    id="department" 
                    name="department" 
                    class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest cursor-pointer"
                >
                    <option value="all" {{ $meta['department'] === 'all' ? 'selected' : '' }}>Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->name }}" {{ (strcasecmp($meta['department'], $dept->name) === 0 || strcasecmp($meta['department'], $dept->code) === 0) ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center gap-2">
                <button 
                    type="submit" 
                    class="bg-primary hover:bg-primary-container text-on-primary font-semibold px-5 py-2 rounded-lg text-sm transition-all shadow-sm flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                    <span>FILTER</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Report Area -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
        <!-- Summary & Actions Bar -->
        <div class="p-4 md:p-5 border-b border-outline-variant bg-surface-container-low/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs md:text-sm text-on-surface">
                <div>
                    <span class="text-on-surface-variant font-medium">Periode:</span>
                    <span class="font-bold ml-1 text-primary">{{ $meta['start_date_formatted'] }} &mdash; {{ $meta['end_date_formatted'] }}</span>
                </div>
                <div>
                    <span class="text-on-surface-variant font-medium">Departemen:</span>
                    <span class="font-bold ml-1 text-on-surface">{{ $meta['department_label'] }}</span>
                </div>
                <div>
                    <span class="text-on-surface-variant font-medium">Total:</span>
                    <span class="font-bold ml-1 text-on-surface">{{ $meta['total_cases'] }} Maintenance</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Excel Export -->
                <a 
                    href="{{ route('reports.export.excel', request()->all()) }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors"
                    title="Export ke format Excel / CSV"
                >
                    <span class="material-symbols-outlined text-[16px]">table_view</span>
                    <span>Export Excel</span>
                </a>

                <!-- PDF Export (inline view) -->
                <a 
                    href="{{ route('reports.export.pdf', request()->all()) }}" 
                    target="_blank" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors"
                    title="Export / Cetak Dokumen PDF Resmi"
                >
                    <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-32">Tanggal</th>
                        <th class="py-3 px-4 w-48">Mesin</th>
                        <th class="py-3 px-4">Deskripsi Masalah / Keluhan</th>
                        <th class="py-3 px-4 w-36">Diperbaiki</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant text-sm">
                    @forelse ($plans as $index => $plan)
                        @php
                            $incidentDate = $plan->reported_at 
                                ? $plan->reported_at->format('d M Y') 
                                : ($plan->scheduled_date ? $plan->scheduled_date->format('d M Y') : $plan->created_at->format('d M Y'));

                            $machineCode = $plan->machine->code ?? '-';
                            $machineName = $plan->machine->name ?? '-';

                            $problemDescription = $plan->notes;
                            if (empty($problemDescription)) {
                                $problemDescription = $plan->maintenanceTemplate ? $plan->maintenanceTemplate->name : 'Paket Perawatan Preventif';
                            }

                            $repairDate = $plan->actual_completion 
                                ? $plan->actual_completion->format('d M Y') 
                                : ($plan->completed_at ? $plan->completed_at->format('d M Y') : '-');

                            $repairTime = $plan->actual_completion 
                                ? $plan->actual_completion->format('H:i') 
                                : ($plan->completed_at ? $plan->completed_at->format('H:i') : '');
                        @endphp
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <!-- Column 1: No -->
                            <td class="py-3.5 px-4 text-center font-mono text-xs text-on-surface-variant">
                                {{ $index + 1 }}
                            </td>

                            <!-- Column 2: Tanggal -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-medium text-on-surface text-xs md:text-sm">
                                {{ $incidentDate }}
                            </td>

                            <!-- Column 3: Mesin (2 lines: Code + Name) -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-xs text-primary">{{ $machineCode }}</div>
                                <div class="text-xs text-on-surface-variant leading-tight">{{ $machineName }}</div>
                            </td>

                            <!-- Column 4: Deskripsi Masalah / Keluhan -->
                            <td class="py-3.5 px-4 text-on-surface leading-relaxed text-xs md:text-sm">
                                {{ $problemDescription }}
                            </td>

                            <!-- Column 5: Diperbaiki (2 lines: Date + visually smaller Time) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-xs md:text-sm text-on-surface">{{ $repairDate }}</div>
                                @if ($repairTime)
                                    <div class="text-[11px] font-mono text-on-surface-variant leading-tight">{{ $repairTime }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-4 text-center text-on-surface-variant text-sm italic">
                                Tidak ada data maintenance pada periode dan departemen yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
