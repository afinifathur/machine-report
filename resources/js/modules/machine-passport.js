/**
 * Machine Passport Module for MRM System
 * Handles tabs, checklist navigation, sparepart mapping, ISO documents, gallery & lightbox.
 */

import { escapeHtml, setText, getCsrfToken } from '../utils/dom.js';
import { fetchJson } from '../utils/http.js';
import { showToast, openModal, closeModal } from '../utils/ui.js';

export function initMachinePassport() {
    // 1. Tab Switching Handler
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            tabButtons.forEach(b => {
                b.classList.remove('text-primary', 'font-bold', 'border-b-2', 'border-primary');
                b.classList.add('text-on-surface-variant');
            });

            btn.classList.add('text-primary', 'font-bold', 'border-b-2', 'border-primary');
            btn.classList.remove('text-on-surface-variant');

            tabPanels.forEach(p => p.classList.add('hidden'));

            const targetId = btn.getAttribute('data-target');
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.remove('hidden');
            }
        });
    });

    // 2. Sparepart Mapping Interactive Logic
    const sparepartModal = document.getElementById('sparepart-modal');
    const btnOpenSparepart = document.getElementById('btn-open-sparepart-modal');
    const btnCloseSparepart = document.getElementById('btn-close-sparepart-modal');
    const btnCancelSparepart = document.getElementById('btn-cancel-sparepart-modal');
    const searchInput = document.getElementById('sparepart-search-input');
    const searchResults = document.getElementById('sparepart-search-results');
    const errorAlert = document.getElementById('sparepart-error-alert');
    const errorText = document.getElementById('error-message-text');

    btnOpenSparepart?.addEventListener('click', () => {
        openModal('sparepart-modal');
        if (searchInput) searchInput.value = '';
        if (searchResults) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
        }
        if (errorAlert) errorAlert.classList.add('hidden');
        searchInput?.focus();
    });

    btnCloseSparepart?.addEventListener('click', () => closeModal('sparepart-modal'));
    btnCancelSparepart?.addEventListener('click', () => closeModal('sparepart-modal'));

    sparepartModal?.addEventListener('click', (e) => {
        if (e.target === sparepartModal) closeModal('sparepart-modal');
    });

    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();
        const machineCode = document.body.getAttribute('data-machine-code') || window.location.pathname.split('/')[2];

        if (query.length < 2) {
            if (searchResults) {
                searchResults.innerHTML = '';
                searchResults.classList.add('hidden');
            }
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchJson(`/machines/${encodeURIComponent(machineCode)}/spareparts/search?q=${encodeURIComponent(query)}`)
                .then(data => {
                    if (!searchResults) return;
                    searchResults.innerHTML = '';
                    if (!data || data.length === 0) {
                        searchResults.innerHTML = '<div class="p-3 text-body-sm text-on-surface-variant italic">Tidak ada sparepart ditemukan</div>';
                        searchResults.classList.remove('hidden');
                        return;
                    }

                    data.forEach(item => {
                        const code = item.code || item.erpCode || item.erp_code || '';
                        const name = item.name || 'Unknown Sparepart';
                        const brand = item.brand || '-';
                        const location = item.location || '-';
                        const stock = item.stock !== undefined ? item.stock : 0;

                        const row = document.createElement('div');
                        row.className = 'p-3 hover:bg-surface-container cursor-pointer transition-colors border-b border-outline-variant last:border-b-0 flex justify-between items-center';
                        row.innerHTML = `
                            <div>
                                <div class="font-body-md font-bold text-on-surface">${escapeHtml(name)}</div>
                                <div class="text-xs text-on-surface-variant flex flex-wrap gap-2 mt-0.5">
                                    <span>ERP: <strong class="mono text-on-surface">${escapeHtml(code)}</strong></span>
                                    <span>Brand: <strong>${escapeHtml(brand)}</strong></span>
                                    <span>Rak: <strong class="mono">${escapeHtml(location)}</strong></span>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-2">
                                <span class="text-xs font-bold mono ${stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'}">Stok: ${stock}</span>
                                <span class="material-symbols-outlined text-primary text-[20px]">add_circle</span>
                            </div>
                        `;
                        row.addEventListener('click', () => {
                            mapSparepart(machineCode, code);
                        });
                        searchResults.appendChild(row);
                    });
                    searchResults.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error fetching autocomplete:', err);
                });
        }, 300);
    });

    async function mapSparepart(machineCode, code) {
        if (errorAlert) errorAlert.classList.add('hidden');

        try {
            const data = await fetchJson(`/machines/${encodeURIComponent(machineCode)}/spareparts`, {
                method: 'POST',
                body: { warehouse_item_code: code }
            });
            if (data.success) {
                window.location.reload();
            }
        } catch (err) {
            if (errorText) errorText.textContent = err.message;
            if (errorAlert) errorAlert.classList.remove('hidden');
        }
    }

    // Bind delete listeners for spareparts
    document.querySelectorAll('.btn-delete-mapping').forEach(btn => {
        btn.addEventListener('click', function () {
            handleDeleteMapping(this);
        });
    });

    async function handleDeleteMapping(button) {
        if (!confirm('Apakah Anda yakin ingin menghapus mapping sparepart ini?')) {
            return;
        }

        const url = button.getAttribute('data-url');
        const row = button.closest('.sparepart-row');

        try {
            const data = await fetchJson(url, { method: 'DELETE' });
            if (data.success) {
                row.remove();
                const list = document.getElementById('spareparts-list');
                if (list && list.children.length === 0) {
                    list.innerHTML = `
                        <tr id="spareparts-empty-state">
                            <td colspan="4" class="text-center py-6 text-on-surface-variant text-xs italic">
                                Belum ada kebutuhan sparepart yang dipetakan untuk mesin ini.
                            </td>
                        </tr>
                    `;
                }
                showToast('Mapping sparepart berhasil dihapus.');
            }
        } catch (err) {
            showToast(err.message || 'Gagal menghapus mapping.', 'error');
        }
    }

    // Document buttons listener
    const btnLinkDoc = document.getElementById('btn-link-document');
    btnLinkDoc?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('modal-add-doc-link');
    });
    const btnLinkDocEmpty = document.getElementById('btn-link-document-empty');
    btnLinkDocEmpty?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('modal-add-doc-link');
    });
}

