<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Inventory System')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    {{-- Shared look for every page. A plain file in public/, so it needs no build step. --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/ui.css') }}?v={{ @filemtime(public_path('css/ui.css')) }}"
    >

</head>


<body>

@unless (app()->environment('production'))

    <div class="dev-mode-banner">
        ⚠ Development / Test Mode — data here is not real. Real deployment has this banner turned off automatically.
    </div>

@endunless


<div class="system-shell">

    @include('layouts.sidebar')

    <main class="system-main">

        <div class="container">

            @if (\App\Support\UserAccess::enabled() && auth()->check() && ! auth()->user()->isAdmin() && ! auth()->user()->location_id)
                <div class="account-no-location" role="alert" style="margin-bottom: 16px; padding: 12px 14px; border-radius: 8px; background: #fef3c7; border: 1px solid #fde68a; color: #92400e;">
                    Your account isn't assigned to a location yet, so there is nothing for you to see or change. Ask an administrator to assign your company and location.
                </div>
            @endif

            @yield('content')

        </div>

    </main>

</div>


<div class="toast-container" id="toast-container">

    @if(session('success'))
        <div class="toast toast-success" data-toast data-toast-timeout="4000">
            <span class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
            </span>
            <span>{{ session('success') }}</span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss">&times;</button>
        </div>
    @endif

    {{-- A form the server turned down always says why, even on a page that has no spot for field errors. --}}
    @if($errors->any() && ! session('error'))
        <div class="toast toast-error" data-toast data-toast-timeout="8000">
            <span class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5"></path><path d="M12 16h.01"></path></svg>
            </span>
            <span>{{ $errors->first() }}</span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="toast toast-error" data-toast data-toast-timeout="6000">
            <span class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5"></path><path d="M12 16h.01"></path></svg>
            </span>
            <span>{{ session('error') }}</span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss">&times;</button>
        </div>
    @endif

</div>


<div class="confirm-modal-overlay" id="confirm-modal-overlay">

    <div class="confirm-modal-box">

        <h3 id="confirm-modal-title">Are you sure?</h3>
        <p id="confirm-modal-message">This action cannot be undone.</p>

        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="confirm-modal-cancel">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirm-modal-confirm">Confirm</button>
        </div>

    </div>

</div>

