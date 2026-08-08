<x-layouts.app 
    title="{{ $type === 'corrective' ? 'Lapor Kerusakan Mesin' : 'Buat Rencana PM' }} | Sistem MRM"
    topbar-title="{{ $type === 'corrective' ? 'Lapor Kerusakan' : 'Buat Rencana PM' }}"
>
    <x-breadcrumb :items="[
        $type === 'corrective' ? 'Kerusakan' : 'Preventive Maintenance' => $type === 'corrective' ? route('breakdowns.index') : route('preventive.index'),
        $type === 'corrective' ? 'Lapor Kerusakan' : 'Buat Rencana PM' => ''
    ]" />

    <!-- Form Container Card -->
    <div class="max-w-3xl mx-auto bg-surface-container-lowest border border-outline-variant p-6 rounded-2xl shadow-none mt-4">
        <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
            @if($type === 'corrective')
                <span class="material-symbols-outlined text-rose-600 text-[26px]">emergency_share</span>
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-black text-slate-800">Form Laporan Breakdown</h2>
                    <p class="text-[11px] text-slate-400">Silakan isi detail laporan gejala kerusakan mesin secara lengkap.</p>
                </div>
            @else
                <span class="material-symbols-outlined text-blue-600 text-[26px]">event_repeat</span>
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-black text-slate-800">Form Pembuatan PM Plan</h2>
                    <p class="text-[11px] text-slate-400">Silakan isi detail perencanaan pemeliharaan preventif mesin.</p>
                </div>
            @endif
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-800 space-y-1 mb-6 animate-pulse">
                <p class="font-bold">Beberapa input tidak valid:</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $type === 'corrective' ? route('planning.store-breakdown') : route('planning.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}" />

            <!-- Form Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                
                <!-- Machine Autocomplete -->
                <div class="space-y-1.5 relative" id="machine-autocomplete-container">
                    <label class="block text-xs font-bold uppercase text-slate-400">Pilih Aset Mesin</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="machine-search-input" 
                            placeholder="Ketik Kode atau Nama Mesin..." 
                            autocomplete="off" 
                            required
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 {{ $type === 'corrective' ? 'focus:ring-rose-500' : 'focus:ring-blue-500' }} pl-10"
                        />
                        <span class="material-symbols-outlined absolute left-3 top-3 text-slate-450 text-[18px]">search</span>
                        <input type="hidden" name="machine_id" id="real-machine-id" required value="{{ old('machine_id') }}" />
                    </div>
                    
                    <!-- Autocomplete Dropdown List -->
                    <div id="machine-dropdown-list" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-56 overflow-y-auto hidden">
                        @foreach ($machines as $machine)
                            <div 
                                class="machine-option-item px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-700 border-b border-slate-100 last:border-0 flex justify-between"
                                data-id="{{ $machine->id }}"
                                data-search-text="{{ strtolower($machine->code . ' ' . $machine->name) }}"
                                data-display-text="{{ $machine->code }} - {{ $machine->name }}"
                            >
                                <span class="font-mono font-bold text-primary">{{ $machine->code }}</span>
                                <span class="text-slate-500">{{ $machine->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($type === 'corrective')
                    <!-- Department Autocomplete (Corrective only) -->
                    <div class="space-y-1.5 relative" id="dept-autocomplete-container">
                        <label class="block text-xs font-bold uppercase text-slate-400">Departemen Pelapor</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="dept-search-input" 
                                placeholder="Ketik nama departemen..." 
                                autocomplete="off" 
                                required
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 pl-10"
                            />
                            <span class="material-symbols-outlined absolute left-3 top-3 text-slate-450 text-[18px]">domain</span>
                            <input type="hidden" name="reported_department" id="real-dept-name" required value="{{ old('reported_department') }}" />
                        </div>
                        
                        <!-- Autocomplete Dropdown List -->
                        <div id="dept-dropdown-list" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-56 overflow-y-auto hidden">
                            @foreach ($departments as $dept)
                                <div 
                                    class="dept-option-item px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-700 border-b border-slate-100 last:border-0"
                                    data-name="{{ $dept->name }}"
                                    data-search-text="{{ strtolower($dept->name) }}"
                                >
                                    {{ $dept->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Reported By -->
                    <div class="space-y-1.5">
                        <label for="reported_by" class="block text-xs font-bold uppercase text-slate-400">Dilaporkan Oleh (Operator/Supervisor)</label>
                        <input 
                            type="text" 
                            name="reported_by" 
                            id="reported_by" 
                            value="{{ old('reported_by') }}" 
                            placeholder="Nama pelapor..."
                            required 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        />
                    </div>

                    <!-- Priority Selection -->
                    <div class="space-y-1.5">
                        <label for="priority" class="block text-xs font-bold uppercase text-slate-400">Tingkat Prioritas Perbaikan</label>
                        <div class="relative">
                            <select name="priority" id="priority" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 appearance-none">
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Tinggi (Masalah Kritis / Downtime)</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Kritis (Emergency / Line Stop)</option>
                                <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Sedang (Mempengaruhi Kualitas)</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Rendah (Gejala Ringan)</option>
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Incident Time (reported_at) -->
                    <div class="space-y-1.5">
                        <label for="reported_at" class="block text-xs font-bold uppercase text-slate-400">Incident Time (Waktu Mulai Downtime)</label>
                        <input 
                            type="datetime-local" 
                            name="reported_at" 
                            id="reported_at" 
                            value="{{ old('reported_at', now()->format('Y-m-d\TH:i')) }}" 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        />
                        <span class="text-[10px] text-slate-400 block leading-normal mt-0.5">When the machine actually stopped.</span>
                    </div>

                    <!-- Target Machine Running (scheduled_date) -->
                    <div class="space-y-1.5">
                        <label for="scheduled_date" class="block text-xs font-bold uppercase text-slate-400">Target Machine Running (Target Selesai)</label>
                        <input 
                            type="datetime-local" 
                            name="scheduled_date" 
                            id="scheduled_date" 
                            value="{{ old('scheduled_date', now()->addHours(4)->format('Y-m-d\TH:i')) }}" 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        />
                        <span class="text-[10px] text-slate-400 block leading-normal mt-0.5">When the machine is expected to be operational again.</span>
                    </div>
                @else
                    <!-- SOP / Maintenance Template (Preventive only) -->
                    <div class="space-y-1.5">
                        <label for="maintenance_template_id" class="block text-xs font-bold uppercase text-slate-400">Paket Perawatan (SOP)</label>
                        <div class="relative">
                            <select name="maintenance_template_id" id="maintenance_template_id" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option value="">-- Pilih SOP Perawatan (Opsional) --</option>
                                @foreach ($templates as $tpl)
                                    <option value="{{ $tpl->id }}" {{ old('maintenance_template_id') == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->machine_category }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 block leading-normal mt-0.5">SOP dapat ditambahkan nanti. Untuk saat ini rencana PM tetap dapat dibuat tanpa paket SOP.</span>
                    </div>

                    <!-- Scheduled Date (Preventive only) -->
                    <div class="space-y-1.5">
                        <label for="scheduled_date" class="block text-xs font-bold uppercase text-slate-400">Tanggal Rencana PM</label>
                        <input 
                            type="date" 
                            name="scheduled_date" 
                            id="scheduled_date" 
                            value="{{ old('scheduled_date', now()->format('Y-m-d')) }}" 
                            required
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Priority Selection (Preventive version) -->
                    <div class="space-y-1.5">
                        <label for="priority" class="block text-xs font-bold uppercase text-slate-400">Prioritas Perawatan</label>
                        <div class="relative">
                            <select name="priority" id="priority" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Tinggi</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Kritis</option>
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Technician (Preventive only) -->
                    <div class="space-y-1.5">
                        <label for="assigned_technician" class="block text-xs font-bold uppercase text-slate-400 font-semibold">Tugaskan Teknisi (Optional)</label>
                        <x-employee-autocomplete 
                            name="assigned_technician" 
                            id="assigned_technician" 
                            selected="{{ old('assigned_technician') }}"
                            required="false"
                            placeholder="Pilih nama teknisi pelaksana..."
                        />
                    </div>
                @endif

            </div>

            <!-- Description / Notes -->
            <div class="space-y-1.5 pt-2">
                @if($type === 'corrective')
                    <label for="breakdown_description" class="block text-xs font-bold uppercase text-slate-400">Deskripsi Kerusakan & Gejala</label>
                    <textarea 
                        name="breakdown_description" 
                        id="breakdown_description" 
                        rows="5" 
                        placeholder="Contoh: Sensor spindle mati, muncul error alarm E-102 pada layar monitor, dll..." 
                        required 
                        class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                    >{{ old('breakdown_description') }}</textarea>
                @else
                    <label for="notes" class="block text-xs font-bold uppercase text-slate-400">Catatan Perencanaan PM</label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        rows="5" 
                        placeholder="Contoh: Lakukan kalibrasi tambahan setelah running test..." 
                        class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >{{ old('notes') }}</textarea>
                @endif
            </div>

            <!-- Form Action Buttons -->
            <div class="flex justify-end gap-2.5 pt-4 border-t border-outline-variant mt-4">
                <a href="{{ $type === 'corrective' ? route('breakdowns.index') : route('preventive.index') }}" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors flex items-center">
                    Batal
                </a>
                <button type="submit" class="{{ $type === 'corrective' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-200' }} text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-none transition-all active:scale-[0.99] inline-flex items-center gap-1.5">
                    @if($type === 'corrective')
                        <span class="material-symbols-outlined text-[16px]">add_alert</span>
                        Kirim Laporan Kerusakan
                    @else
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Buat Rencana PM
                    @endif
                </button>
            </div>

        </form>
    </div>

    <!-- Javascript Navigation and Autocomplete Handlers -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Machine Autocomplete Handlers
            const machineSearch = document.getElementById('machine-search-input');
            const machineRealId = document.getElementById('real-machine-id');
            const machineDropdown = document.getElementById('machine-dropdown-list');
            const machineOptions = document.querySelectorAll('.machine-option-item');

            machineSearch.addEventListener('focus', () => {
                machineDropdown.classList.remove('hidden');
            });

            machineSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                machineOptions.forEach(opt => {
                    const text = opt.getAttribute('data-search-text');
                    if (text.includes(query)) {
                        opt.classList.remove('hidden');
                    } else {
                        opt.classList.add('hidden');
                    }
                });
                machineDropdown.classList.remove('hidden');
            });

            machineOptions.forEach(opt => {
                opt.addEventListener('click', () => {
                    const id = opt.getAttribute('data-id');
                    const text = opt.getAttribute('data-display-text');
                    machineSearch.value = text;
                    machineRealId.value = id;
                    machineDropdown.classList.add('hidden');
                });
            });

            // Department Autocomplete Handlers (CM only)
            const deptSearch = document.getElementById('dept-search-input');
            const deptRealName = document.getElementById('real-dept-name');
            const deptDropdown = document.getElementById('dept-dropdown-list');
            const deptOptions = document.querySelectorAll('.dept-option-item');

            if (deptSearch) {
                deptSearch.addEventListener('focus', () => {
                    deptDropdown.classList.remove('hidden');
                });

                deptSearch.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase();
                    deptOptions.forEach(opt => {
                        const text = opt.getAttribute('data-search-text');
                        if (text.includes(query)) {
                            opt.classList.remove('hidden');
                        } else {
                            opt.classList.add('hidden');
                        }
                    });
                    deptDropdown.classList.remove('hidden');
                });

                deptOptions.forEach(opt => {
                    opt.addEventListener('click', () => {
                        const name = opt.getAttribute('data-name');
                        deptSearch.value = name;
                        deptRealName.value = name;
                        deptDropdown.classList.add('hidden');
                    });
                });
            }

            // Click outside handler to hide autocomplete panels
            document.addEventListener('click', (e) => {
                if (!document.getElementById('machine-autocomplete-container').contains(e.target)) {
                    machineDropdown.classList.add('hidden');
                }
                const deptContainer = document.getElementById('dept-autocomplete-container');
                if (deptContainer && !deptContainer.contains(e.target)) {
                    deptDropdown.classList.add('hidden');
                }
            });

            // Pre-populate autocomplete inputs if validation fails or value was loaded
            const initialMachineId = machineRealId.value;
            if (initialMachineId) {
                const matchedOpt = Array.from(machineOptions).find(opt => opt.getAttribute('data-id') == initialMachineId);
                if (matchedOpt) {
                    machineSearch.value = matchedOpt.getAttribute('data-display-text');
                }
            }
            
            if (deptRealName) {
                const initialDept = deptRealName.value;
                if (initialDept) {
                    const matchedOpt = Array.from(deptOptions).find(opt => opt.getAttribute('data-name') == initialDept);
                    if (matchedOpt) {
                        deptSearch.value = matchedOpt.getAttribute('data-name');
                    }
                }
            }
        });
    </script>
</x-layouts.app>
