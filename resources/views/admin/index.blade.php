<x-layouts.app 
    title="Administrasi Sistem | Sistem MRM"
    topbar-title="Administrasi"
>
    <x-breadcrumb :items="['Administrasi' => '']" />

    <!-- Dashboard Header -->
    <div class="mb-6">
        <h1 class="font-headline-md text-headline-md text-on-surface">Administrasi Panel Kontrol</h1>
        <p class="text-body-md text-on-surface-variant">Konfigurasi variabel master sistem, pemetaan data fisik karyawan, hak akses otentikasi login, dan unit organisasi departemen.</p>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="text-xs font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="text-xs font-bold">{{ session('error') }}</span>
        </div>
    @endif

    <!-- TAB NAVIGATION HEADER -->
    <div class="flex border-b border-outline-variant gap-4 mb-6">
        <button onclick="switchTab('employees')" id="tab-btn-employees" class="pb-3 text-body-md font-bold border-b-2 border-primary text-primary transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">badge</span>
            Master Karyawan
        </button>
        <button onclick="switchTab('users')" id="tab-btn-users" class="pb-3 text-body-md font-bold border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
            User Login
        </button>
        <button onclick="switchTab('departments')" id="tab-btn-departments" class="pb-3 text-body-md font-bold border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">corporate_fare</span>
            Master Departemen
        </button>
        <button onclick="switchTab('categories')" id="tab-btn-categories" class="pb-3 text-body-md font-bold border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">category</span>
            Master Kategori Mesin
        </button>
    </div>

    <!-- TAB CONTENTS -->

    <!-- Tab 1: Master Karyawan -->
    <div id="tab-content-employees" class="space-y-4">
        <div class="flex justify-between items-center mb-2">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-slate-800">Daftar Karyawan Terdaftar</h3>
                <p class="text-xs text-slate-400">Identitas fisik personil operasional pabrik yang berhak menerima penugasan kerja permesinan.</p>
            </div>
            <button onclick="openAddEmployeeModal()" class="bg-primary hover:bg-primary-container text-on-primary font-bold px-4 py-2.5 rounded-lg text-xs flex items-center gap-2 shadow-none">
                <span class="material-symbols-outlined text-[16px]">person_add</span>
                Tambah Karyawan
            </button>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-none">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs text-on-surface">
                    <thead class="bg-surface-container-low font-bold text-on-surface-variant border-b border-outline-variant">
                        <tr>
                            <th class="p-3">Kode Karyawan</th>
                            <th class="p-3">No. Induk</th>
                            <th class="p-3">Nama Lengkap</th>
                            <th class="p-3">Departemen</th>
                            <th class="p-3">Jabatan</th>
                            <th class="p-3 text-center">Status Kerja</th>
                            <th class="p-3">Keahlian</th>
                            <th class="p-3 text-center">Level</th>
                            <th class="p-3 text-center">Assignable</th>
                            <th class="p-3">Linked Login</th>
                            <th class="p-3">Tanggal Join</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($employees as $emp)
                            @php
                                $statusBadgeClass = match($emp->employment_status->value) {
                                    'ACTIVE' => 'bg-green-100 text-green-800 border border-green-200',
                                    'RESIGNED' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                    'RETIRED' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    'TRANSFERRED' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                                    'LEAVE' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-3 font-mono font-bold text-slate-900">{{ $emp->employee_code }}</td>
                                <td class="p-3 font-mono text-slate-500">{{ $emp->employee_number }}</td>
                                <td class="p-3 font-bold text-slate-800">{{ $emp->full_name }}</td>
                                <td class="p-3 text-slate-600 font-semibold">{{ $emp->department->name }}</td>
                                <td class="p-3 text-slate-600 font-semibold">{{ $emp->position->name }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $statusBadgeClass }}">
                                        {{ $emp->employment_status->value }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500 font-semibold">{{ $emp->primary_skill ?? '-' }}</td>
                                <td class="p-3 text-center font-bold text-slate-600">{{ $emp->level ?? '-' }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $emp->is_assignable ? 'bg-blue-100 text-blue-800' : 'bg-slate-150 text-slate-500' }}">
                                        {{ $emp->is_assignable ? 'YES' : 'NO' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($emp->user)
                                        <span class="font-bold text-primary font-mono text-[10px]">{{ $emp->user->email }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-[10px]">No Account</span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-400 font-mono">{{ $emp->employment_start_date->format('d/m/y') }}</td>
                                <td class="p-3 text-right">
                                    <button 
                                        type="button" 
                                        onclick="openEditEmployeeModal({{ json_encode($emp) }})"
                                        class="text-primary hover:bg-primary-container hover:text-on-primary-container px-2 py-1.5 rounded-lg inline-flex items-center gap-1 text-[10px] font-bold transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="p-8 text-center text-slate-400 italic">Belum ada data karyawan terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: User Login -->
    <div id="tab-content-users" class="hidden space-y-4">
        <div class="flex justify-between items-center mb-2">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-slate-800">Daftar Akun Login Otentikasi</h3>
                <p class="text-xs text-slate-400">Akun kredensial akses login beserta peran izin keamanan otentikasi.</p>
            </div>
            <button onclick="openAddUserModal()" class="bg-primary hover:bg-primary-container text-on-primary font-bold px-4 py-2.5 rounded-lg text-xs flex items-center gap-2 shadow-none">
                <span class="material-symbols-outlined text-[16px]">person_add</span>
                Tambah Akun Login
            </button>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-none">
            <table class="w-full border-collapse text-left text-xs text-on-surface">
                <thead class="bg-surface-container-low font-bold text-on-surface-variant border-b border-outline-variant">
                    <tr>
                        <th class="p-3">Nama Pengguna</th>
                        <th class="p-3">Email Kredensial</th>
                        <th class="p-3 text-center">Hak Akses / Peran</th>
                        <th class="p-3">Karyawan Terasosiasi</th>
                        <th class="p-3">Waktu Terdaftar</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-3 font-bold text-slate-800 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-bold text-[10px] uppercase">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                {{ $user->name }}
                            </td>
                            <td class="p-3 font-mono text-slate-550">{{ $user->email }}</td>
                            <td class="p-3 text-center">
                                @foreach($user->roles as $role)
                                    <span class="inline-block text-[9px] uppercase font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="p-3">
                                @if($user->employee)
                                    <span class="font-bold text-slate-850 font-mono text-[10px]">{{ $user->employee->full_name }} ({{ $user->employee->employee_code }})</span>
                                @else
                                    <span class="text-slate-400 italic text-[10px]">Belum terhubung</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-400">{{ $user->created_at->format('d M Y H:i') }}</td>
                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openEditUserModal({{ json_encode($user) }})"
                                        class="text-primary hover:bg-primary-container px-2 py-1 rounded text-[10px] font-bold transition-colors"
                                    >
                                        Edit
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun login ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error hover:bg-error-container hover:text-on-error-container px-2 py-1 rounded inline-flex items-center gap-1 text-[10px] font-bold transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic font-medium pr-2.5 self-center">Aktif (Anda)</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada akun login terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Master Departemen -->
    <div id="tab-content-departments" class="hidden space-y-4">
        <div>
            <h3 class="font-headline-sm text-headline-sm text-slate-800">Master Departemen Operasional</h3>
            <p class="text-xs text-slate-400">Grup departemen untuk pemetaan lokasi kepemilikan mesin dan sumber pelaporan kerusakan.</p>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-none">
            <table class="w-full border-collapse text-left text-xs text-on-surface">
                <thead class="bg-surface-container-low font-bold text-on-surface-variant border-b border-outline-variant">
                    <tr>
                        <th class="p-4">Kode Departemen</th>
                        <th class="p-4">Nama Departemen</th>
                        <th class="p-4 text-center">Urutan Sortir</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($departments as $dept)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-mono font-bold text-slate-800">{{ $dept->code }}</td>
                            <td class="p-4 text-slate-700 font-semibold">{{ $dept->name }}</td>
                            <td class="p-4 text-center text-slate-400 font-mono">{{ $dept->sort_order }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-green-100 text-green-800 border border-green-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Master Kategori Mesin -->
    <div id="tab-content-categories" class="hidden space-y-4">
        <div>
            <h3 class="font-headline-sm text-headline-sm text-slate-800">Master Kategori Alat/Mesin</h3>
            <p class="text-xs text-slate-400">Kategori instrumen manufaktur untuk membagi struktur SOP pemeliharaan preventif.</p>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-none">
            <table class="w-full border-collapse text-left text-xs text-on-surface">
                <thead class="bg-surface-container-low font-bold text-on-surface-variant border-b border-outline-variant">
                    <tr>
                        <th class="p-4">Kode Kategori</th>
                        <th class="p-4">Nama Kategori</th>
                        <th class="p-4 text-center">Urutan Sortir</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($categories as $cat)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-mono font-bold text-slate-800">{{ $cat->code }}</td>
                            <td class="p-4 text-slate-700 font-semibold">{{ $cat->name }}</td>
                            <td class="p-4 text-center text-slate-400 font-mono">{{ $cat->sort_order }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-green-100 text-green-800 border border-green-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- EMPLOYEE MODAL (ADD & EDIT) -->
    <div id="employee-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeEmployeeModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white border border-outline-variant p-6 shadow-xl transition-all w-full max-w-lg">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    <h3 id="employee-modal-title" class="text-base font-black text-slate-800">Daftarkan Karyawan Baru</h3>
                </div>
                <form id="employee-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="employee-method" value="POST" />
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="emp-number" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Nomor Induk Karyawan</label>
                            <input type="text" name="employee_number" id="emp-number" required placeholder="Contoh: 9199" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="emp-name" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="emp-name" required placeholder="Contoh: Deny Romadhon" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="emp-dept" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Departemen</label>
                            <select name="department_id" id="emp-dept" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="emp-pos" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Jabatan</label>
                            <select name="position_id" id="emp-pos" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="emp-status" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Kepegawaian</label>
                            <select name="employment_status" id="emp-status" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="RESIGNED">RESIGNED</option>
                                <option value="RETIRED">RETIRED</option>
                                <option value="TRANSFERRED">TRANSFERRED</option>
                                <option value="LEAVE">LEAVE</option>
                            </select>
                        </div>
                        <div>
                            <label for="emp-start" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tanggal Mulai Kerja</label>
                            <input type="date" name="employment_start_date" id="emp-start" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div>
                            <label for="emp-end" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tanggal Keluar Kerja (Optional)</label>
                            <input type="date" name="employment_end_date" id="emp-end" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center cursor-pointer gap-2 select-none">
                                <input type="checkbox" name="is_assignable" id="emp-assignable" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-xs font-bold text-slate-700">Dapat Menerima Tiket MTC</span>
                            </label>
                        </div>
                        <div>
                            <label for="emp-skill" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Keahlian Utama (Optional)</label>
                            <input type="text" name="primary_skill" id="emp-skill" placeholder="Contoh: Mechanical, Electrical" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="emp-level" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tingkat Keahlian (Level)</label>
                            <input type="text" name="level" id="emp-level" placeholder="Contoh: Junior, Senior" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="emp-phone" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">No. Telp / HP</label>
                            <input type="text" name="phone" id="emp-phone" placeholder="Contoh: 0812xxxx" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="emp-user" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Akun Login Terkait (Optional)</label>
                            <select name="linked_user_id" id="emp-user" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tanpa Akun Login</option>
                                @foreach($unlinkedUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="emp-remarks" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Catatan Tambahan</label>
                        <textarea name="remarks" id="emp-remarks" rows="2" placeholder="Catatan internal..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeEmployeeModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-primary text-on-primary hover:bg-primary-container rounded-xl shadow-none transition-colors">Simpan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- USER MODAL (ADD & EDIT) -->
    <div id="user-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeUserModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white border border-outline-variant p-6 shadow-xl transition-all w-full max-w-sm">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary">manage_accounts</span>
                    <h3 id="user-modal-title" class="text-base font-black text-slate-800">Daftarkan Kredensial Baru</h3>
                </div>
                <form id="user-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="user-method" value="POST" />
                    
                    <div class="space-y-3.5 mb-4">
                        <div id="user-name-wrapper">
                            <label for="user-name" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Nama Pengguna</label>
                            <input type="text" name="name" id="user-name" placeholder="Contoh: Budi Santoso" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="user-email" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Email / Kredensial</label>
                            <input type="email" name="email" id="user-email" required placeholder="budi@peroniks.com" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div id="user-password-wrapper">
                            <label for="user-password" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Kata Sandi</label>
                            <input type="password" name="password" id="user-password" placeholder="Min. 6 Karakter" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label for="user-role" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Hak Akses / Peran</label>
                            <div class="relative">
                                <select name="role" id="user-role" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="user-employee" class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Hubungkan ke Karyawan</label>
                            <select name="linked_employee_id" id="user-employee" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tidak Terhubung Karyawan</option>
                                @foreach($unlinkedEmployees as $e)
                                    <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeUserModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-primary text-on-primary hover:bg-primary-container rounded-xl shadow-none transition-colors">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Javascript Navigation and Modal Handlers -->
    <script>
        const unlinkedUsers = @json($unlinkedUsers);
        const unlinkedEmployees = @json($unlinkedEmployees);

        function switchTab(tabId) {
            const tabs = ['employees', 'users', 'departments', 'categories'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                
                if (t === tabId) {
                    btn.classList.add('border-primary', 'text-primary');
                    btn.classList.remove('border-transparent', 'text-on-surface-variant');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-primary', 'text-primary');
                    btn.classList.add('border-transparent', 'text-on-surface-variant');
                    content.classList.add('hidden');
                }
            });
        }

        // EMPLOYEE MODALS
        function openAddEmployeeModal() {
            document.getElementById('employee-modal-title').innerText = 'Daftarkan Karyawan Baru';
            document.getElementById('employee-form').action = "{{ route('admin.employees.store') }}";
            document.getElementById('employee-method').value = 'POST';
            
            document.getElementById('emp-number').value = '';
            document.getElementById('emp-name').value = '';
            document.getElementById('emp-dept').selectedIndex = 0;
            document.getElementById('emp-pos').selectedIndex = 0;
            document.getElementById('emp-status').value = 'ACTIVE';
            document.getElementById('emp-start').value = "{{ date('Y-m-d') }}";
            document.getElementById('emp-end').value = '';
            document.getElementById('emp-assignable').checked = true;
            document.getElementById('emp-skill').value = '';
            document.getElementById('emp-level').value = '';
            document.getElementById('emp-phone').value = '';
            document.getElementById('emp-remarks').value = '';
            
            const userSelect = document.getElementById('emp-user');
            userSelect.innerHTML = '<option value="">Tanpa Akun Login</option>';
            unlinkedUsers.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.text = u.name + ' (' + u.email + ')';
                userSelect.appendChild(opt);
            });

            document.getElementById('employee-modal').classList.remove('hidden');
        }

        function openEditEmployeeModal(emp) {
            document.getElementById('employee-modal-title').innerText = 'Perbarui Data Karyawan';
            
            // Build action route template
            let routeTemplate = "{{ route('admin.employees.update', ':id') }}";
            document.getElementById('employee-form').action = routeTemplate.replace(':id', emp.id);
            document.getElementById('employee-method').value = 'PUT';
            
            document.getElementById('emp-number').value = emp.employee_number;
            document.getElementById('emp-name').value = emp.full_name;
            document.getElementById('emp-dept').value = emp.department_id;
            document.getElementById('emp-pos').value = emp.position_id;
            document.getElementById('emp-status').value = emp.employment_status;
            
            document.getElementById('emp-start').value = emp.employment_start_date ? emp.employment_start_date.substring(0, 10) : '';
            document.getElementById('emp-end').value = emp.employment_end_date ? emp.employment_end_date.substring(0, 10) : '';
            document.getElementById('emp-assignable').checked = !!emp.is_assignable;
            document.getElementById('emp-skill').value = emp.primary_skill || '';
            document.getElementById('emp-level').value = emp.level || '';
            document.getElementById('emp-phone').value = emp.phone || '';
            document.getElementById('emp-remarks').value = emp.remarks || '';
            
            const userSelect = document.getElementById('emp-user');
            userSelect.innerHTML = '<option value="">Tanpa Akun Login</option>';
            
            if (emp.user) {
                const opt = document.createElement('option');
                opt.value = emp.user.id;
                opt.text = emp.user.name + ' (' + emp.user.email + ')';
                opt.selected = true;
                userSelect.appendChild(opt);
            }
            
            unlinkedUsers.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.text = u.name + ' (' + u.email + ')';
                userSelect.appendChild(opt);
            });

            document.getElementById('employee-modal').classList.remove('hidden');
        }

        function closeEmployeeModal() {
            document.getElementById('employee-modal').classList.add('hidden');
        }

        // USER MODALS
        function openAddUserModal() {
            document.getElementById('user-modal-title').innerText = 'Daftarkan Kredensial Baru';
            document.getElementById('user-form').action = "{{ route('admin.users.store') }}";
            document.getElementById('user-method').value = 'POST';
            
            document.getElementById('user-name-wrapper').classList.remove('hidden');
            document.getElementById('user-password-wrapper').classList.remove('hidden');
            document.getElementById('user-name').required = true;
            document.getElementById('user-password').required = true;
            
            document.getElementById('user-name').value = '';
            document.getElementById('user-email').value = '';
            document.getElementById('user-password').value = '';
            document.getElementById('user-role').selectedIndex = 0;
            
            const empSelect = document.getElementById('user-employee');
            empSelect.innerHTML = '<option value="">Tidak Terhubung Karyawan</option>';
            unlinkedEmployees.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.text = e.full_name + ' (' + e.employee_code + ')';
                empSelect.appendChild(opt);
            });

            document.getElementById('user-modal').classList.remove('hidden');
        }

        function openEditUserModal(user) {
            document.getElementById('user-modal-title').innerText = 'Perbarui Akun Login';
            
            let routeTemplate = "{{ route('admin.users.update', ':id') }}";
            document.getElementById('user-form').action = routeTemplate.replace(':id', user.id);
            document.getElementById('user-method').value = 'PUT';
            
            document.getElementById('user-name-wrapper').classList.add('hidden');
            document.getElementById('user-password-wrapper').classList.add('hidden');
            document.getElementById('user-name').required = false;
            document.getElementById('user-password').required = false;
            
            document.getElementById('user-email').value = user.email;
            
            const userRole = user.roles && user.roles.length > 0 ? user.roles[0].name : '';
            document.getElementById('user-role').value = userRole;

            const empSelect = document.getElementById('user-employee');
            empSelect.innerHTML = '<option value="">Tidak Terhubung Karyawan</option>';
            
            if (user.employee) {
                const opt = document.createElement('option');
                opt.value = user.employee.id;
                opt.text = user.employee.full_name + ' (' + user.employee.employee_code + ')';
                opt.selected = true;
                empSelect.appendChild(opt);
            }
            
            unlinkedEmployees.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.text = e.full_name + ' (' + e.employee_code + ')';
                empSelect.appendChild(opt);
            });

            document.getElementById('user-modal').classList.remove('hidden');
        }

        function closeUserModal() {
            document.getElementById('user-modal').classList.add('hidden');
        }
    </script>
</x-layouts.app>
