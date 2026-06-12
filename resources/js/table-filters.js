const filterableTables = new Map();
const tableSortState = new Map();
const tablePaginationState = new Map();

const DEFAULT_PER_PAGE = 5;

function getPaginationState(tableId) {
    if (!tablePaginationState.has(tableId)) {
        tablePaginationState.set(tableId, { page: 1, perPage: DEFAULT_PER_PAGE });
    }

    return tablePaginationState.get(tableId);
}

function getColumnFilters(tableId) {
    return [...document.querySelectorAll(`.js-dt-column-filter[data-table="${tableId}"]`)]
        .map((select) => ({
            column: parseInt(select.dataset.column, 10),
            value: select.value.trim(),
        }))
        .filter((filter) => filter.value !== '' && !Number.isNaN(filter.column));
}

function getCellText(cell) {
    if (!cell) {
        return '';
    }

    return (cell.getAttribute('data-filter') || cell.textContent || '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

function getRowSearchText(row) {
    return Array.from(row.cells)
        .map((cell) => getCellText(cell))
        .join(' ')
        .trim();
}

function parseSortValue(cell, sortType) {
    if (!cell) {
        return sortType === 'number' ? 0 : '';
    }

    const raw = (
        cell.getAttribute('data-sort') ??
        cell.getAttribute('data-filter') ??
        cell.textContent ??
        ''
    )
        .replace(/\s+/g, ' ')
        .trim();

    if (sortType === 'number') {
        const numeric = parseFloat(raw.replace(/[^\d.-]/g, ''));

        return Number.isNaN(numeric) ? 0 : numeric;
    }

    if (sortType === 'date') {
        if (/^\d+$/.test(raw)) {
            return parseInt(raw, 10);
        }

        const timestamp = Date.parse(raw);

        if (!Number.isNaN(timestamp)) {
            return timestamp;
        }

        const dmy = raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{1,2}):(\d{2}))?/);

        if (dmy) {
            const [, day, month, year, hour = '0', minute = '0'] = dmy;

            return new Date(
                parseInt(year, 10),
                parseInt(month, 10) - 1,
                parseInt(day, 10),
                parseInt(hour, 10),
                parseInt(minute, 10),
            ).getTime();
        }

        return 0;
    }

    return raw.toLowerCase();
}

function compareSortValues(a, b, sortType) {
    if (sortType === 'number' || sortType === 'date') {
        return a - b;
    }

    return String(a).localeCompare(String(b), 'id', { sensitivity: 'base' });
}

function getDataRows(table) {
    return Array.from(table.querySelectorAll('tbody tr')).filter(
        (row) => !row.querySelector('td[colspan]'),
    );
}

function rowMatchesFilters(row, columnFilters, textQuery) {
    for (const filter of columnFilters) {
        const cell = row.cells[filter.column];
        const cellText = getCellText(cell);
        const term = filter.value.toLowerCase();

        if (!cellText.includes(term)) {
            return false;
        }
    }

    if (textQuery && !getRowSearchText(row).includes(textQuery)) {
        return false;
    }

    return true;
}

function ensurePaginationUI(table) {
    const tableId = table.id;

    if (!tableId) {
        return null;
    }

    let nav = document.querySelector(`.js-table-pagination[data-table="${tableId}"]`);

    if (nav) {
        return nav;
    }

    nav = document.createElement('div');
    nav.className =
        'js-table-pagination d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3';
    nav.dataset.table = tableId;
    nav.innerHTML = `
        <span class="small text-muted js-pagination-info"></span>
        <nav aria-label="Paginasi tabel">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item js-page-prev-wrap">
                    <button type="button" class="page-link js-page-prev">Sebelumnya</button>
                </li>
                <li class="page-item disabled">
                    <span class="page-link js-page-status">1 / 1</span>
                </li>
                <li class="page-item js-page-next-wrap">
                    <button type="button" class="page-link js-page-next">Berikutnya</button>
                </li>
            </ul>
        </nav>
    `;

    const container = table.closest('.table-responsive');

    if (container) {
        container.after(nav);
    } else {
        table.after(nav);
    }

    nav.querySelector('.js-page-prev').addEventListener('click', () => {
        const state = getPaginationState(tableId);

        if (state.page > 1) {
            state.page--;
            applyTableFilters(tableId);
        }
    });

    nav.querySelector('.js-page-next').addEventListener('click', () => {
        const state = getPaginationState(tableId);
        const tableEl = document.getElementById(tableId);
        const matchedCount = tableEl ? getMatchedRows(tableId).length : 0;
        const totalPages = Math.max(1, Math.ceil(matchedCount / state.perPage));

        if (state.page < totalPages) {
            state.page++;
            applyTableFilters(tableId);
        }
    });

    return nav;
}

