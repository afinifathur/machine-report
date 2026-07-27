<x-layouts.app 
    title="Unmapped Machines | Sistem MRM"
    topbar-title="Unmapped Machines"
>
    <!-- Breadcrumbs -->
    <div class="mb-6">
        <x-breadcrumb :items="['Integrasi Sparepart' => route('spareparts.index'), 'Mesin Tanpa Mapping' => '']" />
    </div>

    <!-- Intro Banner -->
    <header class="mb-8 p-6 bg-gradient-to-r from-primary to-primary-container text-on-primary rounded-2xl shadow-sm border border-outline-variant relative overflow-hidden">
        <div class="z-10 relative">
            <span class="font-label-md text-label-md opacity-80 uppercase tracking-wider">Clinical Completeness</span>
            <h1 class="font-headline-md text-headline-md mt-1 font-bold">Mesin Tanpa Mapping Sparepart</h1>
            <p class="font-body-md text-body-md mt-2 opacity-90">Halaman ini menampilkan daftar seluruh mesin aktif yang belum memiliki pemetaan (mapping) kebutuhan sparepart. Hubungkan minimal satu sparepart penting pada Paspor Mesin agar monitor ketersediaan dapat berjalan dengan benar.</p>
        </div>
        <div class="absolute -right-16 -top-16 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    </header>

    <!-- Unmapped Machines List -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600" data-icon="report_problem">report_problem</span>
                Daftar Mesin ({{ $unmappedMachines->count() }} Mesin)
            </h3>
        </div>

        @if($unmappedMachines->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-bright border-b border-outline-variant">
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kode Mesin</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Nama Mesin</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Lokasi / Dept</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status Operasional</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Health Score</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($unmappedMachines as $machine)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <!-- Code -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="mono text-body-sm font-bold text-primary">{{ $machine->code }}</span>
                                </td>

                                <!-- Name -->
                                <td class="px-4 py-4">
                                    <span class="text-body-sm font-bold text-on-surface">{{ $machine->name }}</span>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-body-sm text-on-surface-variant font-medium">{{ $machine->location }}</span>
                                </td>

                                <!-- Operational Status -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @php
                                        $opClass = match ($machine->operational_status) {
                                            'running' => 'bg-green-100 text-green-700 border-green-200',
                                            'idle' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'maintenance' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            default => 'bg-red-100 text-red-700 border-red-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-label-sm border font-bold uppercase {{ $opClass }}">
                                        {{ $machine->operational_status }}
                                    </span>
                                </td>

                                <!-- Health Score -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <x-health-score :score="$machine->health_score" type="badge" />
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('machines.show', $machine->code) }}#spareparts" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary text-on-primary text-label-md font-bold hover:bg-opacity-90 transition-all">
                                        <span class="material-symbols-outlined text-[16px]">link</span>
                                        Hubungkan Sparepart
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Empty state when all machines mapped -->
            <x-empty-state 
                title="Semua Mesin Sudah Terpetakan!" 
                description="Luar biasa! Seluruh mesin aktif Anda saat ini sudah memiliki pemetaan suku cadang terhubung." 
                icon="check_circle"
            />
        @endif
    </div>
</x-layouts.app>
