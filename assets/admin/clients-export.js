/**
 * Модалка экспорта и сбор query-фильтров для страницы клиентов.
 */
(function () {
    const MODAL_ID = 'clients-export-modal';
    const EXPORT_URL = window.__clientsExportUrl;

    const FILTER_FIELDS = [
        { param: 'search', elementId: 'clients-search', getValue: (el) => el.value.trim() },
        { param: 'status', elementId: 'clients-filter-status', getValue: (el) => el.value },
        { param: 'period', elementId: 'clients-filter-period', getValue: (el) => el.value },
        { param: 'sort', elementId: 'clients-sort', getValue: (el) => el.value },
    ];

    const modal = document.getElementById(MODAL_ID);
    const backdrop = document.getElementById(`${MODAL_ID}-backdrop`);

    if (!modal || !backdrop) {
        return;
    }

    function collectFilterParams() {
        const params = new URLSearchParams();

        for (const { param, elementId, getValue } of FILTER_FIELDS) {
            const el = document.getElementById(elementId);
            if (!el) {
                continue;
            }

            const value = getValue(el);
            if (value !== '' && value != null) {
                params.set(param, String(value));
            }
        }

        return params;
    }

    function buildExportUrl(format) {
        if (!EXPORT_URL) {
            console.error('Export URL is not defined (window.__clientsExportUrl).');
            return null;
        }

        const params = collectFilterParams();
        params.set('format', format);

        return `${EXPORT_URL}?${params.toString()}`;
    }

    function openModal() {
        modal.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        backdrop.setAttribute('aria-hidden', 'false');
        document.body.classList.add('admin-modal-open');

        const firstOption = modal.querySelector('[data-export-format].is-available');
        firstOption?.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('admin-modal-open');
    }

    function applyFiltersFromUrl() {
        const url = new URL(window.location.href);

        for (const { param, elementId } of FILTER_FIELDS) {
            const el = document.getElementById(elementId);
            if (!el || !url.searchParams.has(param)) {
                continue;
            }

            el.value = url.searchParams.get(param);
        }
    }

    function pushFiltersToUrl(replace = false) {
        const url = new URL(window.location.href);
        const params = collectFilterParams();

        ['search', 'status', 'period', 'sort', 'page', 'perPage'].forEach((key) => {
            url.searchParams.delete(key);
        });

        params.forEach((value, key) => {
            url.searchParams.set(key, value);
        });

        const method = replace ? 'replaceState' : 'pushState';
        window.history[method]({}, '', url);
    }

    document.querySelectorAll('[data-export-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const targetId = trigger.getAttribute('data-export-open');
            if (targetId === MODAL_ID) {
                openModal();
            }
        });
    });

    modal.querySelectorAll('[data-export-modal-close]').forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    modal.querySelectorAll('[data-export-format].is-available').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            const format = link.getAttribute('data-export-format');
            const url = buildExportUrl(format);

            if (!url) {
                return;
            }

            closeModal();
            window.location.href = url;
        });
    });

    const resetBtn = document.getElementById('clients-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            for (const { elementId } of FILTER_FIELDS) {
                const el = document.getElementById(elementId);
                if (!el) {
                    continue;
                }

                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = '';
                }
            }

            pushFiltersToUrl(true);
        });
    }

    let filterDebounceTimer;
    const panel = document.querySelector('.clients-panel');

    if (panel) {
        panel.addEventListener('input', (event) => {
            if (event.target.id !== 'clients-search') {
                return;
            }

            clearTimeout(filterDebounceTimer);
            filterDebounceTimer = setTimeout(() => pushFiltersToUrl(true), 400);
        });

        panel.addEventListener('change', (event) => {
            const id = event.target.id;
            if (!FILTER_FIELDS.some((f) => f.elementId === id)) {
                return;
            }

            pushFiltersToUrl(true);
        });
    }

    applyFiltersFromUrl();
})();