function getMatchedRows(tableId) {
    const table = document.getElementById(tableId);

    if (!table) {
        return [];
    }

    const columnFilters = getColumnFilters(tableId);
    const textQuery = (
        document.querySelector(`.js-dt-text-search[data-table="${tableId}"]`)?.value ?? ''
    )
        .trim()
        .toLowerCase();

    return getDataRows(table).filter((row) => rowMatchesFilters(row, columnFilters, textQuery));
}

function updatePaginationUI(tableId, totalMatched, state) {
    const nav = document.querySelector(`.js-table-pagination[data-table="${tableId}"]`);

    if (!nav) {
        return;
    }

    const totalPages = Math.max(1, Math.ceil(totalMatched / state.perPage));
    const start = totalMatched === 0 ? 0 : (state.page - 1) * state.perPage + 1;
    const end = Math.min(state.page * state.perPage, totalMatched);

    nav.querySelector('.js-pagination-info').textContent =
        totalMatched === 0
            ? 'Tidak ada data'
            : `Menampilkan ${start}–${end} dari ${totalMatched} data`;

    nav.querySelector('.js-page-status').textContent = `${state.page} / ${totalPages}`;

    nav.querySelector('.js-page-prev-wrap').classList.toggle('disabled', state.page <= 1);
    nav.querySelector('.js-page-next-wrap').classList.toggle('disabled', state.page >= totalPages);
    nav.classList.toggle('d-none', totalMatched === 0);
}

