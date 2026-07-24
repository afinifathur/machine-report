<x-layouts.app 
    title="Detail Pengadaan {{ $procurement->case_number }} | Sistem MRM"
    topbar-title="Pengadaan Khusus"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pengadaan Khusus' => route('procurements.index'), $procurement->case_number => '']" />

    <x-page-header 
        title="Detail Pengadaan: {{ $procurement->case_number }}" 
        subtitle="Informasi status pelacakan pengadaan" 
        class="mb-6" 
        back-url="{{ route('procurements.index') }}"
    />

    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-container text-on-secondary-fixed border border-outline-variant rounded-xl text-body-sm shadow-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-error-container text-on-error-container border border-error rounded-xl text-body-sm shadow-sm">
            <p class="font-bold mb-1">Terjadi kesalahan:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details (Left 2 columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="pb-4 mb-4 border-b border-outline-variant flex justify-between items-center">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Informasi Barang & Kebutuhan</h3>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $procurement->urgency->value === 'emergency' ? 'bg-error-container text-on-error-container border border-error' : '' }}
                            {{ $procurement->urgency->value === 'urgent' ? 'bg-tertiary-fixed text-on-tertiary-fixed' : '' }}
                            {{ $procurement->urgency->value === 'normal' ? 'bg-secondary-container text-on-secondary-fixed-variant' : '' }}
                        ">
                            URGENSI: {{ strtoupper($procurement->urgency->value) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-on-surface">
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Nama Barang / Komponen</p>
                        <p class="font-semibold text-lg mt-0.5">{{ $procurement->item_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Target Tanggal Dibutuhkan</p>
                        <p class="font-semibold text-lg mt-0.5">{{ $procurement->target_needed_date->format('d F Y') }}</p>
                    </div>
                    <div class="col-span-2 mt-2">
                        <p class="text-xs text-on-surface-variant font-medium mb-1">Deskripsi Kerusakan & Kebutuhan Spesifikasi</p>
                        <div class="bg-surface-container p-4 rounded-lg text-body-md text-sm whitespace-pre-line leading-relaxed">
                            {{ $procurement->description }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- PO & Shipping Metadata -->
            @if($procurement->po_number)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold pb-4 mb-4 border-b border-outline-variant">
                        Informasi Pembelian & Pengiriman (PO)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-on-surface">
                        <div>
                            <p class="text-xs text-on-surface-variant font-medium">Nomor PO</p>
                            <p class="font-semibold mt-0.5 mono text-primary">{{ $procurement->po_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant font-medium">Nama Vendor</p>
                            <p class="font-semibold mt-0.5">{{ $procurement->vendor_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant font-medium">Tanggal Penerbitan PO</p>
                            <p class="font-semibold mt-0.5">{{ $procurement->po_date ? $procurement->po_date->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant font-medium">Lokasi Rack Penyimpanan</p>
                            <p class="font-semibold mt-0.5">{{ $procurement->rack_location ?? 'Belum ditentukan' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Machine Context Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold pb-4 mb-4 border-b border-outline-variant">
                    Mesin & Aset Fisik
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Nama Mesin</p>
                        <p class="font-semibold mt-0.5">{{ $procurement->machine->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Kode Aset</p>
                        <p class="font-semibold mt-0.5 mono text-primary">{{ $procurement->machine->code }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Departemen Pemilik</p>
                        <p class="font-semibold mt-0.5">{{ $procurement->machine->department }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Area Produksi</p>
                        <p class="font-semibold mt-0.5">{{ $procurement->machine->production_area }}</p>
                    </div>
                </div>
            </div>

            <!-- Approval History Card -->
            @if($procurement->approvals->isNotEmpty())
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold pb-4 mb-4 border-b border-outline-variant">
                        Riwayat Keputusan & Catatan Audit
                    </h3>
                    <div class="space-y-4">
                        @foreach($procurement->approvals as $approval)
                            <div class="p-4 bg-surface-container rounded-xl text-sm border border-outline-variant">
                                <div class="flex justify-between items-center mb-2">
                                    <p class="font-semibold text-on-surface">Stage {{ $approval->stage }}: {{ $approval->user->name }}</p>
                                    <span class="px-2 py-0.5 rounded text-xs font-bold 
                                        {{ $approval->decision->value === 'approved' ? 'bg-success-container text-on-success-container' : '' }}
                                        {{ $approval->decision->value === 'returned_for_info' ? 'bg-warning-container text-on-warning-container border border-outline-variant' : '' }}
                                        {{ $approval->decision->value === 'rejected' ? 'bg-error-container text-on-error-container border border-error' : '' }}
                                    ">
                                        {{ strtoupper(str_replace('_', ' ', $approval->decision->value)) }}
                                    </span>
                                </div>
                                <p class="text-on-surface-variant italic">"{{ $approval->note ?? 'Tidak ada catatan tambahan.' }}"</p>
                                <p class="text-[10px] text-on-surface-variant mt-2 text-right font-medium">{{ $approval->created_at->format('d M Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- NEED INFO Inline Edit Form -->
            @if($procurement->status->value === 'need_info')
                @can('updateInformation', $procurement)
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                        <div class="pb-4 mb-4 border-b border-outline-variant">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Lengkapi Informasi Revisi</h3>
                            <p class="text-body-sm text-on-surface-variant mt-1">
                                Perbaiki data di bawah sesuai catatan Purchasing/Kabag, lalu ajukan ulang.
                            </p>
                        </div>
                        <form action="{{ route('procurements.update-information', $procurement->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label for="item_name" class="block text-xs font-semibold mb-1">Nama Barang <span class="text-error">*</span></label>
                                    <input type="text" name="item_name" id="item_name" value="{{ $procurement->item_name }}" required
                                           class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm"/>
                                </div>
                                <div>
                                    <label for="machine_id" class="block text-xs font-semibold mb-1">Mesin Terkait <span class="text-error">*</span></label>
                                    <select name="machine_id" id="machine_id" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                                        @foreach(\App\Models\Machine::orderBy('name')->get() as $m)
                                            <option value="{{ $m->id }}" {{ $procurement->machine_id == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }} ({{ $m->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="urgency" class="block text-xs font-semibold mb-1">Urgensi <span class="text-error">*</span></label>
                                    <select name="urgency" id="urgency" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">
                                        <option value="normal" {{ $procurement->urgency->value === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="urgent" {{ $procurement->urgency->value === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="emergency" {{ $procurement->urgency->value === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="target_needed_date" class="block text-xs font-semibold mb-1">Target Tanggal <span class="text-error">*</span></label>
                                    <input type="date" name="target_needed_date" id="target_needed_date" value="{{ $procurement->target_needed_date->format('Y-m-d') }}" required
                                           class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm"/>
                                </div>
                            </div>
                            <div>
                                <label for="description" class="block text-xs font-semibold mb-1">Deskripsi Kerusakan / Revisi Spesifikasi <span class="text-error">*</span></label>
                                <textarea name="description" id="description" rows="3" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm">{{ $procurement->description }}</textarea>
                            </div>
                            <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">send</span>
                                Update & Ajukan Ulang
                            </button>
                        </form>
                    </div>
                @endcan
            @endif
        </div>

        <!-- Sidebar / Tracking status (Right 1 column) -->
        <div class="space-y-6">
            <!-- Ownership & Status Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold pb-4 mb-4 border-b border-outline-variant">
                    Status Pelacakan
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Status Saat Ini</p>
                        <div class="mt-1">
                            <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-primary-fixed text-on-primary-fixed border border-primary block text-center uppercase">
                                {{ str_replace('_', ' ', $procurement->status->value) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Penanggung Jawab (Current Owner)</p>
                        <div class="mt-1 bg-surface-container p-3 rounded-lg flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person</span>
                            <span class="font-bold text-sm text-on-surface">{{ $procurement->current_owner }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs opacity-75 pt-2 border-t border-outline-variant">
                        <div>
                            <p class="text-on-surface-variant">Dibuat Oleh</p>
                            <p class="font-semibold">{{ $procurement->creator->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant">Tanggal Pengajuan</p>
                            <p class="font-semibold">{{ $procurement->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Controls Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-6">
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold pb-4 border-b border-outline-variant mb-0">
                    Panel Tindakan Workflow
                </h3>

                <!-- DRAFT ACTIONS -->
                @if($procurement->status->value === 'draft')
                    @can('submit', $procurement)
                        <form action="{{ route('procurements.submit', $procurement->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center justify-center gap-2 text-sm shadow-sm">
                                <span class="material-symbols-outlined text-[20px]">send</span>
                                Ajukan ke Kabag MTC
                            </button>
                        </form>
                    @endcan

                    @can('update', $procurement)
                        <a href="{{ route('procurements.edit', $procurement->id) }}" class="w-full border border-outline text-secondary hover:bg-surface-container py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center justify-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                            Edit Draft
                        </a>
                    @endcan

                    @can('delete', $procurement)
                        <form action="{{ route('procurements.destroy', $procurement->id) }}" method="POST" class="w-full" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draft ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full border border-error text-error hover:bg-error-container hover:text-on-error-container py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center justify-center gap-2 text-sm">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                Hapus Draft
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- PENDING KABAG (STAGE 1) ACTIONS -->
                @if($procurement->status->value === 'pending_kabag')
                    @can('approveStage1', $procurement)
                        <form action="{{ route('procurements.approve-stage-1', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Kabag (Opsional)</label>
                            <textarea name="note" rows="2" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Tulis komentar..."></textarea>
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span> Setujui Stage 1
                            </button>
                        </form>
                    @endcan

                    @can('returnForInformation', $procurement)
                        <form action="{{ route('procurements.return-information', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Butuh Info <span class="text-error">*</span></label>
                            <textarea name="note" required rows="2" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Kenapa dikembalikan? Wajib diisi..."></textarea>
                            <button type="submit" class="w-full bg-warning-container text-on-warning-container hover:bg-surface-variant py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 border border-outline-variant">
                                <span class="material-symbols-outlined text-[18px]">help</span> Kembalikan (Butuh Info)
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- PENDING DIR (STAGE 2) ACTIONS -->
                @if($procurement->status->value === 'pending_dir')
                    @can('approveStage2', $procurement)
                        <form action="{{ route('procurements.approve-stage-2', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Direktur (Opsional)</label>
                            <textarea name="note" rows="2" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Tulis komentar..."></textarea>
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span> Setujui Stage 2
                            </button>
                        </form>
                    @endcan

                    @can('returnForInformation', $procurement)
                        <form action="{{ route('procurements.return-information', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Butuh Info <span class="text-error">*</span></label>
                            <textarea name="note" required rows="2" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Kenapa dikembalikan? Wajib diisi..."></textarea>
                            <button type="submit" class="w-full bg-warning-container text-on-warning-container hover:bg-surface-variant py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 border border-outline-variant">
                                <span class="material-symbols-outlined text-[18px]">help</span> Kembalikan (Butuh Info)
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- PROCESSING ACTIONS (Purchasing PO input) -->
                @if($procurement->status->value === 'processing')
                    @can('inputPO', $procurement)
                        <form action="{{ route('procurements.input-po', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant">
                            @csrf
                            <h4 class="text-xs font-bold text-on-surface">Input Data Pembelian</h4>
                            
                            <div>
                                <label class="block text-[11px] font-semibold mb-1">Nama Vendor <span class="text-error">*</span></label>
                                <input type="text" name="vendor_name" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Nama PT / Pemasok..."/>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold mb-1">Nomor PO <span class="text-error">*</span></label>
                                <input type="text" name="po_number" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="PO-XXXXXX..."/>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold mb-1">Tanggal Terbit PO <span class="text-error">*</span></label>
                                <input type="date" name="po_date" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs"/>
                            </div>

                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">local_shipping</span> Simpan PO & Kirim
                            </button>
                        </form>
                    @endcan

                    @can('returnForInformation', $procurement)
                        <form action="{{ route('procurements.return-information', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Butuh Info <span class="text-error">*</span></label>
                            <textarea name="note" required rows="2" class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Apa spesifikasi yang kurang lengkap? Wajib..."></textarea>
                            <button type="submit" class="w-full bg-warning-container text-on-warning-container hover:bg-surface-variant py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 border border-outline-variant">
                                <span class="material-symbols-outlined text-[18px]">help</span> Kembalikan ke Mtc
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- WAITING DELIVERY ACTIONS (Warehouse check-in) -->
                @if($procurement->status->value === 'waiting_delivery')
                    @can('confirmArrival', $procurement)
                        <form action="{{ route('procurements.confirm-arrival', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant">
                            @csrf
                            <h4 class="text-xs font-bold text-on-surface">Penerimaan Gudang Sparepart</h4>

                            <div>
                                <label class="block text-[11px] font-semibold mb-1">Lokasi Rack Penyimpanan <span class="text-error">*</span></label>
                                <input type="text" name="rack_location" required class="w-full px-3 py-2 bg-surface-container border border-outline-variant rounded-lg text-xs" placeholder="Contoh: RAK-B2, RAK-C1..."/>
                            </div>

                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">shelves</span> Konfirmasi Kedatangan
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- READY TO PICKUP ACTIONS -->
                @if($procurement->status->value === 'ready_to_pickup')
                    @can('confirmPickup', $procurement)
                        <form action="{{ route('procurements.confirm-pickup', $procurement->id) }}" method="POST" class="w-full border-t pt-4 border-outline-variant">
                            @csrf
                            <p class="text-xs text-on-surface-variant mb-2 leading-relaxed">Pastikan Anda sudah menerima fisik barang di gudang sebelum konfirmasi.</p>
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                                Konfirmasi Barang Sudah Diambil
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- GENERAL CANCEL BUTTON -->
                @can('cancel', $procurement)
                    <form action="{{ route('procurements.cancel', $procurement->id) }}" method="POST" class="space-y-2 border-t pt-4 border-outline-variant" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan request pengadaan ini?')">
                        @csrf
                        <label class="block text-xs font-semibold mb-1 text-error">Alasan Pembatalan <span class="text-error">*</span></label>
                        <textarea name="reason" required rows="2" class="w-full px-3 py-2 bg-surface-container border border-error rounded-lg text-xs" placeholder="Alasan pembatalan resmi..."></textarea>
                        <button type="submit" class="w-full border border-error text-error hover:bg-error-container hover:text-on-error-container py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">cancel</span> Batalkan Request
                        </button>
                    </form>
                @endcan

                @if(in_array($procurement->status->value, ['closed', 'cancelled']))
                    <div class="p-4 bg-surface-container rounded-lg text-xs text-on-surface-variant leading-relaxed text-center font-medium border border-outline-variant">
                        Workflow telah ditutup (Status: {{ strtoupper($procurement->status->value) }}). Tidak ada tindakan lebih lanjut yang diizinkan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
