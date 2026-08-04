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

    <!-- Status-based Banners -->
    @if($procurement->status->value === 'draft')
        @php
            $latestReject = $procurement->approvals()
                ->where('decision', \App\Enums\ApprovalDecision::REJECTED->value)
                ->latest()
                ->first();
        @endphp

        @if($latestReject)
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-300 text-yellow-800 rounded-xl text-sm shadow-sm flex items-start gap-3">
                <span class="material-symbols-outlined text-yellow-700 shrink-0">warning</span>
                <div>
                    <p class="font-bold">Review Note (Stage {{ $latestReject->stage }}: {{ $latestReject->user->name ?? 'User' }})</p>
                    <p class="mt-1 italic">"{{ $latestReject->note }}"</p>
                </div>
            </div>
        @endif

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm shadow-sm flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 shrink-0">info</span>
            <div>
                <p class="font-bold">Draft</p>
                <p class="mt-0.5">Silakan periksa kembali data Procurement.</p>
            </div>
        </div>
    @endif

    @if($procurement->status->value === 'pending_kabag')
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm shadow-sm flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 shrink-0">hourglass_empty</span>
            <div>
                <p class="font-bold">Waiting Approval</p>
                <p class="mt-0.5">Permintaan sedang menunggu persetujuan dari Kabag Maintenance.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Main Details (Left 2 columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="pb-4 mb-4 border-b border-outline-variant flex justify-between items-center">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Informasi Barang & Kebutuhan</h3>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $procurement->urgency->value === 'emergency' ? 'bg-error-container text-on-error-container border border-error animate-pulse' : '' }}
                            {{ $procurement->urgency->value === 'urgent' ? 'bg-tertiary-fixed text-on-tertiary-fixed border border-amber-300' : '' }}
                            {{ $procurement->urgency->value === 'normal' ? 'bg-secondary-container text-on-secondary-fixed-variant' : '' }}
                        ">
                            URGENSI: {{ strtoupper($procurement->urgency->value) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-on-surface">
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Nama Barang / Komponen</p>
                        <p class="font-semibold text-lg mt-0.5 text-primary">{{ $procurement->item_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Kategori</p>
                        <p class="font-semibold text-lg mt-0.5">{{ $procurement->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Target Tanggal Dibutuhkan</p>
                        <p class="font-semibold text-lg mt-0.5">{{ $procurement->target_needed_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-on-surface-variant font-medium">Status Operasional Mesin</p>
                        <p class="mt-1">
                            @if($procurement->machine_down)
                                <span class="px-3 py-1 rounded bg-error text-on-error text-xs font-bold uppercase tracking-wider animate-pulse inline-block">
                                    MACHINE DOWN (Breakdown)
                                </span>
                            @else
                                <span class="px-3 py-1 rounded bg-surface-container-high text-on-surface-variant text-xs font-semibold uppercase tracking-wider inline-block">
                                    Running (Normal)
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="col-span-2 mt-2">
                        <p class="text-xs text-on-surface-variant font-medium mb-1">Deskripsi Kerusakan & Kebutuhan Spesifikasi</p>
                        <div class="bg-surface-container p-4 rounded-lg text-body-md text-sm whitespace-pre-line leading-relaxed border border-outline-variant">
                            {{ $procurement->description }}
                        </div>
                    </div>
                    <div class="col-span-2 mt-2">
                        <p class="text-xs text-on-surface-variant font-medium mb-1">Alasan Pengadaan</p>
                        <div class="bg-surface-container p-4 rounded-lg text-body-md text-sm whitespace-pre-line leading-relaxed border border-outline-variant">
                            {{ $procurement->reason ?? 'Tidak ada alasan khusus dicantumkan.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            <div id="attachments-section" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <div class="pb-3 border-b border-outline-variant flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">attach_file</span>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold text-sm">Lampiran / Dokumen Pendukung</h3>
                    </div>
                    <span class="text-xs text-on-surface-variant font-medium">Maks. 10 file (Maks. 5 MB/file)</span>
                </div>

                @if($procurement->attachments->isEmpty())
                    <p class="text-xs text-on-surface-variant italic">Belum ada lampiran.</p>
                @else
                    <x-attachment-gallery 
                        :attachments="$procurement->attachments" 
                        downloadRoute="procurements.attachments.download" 
                        deleteRoute="procurements.attachments.destroy" 
                        deletePermission="deleteAttachment" 
                        storagePath="storage/procurements/" 
                    />
                @endif

                @can('uploadAttachment', $procurement)
                    @if($procurement->attachments->count() < 10)
                        <div class="p-4 bg-surface-container rounded-xl border border-dashed border-outline-variant">
                            <form action="{{ route('procurements.attachments.upload', $procurement->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div class="space-y-3">
                                    <div class="w-full">
                                        <label class="block text-xs font-bold text-on-surface mb-2">+ Upload Foto (JPG, PNG, WEBP, PDF)</label>
                                        <input type="file" name="file" required class="w-full text-sm text-on-surface-variant bg-surface-container-lowest border border-outline-variant rounded-lg file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-fixed file:text-on-primary-fixed hover:file:bg-surface-dim cursor-pointer"/>
                                    </div>
                                    <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                                        <span class="material-symbols-outlined text-[20px]">upload</span>
                                        Unggah Lampiran
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-3 bg-error-container text-on-error-container border border-error rounded-xl text-xs font-semibold text-center">
                            Batas maksimal 10 file lampiran telah tercapai.
                        </div>
                    @endif
                @endcan
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
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-3.5 rounded-xl font-bold transition-colors flex items-center justify-center gap-2 text-base shadow-md">
                                <span class="material-symbols-outlined text-[22px]">send</span>
                                Submit Procurement
                            </button>
                        </form>
                    @endcan

                    @can('update', $procurement)
                        <a href="{{ route('procurements.edit', $procurement->id) }}" class="w-full border-2 border-outline text-secondary hover:bg-surface-container py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                            Edit
                        </a>
                    @endcan

                    <a href="#attachments-section" class="w-full border-2 border-outline text-secondary hover:bg-surface-container py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-[20px]">attach_file</span>
                        Upload Foto
                    </a>

                    @can('delete', $procurement)
                        <form action="{{ route('procurements.destroy', $procurement->id) }}" method="POST" class="w-full" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draft ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full border border-error text-error hover:bg-error-container hover:text-on-error-container py-2.5 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 text-sm">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                Hapus Draft
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- PENDING KABAG (STAGE 1) ACTIONS -->
                @if($procurement->status->value === 'pending_kabag')
                    @can('approveStage1', $procurement)
                        <form action="{{ route('procurements.approve-stage-1', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Kabag (Opsional)</label>
                            <textarea name="note" rows="3" class="w-full px-3 py-2.5 bg-surface-container border border-outline-variant rounded-lg text-sm" placeholder="Tulis komentar..."></textarea>
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-3.5 rounded-xl text-base font-bold flex items-center justify-center gap-2 shadow-md">
                                <span class="material-symbols-outlined text-[22px]">check_circle</span> Setujui (Approve)
                            </button>
                        </form>

                        <form action="{{ route('procurements.reject', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan ini?')">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Penolakan (Review Note) <span class="text-error">*</span></label>
                            <textarea name="reason" required rows="3" class="w-full px-3 py-2.5 bg-surface-container border border-outline-variant rounded-lg text-sm" placeholder="Tulis alasan penolakan..."></textarea>
                            <button type="submit" class="w-full bg-error-container text-on-error-container hover:bg-surface-variant py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2 border border-error">
                                <span class="material-symbols-outlined text-[20px]">cancel</span> Tolak (Reject)
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- PENDING DIR (STAGE 2) ACTIONS -->
                @if($procurement->status->value === 'pending_dir')
                    @can('approveStage2', $procurement)
                        <form action="{{ route('procurements.approve-stage-2', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Direktur (Opsional)</label>
                            <textarea name="note" rows="3" class="w-full px-3 py-2.5 bg-surface-container border border-outline-variant rounded-lg text-sm" placeholder="Tulis komentar..."></textarea>
                            <button type="submit" class="w-full bg-primary hover:bg-primary-container text-on-primary py-3.5 rounded-xl text-base font-bold flex items-center justify-center gap-2 shadow-md">
                                <span class="material-symbols-outlined text-[22px]">check_circle</span> Setujui (Approve)
                            </button>
                        </form>

                        <form action="{{ route('procurements.reject', $procurement->id) }}" method="POST" class="space-y-3 border-t pt-4 border-outline-variant" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan ini?')">
                            @csrf
                            <label class="block text-xs font-semibold mb-1 text-on-surface">Catatan Penolakan (Review Note) <span class="text-error">*</span></label>
                            <textarea name="reason" required rows="3" class="w-full px-3 py-2.5 bg-surface-container border border-outline-variant rounded-lg text-sm" placeholder="Tulis alasan penolakan..."></textarea>
                            <button type="submit" class="w-full bg-error-container text-on-error-container hover:bg-surface-variant py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2 border border-error">
                                <span class="material-symbols-outlined text-[20px]">cancel</span> Tolak (Reject)
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

            <!-- Activity Center Placeholder -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <div class="pb-3 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">forum</span>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold text-sm">Activity Center</h3>
                </div>
                <div class="py-6 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px] text-outline opacity-50 mb-2">pending_actions</span>
                    <p class="text-xs font-semibold">Activity Center (Coming Soon)</p>
                    <p class="text-[10px] opacity-75 mt-1">Timeline, komentar, lampiran, dan riwayat aktivitas bisnis akan hadir pada Phase 3B.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
