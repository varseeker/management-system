import './bootstrap';

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

window.bootstrap = bootstrap;

import { initApprovalActions } from './approval-actions';
import { initFlashNotifications } from './flash-notifications';
import { initBorrowingPhotos } from './borrowing-photos';
import { initLegacyDataTable, initTableFilters } from './table-filters';

function initMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    if (!sidebar || !overlay || !toggle) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 991.98px)');

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        overlay.classList.toggle('is-visible', open);
        document.body.classList.toggle('sidebar-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    }

    function closeSidebar() {
        setOpen(false);
    }

    toggle.addEventListener('click', () => {
        const isOpen = sidebar.classList.contains('is-open');
        setOpen(!isOpen);
    });

    overlay.addEventListener('click', closeSidebar);

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    sidebar.querySelectorAll('.sidebar-menu a').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    mobileQuery.addEventListener('change', (event) => {
        if (!event.matches) {
            closeSidebar();
        }
    });
}

function initAlpineIfNeeded() {
    if (!document.querySelector('[x-data]')) {
        return;
    }

    import('alpinejs').then(({ default: Alpine }) => {
        window.Alpine = Alpine;
        Alpine.start();
    });
}

function bootTables() {
    initTableFilters();
    initLegacyDataTable();
}

function bootApp() {
    initMobileSidebar();
    bootTables();
    initBorrowingPhotos();
    initFlashNotifications();
    initApprovalActions();
    initAlpineIfNeeded();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
} else {
    bootApp();
}
