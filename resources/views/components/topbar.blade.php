@props([
    'title' => 'MRM System',
    'subnav' => [],
    'activeSubnav' => null
])

<header class="fixed top-0 right-0 w-[calc(100%-208px)] h-16 bg-surface-bright dark:bg-surface-dim border-b border-outline-variant dark:border-outline flex justify-between items-center px-margin-desktop z-40 shadow-sm dark:shadow-none ml-52">
    <div class="flex items-center gap-8">
        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface dark:text-on-surface">{{ $title }}</h2>
        
        @if(!empty($subnav))
            <nav class="hidden md:flex gap-6">
                @foreach($subnav as $label => $url)
                    @php
                        $isActive = ($activeSubnav === $label);
                    @endphp
                    <a class="{{ $isActive ? 'text-primary dark:text-primary-fixed border-b-2 border-primary dark:border-primary-fixed pb-1' : 'text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed' }} transition-all font-body-md text-body-md" 
                       href="{{ $url }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
    
    <div class="flex items-center gap-4">
        <!-- Emergency Report Button -->
        <a href="{{ route('maintenances.create') }}" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-lg font-body-md flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-[20px]" data-icon="warning">warning</span>
            Lapor Darurat
        </a>
        
        <div class="h-8 w-px bg-outline-variant mx-2"></div>
        
        <!-- Notifications & Profile Icons -->
        <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary" data-icon="notifications">notifications</span>
        
        <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary cursor-pointer hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">account_circle</span>
        </div>
    </div>
</header>