function updateSortHeaders(table, activeColumn, direction) {
    table.querySelectorAll('thead th.js-sort-col').forEach((th) => {
        th.classList.remove('sort-asc', 'sort-desc');

        if (th.cellIndex === activeColumn) {
            th.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

function sortTableRows(tableId) {
    const table = document.getElementById(tableId);
    const state = tableSortState.get(tableId);

    if (!table || !state) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const rows = getDataRows(table);
    const header = table.querySelector('thead tr')?.children[state.column];
    const sortType =
        header?.classList.contains('js-sort-col') ? header.dataset.sortType || 'text' : 'text';

    rows.sort((rowA, rowB) => {
        const valueA = parseSortValue(rowA.cells[state.column], sortType);
        const valueB = parseSortValue(rowB.cells[state.column], sortType);
        const compared = compareSortValues(valueA, valueB, sortType);

        return state.direction === 'desc' ? -compared : compared;
    });

    rows.forEach((row) => tbody.appendChild(row));
    updateSortHeaders(table, state.column, state.direction);
}

function applyTableFilters(tableId, resetPage = false) {
    const table = document.getElementById(tableId);

    if (!table) {
        return;
    }

    const state = getPaginationState(tableId);

    if (resetPage) {
        state.page = 1;
    }

    const columnFilters = getColumnFilters(tableId);
    const textQuery = (
        document.querySelector(`.js-dt-text-search[data-table="${tableId}"]`)?.value ?? ''
    )
        .trim()
        .toLowerCase();

    const totalCount = getDataRows(table).length;
    const matchedRows = getDataRows(table).filter((row) =>
        rowMatchesFilters(row, columnFilters, textQuery),
    );
    const visibleCount = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(visibleCount / state.perPage));

    if (state.page > totalPages) {
        state.page = totalPages;
    }

    if (state.page < 1) {
        state.page = 1;
    }

    const start = (state.page - 1) * state.perPage;
    const end = start + state.perPage;
    const pageRows = new Set(matchedRows.slice(start, end));

    getDataRows(table).forEach((row) => {
        row.classList.toggle('d-none', !pageRows.has(row));
    });

    const info = document.querySelector(`.js-filter-info[data-table="${tableId}"]`);
    const emptyState = document.querySelector(`.js-filter-empty[data-table="${tableId}"]`);
    const hasActiveFilters =
        columnFilters.length > 0 || textQuery.length > 0;

    if (info) {
        if (!hasActiveFilters) {
            info.textContent = '';
        } else {
            info.textContent =
                visibleCount === totalCount
                    ? `${visibleCount} data cocok dengan filter`
                    : `${visibleCount} dari ${totalCount} data cocok dengan filter`;
        }
    }

    if (emptyState) {
        emptyState.classList.toggle('d-none', totalCount === 0 || visibleCount > 0);
    }

    updatePaginationUI(tableId, visibleCount, state);
}

function wireTableSorting(table) {
    const tableId = table.id;

    table.querySelectorAll('thead th.js-sort-col').forEach((th) => {
        if (th.dataset.sortWired === '1') {
            return;
        }

        th.dataset.sortWired = '1';

        th.addEventListener('click', () => {
            const column = th.cellIndex;
            const current = tableSortState.get(tableId);

            let direction = 'asc';

            if (current?.column === column) {
                direction = current.direction === 'asc' ? 'desc' : 'asc';
            }

            tableSortState.set(tableId, { column, direction });
            getPaginationState(tableId).page = 1;
            sortTableRows(tableId);
            applyTableFilters(tableId);
        });
    });
}

function wireFilterableTable(table) {
    const tableId = table.id;

    if (!tableId || filterableTables.has(tableId)) {
        return;
    }

    ensurePaginationUI(table);

    const handler = () => applyTableFilters(tableId, true);

    document.querySelectorAll(`.js-dt-column-filter[data-table="${tableId}"]`).forEach((select) => {
        select.addEventListener('change', handler);
        select.addEventListener('input', handler);
    });

    const textSearch = document.querySelector(`.js-dt-text-search[data-table="${tableId}"]`);

    if (textSearch) {
        textSearch.addEventListener('input', handler);
    }

    wireTableSorting(table);
    filterableTables.set(tableId, handler);
    applyTableFilters(tableId);
}

function wirePaginatedTable(table) {
    if (!table.id) {
        return;
    }

    if (filterableTables.has(table.id)) {
        return;
    }

    ensurePaginationUI(table);
    filterableTables.set(table.id, () => applyTableFilters(table.id));
    applyTableFilters(table.id);
}

export function initTableFilters() {
    document.querySelectorAll('.js-filterable-table').forEach((table) => {
        wireFilterableTable(table);
    });

    document.querySelectorAll('.js-paginated-table').forEach((table) => {
        wirePaginatedTable(table);
    });
}

export function initLegacyDataTable() {
    const legacyTable = document.getElementById('dataTable');

    if (!legacyTable || legacyTable.classList.contains('js-filterable-table')) {
        return;
    }

    Promise.all([import('jquery'), import('datatables.net-bs5')]).then(
        ([{ default: $ }, { default: DataTable }]) => {
            window.$ = $;
            window.jQuery = $;

            if (DataTable.isDataTable(legacyTable)) {
                return;
            }

            new DataTable(legacyTable, {
                pageLength: DEFAULT_PER_PAGE,
                ordering: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,
                lengthMenu: [
                    [5, 10, 25, 50],
                    [5, 10, 25, 50],
                ],
                language: {
                    emptyTable: 'Belum ada data',
                    zeroRecords: 'Tidak ada data yang cocok',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya',
                    },
                },
            });
        },
    );
}