<script>
    /*
    |--------------------------------------------------------------------------
    | Live refresh
    |--------------------------------------------------------------------------
    |
    | Stock changes constantly from the POS side. Pages that show live
    | numbers (dashboard, inventory list) set the "autoRefreshSeconds"
    | section and mark the parts that change with data-live="name".
    | Every few seconds the page quietly fetches itself in the background
    | and swaps in only the marked parts whose content changed, so there
    | is no reload, no flash and no jump in scroll position.
    |
    | A part is left alone while the person is typing in it, and a tab
    | that is hidden doesn't fetch at all. If the session has ended (the
    | fetch lands on the login page) it stops instead of swapping in a
    | login form.
    */
    (function () {
        var seconds = @yield('autoRefreshSeconds', 'null');

        if (!seconds || !window.fetch || !window.DOMParser) {
            return;
        }

        var busy = false;

        function refresh() {
            if (busy || document.hidden) {
                return;
            }

            busy = true;

            fetch(window.location.href, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            })
                .then(function (response) {
                    // Signed out: stop for good. Any other hiccup: just try again next time.
                    if (response.redirected || response.status === 401 || response.status === 419) {
                        clearInterval(timer);
                    }

                    if (!response.ok || response.redirected) {
                        throw new Error('stop');
                    }

                    return response.text();
                })
                .then(function (html) {
                    var fresh = new DOMParser().parseFromString(html, 'text/html');

                    document.querySelectorAll('[data-live]').forEach(function (current) {
                        var replacement = fresh.querySelector('[data-live="' + current.getAttribute('data-live') + '"]');

                        if (!replacement || current.contains(document.activeElement)) {
                            return;
                        }

                        if (replacement.innerHTML !== current.innerHTML) {
                            current.innerHTML = replacement.innerHTML;
                        }
                    });
                })
                .catch(function () {})
                .then(function () {
                    busy = false;
                });
        }

        var timer = setInterval(refresh, seconds * 1000);
    })();
    /*
    |--------------------------------------------------------------------------
    | Toast notifications
    |--------------------------------------------------------------------------
    | Auto-dismisses each flash toast after its data-toast-timeout, and
    | lets the close button dismiss it early. Toasts stack in the
    | top-right corner instead of pushing page content down.
    */
    document.querySelectorAll('[data-toast]').forEach(function (toast) {
        var timeout = parseInt(toast.getAttribute('data-toast-timeout'), 10) || 4000;

        function dismiss() {
            toast.classList.add('toast-hiding');
            setTimeout(function () {
                toast.remove();
            }, 200);
        }

        var timer = setTimeout(dismiss, timeout);

        var closeBtn = toast.querySelector('[data-toast-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timer);
                dismiss();
            });
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Confirm modal
    |--------------------------------------------------------------------------
    | Replaces the browser's native confirm() popup for destructive
    | actions. Two ways to opt in:
    |
    |   1. <form data-confirm="message" [data-confirm-title="Title"]> —
    |      intercepted on submit, for forms with a single submit button.
    |
    |   2. <button data-confirm="message" [data-confirm-title="Title"]
    |      type="submit"> — intercepted on click instead, for forms with
    |      more than one submit button (e.g. "Pass" / "Fail") where only
    |      one of them needs confirmation.
    |
    | Either way, the real submission only happens once the user clicks
    | Confirm — via requestSubmit()/click(), which (unlike form.submit())
    | still fires real submit/click events so the double-submit guard
    | below still applies.
    */
    var confirmOverlay = document.getElementById('confirm-modal-overlay');
    var confirmTitleEl = document.getElementById('confirm-modal-title');
    var confirmMessageEl = document.getElementById('confirm-modal-message');
    var confirmConfirmBtn = document.getElementById('confirm-modal-confirm');
    var confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    var pendingConfirmTarget = null;

    function closeConfirmModal() {
        confirmOverlay.classList.remove('is-open');
        pendingConfirmTarget = null;
    }

    confirmCancelBtn.addEventListener('click', closeConfirmModal);

    confirmOverlay.addEventListener('click', function (event) {
        if (event.target === confirmOverlay) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && confirmOverlay.classList.contains('is-open')) {
            closeConfirmModal();
        }
    });

    confirmConfirmBtn.addEventListener('click', function () {
        var target = pendingConfirmTarget;
        confirmOverlay.classList.remove('is-open');
        pendingConfirmTarget = null;

        if (!target) {
            return;
        }

        target.dataset.confirmed = 'true';

        if (target instanceof HTMLFormElement) {
            target.requestSubmit();
        } else {
            target.click();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        confirmTitleEl.textContent = form.dataset.confirmTitle || 'Are you sure?';
        confirmMessageEl.textContent = form.dataset.confirm;
        pendingConfirmTarget = form;
        confirmOverlay.classList.add('is-open');
    }, true);

    document.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-confirm], input[type="submit"][data-confirm]');

        if (!button) {
            return;
        }

        if (button.dataset.confirmed === 'true') {
            delete button.dataset.confirmed;
            return;
        }

        event.preventDefault();

        confirmTitleEl.textContent = button.dataset.confirmTitle || 'Are you sure?';
        confirmMessageEl.textContent = button.dataset.confirm;
        pendingConfirmTarget = button;
        confirmOverlay.classList.add('is-open');
    }, true);


    /*
    |--------------------------------------------------------------------------
    | Double-submit guard + loading state
    |--------------------------------------------------------------------------
    | Disables a form's submit button the instant it actually submits (so
    | a double-click can't fire the same request twice) and shows a
    | spinner in place of its label, so "did that actually work?" never
    | happens on a slow connection. Checked against event.defaultPrevented
    | so a page's own client-side validation (which calls
    | preventDefault() to block a bad submission) is never left with a
    | permanently-disabled button — this only fires when the form is
    | genuinely on its way to the server.
    */
    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) {
            return;
        }

        var form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        /*
         * A disabled button's name/value is left out of the request, so a
         * form with "Pass" / "Fail" buttons would arrive without saying which
         * one was clicked. Copy the clicked button's value into a hidden field
         * before disabling it.
         */
        var submitter = event.submitter;

        if (submitter && submitter.name && !form.querySelector('input[type="hidden"][data-submitter-copy]')) {
            var copy = document.createElement('input');
            copy.type = 'hidden';
            copy.name = submitter.name;
            copy.value = submitter.value;
            copy.setAttribute('data-submitter-copy', '');
            form.appendChild(copy);
        }

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Number-only text fields
    |--------------------------------------------------------------------------
    | Quantities, prices and reorder points are plain text fields marked
    | data-numeric, not type="number": a number field changes its value when
    | the mouse wheel passes over it, which silently corrupted entries. These
    | accept only digits and one decimal point; the decimals allowed come
    | from the field's step (0.01 = 2 places, 0.0001 = 4 places).
    */
    function sanitizeNumeric(input) {
        var step = input.getAttribute('step') || '1';
        var decimals = step.indexOf('.') === -1 ? 0 : step.split('.')[1].length;
        var raw = input.value;
        var clean = raw.replace(/[^0-9.]/g, '');

        if (decimals === 0) {
            clean = clean.replace(/\./g, '');
        } else {
            var firstDot = clean.indexOf('.');

            if (firstDot !== -1) {
                clean = clean.slice(0, firstDot + 1) + clean.slice(firstDot + 1).replace(/\./g, '').slice(0, decimals);
            }
        }

        if (clean !== raw) {
            var caret = input.selectionStart - (raw.length - clean.length);
            input.value = clean;

            try {
                input.setSelectionRange(Math.max(caret, 0), Math.max(caret, 0));
            } catch (e) {}
        }

        var value = parseFloat(clean);
        var min = parseFloat(input.getAttribute('min'));
        var max = parseFloat(input.getAttribute('max'));
        var message = '';

        if (clean !== '' && !isNaN(value)) {
            if (!isNaN(min) && value < min) {
                message = 'Enter ' + min + ' or more.';
            } else if (!isNaN(max) && value > max) {
                message = 'Enter ' + max + ' or less.';
            }
        }

        input.setCustomValidity(message);
    }

    document.addEventListener('input', function (event) {
        if (event.target instanceof HTMLInputElement && event.target.hasAttribute('data-numeric')) {
            sanitizeNumeric(event.target);
        }
    }, true);


    /*
    |--------------------------------------------------------------------------
    | Uppercase set-up fields
    |--------------------------------------------------------------------------
    | Names and codes on the maintenance pages are stored in capitals so
    | every record looks the same. Typed text is capitalised as you go; the
    | server capitalises it again on save, so this is only for what you see.
    */
    var uppercasePages = /^\/(companies|locations|product-categories|units-of-measure|suppliers|products)(\/|$)/;
    var uppercaseFields = ['name', 'code', 'sku', 'item_code'];

    document.addEventListener('input', function (event) {
        var input = event.target;

        if (!(input instanceof HTMLInputElement) || !uppercasePages.test(window.location.pathname)) {
            return;
        }

        if (uppercaseFields.indexOf(input.name) === -1 || input.type !== 'text') {
            return;
        }

        var upper = input.value.toUpperCase();

        if (upper !== input.value) {
            var start = input.selectionStart;
            var end = input.selectionEnd;
            input.value = upper;

            try {
                input.setSelectionRange(start, end);
            } catch (e) {}
        }
    }, true);


    /*
    |--------------------------------------------------------------------------
    | Pictures that fail to load
    |--------------------------------------------------------------------------
    | A product picture whose file is missing would show a broken-image icon
    | and its name spilling out of the thumbnail. Swap it for a plain grey
    | tile, the same as a product with no picture.
    */
    var missingImage = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><rect width="48" height="48" fill="#f1f5f9"/>' +
        '<g fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' +
        '<rect x="15" y="16" width="18" height="16" rx="2"/><circle cx="21" cy="22" r="1.6"/><path d="M33 28l-5-5-8 9"/></g></svg>'
    );

    document.addEventListener('error', function (event) {
        var image = event.target;

        if (image instanceof HTMLImageElement && !image.classList.contains('img-missing')) {
            image.classList.add('img-missing');
            image.src = missingImage;
        }
    }, true);
</script>

</body>

</html>
