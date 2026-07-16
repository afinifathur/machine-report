<nav class="h-screen w-52 fixed left-0 top-0 bg-surface-container dark:bg-surface-container-low border-r border-outline-variant dark:border-outline flex flex-col py-margin-desktop z-50">
    <div class="px-4 mb-6">
        <h1 class="font-headline-sm text-headline-sm font-bold text-primary dark:text-primary-fixed">MRM System</h1>
        <p class="font-label-md text-label-md opacity-70">Clinical Precision</p>
    </div>
    
    <div class="flex-1 space-y-1">
        <!-- Dashboard -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('dashboard') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('dashboard') ? 'font-semibold' : '' }}">Dashboard</span>
        </a>

        <!-- Machine Registry -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('machines.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('machines.index') }}">
            <span class="material-symbols-outlined" data-icon="precision_manufacturing">precision_manufacturing</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('machines.*') ? 'font-semibold' : '' }}">Daftar Mesin</span>
        </a>

        <!-- Maintenance -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('maintenances.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('maintenances.index') }}">
            <span class="material-symbols-outlined" data-icon="build">build</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('maintenances.*') ? 'font-semibold' : '' }}">Perawatan</span>
        </a>

        <!-- Breakdown & Downtime -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('breakdowns.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('breakdowns.index') }}">
            <span class="material-symbols-outlined" data-icon="emergency_home">emergency_home</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('breakdowns.*') ? 'font-semibold' : '' }}">Kerusakan & Downtime</span>
        </a>

        <!-- Sparepart Integration -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('spareparts.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('spareparts.index') }}">
            <span class="material-symbols-outlined" data-icon="inventory_2">inventory_2</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('spareparts.*') ? 'font-semibold' : '' }}">Integrasi Sparepart</span>
        </a>

        <!-- Planning -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('planning.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('planning.index') }}">
            <span class="material-symbols-outlined" data-icon="event_note">event_note</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('planning.*') ? 'font-semibold' : '' }}">Perencanaan</span>
        </a>

        <!-- Reports -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('reports.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('reports.index') }}">
            <span class="material-symbols-outlined" data-icon="description">description</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('reports.*') ? 'font-semibold' : '' }}">Laporan</span>
        </a>

        <!-- Administration -->
        <a class="flex items-center gap-2 px-4 py-2.5 transition-colors {{ request()->routeIs('admin.*') ? 'text-primary dark:text-primary-fixed border-r-4 border-primary dark:border-primary-fixed bg-secondary-container dark:bg-secondary-container-highest' : 'text-on-surface-variant dark:text-on-surface-variant opacity-80 hover:bg-surface-container-high dark:hover:bg-surface-container-highest' }}" 
           href="{{ route('admin.index') }}">
            <span class="material-symbols-outlined" data-icon="settings">settings</span>
            <span class="font-body-md text-body-md {{ request()->routeIs('admin.*') ? 'font-semibold' : '' }}">Administrasi</span>
        </a>
    </div>

    <!-- User Profile Widget -->
    <div class="mt-auto px-4 pt-3 border-t border-outline-variant">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary">
                <span class="material-symbols-outlined" data-icon="person">person</span>
            </div>
            <div>
                <p class="font-label-md text-label-md font-bold">Admin User</p>
                <p class="font-label-sm text-label-sm opacity-60 text-xs">System Exec.</p>
            </div>
        </div>
    </div>
</nav>
