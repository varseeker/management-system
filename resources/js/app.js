import './bootstrap';

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

window.bootstrap = bootstrap;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import $ from 'jquery';

window.$ = $;
window.jQuery = $;

import { initLegacyDataTable, initTableFilters } from './table-filters';

function bootTables() {
    initTableFilters();
    initLegacyDataTable();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTables);
} else {
    bootTables();
}
