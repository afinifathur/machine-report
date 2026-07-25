<x-layouts.app 
    title="Daftar Pengadaan Khusus | Sistem MRM"
    topbar-title="Pengadaan Khusus"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pengadaan Khusus' => '']" />

    <!-- Page Header -->
    <x-page-header title="Daftar Pengadaan Khusus" subtitle="Permintaan Pembelian Suku Cadang Non-Rutin" class="mb-4 md:mb-6">
        <x-slot:right>
            @can('create', App\Models\ProcurementCase::class)
                <a href="{{ route('procurements.create') }}" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2.5 md:px-5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span class="hidden sm:inline">Buat Pengadaan Baru</span>
                    <span class="sm:hidden">Buat Baru</span>
                </a>
            @endcan
        </x-slot:right>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-4 bg-secondary-container text-on-secondary-container border border-outline-variant rounded-xl text-body-sm shadow-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Workflow Timeline Card — horizontal scroll on all sizes -->
    <div class="mb-4 md:mb-6 bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary">route</span>
            <h4 class="font-semibold text-sm text-on-surface">Alur Pengadaan Khusus</h4>
        </div>
        <div class="overflow-x-auto hide-scrollbar">
            <div class="flex items-center gap-2 text-xs font-semibold text-on-surface-variant min-w-max pb-1">
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-outline"></span> Draft
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Submit (Kabag)
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Approval Kabag
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Approval Direktur
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Purchasing
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Waiting Delivery
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Ready Pickup
                </div>
                <span class="material-symbols-outlined opacity-50">chevron_right</span>
                <div class="px-3 py-1.5 rounded-lg bg-success-container text-on-success-container border border-green-500 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-700"></span> Closed / Selesai
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards — 2-col on mobile, 5-col on desktop -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-4 md:mb-6">
        <!-- Draft -->
        <a href="{{ route('procurements.index', array_merge(request()->except('page'), ['status_group' => 'draft', 'status' => ''])) }}" 
           class="p-3 md:p-4 rounded-xl border transition-all flex items-center gap-3 shadow-sm
           {{ request('status_group') === 'draft' ? 'bg-primary-fixed text-on-primary-fixed border-primary' : 'bg-surface-container-lowest hover:bg-surface-bright border-outline-variant text-on-surface' }}">
            <span class="material-symbols-outlined text-[28px] md:text-[32px] {{ request('status_group') === 'draft' ? 'text-primary' : 'text-secondary' }}">edit_note</span>
            <div>
                <p class="text-xs font-semibold opacity-75">Draft</p>
                <h3 class="text-xl font-bold">{{ $draftCount }}</h3>
            </div>
        </a>
        <!-- Pending Approval -->
        <a href="{{ route('procurements.index', array_merge(request()->except('page'), ['status_group' => 'pending_approval', 'status' => ''])) }}" 
           class="p-3 md:p-4 rounded-xl border transition-all flex items-center gap-3 shadow-sm
           {{ request('status_group') === 'pending_approval' ? 'bg-primary-fixed text-on-primary-fixed border-primary' : 'bg-surface-container-lowest hover:bg-surface-bright border-outline-variant text-on-surface' }}">
            <span class="material-symbols-outlined text-[28px] md:text-[32px] {{ request('status_group') === 'pending_approval' ? 'text-primary' : 'text-secondary' }}">gavel</span>
            <div>
                <p class="text-xs font-semibold opacity-75">Pending</p>
                <h3 class="text-xl font-bold">{{ $pendingCount }}</h3>
            </div>
        </a>
        <!-- Processing -->
        <a href="{{ route('procurements.index', array_merge(request()->except('page'), ['status_group' => 'processing', 'status' => ''])) }}" 
           class="p-3 md:p-4 rounded-xl border transition-all flex items-center gap-3 shadow-sm
           {{ request('status_group') === 'processing' ? 'bg-primary-fixed text-on-primary-fixed border-primary' : 'bg-surface-container-lowest hover:bg-surface-bright border-outline-variant text-on-surface' }}">
            <span class="material-symbols-outlined text-[28px] md:text-[32px] {{ request('status_group') === 'processing' ? 'text-primary' : 'text-secondary' }}">local_shipping</span>
            <div>
                <p class="text-xs font-semibold opacity-75">Processing</p>
                <h3 class="text-xl font-bold">{{ $processingCount }}</h3>
            </div>
        </a>
        <!-- Ready Pickup -->
        <a href="{{ route('procurements.index', array_merge(request()->except('page'), ['status_group' => 'ready_pickup', 'status' => ''])) }}" 
           class="p-3 md:p-4 rounded-xl border transition-all flex items-center gap-3 shadow-sm
           {{ request('status_group') === 'ready_pickup' ? 'bg-primary-fixed text-on-primary-fixed border-primary' : 'bg-surface-container-lowest hover:bg-surface-bright border-outline-variant text-on-surface' }}">
            <span class="material-symbols-outlined text-[28px] md:text-[32px] {{ request('status_group') === 'ready_pickup' ? 'text-primary' : 'text-secondary' }}">hail</span>
            <div>
                <p class="text-xs font-semibold opacity-75">Ready</p>
                <h3 class="text-xl font-bold">{{ $readyCount }}</h3>
            </div>
        </a>
        <!-- Closed -->
        <a href="{{ route('procurements.index', array_merge(request()->except('page'), ['status_group' => 'closed', 'status' => ''])) }}" 
           class="p-3 md:p-4 rounded-xl border transition-all flex items-center gap-3 shadow-sm
           {{ request('status_group') === 'closed' ? 'bg-primary-fixed text-on-primary-fixed border-primary' : 'bg-surface-container-lowest hover:bg-surface-bright border-outline-variant text-on-surface' }}">
            <span class="material-symbols-outlined text-[28px] md:text-[32px] {{ request('status_group') === 'closed' ? 'text-primary' : 'text-secondary' }}">check_circle</span>
            <div>
                <p class="text-xs font-semibold opacity-75">Closed</p>
                <h3 class="text-xl font-bold">{{ $closedCount }}</h3>
            </div>
        </a>
    </div>

    <!-- ================================================================
         SEARCH & FILTER
         Desktop: always visible grid
         Mobile:  search always on top, filters collapse via toggle
    ================================================================ -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl mb-4 md:mb-6 shadow-sm overflow-hidden">
        <form action="{{ route('procurements.index') }}" method="GET" id="filter-form">
            @if(request('status_group'))
                <input type="hidden" name="status_group" value="{{ request('status_group') }}">
            @endif

            {{-- ── Search row (always visible on all sizes) ──}}
            <div class="p-4 md:p-5 pb-0 md:pb-0">
                <label for="search" class="block text-xs font-semibold text-on-surface mb-1">Cari Kata Kunci</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant opacity-60">search</span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="No Case, Nama Barang, Mesin..." 
                               class="w-full pl-10 pr-4 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                    {{-- Mobile: toggle filter button --}}
                    <button type="button" id="filter-toggle-btn"
                            class="md:hidden flex items-center gap-1.5 px-3 py-2 border border-outline-variant rounded-lg text-sm font-semibold text-on-surface bg-surface-container hover:bg-surface-container-high transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                        Filter
                        <span id="filter-arrow" class="material-symbols-outlined text-[16px] transition-transform duration-200">expand_more</span>
                    </button>
                </div>
            </div>

            {{-- ── Filter fields (hidden on mobile by default, always shown on desktop) ──}}
            <div id="filter-fields" style="display:none">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4 md:p-5 pt-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-on-surface mb-1">Status</label>
                        <select name="status" id="status" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                            <option value="">-- Semua Status --</option>
                            @foreach(\App\Enums\ProcurementStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', strtoupper($status->value)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Urgensi Filter -->
                    <div>
                        <label for="urgency" class="block text-xs font-semibold text-on-surface mb-1">Urgensi</label>
                        <select name="urgency" id="urgency" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                            <option value="">-- Semua Urgensi --</option>
                            @foreach(\App\Enums\ProcurementUrgency::cases() as $urg)
                                <option value="{{ $urg->value }}" {{ request('urgency') === $urg->value ? 'selected' : '' }}>
                                    {{ strtoupper($urg->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori Filter -->
                    <div>
                        <label for="category" class="block text-xs font-semibold text-on-surface mb-1">Kategori</label>
                        <select name="category" id="category" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Owner Filter -->
                    <div>
                        <label for="owner" class="block text-xs font-semibold text-on-surface mb-1">Current Owner</label>
                        <select name="owner" id="owner" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                            <option value="">-- Semua Owner --</option>
                            @foreach($owners as $ownerName)
                                @if(!empty($ownerName))
                                    <option value="{{ $ownerName }}" {{ request('owner') === $ownerName ? 'selected' : '' }}>
                                        {{ $ownerName }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- My Cases Checkbox -->
                    <div class="flex items-center gap-2 md:pt-6">
                        <input type="checkbox" name="my_cases" id="my_cases" value="1" {{ request('my_cases') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 text-primary bg-surface-container border border-outline-variant rounded focus:ring-primary"/>
                        <label for="my_cases" class="text-sm font-semibold text-on-surface cursor-pointer select-none">Tugasku Saja</label>
                    </div>

                    <!-- Action buttons -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-5 flex justify-end gap-2">
                        <a href="{{ route('procurements.index') }}" class="px-4 py-2 border border-outline text-secondary hover:bg-surface-container rounded-lg font-semibold text-sm transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                            Reset
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-5 py-2 rounded-lg font-semibold text-sm transition-colors flex items-center gap-1 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile: search submit button when filters are hidden --}}
            <div class="md:hidden p-4 pt-3 flex justify-end gap-2">
                <a href="{{ route('procurements.index') }}" class="px-3 py-2 border border-outline text-secondary hover:bg-surface-container rounded-lg font-semibold text-sm transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    Reset
                </a>
                <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-lg font-semibold text-sm transition-colors flex items-center gap-1 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">search</span>
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- ================================================================
         CASES LIST
         Desktop → table (unchanged)
         Mobile  → card list
    ================================================================ -->
    @if($cases->isEmpty())
        <!-- Empty State -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm px-6 py-16 flex flex-col items-center text-center max-w-xl mx-auto">
            <div class="w-20 h-20 rounded-full bg-secondary-container flex items-center justify-center text-primary mb-6 shadow-sm">
                <span class="material-symbols-outlined text-[48px]">shopping_bag</span>
            </div>
            <h3 class="text-xl font-bold text-on-surface mb-2">Belum ada Pengadaan Khusus</h3>
            <p class="text-sm text-on-surface-variant leading-relaxed mb-6">
                Gunakan modul ini untuk membuat permintaan pembelian sparepart non-rutin, machining, fabrication, service, atau kebutuhan maintenance yang tidak tersedia di gudang.
            </p>
            @can('create', App\Models\ProcurementCase::class)
                <a href="{{ route('procurements.create') }}" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 text-sm shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Buat Pengadaan Baru
                </a>
            @endcan
        </div>
    @else

        {{-- ════════════════════════════════════════════════════════════
             DESKTOP TABLE (hidden on mobile)
        ════════════════════════════════════════════════════════════ --}}
        <div class="hidden md:block bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container border-b border-outline-variant text-label-md font-label-md text-on-surface font-semibold">
                            <th class="px-6 py-4">Nomor Case</th>
                            <th class="px-6 py-4">Nama Barang</th>
                            <th class="px-6 py-4">Mesin</th>
                            <th class="px-6 py-4">Urgensi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Target Dibutuhkan</th>
                            <th class="px-6 py-4">Tanggal Buat</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($cases as $case)
                            <tr class="hover:bg-surface-bright text-body-md text-sm text-on-surface">
                                <td class="px-6 py-4 font-semibold mono text-primary">{{ $case->case_number }}</td>
                                <td class="px-6 py-4 font-medium max-w-xs break-words leading-relaxed">{{ $case->item_name }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $case->machine->name }}</div>
                                    <div class="text-xs opacity-70 mono">{{ $case->machine->code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                        {{ $case->urgency->value === 'emergency' ? 'bg-error-container text-on-error-container border border-error animate-pulse' : '' }}
                                        {{ $case->urgency->value === 'urgent' ? 'bg-tertiary-fixed text-on-tertiary-fixed border border-amber-300' : '' }}
                                        {{ $case->urgency->value === 'normal' ? 'bg-secondary-container text-on-secondary-fixed-variant' : '' }}
                                    ">
                                        {{ strtoupper($case->urgency->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border
                                        {{ $case->status->value === 'draft' ? 'bg-surface-container-high text-on-surface-variant border-outline' : '' }}
                                        {{ $case->status->value === 'pending_kabag' ? 'bg-amber-100 text-amber-800 border-amber-400' : '' }}
                                        {{ $case->status->value === 'pending_dir' ? 'bg-purple-100 text-purple-800 border-purple-400' : '' }}
                                        {{ $case->status->value === 'processing' ? 'bg-blue-100 text-blue-800 border-blue-400' : '' }}
                                        {{ $case->status->value === 'need_info' ? 'bg-red-100 text-red-800 border-red-400' : '' }}
                                        {{ $case->status->value === 'waiting_delivery' ? 'bg-indigo-100 text-indigo-800 border-indigo-400' : '' }}
                                        {{ $case->status->value === 'ready_to_pickup' ? 'bg-green-100 text-green-800 border-green-400' : '' }}
                                        {{ $case->status->value === 'closed' ? 'bg-green-600 text-white border-green-700' : '' }}
                                        {{ $case->status->value === 'cancelled' ? 'bg-gray-100 text-gray-800 border-gray-400 line-through' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', strtoupper($case->status->value)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-xs">{{ $case->target_needed_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-xs opacity-75">{{ $case->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('procurements.show', $case->id) }}" 
                                           class="bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant rounded px-2.5 py-1.5 text-xs font-bold transition-all flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span> Detail
                                        </a>
                                        @can('update', $case)
                                            <a href="{{ route('procurements.edit', $case->id) }}" 
                                               class="bg-secondary-container hover:bg-primary-container hover:text-on-primary text-on-secondary-container border border-outline-variant rounded px-2.5 py-1.5 text-xs font-bold transition-all flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($cases->hasPages())
                <div class="px-6 py-4 border-t border-outline-variant">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════════════════
             MOBILE CARD LIST (hidden on desktop)
        ════════════════════════════════════════════════════════════ --}}
        <div class="md:hidden space-y-3">
            @foreach($cases as $case)
                @php
                    $urgencyClass = match($case->urgency->value) {
                        'emergency' => 'bg-error-container text-on-error-container border border-error',
                        'urgent'    => 'bg-amber-100 text-amber-800 border border-amber-400',
                        default     => 'bg-secondary-container text-on-secondary-fixed-variant border border-transparent',
                    };
                    $statusClass = match($case->status->value) {
                        'draft'           => 'bg-surface-container-high text-on-surface-variant border-outline',
                        'pending_kabag'   => 'bg-amber-100 text-amber-800 border-amber-400',
                        'pending_dir'     => 'bg-purple-100 text-purple-800 border-purple-400',
                        'processing'      => 'bg-blue-100 text-blue-800 border-blue-400',
                        'need_info'       => 'bg-red-100 text-red-800 border-red-400',
                        'waiting_delivery'=> 'bg-indigo-100 text-indigo-800 border-indigo-400',
                        'ready_to_pickup' => 'bg-green-100 text-green-800 border-green-400',
                        'closed'          => 'bg-green-600 text-white border-green-700',
                        'cancelled'       => 'bg-gray-100 text-gray-800 border-gray-400',
                        default           => 'bg-surface-container text-on-surface border-outline-variant',
                    };
                    $isEmergency = $case->urgency->value === 'emergency';
                @endphp
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden {{ $isEmergency ? 'border-l-4 border-l-red-500' : ($case->urgency->value === 'urgent' ? 'border-l-4 border-l-amber-400' : '') }}">
                    {{-- Card Header: Case Number + Badges --}}
                    <div class="flex items-center justify-between px-4 pt-4 pb-2">
                        <span class="font-mono font-bold text-primary text-sm tracking-wide">{{ $case->case_number }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $urgencyClass }} {{ $isEmergency ? 'animate-pulse' : '' }}">
                                {{ strtoupper($case->urgency->value) }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $statusClass }}">
                                {{ str_replace('_', ' ', strtoupper($case->status->value)) }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="px-4 pb-4 space-y-3">
                        {{-- Item Name --}}
                        <p class="font-semibold text-on-surface text-base leading-snug">{{ $case->item_name }}</p>

                        {{-- Machine --}}
                        <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                            <span class="material-symbols-outlined text-[16px] text-secondary">precision_manufacturing</span>
                            <span class="font-medium">{{ $case->machine->name }}</span>
                            <span class="text-xs opacity-60 font-mono">{{ $case->machine->code }}</span>
                        </div>

                        {{-- Target Date --}}
                        <div class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[16px] text-secondary">event</span>
                            <span class="text-on-surface-variant">Target:</span>
                            <span class="font-semibold text-on-surface">{{ $case->target_needed_date->format('d M Y') }}</span>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-1">
                            <a href="{{ route('procurements.show', $case->id) }}"
                               class="w-full flex items-center justify-center gap-2 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-sm font-bold transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Pagination --}}
            @if($cases->hasPages())
                <div class="pt-2">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>

    @endif

    {{-- Filter Toggle Script + Desktop override --}}
    <style>
    @media (min-width: 768px) {
        #filter-fields { display: block !important; }
        #filter-toggle-btn { display: none !important; }
    }
    </style>
    <script>
    (function() {
        const btn    = document.getElementById('filter-toggle-btn');
        const fields = document.getElementById('filter-fields');
        const arrow  = document.getElementById('filter-arrow');
        if (!btn || !fields) return;

        // If any filter is active on page load, auto-open on mobile
        const params = new URLSearchParams(window.location.search);
        const active = ['status','urgency','category','owner','my_cases']
            .some(k => params.has(k) && params.get(k) !== '');

        if (active && window.innerWidth < 768) {
            fields.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }

        btn.addEventListener('click', function() {
            const isHidden = (fields.style.display === 'none' || fields.style.display === '');
            fields.style.display = isHidden ? 'block' : 'none';
            if (arrow) arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        });
    })();
    </script>
</x-layouts.app>
