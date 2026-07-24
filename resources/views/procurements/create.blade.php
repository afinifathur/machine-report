<x-layouts.app 
    title="Buat Request Pengadaan Baru | Sistem MRM"
    topbar-title="Pengadaan Khusus"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pengadaan Khusus' => route('procurements.index'), 'Buat Request' => '']" />

    <x-page-header title="Buat Request Pengadaan" subtitle="Pengajuan Pembelian Komponen Khusus Non-Rutin" class="mb-6" back-url="{{ route('procurements.index') }}" />

    <div class="max-w-3xl bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
        <div class="mb-6 pb-4 border-b border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Detail Kebutuhan Sparepart</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">
                Isi formulir pengadaan khusus di bawah ini. Permintaan akan disimpan sebagai <strong>Draft</strong> sebelum diajukan ke Kabag Maintenance.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-error-container text-on-error-container border border-error rounded-xl text-body-sm shadow-sm">
                <p class="font-bold mb-1">Periksa kembali input Anda:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('procurements.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Item Name -->
                <div class="col-span-2">
                    <label for="item_name" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Nama Barang / Suku Cadang <span class="text-error">*</span>
                    </label>
                    <input type="text" name="item_name" id="item_name" value="{{ old('item_name') }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                           placeholder="Contoh: Reducer WPA 80 Ratio 1:30, Motor Servo Yaskawa 400W..."/>
                </div>

                <!-- Machine Selection -->
                <div>
                    <label for="machine_id" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Mesin Terkait <span class="text-error">*</span>
                    </label>
                    <select name="machine_id" id="machine_id" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        <option value="">-- Pilih Mesin --</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                {{ $machine->name }} ({{ $machine->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Urgency Level -->
                <div>
                    <label for="urgency" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Tingkat Urgensi <span class="text-error">*</span>
                    </label>
                    <select name="urgency" id="urgency" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        <option value="normal" {{ old('urgency') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>

                <!-- Target Needed Date -->
                <div>
                    <label for="target_needed_date" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Target Tanggal Dibutuhkan <span class="text-error">*</span>
                    </label>
                    <input type="date" name="target_needed_date" id="target_needed_date" value="{{ old('target_needed_date', now()->addDays(7)->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm"/>
                </div>
            </div>

            <!-- Description / Reason for damage -->
            <div>
                <label for="description" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                    Deskripsi Kerusakan & Spesifikasi Detail <span class="text-error">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                          class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                          placeholder="Jelaskan secara mendetail gejala kerusakan, ukuran, merk, serial number, atau detail spesifikasi teknis lainnya..."></textarea>
            </div>

            <!-- Action buttons -->
            <div class="pt-6 border-t border-outline-variant flex gap-4 justify-end">
                <a href="{{ route('procurements.index') }}" class="px-6 py-2.5 border border-outline text-secondary hover:bg-surface-container rounded-lg font-body-md font-semibold transition-colors text-sm">
                    Batal
                </a>
                <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Simpan sebagai Draft
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