// Helper exposed for inline Blade handlers where needed
window.openSharedMachinesModal = function(machines, name) {
    const listContainer = document.getElementById('shared-machines-modal-list');
    setText('shared-machines-modal-title', name);
    
    if (listContainer) {
        listContainer.innerHTML = '';
        if (Array.isArray(machines)) {
            machines.forEach(m => {
                const item = document.createElement('a');
                item.href = `/machines/${encodeURIComponent(m.code)}`;
                item.target = '_blank';
                item.className = 'p-2 rounded-lg border border-outline-variant/60 hover:border-primary/40 hover:bg-primary/5 transition-all flex items-center justify-between group';
                item.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[16px]">precision_manufacturing</span>
                        <div>
                            <p class="mono font-bold text-xs text-primary group-hover:underline">${escapeHtml(m.code)}</p>
                            <p class="text-[11px] text-on-surface-variant line-clamp-1">${escapeHtml(m.name)}</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant group-hover:text-primary">arrow_forward</span>
                `;
                listContainer.appendChild(item);
            });
        }
    }
    openModal('modal-shared-machines');
};

window.closeSharedMachinesModal = function() {
    closeModal('modal-shared-machines');
};

window.navigateChecklist = function(item) {
    if (item === 'identitas') {
        const machineCode = document.body.getAttribute('data-machine-code');
        if (machineCode) window.location.href = `/machines/${encodeURIComponent(machineCode)}/edit`;
    } else if (item === 'sparepart') {
        const tabBtn = document.querySelector('[data-target="panel-spareparts"]');
        if (tabBtn) tabBtn.click();
        setTimeout(() => {
            const addMappingBtn = document.getElementById('btn-open-sparepart-modal');
            if (addMappingBtn) {
                addMappingBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                addMappingBtn.click();
            }
        }, 100);
    } else if (item === 'manual') {
        const tabBtn = document.querySelector('[data-target="panel-documents"]');
        if (tabBtn) tabBtn.click();
    } else if (item === 'photo') {
        const tabBtn = document.querySelector('[data-target="panel-photos"]');
        if (tabBtn) tabBtn.click();
    } else if (item === 'qr') {
        const tabBtn = document.querySelector('[data-target="panel-overview"]');
        if (tabBtn) tabBtn.click();
    } else if (item === 'components') {
        const tabBtn = document.querySelector('[data-target="panel-components"]');
        if (tabBtn) tabBtn.click();
    }
};
