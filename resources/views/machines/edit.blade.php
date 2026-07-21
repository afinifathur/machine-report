<x-layouts.app 
    title="Ubah Paspor Mesin - {{ $machine->code }} | Sistem MRM"
    topbar-title="Manajemen Aset"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Daftar Mesin' => route('machines.index'), $machine->code => route('machines.show', $machine->code), 'Ubah Paspor' => '']" />

    <x-page-header title="Ubah Paspor Mesin" subtitle="Kode Aset: {{ $machine->code }}" class="mb-6" back-url="{{ route('machines.show', $machine->code) }}" />

    <div class="max-w-4xl bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
        <div class="mb-6 pb-4 border-b border-outline-variant flex justify-between items-center">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Detail Paspor Mesin</h3>
                <p class="text-body-sm text-on-surface-variant mt-1">Perbarui rincian operasional dan fisik mesin.</p>
            </div>
            
            <!-- Archive Trigger Button -->
            <div>
                <button type="button" onclick="confirmArchive()" class="bg-error-container hover:bg-error/20 text-on-error-container px-4 py-2 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 border border-error">
                    <span class="material-symbols-outlined text-[20px]">archive</span>
                    Arsipkan Mesin
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-error-container text-on-error-container border border-error rounded-lg text-body-sm">
                <p class="font-bold mb-1">Periksa kembali input Anda:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Edit Form -->
        <form action="{{ route('machines.update', $machine->code) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Identity Group -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Machine Code (Immutable/Read-only) -->
                <div>
                    <label class="block text-label-md font-label-md text-on-surface-variant mb-2">Kode Mesin (Permanen)</label>
                    <div class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg font-body-md text-sm uppercase mono text-on-surface-variant select-none" style="opacity: 0.85;">
                        {{ $machine->code }}
                    </div>
                    <span class="text-xs text-on-surface-variant mt-1 block">Kode identitas mesin bersifat permanen dan tidak dapat diubah.</span>
                </div>

                <!-- Machine Name -->
                <div>
                    <label for="name" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Nama Mesin <span class="text-error">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $machine->name) }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" />
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Departemen Pemilik <span class="text-error">*</span>
                    </label>
                    <select name="department" id="department" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $machine->department) === $dept->name ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Kategori Mesin <span class="text-error">*</span>
                    </label>
                    <select name="category" id="category" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category', $machine->category) === $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Production Area Dropdown -->
                <div>
                    <label for="production_area_id" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Area Produksi <span class="text-error">*</span>
                    </label>
                    <select name="production_area_id" id="production_area_id" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        <option value="">-- Pilih Area Produksi --</option>
                        @foreach($productionAreas as $area)
                            <option value="{{ $area->id }}" {{ old('production_area_id', $machine->production_area_id) == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Optional Group -->
            <div class="mt-8 pt-6 border-t border-outline-variant">
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4">Detail Tambahan (Opsional)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Specific Zone / Location -->
                    <div>
                        <label for="production_area" class="block text-label-md font-label-md text-on-surface mb-2">Zona / Lokasi Spesifik (Opsional)</label>
                        <input type="text" name="production_area" id="production_area" value="{{ old('production_area', $machine->production_area) }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                               placeholder="Contoh: Area A, Gedung B, Lantai 2..."/>
                    </div>

                    <!-- Manufacturer -->
                    <div>
                        <label for="manufacturer" class="block text-label-md font-label-md text-on-surface mb-2">Produsen / Manufaktur</label>
                        <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $machine->manufacturer) }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" />
                    </div>

                    <!-- Model -->
                    <div>
                        <label for="model" class="block text-label-md font-label-md text-on-surface mb-2">Model / Tipe</label>
                        <input type="text" name="model" id="model" value="{{ old('model', $machine->model) }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" />
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label for="serial_number" class="block text-label-md font-label-md text-on-surface mb-2">Nomor Seri (Serial Number)</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $machine->serial_number) }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm uppercase mono" />
                    </div>

                    <!-- Installation Date -->
                    <div>
                        <label for="installation_date" class="block text-label-md font-label-md text-on-surface mb-2">Tanggal Instalasi</label>
                        <input type="date" name="installation_date" id="installation_date" value="{{ old('installation_date', $machine->installation_date ? $machine->installation_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm"/>
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label for="vendor" class="block text-label-md font-label-md text-on-surface mb-2">Nama Vendor/Pemasok</label>
                        <input type="text" name="vendor" id="vendor" value="{{ old('vendor', $machine->vendor) }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" />
                    </div>

                    <!-- Lifecycle Status -->
                    <div>
                        <label for="lifecycle_status" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">Siklus Hidup Aset</label>
                        <select name="lifecycle_status" id="lifecycle_status" required
                                class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            @foreach($lifecycles as $lc)
                                <option value="{{ $lc }}" {{ old('lifecycle_status', $machine->lifecycle_status) === $lc ? 'selected' : '' }}>
                                    {{ $lc === 'ACTIVE' ? 'Aktif' : ($lc === 'INACTIVE' ? 'Nonaktif' : 'Pensiun') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-label-md font-label-md text-on-surface mb-2">Catatan Internal / Riwayat Awal</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">{{ old('notes', $machine->notes) }}</textarea>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="mt-8 pt-6 border-t border-outline-variant flex gap-4 justify-end">
                <x-button variant="secondary" href="{{ route('machines.show', $machine->code) }}">Batal</x-button>
                <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Hidden Archive Form -->
    <form id="archive-machine-form" action="{{ route('machines.destroy', $machine->code) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
    <script>
        function confirmArchive() {
            if (confirm('Apakah Anda yakin ingin mengarsipkan mesin ini? Status mesin akan diubah menjadi Nonaktif dan tidak akan tampil di perencanaan default.')) {
                document.getElementById('archive-machine-form').submit();
            }
        }
    </script>
    @endpush
</x-layouts.app>
