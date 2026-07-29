@props([
    'id' => 'wms-sparepart-autocomplete',
    'machineCode',
    'placeholder' => 'Cari sparepart berdasarkan part number, brand, atau spesifikasi...',
])

<div class="relative w-full sparepart-autocomplete-wrapper" id="wrapper-{{ $id }}">
    <div class="relative">
        <input 
            type="text" 
            id="search-{{ $id }}" 
            placeholder="{{ $placeholder }}" 
            autocomplete="off" 
            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 pl-10"
        />
        <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[18px]">search</span>
    </div>

    <!-- Dropdown List -->
    <div id="list-{{ $id }}" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-56 overflow-y-auto hidden">
        <div id="no-result-{{ $id }}" class="px-4 py-3 text-xs text-slate-400 italic hidden">
            Tidak ada sparepart ditemukan.
        </div>
        <div id="results-container-{{ $id }}"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('wrapper-{{ $id }}');
        const searchInput = document.getElementById('search-{{ $id }}');
        const dropdownList = document.getElementById('list-{{ $id }}');
        const noResult = document.getElementById('no-result-{{ $id }}');
        const container = document.getElementById('results-container-{{ $id }}');

        let debounceTimer = null;
        let activeIndex = -1;
        let visibleOptions = [];

        function highlightOption(index) {
            visibleOptions.forEach(opt => opt.classList.remove('bg-slate-100'));
            if (index >= 0 && index < visibleOptions.length) {
                const target = visibleOptions[index];
                target.classList.add('bg-slate-100');
                target.scrollIntoView({ block: 'nearest' });
            }
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();
            activeIndex = -1;

            if (query.length < 2) {
                container.innerHTML = '';
                dropdownList.classList.add('hidden');
                visibleOptions = [];
                return;
            }

            debounceTimer = setTimeout(() => {
                const searchUrl = `/machine-report/public/index.php/machines/{{ $machineCode }}/spareparts/search`;
                fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        container.innerHTML = '';
                        if (data.length === 0) {
                            noResult.classList.remove('hidden');
                            dropdownList.classList.remove('hidden');
                            visibleOptions = [];
                            return;
                        }

                        noResult.classList.add('hidden');
                        data.forEach(item => {
                            const code = item.code || '';
                            const name = item.name || '';
                            const brand = item.brand || '-';
                            const location = item.location || '-';
                            const stock = item.stock !== undefined ? item.stock : 0;
                            const criticality = item.maintenance_criticality;
                            const leadTime = item.lead_time_days;

                            // Criticality Badge styling
                            let critBadge = '';
                            if (criticality) {
                                let badgeColor = 'bg-slate-100 text-slate-500';
                                if (criticality === 'A') badgeColor = 'bg-rose-100 text-rose-800';
                                if (criticality === 'B') badgeColor = 'bg-amber-100 text-amber-800';
                                critBadge = `<span class="px-1.5 py-0.25 rounded text-[8px] font-bold ${badgeColor}">Kelas ${criticality}</span>`;
                            } else {
                                critBadge = `<span class="px-1.5 py-0.25 rounded text-[8px] font-bold bg-slate-100 text-slate-400">Unmapped</span>`;
                            }

                            // Lead Time label
                            const ltLabel = leadTime !== null ? `${leadTime} hari` : '-';

                            const div = document.createElement('div');
                            div.className = 'sparepart-option px-4 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 flex flex-col gap-0.5';
                            div.setAttribute('data-code', code);
                            div.setAttribute('data-name', name);
                            div.setAttribute('data-brand', brand);
                            div.setAttribute('data-location', location);
                            div.setAttribute('data-stock', stock);
                            div.setAttribute('data-criticality', criticality || '');
                            div.setAttribute('data-lead-time', leadTime || '');

                            div.innerHTML = `
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-800">${name}</span>
                                    <span class="text-[9px] font-mono bg-slate-100 text-slate-500 px-1 py-0.25 rounded font-bold">${code}</span>
                                </div>
                                <div class="flex justify-between text-[9px] text-slate-400 font-semibold items-center">
                                    <span>Brand: ${brand} | Rak: ${location}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold ${stock > 0 ? 'text-emerald-600' : 'text-red-600'}">Stok: ${stock}</span>
                                        ${critBadge}
                                        <span class="text-slate-400">LT: ${ltLabel}</span>
                                    </div>
                                </div>
                            `;

                            div.addEventListener('click', () => {
                                selectItem(item);
                            });

                            container.appendChild(div);
                        });

                        visibleOptions = Array.from(container.querySelectorAll('.sparepart-option'));
                        dropdownList.classList.remove('hidden');
                    })
                    .catch(err => {
                        console.error('Error fetching spareparts search:', err);
                    });
            }, 300);
        });

        function selectItem(item) {
            const event = new CustomEvent('wms-sparepart-selected', {
                detail: item
            });
            wrapper.dispatchEvent(event);
            
            // Clear search
            searchInput.value = '';
            container.innerHTML = '';
            dropdownList.classList.add('hidden');
            visibleOptions = [];
            activeIndex = -1;
        }

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

        // Click outside handler
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdownList.classList.add('hidden');
            }
        });
    });
</script>
