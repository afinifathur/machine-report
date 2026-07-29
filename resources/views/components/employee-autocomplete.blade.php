@props([
    'name',
    'id',
    'selected' => '',
    'placeholder' => 'Cari nama, NIK, jabatan, atau departemen...',
    'required' => false,
])

@php
    $employees = \App\Models\Employee::with(['department', 'position'])
        ->where('employment_status', \App\Enums\EmploymentStatus::ACTIVE)
        ->where('is_assignable', true)
        ->orderBy('full_name')
        ->get();
@endphp

<div class="relative w-full employee-autocomplete-wrapper" id="wrapper-{{ $id }}">
    <div class="relative">
        <input 
            type="text" 
            id="search-{{ $id }}" 
            placeholder="{{ $placeholder }}" 
            autocomplete="off" 
            @if($required) required @endif
            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 pl-10"
        />
        <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[18px]">search</span>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $selected }}" />
    </div>

    <!-- Dropdown List -->
    <div id="list-{{ $id }}" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-56 overflow-y-auto hidden">
        <div id="no-result-{{ $id }}" class="px-4 py-3 text-xs text-slate-400 italic hidden">
            No matching employee found.
        </div>
        
        @foreach($employees as $emp)
            @php
                $dispText = $emp->employee_number . ' • ' . $emp->full_name;
                $searchText = strtolower(implode(' ', [
                    $emp->employee_number,
                    $emp->employee_code,
                    $emp->full_name,
                    optional($emp->position)->name,
                    optional($emp->department)->name
                ]));
            @endphp
            <div 
                class="option-{{ $id }} px-4 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 flex flex-col gap-0.5"
                data-value="{{ $emp->full_name }}"
                data-display="{{ $dispText }}"
                data-search="{{ $searchText }}"
            >
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-800">{{ $emp->full_name }}</span>
                    <span class="text-[9px] font-mono bg-slate-100 text-slate-500 px-1 py-0.25 rounded font-bold">{{ $emp->employee_number }}</span>
                </div>
                <div class="flex justify-between text-[9px] text-slate-400 font-semibold">
                    <span>{{ optional($emp->position)->name }}</span>
                    <span>{{ optional($emp->department)->name }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('wrapper-{{ $id }}');
        const searchInput = document.getElementById('search-{{ $id }}');
        const hiddenInput = document.getElementById('{{ $id }}');
        const dropdownList = document.getElementById('list-{{ $id }}');
        const noResult = document.getElementById('no-result-{{ $id }}');
        const options = document.querySelectorAll('.option-{{ $id }}');

        let activeIndex = -1;
        let visibleOptions = [];

        function updateVisibleOptions() {
            visibleOptions = Array.from(options).filter(opt => !opt.classList.contains('hidden'));
        }

        function highlightOption(index) {
            visibleOptions.forEach(opt => opt.classList.remove('bg-slate-100'));
            if (index >= 0 && index < visibleOptions.length) {
                const target = visibleOptions[index];
                target.classList.add('bg-slate-100');
                target.scrollIntoView({ block: 'nearest' });
            }
        }

        // Set initial value if matches any employee name
        const initialVal = hiddenInput.value;
        if (initialVal) {
            let matched = false;
            options.forEach(opt => {
                if (opt.getAttribute('data-value') === initialVal) {
                    searchInput.value = opt.getAttribute('data-display');
                    matched = true;
                }
            });
            if (!matched) {
                // If it is from seeded/migrated name that isn't fully linked
                searchInput.value = initialVal;
            }
        }

        searchInput.addEventListener('focus', () => {
            dropdownList.classList.remove('hidden');
            updateVisibleOptions();
        });

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0;
            activeIndex = -1;
            options.forEach(opt => opt.classList.remove('bg-slate-100'));

            options.forEach(opt => {
                const searchContent = opt.getAttribute('data-search');
                if (searchContent.includes(query)) {
                    opt.classList.remove('hidden');
                    visibleCount++;
                } else {
                    opt.classList.add('hidden');
                }
            });

            if (visibleCount === 0) {
                noResult.classList.remove('hidden');
            } else {
                noResult.classList.add('hidden');
            }
            dropdownList.classList.remove('hidden');
            updateVisibleOptions();
        });

        searchInput.addEventListener('keydown', (e) => {
            if (dropdownList.classList.contains('hidden')) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    dropdownList.classList.remove('hidden');
                    e.preventDefault();
                }
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex++;
                if (activeIndex >= visibleOptions.length) activeIndex = 0;
                highlightOption(activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex--;
                if (activeIndex < 0) activeIndex = visibleOptions.length - 1;
                highlightOption(activeIndex);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && activeIndex < visibleOptions.length) {
                    e.preventDefault();
                    visibleOptions[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                dropdownList.classList.add('hidden');
                searchInput.blur();
            }
        });

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                const val = opt.getAttribute('data-value');
                const disp = opt.getAttribute('data-display');
                
                hiddenInput.value = val;
                searchInput.value = disp;
                dropdownList.classList.add('hidden');
            });
        });

        // Click outside handler
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdownList.classList.add('hidden');
            }
        });
    });
</script>
