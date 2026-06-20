(function () {
    'use strict';

    var activeRequests = 0;
    var root;
    var bar;
    var label;
    var hideTimer;
    var mainContent;

    function getRoot() {
        if (!root) {
            root = document.getElementById('pos-loading');
            bar = root ? root.querySelector('.pos-loading__bar') : null;
            label = root ? root.querySelector('.pos-loading__label') : null;
            mainContent = document.getElementById('main-content')
                || document.querySelector('.content')
                || document.querySelector('.main');
            mountRoot();
        }

        return root;
    }

    function mountRoot() {
        if (!root || root.parentNode === document.body) {
            return;
        }

        document.body.appendChild(root);
    }

    function setMessage(message) {
        if (label && message) {
            label.textContent = message;
        }
    }

    function lockPage() {
        document.body.classList.add('pos-is-loading');

        if (mainContent) {
            mainContent.setAttribute('inert', '');
        }
    }

    function unlockPage() {
        document.body.classList.remove('pos-is-loading');

        if (mainContent) {
            mainContent.removeAttribute('inert');
        }
    }

    function blockInteraction(event) {
        if (activeRequests <= 0) {
            return;
        }

        var el = getRoot();

        if (el && el.contains(event.target)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
    }

    function bindBlockers() {
        document.addEventListener('click', blockInteraction, true);
        document.addEventListener('mousedown', blockInteraction, true);
        document.addEventListener('touchstart', blockInteraction, true);
        document.addEventListener('keydown', blockInteraction, true);
    }

    function unbindBlockers() {
        document.removeEventListener('click', blockInteraction, true);
        document.removeEventListener('mousedown', blockInteraction, true);
        document.removeEventListener('touchstart', blockInteraction, true);
        document.removeEventListener('keydown', blockInteraction, true);
    }

    function forceHide() {
        var el = getRoot();

        window.clearTimeout(hideTimer);
        activeRequests = 0;
        unbindBlockers();
        unlockPage();

        if (!el || !bar) {
            return;
        }

        el.hidden = true;
        el.setAttribute('aria-hidden', 'true');
        el.setAttribute('aria-busy', 'false');
        bar.classList.remove('is-complete', 'is-active');
    }

    function start(message) {
        var el = getRoot();

        if (!el || !bar) {
            return;
        }

        window.clearTimeout(hideTimer);
        activeRequests += 1;

        if (activeRequests === 1) {
            bindBlockers();
            lockPage();
        }

        setMessage(message || 'Memproses...');
        el.hidden = false;
        el.setAttribute('aria-hidden', 'false');
        el.setAttribute('aria-busy', 'true');
        bar.classList.remove('is-complete');
        bar.classList.add('is-active');
    }

    function done() {
        var el = getRoot();

        if (!el || !bar) {
            return;
        }

        activeRequests = Math.max(0, activeRequests - 1);

        if (activeRequests > 0) {
            return;
        }

        bar.classList.add('is-complete');
        bar.classList.remove('is-active');

        hideTimer = window.setTimeout(function () {
            forceHide();
        }, 300);
    }

    function shouldSkipForm(form) {
        if (!form || form.tagName !== 'FORM') {
            return true;
        }

        if (form.classList.contains('no-loading')) {
            return true;
        }

        if (form.getAttribute('target') === '_blank') {
            return true;
        }

        return false;
    }

    function shouldSkipLink(anchor) {
        if (!anchor || !anchor.getAttribute('href')) {
            return true;
        }

        if (anchor.classList.contains('no-loading')) {
            return true;
        }

        if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
            return true;
        }

        var href = anchor.getAttribute('href');

        if (!href || href.charAt(0) === '#') {
            return true;
        }

        if (href.indexOf('javascript:') === 0) {
            return true;
        }

        try {
            var url = new URL(anchor.href, window.location.origin);

            if (url.origin !== window.location.origin) {
                return true;
            }
        } catch (error) {
            return true;
        }

        return false;
    }

    function shouldTrackFetch(input) {
        var requestUrl = '';

        if (typeof input === 'string') {
            requestUrl = input;
        } else if (input && typeof input.url === 'string') {
            requestUrl = input.url;
        }

        if (!requestUrl) {
            return true;
        }

        try {
            var url = new URL(requestUrl, window.location.origin);

            if (url.origin !== window.location.origin) {
                return false;
            }

            if (/\.(css|js|png|jpe?g|gif|webp|svg|woff2?|ico)(\?|$)/i.test(url.pathname)) {
                return false;
            }
        } catch (error) {
            return true;
        }

        return true;
    }

    function bindEvents() {
        document.addEventListener('submit', function (event) {
            if (shouldSkipForm(event.target)) {
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            start(event.target.dataset.loadingMessage || 'Memproses...');
        }, true);

        document.addEventListener('click', function (event) {
            var anchor = event.target.closest('a[href]');

            if (shouldSkipLink(anchor)) {
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            start('Memuat halaman...');
        }, true);

        window.addEventListener('beforeunload', function () {
            if (activeRequests === 0) {
                start('Memuat halaman...');
            }
        });

        window.addEventListener('pageshow', function () {
            forceHide();
        });

        if (window.fetch) {
            var nativeFetch = window.fetch;

            window.fetch = function (input, init) {
                if (!shouldTrackFetch(input)) {
                    return nativeFetch.apply(this, arguments);
                }

                start('Memproses...');

                return nativeFetch.apply(this, arguments).finally(function () {
                    done();
                });
            };
        }
    }

    window.PosLoading = {
        start: start,
        done: done,
        forceHide: forceHide,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            forceHide();
            bindEvents();
        });
    } else {
        forceHide();
        bindEvents();
    }
})();
