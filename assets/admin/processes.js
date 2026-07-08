/**
 * Live polling and filter handling for the admin background processes page.
 */
(function () {
    const FEED_URL = window.__processesFeedUrl;
    const panel = document.querySelector('[data-processes-panel]');
    const tableBody = document.getElementById('processes-table-body');
    const emptyState = document.getElementById('processes-empty');
    const totalCounter = document.getElementById('processes-count');
    const resetBtn = document.getElementById('processes-reset-filters');

    if (!FEED_URL || !panel || !tableBody) {
        return;
    }

    const FILTER_FIELDS = [
        { param: 'type', elementId: 'processes-type' },
        { param: 'status', elementId: 'processes-status' },
        { param: 'resource', elementId: 'processes-resource' },
        { param: 'format', elementId: 'processes-format' },
        { param: 'dateFrom', elementId: 'processes-date-from' },
        { param: 'dateTo', elementId: 'processes-date-to' },
    ];

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function collectParams() {
        const params = new URLSearchParams();
        const currentUrl = new URL(window.location.href);

        for (const { param, elementId } of FILTER_FIELDS) {
            const el = document.getElementById(elementId);
            if (!el || el.value === '') {
                continue;
            }

            params.set(param, el.value);
        }

        if (currentUrl.searchParams.has('page')) {
            params.set('page', currentUrl.searchParams.get('page'));
        }

        if (currentUrl.searchParams.has('perPage')) {
            params.set('perPage', currentUrl.searchParams.get('perPage'));
        }

        return params;
    }

    function updateUrl() {
        const url = new URL(window.location.href);
        const params = collectParams();

        ['type', 'status', 'resource', 'format', 'dateFrom', 'dateTo', 'page', 'perPage'].forEach((key) => {
            url.searchParams.delete(key);
        });

        params.forEach((value, key) => {
            if (key !== 'page') {
                url.searchParams.set(key, value);
            }
        });

        window.history.replaceState({}, '', url);
    }

    function renderAction(process) {
        if (process.downloadUrl) {
            return `<a href="${escapeHtml(process.downloadUrl)}" class="btn-admin-primary btn-sm"><i class="bi bi-download"></i> Скачать</a>`;
        }

        if (process.status === 'failed') {
            return `<span class="process-error" title="${escapeHtml(process.errorMessage ?? '')}">Ошибка</span>`;
        }

        return '<span class="text-muted">Ожидание</span>';
    }

    function renderRows(items) {
        if (items.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Фоновых процессов пока нет</td></tr>';
            emptyState?.classList.remove('hidden');
            return;
        }

        emptyState?.classList.add('hidden');
        tableBody.innerHTML = items.map((process) => `
            <tr data-job-id="${escapeHtml(process.jobId)}">
                <td>
                    <div class="process-main">
                        <span class="order-id">#${escapeHtml(process.jobId.slice(0, 8))}</span>
                        ${process.detailLabel ? `<small>${escapeHtml(process.detailLabel)}</small>` : ''}
                    </div>
                </td>
                <td>${escapeHtml(process.typeLabel)}</td>
                <td><span class="badge-status ${escapeHtml(process.status)}">${escapeHtml(process.statusLabel)}</span></td>
                <td>${escapeHtml(process.requestedBy)}</td>
                <td>${escapeHtml(process.createdAt)}</td>
                <td>${escapeHtml(process.completedAt)}</td>
                <td>${escapeHtml(process.resourceLabel)}</td>
                <td>${escapeHtml(process.formatLabel)}</td>
                <td>${escapeHtml(process.storageLabel)}</td>
                <td>${renderAction(process)}</td>
            </tr>
        `).join('');
    }

    function renderStats(stats) {
        Object.entries(stats).forEach(([key, value]) => {
            const el = document.querySelector(`[data-processes-stat="${key}"]`);
            if (el) {
                el.textContent = value;
            }
        });
    }

    async function refresh() {
        if (document.hidden) {
            return;
        }

        const params = collectParams();
        const url = params.toString() === '' ? FEED_URL : `${FEED_URL}?${params.toString()}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`Processes feed failed with status ${response.status}`);
            }

            const payload = await response.json();
            renderRows(payload.items ?? []);
            renderStats(payload.stats ?? {});

            if (totalCounter) {
                totalCounter.textContent = payload.total ?? 0;
            }
        } catch (error) {
            console.error(error);
        }
    }

    panel.addEventListener('change', (event) => {
        if (!FILTER_FIELDS.some((field) => field.elementId === event.target.id)) {
            return;
        }

        updateUrl();
        refresh();
    });

    resetBtn?.addEventListener('click', () => {
        for (const { elementId } of FILTER_FIELDS) {
            const el = document.getElementById(elementId);
            if (el) {
                el.value = '';
            }
        }

        updateUrl();
        refresh();
    });

    setInterval(refresh, 5000);
})();
