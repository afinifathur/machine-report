<x-layouts.app 
    title="Daftar Pengadaan Khusus | Sistem MRM"
    topbar-title="Pengadaan Khusus"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pengadaan Khusus' => '']" />

    <x-page-header title="Daftar Pengadaan Khusus" subtitle="Permintaan Pembelian Suku Cadang Non-Rutin" class="mb-6">
        @can('create', App\Models\ProcurementCase::class)
            <a href="{{ route('procurements.create') }}" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Buat Request Baru
            </a>
        @endcan
    </x-page-header>

    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-container text-on-secondary-container border border-outline-variant rounded-xl text-body-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container border-b border-outline-variant text-label-md font-label-md text-on-surface font-semibold">
                        <th class="px-6 py-4">Nomor Case</th>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4">Mesin</th>
                        <th class="px-6 py-4">Urgensi</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Current Owner</th>
                        <th class="px-6 py-4">Tanggal Buat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($cases as $case)
                        <tr class="hover:bg-surface-bright text-body-md text-sm text-on-surface">
                            <td class="px-6 py-4 font-semibold mono text-primary">{{ $case->case_number }}</td>
                            <td class="px-6 py-4 font-medium">{{ $case->item_name }}</td>
                            <td class="px-6 py-4">{{ $case->machine->name }} ({{ $case->machine->code }})</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $case->urgency->value === 'emergency' ? 'bg-error-container text-on-error-container border border-error' : '' }}
                                    {{ $case->urgency->value === 'urgent' ? 'bg-tertiary-fixed text-on-tertiary-fixed' : '' }}
                                    {{ $case->urgency->value === 'normal' ? 'bg-secondary-container text-on-secondary-fixed-variant' : '' }}
                                ">
                                    {{ strtoupper($case->urgency->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-container-high text-on-surface-variant border border-outline-variant">
                                    {{ str_replace('_', ' ', strtoupper($case->status->value)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold">{{ $case->current_owner }}</td>
                            <td class="px-6 py-4 text-xs opacity-75">{{ $case->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('procurements.show', $case->id) }}" class="text-primary hover:underline text-sm font-semibold px-2 py-1">Detail</a>
                                    @can('update', $case)
                                        <a href="{{ route('procurements.edit', $case->id) }}" class="text-secondary hover:underline text-sm font-semibold px-2 py-1">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant text-body-md">
                                Tidak ada data pengadaan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cases->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant">
                {{ $cases->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
