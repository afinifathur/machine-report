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
                Isi formulir pengadaan khusus di bawah ini. Anda dapat menyimpannya sebagai **Draft** terlebih dahulu atau langsung mengajukannya (**Submit**) ke Kabag Maintenance.
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

        <form action="{{ route('procurements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                            <option value="{{ $machine->id }}"
                                    data-code="{{ $machine->code }}"
                                    data-name="{{ $machine->name }}"
                                    data-area="{{ $machine->production_area ?? ($machine->productionArea->name ?? '') }}"
                                    {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                {{ $machine->code }} — {{ $machine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label for="procurement_category_id" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Kategori <span class="text-error">*</span>
                    </label>
                    <select name="procurement_category_id" id="procurement_category_id" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('procurement_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
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

                <!-- Machine Down Checkbox -->
                <div class="col-span-2 flex items-center gap-3 py-2">
                    <input type="hidden" name="machine_down" value="0">
                    <input type="checkbox" name="machine_down" id="machine_down" value="1" {{ old('machine_down') == '1' ? 'checked' : '' }}
                           class="w-5 h-5 text-error bg-surface-container border border-outline-variant rounded focus:ring-error focus:ring-2"/>
                    <label for="machine_down" class="text-sm font-semibold text-on-surface cursor-pointer select-none">
                        Apakah Mesin Mengalami Breakdown (Machine Down)?
                    </label>
                </div>
            </div>

            <!-- Description / Technical Specifications -->
            <div>
                <label for="description" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                    Deskripsi Kerusakan & Spesifikasi Detail <span class="text-error">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                          class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                          placeholder="Jelaskan secara mendetail gejala kerusakan, ukuran, merk, serial number, atau detail spesifikasi teknis lainnya...">{{ old('description') }}</textarea>
            </div>

            <!-- Reason / Justification -->
            <div>
                <label for="reason" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                    Alasan Pengadaan <span class="text-error">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" required
                          class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                          placeholder="Jelaskan mengapa komponen ini perlu dibeli sekarang (misal: tidak ada stok cadangan di gudang, kebutuhan mendesak produksi)...">{{ old('reason') }}</textarea>
            </div>

            <!-- Attachment Section -->
            <div class="border border-outline-variant rounded-xl overflow-hidden">
                <div class="bg-surface-container px-5 py-3 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">attach_file</span>
                    <h4 class="font-semibold text-sm text-on-surface">Lampiran / Foto Kerusakan</h4>
                    <span class="ml-auto text-xs text-on-surface-variant">Maks. 10 file · Maks. 5 MB/file</span>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-xs text-on-surface-variant">
                        Lampirkan foto kerusakan, nameplate mesin, atau dokumen pendukung lainnya.
                        Format: JPG, PNG, WEBP, PDF.
                    </p>

                    <!-- File Input -->
                    <div>
                        <label for="attachments" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                            Pilih File
                        </label>
                        <div class="flex items-center gap-3">
                            <label for="attachments"
                                   class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 bg-surface-container hover:bg-surface-container-high border border-outline-variant rounded-lg text-sm font-semibold text-on-surface transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-primary">upload_file</span>
                                Pilih File
                            </label>
                            <input type="file"
                                   id="attachments"
                                   name="attachments[]"
                                   accept="image/jpeg,image/png,image/webp,.pdf,application/pdf"
                                   multiple
                                   class="sr-only"
                                   onchange="handleFileSelect(this)"/>
                            <span id="file-count-label" class="text-sm text-on-surface-variant italic">Belum ada file dipilih.</span>
                        </div>
                    </div>

                    <!-- Selected Files List -->
                    <ul id="file-list" class="space-y-1.5 hidden"></ul>

                    <!-- Error messages -->
                    <div id="file-errors" class="hidden p-3 bg-error-container text-on-error-container rounded-lg text-xs space-y-1"></div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-6 border-t border-outline-variant flex flex-wrap gap-4 justify-between">
                <a href="{{ route('procurements.index') }}" class="px-6 py-2.5 border border-outline text-secondary hover:bg-surface-container rounded-lg font-body-md font-semibold transition-colors text-sm">
                    Batal
                </a>
                <div class="flex gap-2">
                    <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Simpan Draft
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    function handleFileSelect(input) {
        const fileList   = document.getElementById('file-list');
        const countLabel = document.getElementById('file-count-label');
        const errorBox   = document.getElementById('file-errors');
        const MAX_FILES  = 10;
        const MAX_BYTES  = 5 * 1024 * 1024; // 5 MB

        const files  = Array.from(input.files);
        const errors = [];

        // Reset
        fileList.innerHTML  = '';
        errorBox.innerHTML  = '';
        errorBox.classList.add('hidden');

        if (files.length === 0) {
            countLabel.textContent = 'Belum ada file dipilih.';
            fileList.classList.add('hidden');
            return;
        }

        if (files.length > MAX_FILES) {
            errors.push(`Maksimal ${MAX_FILES} file. Anda memilih ${files.length} file.`);
        }

        files.forEach((file, idx) => {
            if (idx >= MAX_FILES) return; // skip extras

            const isImage = file.type.startsWith('image/');
            const isPdf   = file.type === 'application/pdf' || file.name.endsWith('.pdf');

            if (!isImage && !isPdf) {
                errors.push(`"${file.name}" — format tidak didukung (gunakan JPG, PNG, WEBP, atau PDF).`);
                return;
            }

            if (file.size > MAX_BYTES) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                errors.push(`"${file.name}" — ukuran ${sizeMB} MB melebihi batas 5 MB.`);
                return;
            }

            const sizeLabel = file.size >= 1048576
                ? (file.size / 1048576).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(1) + ' KB';

            const icon = isImage ? 'image' : 'description';

            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 text-sm text-on-surface';
            li.innerHTML = `
                <span class="material-symbols-outlined text-[16px] text-primary shrink-0">${icon}</span>
                <span class="font-medium truncate">${file.name}</span>
                <span class="ml-auto text-xs text-on-surface-variant shrink-0">${sizeLabel}</span>
            `;
            fileList.appendChild(li);
        });

        const validCount = fileList.children.length;
        countLabel.textContent = validCount > 0
            ? `${validCount} file dipilih`
            : 'Belum ada file valid dipilih.';

        fileList.classList.toggle('hidden', validCount === 0);

        if (errors.length > 0) {
            errors.forEach(e => {
                const p = document.createElement('p');
                p.textContent = '⚠ ' + e;
                errorBox.appendChild(p);
            });
            errorBox.classList.remove('hidden');
        }
    }
    </script>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <style>
            /* Tom Select custom styling overrides to match Tailwind / Geist theme */
            .ts-wrapper .ts-control {
                width: 100% !important;
                padding: 10px 16px !important;
                background-color: var(--color-surface-container, #eceef0) !important;
                border: 1px solid var(--color-outline-variant, #c4c5d5) !important;
                border-radius: 8px !important;
                font-family: 'Geist', sans-serif !important;
                font-size: 0.875rem !important;
                color: var(--color-on-surface, #191c1e) !important;
                box-shadow: none !important;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            }
            .ts-wrapper.focus .ts-control {
                border-color: #00288e !important;
                outline: 0 !important;
                box-shadow: 0 0 0 2px rgba(0, 40, 142, 0.2) !important;
            }
            .ts-wrapper .ts-control input {
                font-family: 'Geist', sans-serif !important;
                font-size: 0.875rem !important;
                color: var(--color-on-surface, #191c1e) !important;
            }
            .ts-dropdown {
                background-color: #ffffff !important;
                border: 1px solid #c4c5d5 !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
                z-index: 50 !important;
                font-family: 'Geist', sans-serif !important;
                font-size: 0.875rem !important;
                margin-top: 4px !important;
            }
            .ts-dropdown .option {
                padding: 8px 16px !important;
                color: #191c1e !important;
            }
            .ts-dropdown .active {
                background-color: #dde1ff !important;
                color: #001453 !important;
            }
            .ts-dropdown .no-results {
                padding: 8px 16px !important;
                color: #444653 !important;
                font-style: italic !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectEl = document.getElementById('machine_id');
                const ts = new TomSelect(selectEl, {
                    create: false,
                    searchField: ['value', 'text', 'code', 'name', 'area'],
                    dataAttr: 'data-*',
                    render: {
                        no_results: function(data, escape) {
                            return '<div class="no-results">No machine found</div>';
                        }
                    }
                });
                if (selectEl.hasAttribute('autofocus')) {
                    ts.focus();
                }
            });
        </script>
    @endpush
</x-layouts.app>
