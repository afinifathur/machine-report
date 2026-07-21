<x-layouts.app 
    title="Registrasi Mesin Baru | Sistem MRM"
    topbar-title="Manajemen Aset"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Daftar Mesin' => route('machines.index'), 'Registrasi Mesin' => '']" />

    <x-page-header title="Registrasi Mesin Baru" subtitle="Identitas Aset Fisik Pabrik" class="mb-6" back-url="{{ route('machines.index') }}" />

    <div class="max-w-4xl bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
        <div class="mb-6 pb-4 border-b border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Informasi Identitas Dasar</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">
                Isi data berikut untuk mendaftarkan identitas mesin baru. Hanya empat kolom pertama yang wajib diisi untuk mempercepat registrasi progresif.
            </p>
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

        <form action="{{ route('machines.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Required Fields Group -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Machine Code -->
                <div>
                    <label for="code" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Kode Mesin <span class="text-error">*</span>
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm uppercase mono" 
                           placeholder="CONTOH: CNC-21"/>
                    <span class="text-xs text-on-surface-variant mt-1 block">Kode bersifat permanen dan tidak dapat diubah setelah disimpan.</span>
                </div>

                <!-- Machine Name -->
                <div>
                    <label for="name" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Nama Mesin <span class="text-error">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                           placeholder="Nama operasional mesin..."/>
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Departemen Pemilik <span class="text-error">*</span>
                    </label>
                    <select name="department" id="department" required
                            class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department') === $dept->name ? 'selected' : '' }}>
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
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') === $cat->name ? 'selected' : '' }}>
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
                            <option value="{{ $area->id }}" {{ old('production_area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Optional Fields Group -->
            <div class="mt-8 pt-6 border-t border-outline-variant">
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-4">Detail Tambahan (Opsional)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Specific Zone / Location -->
                    <div>
                        <label for="production_area" class="block text-label-md font-label-md text-on-surface mb-2">Zona / Lokasi Spesifik (Opsional)</label>
                        <input type="text" name="production_area" id="production_area" value="{{ old('production_area') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                               placeholder="Contoh: Area A, Gedung B, Lantai 2..."/>
                    </div>

                    <!-- Manufacturer -->
                    <div>
                        <label for="manufacturer" class="block text-label-md font-label-md text-on-surface mb-2">Produsen / Manufaktur</label>
                        <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                               placeholder="Contoh: Siemens, Fanuc"/>
                    </div>

                    <!-- Model -->
                    <div>
                        <label for="model" class="block text-label-md font-label-md text-on-surface mb-2">Model / Tipe</label>
                        <input type="text" name="model" id="model" value="{{ old('model') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                               placeholder="Contoh: VF-2, R-2000iC"/>
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label for="serial_number" class="block text-label-md font-label-md text-on-surface mb-2">Nomor Seri (Serial Number)</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm uppercase mono" 
                               placeholder="Contoh: SN-123456"/>
                    </div>

                    <!-- Installation Date -->
                    <div>
                        <label for="installation_date" class="block text-label-md font-label-md text-on-surface mb-2">Tanggal Instalasi</label>
                        <input type="date" name="installation_date" id="installation_date" value="{{ old('installation_date') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm"/>
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label for="vendor" class="block text-label-md font-label-md text-on-surface mb-2">Nama Vendor/Pemasok</label>
                        <input type="text" name="vendor" id="vendor" value="{{ old('vendor') }}"
                               class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                               placeholder="Penyedia jasa pemasangan/pembelian..."/>
                    </div>

                    <!-- Lifecycle Status -->
                    <div>
                        <label for="lifecycle_status" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">Siklus Hidup Aset</label>
                        <select name="lifecycle_status" id="lifecycle_status" required
                                class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm">
                            @foreach($lifecycles as $lc)
                                <option value="{{ $lc }}" {{ old('lifecycle_status', 'ACTIVE') === $lc ? 'selected' : '' }}>
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
                              class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                              placeholder="Tulis informasi khusus, catatan kontrak, atau detail garansi di sini..."></textarea>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="mt-8 pt-6 border-t border-outline-variant flex gap-4 justify-end">
                <x-button variant="secondary" href="{{ route('machines.index') }}">Batal</x-button>
                <button type="submit" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Simpan Registrasi
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
