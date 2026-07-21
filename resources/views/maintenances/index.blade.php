<x-layouts.app 
    title="Pelaksanaan Perawatan | Sistem MRM"
    topbar-title="Pelaksanaan Perawatan"
    :subnav="[
        'Semua Eksekusi' => route('maintenances.index'),
        'Hari Ini' => route('planning.index', ['status' => 'approved']),
        'Menunggu Review' => route('planning.index', ['readiness_status' => 'Waiting Review']),
        'Riwayat Eksekusi' => route('planning.index', ['readiness_status' => 'Completed'])
    ]"
    active-subnav="Semua Eksekusi"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pelaksanaan Perawatan' => route('maintenances.index')]" />

    <x-page-header title="Semua Eksekusi Perawatan" subtitle="Log tindakan pelaksanaan perawatan peralatan manufaktur" class="mb-6">
    </x-page-header>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
                <div class="relative w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md" placeholder="Cari berdasarkan Mesin, Teknisi, Tipe..." type="text"/>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-bright border-b border-outline-variant">
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Aset</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Dilakukan Oleh</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Biaya</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <!-- Row 1 -->
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 mono text-body-sm font-bold text-primary">CNC-08</td>
                            <td class="px-6 py-4 font-body-md font-semibold">Terjadwal</td>
                            <td class="px-6 py-4 font-body-sm text-on-surface-variant">Penggantian kipas pendingin dan kalibrasi</td>
                            <td class="px-6 py-4 font-body-sm">R. Miller</td>
                            <td class="px-6 py-4 font-body-sm font-bold">$150.00</td>
                            <td class="px-6 py-4 font-body-sm">10 Okt 2023</td>
                            <td class="px-6 py-4">
                                <x-status-badge type="success" label="Selesai" />
                            </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 mono text-body-sm font-bold text-primary">CNC-04</td>
                            <td class="px-6 py-4 font-body-md font-semibold text-error">Korektif</td>
                            <td class="px-6 py-4 font-body-sm text-on-surface-variant">Perbaikan darurat: spindle utama mengalami overheating</td>
                            <td class="px-6 py-4 font-body-sm">R. Thompson</td>
                            <td class="px-6 py-4 font-body-sm font-bold">$420.00</td>
                            <td class="px-6 py-4 font-body-sm">02 Okt 2023</td>
                            <td class="px-6 py-4">
                                <x-status-badge type="success" label="Selesai" />
                            </td>
                        </tr>
                        <!-- Row 3 -->
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 mono text-body-sm font-bold text-primary">ARM-12</td>
                            <td class="px-6 py-4 font-body-md font-semibold text-tertiary">Prediktif</td>
                            <td class="px-6 py-4 font-body-sm text-on-surface-variant">Pemeriksaan dan penyelarasan sabuk terjadwal</td>
                            <td class="px-6 py-4 font-body-sm">S. Chen</td>
                            <td class="px-6 py-4 font-body-sm font-bold">$75.00</td>
                            <td class="px-6 py-4 font-body-sm">24 Nov 2023</td>
                            <td class="px-6 py-4">
                                <x-status-badge type="warning" label="Terjadwal" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
