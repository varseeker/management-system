const filterableTables = new Map();
const tableSortState = new Map();

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

function applyTableFilters(tableId) {
    const table = document.getElementById(tableId);

    if (!table) {
        return;
    }

    const columnFilters = getColumnFilters(tableId);
    const textQuery = (
        document.querySelector(`.js-dt-text-search[data-table="${tableId}"]`)?.value ?? ''
    )
        .trim()
        .toLowerCase();

    let visibleCount = 0;
    let totalCount = 0;

    getDataRows(table).forEach((row) => {
        totalCount++;

        let visible = true;

        for (const filter of columnFilters) {
            const cell = row.cells[filter.column];
            const cellText = getCellText(cell);
            const term = filter.value.toLowerCase();

            if (!cellText.includes(term)) {
                visible = false;
                break;
            }
        }

        if (visible && textQuery && !getRowSearchText(row).includes(textQuery)) {
            visible = false;
        }

        row.classList.toggle('d-none', !visible);

        if (visible) {
            visibleCount++;
        }
    });

    const info = document.querySelector(`.js-filter-info[data-table="${tableId}"]`);
    const emptyState = document.querySelector(`.js-filter-empty[data-table="${tableId}"]`);

    if (info) {
        info.textContent =
            visibleCount === totalCount
                ? `Menampilkan ${totalCount} data`
                : `Menampilkan ${visibleCount} dari ${totalCount} data`;
    }

    if (emptyState) {
        emptyState.classList.toggle('d-none', totalCount === 0 || visibleCount > 0);
    }
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

    const handler = () => applyTableFilters(tableId);

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
    handler();
}

export function initTableFilters() {
    document.querySelectorAll('.js-filterable-table').forEach((table) => {
        wireFilterableTable(table);
    });
}

export function initLegacyDataTable() {
    const legacyTable = document.getElementById('dataTable');

    if (!legacyTable || legacyTable.classList.contains('js-filterable-table')) {
        return;
    }

    import('datatables.net-bs5').then(({ default: DataTable }) => {
        if (DataTable.isDataTable(legacyTable)) {
            return;
        }

        new DataTable(legacyTable, {
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,
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
    });
}
