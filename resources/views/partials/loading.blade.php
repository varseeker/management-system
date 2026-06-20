<div id="pos-loading" class="pos-loading" hidden aria-hidden="true" aria-live="polite" aria-busy="false">
    <style>
        #pos-loading {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            z-index: 2147483646 !important;
            pointer-events: auto !important;
        }

        #pos-loading[hidden] {
            display: none !important;
        }

        #pos-loading:not([hidden]) {
            display: block !important;
        }
    </style>
    <div class="pos-loading__overlay" aria-hidden="true"></div>
    <div class="pos-loading__topbar" aria-hidden="true">
        <div class="pos-loading__bar"></div>
    </div>
    <div class="pos-loading__center" role="status">
        <div class="pos-loading__spinner" aria-hidden="true"></div>
        <p class="pos-loading__label">Memproses...</p>
    </div>
</div>
