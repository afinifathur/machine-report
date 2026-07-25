<x-layouts.app 
    title="Edit Request Pengadaan | Sistem MRM"
    topbar-title="Pengadaan Khusus"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="['Pengadaan Khusus' => route('procurements.index'), $procurement->case_number => route('procurements.show', $procurement->id), 'Edit' => '']" />

    <x-page-header title="Edit Request Pengadaan" subtitle="Pembaruan Data Draft Kasus" class="mb-6" back-url="{{ route('procurements.show', $procurement->id) }}" />

    <div class="max-w-3xl bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
        <div class="mb-6 pb-4 border-b border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Edit Detail Kebutuhan</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">
                Perubahan hanya dapat disimpan apabila status kasus masih berupa **Draft**. Anda juga dapat langsung mengajukannya (**Submit**) dari sini.
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

        <form action="{{ route('procurements.update', $procurement->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Item Name -->
                <div class="col-span-2">
                    <label for="item_name" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Nama Barang / Suku Cadang <span class="text-error">*</span>
                    </label>
                    <input type="text" name="item_name" id="item_name" value="{{ old('item_name', $procurement->item_name) }}" required
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
                            <option value="{{ $machine->id }}" {{ old('machine_id', $procurement->machine_id) == $machine->id ? 'selected' : '' }}>
                                {{ $machine->name }} ({{ $machine->code }})
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
                            <option value="{{ $cat->id }}" {{ old('procurement_category_id', $procurement->procurement_category_id) == $cat->id ? 'selected' : '' }}>
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
                        <option value="normal" {{ old('urgency', $procurement->urgency->value) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="urgent" {{ old('urgency', $procurement->urgency->value) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="emergency" {{ old('urgency', $procurement->urgency->value) === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>

                <!-- Target Needed Date -->
                <div>
                    <label for="target_needed_date" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                        Target Tanggal Dibutuhkan <span class="text-error">*</span>
                    </label>
                    <input type="date" name="target_needed_date" id="target_needed_date" value="{{ old('target_needed_date', $procurement->target_needed_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm"/>
                </div>

                <!-- Machine Down Checkbox -->
                <div class="col-span-2 flex items-center gap-3 py-2">
                    <input type="hidden" name="machine_down" value="0">
                    <input type="checkbox" name="machine_down" id="machine_down" value="1" {{ old('machine_down', $procurement->machine_down) ? 'checked' : '' }}
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
                          placeholder="Jelaskan secara mendetail gejala kerusakan...Required">{{ old('description', $procurement->description) }}</textarea>
            </div>

            <!-- Reason / Justification -->
            <div>
                <label for="reason" class="block text-label-md font-label-md text-on-surface font-semibold mb-2">
                    Alasan Pengadaan <span class="text-error">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" required
                          class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-body-md text-sm" 
                          placeholder="Jelaskan mengapa komponen ini perlu dibeli sekarang...Required">{{ old('reason', $procurement->reason) }}</textarea>
            </div>

            <!-- Action buttons -->
            <div class="pt-6 border-t border-outline-variant flex flex-wrap gap-4 justify-between">
                <a href="{{ route('procurements.show', $procurement->id) }}" class="px-6 py-2.5 border border-outline text-secondary hover:bg-surface-container rounded-lg font-body-md font-semibold transition-colors text-sm">
                    Batal
                </a>
                <div class="flex gap-2">
                    <button type="submit" name="action" value="draft" class="border border-primary text-primary hover:bg-primary-fixed px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Simpan Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-lg font-body-md font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                        Simpan & Ajukan
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
