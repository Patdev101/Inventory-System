{{--
    Suggested units for the product form.

    Once a base unit is chosen, this lists the units that convert to it
    (dozen for piece, kilogram for gram...) with the conversion already
    worked out. One click adds the unit row and fills the conversion in.
    Packaging units (box, case, pack...) are offered too, but their
    quantity differs per product, so that one is left for the user to type.

    Expects the page's own unit table: #base_unit_id, #units-container,
    #add-unit, and rows of .unit-row / .unit-select / .conversion-input.
--}}
@php
    $unitSuggestionData = $units->map(function ($unit) {
        $meta = \App\Support\DefaultUnits::UNITS[strtoupper((string) $unit->code)] ?? null;

        return [
            'id' => (string) $unit->id,
            'name' => $unit->name,
            'code' => $unit->code,
            'family' => $meta['family'] ?? null,
            'size' => $meta['size'] ?? null,
        ];
    })->values();
@endphp

<style>
    /* Form sections: the same white card the rest of the system uses */
    .product-form .form-section {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
        padding: 24px 25px; margin-bottom: 20px;
    }
    .product-form .form-section > h2 { margin: 0 0 16px; font-size: 17px; font-weight: 700; color: #0f172a; }
    .product-form .form-section > h2:not(:first-child) { margin-top: 26px; padding-top: 22px; border-top: 1px solid #e2e8f0; }
    .product-form .form-section > .muted { margin: 0 0 16px; font-size: 14px; color: #64748b; }
    .product-form .form-section > h2 + .muted { margin-top: -10px; }

    /* The summary line under Step 2 repeats the table and the note above it. */
    #unit-info { display: none; }

    /* No picture chosen yet: do not show the broken-image label. */
    #image-upload-img:not([src]) { display: none; }
    .product-form .form-section > .form-group:last-child { margin-bottom: 0; }

    /* Counting unit hint + Step 2 */
    #base-unit-hint { display: block; margin-top: 6px; color: #64748b; font-size: 13px; }
    .unit-table-wrapper[hidden], .unit-empty[hidden] { display: none; }
    .unit-empty { margin: 0 0 14px; padding: 12px 14px; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; color: #64748b; font-size: 14px; }
    .unit-row.is-base { display: none; }
    .unit-sentence { font-size: 13px; color: #64748b; }
    .unit-sentence b { font-weight: 600; color: #334155; }
    .u-th-hint { font-weight: 400; color: #64748b; font-size: 12px; }

    /* Quick add chips */
    .unit-suggestions { margin: 6px 0 4px; }
    .unit-suggestions[hidden] { display: none; }
    .unit-suggestions-title { font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .unit-suggestions-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .unit-suggestion {
        border: 1px solid #d1d5db; background: #fff; color: #0f172a;
        border-radius: 6px; padding: 7px 12px; font-size: 13px; font-family: inherit; cursor: pointer;
    }
    .unit-suggestion:hover, .unit-suggestion:focus-visible { outline: none; border-color: #2563eb; background: #eff6ff; }
    .unit-suggestion strong { font-weight: 600; }
    .unit-suggestion span { color: #64748b; }
    .unit-suggestions-note { margin-top: 8px; font-size: 12px; color: #64748b; }

    /* Cost calculator: a normal button, then a plain panel */
    .cost-calc { margin-bottom: 18px; }
    .cost-calc-toggle { margin: 0; }
    .cost-calc-caption { display: inline-block; margin: 0 0 0 12px; vertical-align: middle; }
    .cost-calc-panel {
        margin-top: 12px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc;
        display: grid; gap: 12px;
    }
    .cost-calc-panel[hidden] { display: none; }
    .cost-calc-sentence { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; font-size: 14px; color: #0f172a; }
    .cost-calc-money {
        display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; height: 41px; box-sizing: border-box;
        border: 1px solid #d1d5db; border-radius: 6px; background: #fff;
    }
    /* The wrapper draws the box; the global input style must not draw a second one inside it. */
    .cost-calc .cost-calc-money input,
    .cost-calc .cost-calc-money input:focus {
        width: 130px !important; height: 100%; margin: 0; padding: 0 !important;
        border: 0 !important; border-radius: 0 !important; box-shadow: none !important; outline: 0 !important;
        background: transparent !important; font-size: 14px;
    }
    .cost-calc-money:focus-within { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, .1); }
    .cost-calc-sentence select {
        width: auto; height: 41px; min-width: 220px; max-width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px;
        padding: 0 10px; font-size: 14px; background: #fff;
    }
    .cost-calc-result { padding: 10px 12px; border-radius: 6px; background: #fff; border: 1px solid #e2e8f0; color: #334155; font-size: 14px; }
    .cost-calc-result.is-ready { background: #f0fdf4; border-color: #bbf7d0; color: #166534; font-weight: 600; }
    .cost-calc-result.is-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .cost-calc-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; }
    .cost-calc-note { font-size: 12px; color: #64748b; }

    /* Searchable unit dropdown: same box as .form-control */
    .ss-native { position: absolute !important; width: 1px; height: 1px; margin: 0; padding: 0; border: 0; opacity: 0; pointer-events: none; overflow: hidden; clip: rect(0 0 0 0); }
    .ss { position: relative; display: block; width: 100%; }
    .ss-button {
        width: 100%; min-height: 41px; box-sizing: border-box; display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; color: #0f172a;
        font-family: inherit; font-size: 14px; text-align: left; cursor: pointer;
    }
    .ss-button:hover { border-color: #9ca3af; }
    .ss-button:focus-visible, .ss.is-open .ss-button { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, .1); }
    .ss-button[disabled] { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .ss-placeholder { color: #9ca3af; }
    .ss-arrow { flex: none; color: #6b7280; font-size: 11px; }
    .ss-panel {
        position: fixed; z-index: 1000; left: 0; top: 0; width: 280px;
        background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, .14); padding: 8px;
    }
    .ss-panel[hidden] { display: none; }
    .ss-search { width: 100%; height: 38px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 14px; box-sizing: border-box; }
    .ss-search:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, .1); }
    .ss-list { list-style: none; margin: 8px 0 0; padding: 0; max-height: 260px; overflow-y: auto; }
    .ss-option { padding: 8px 10px; border-radius: 6px; cursor: pointer; display: flex; justify-content: space-between; gap: 10px; font-size: 14px; }
    .ss-option small { color: #6b7280; }
    .ss-option.is-active { background: #eff6ff; }
    .ss-option.is-selected { font-weight: 600; color: #1d4ed8; }
    .ss-empty { padding: 12px 10px; color: #6b7280; font-size: 14px; }
</style>

<div class="unit-suggestions" id="unit-suggestions" hidden>
    <div class="unit-suggestions-title">Quick add: units that usually go with <b class="u-base-head">your counting unit</b> (click one)</div>
    <div class="unit-suggestions-list" id="unit-suggestions-list"></div>
    <div class="unit-suggestions-note">The number is filled in for you. For a Box, Case or Pack, type how many it holds.</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var units = @json($unitSuggestionData);
        var baseSelect = document.getElementById('base_unit_id');
        var container = document.getElementById('units-container');
        var addButton = document.getElementById('add-unit');
        var box = document.getElementById('unit-suggestions');
        var list = document.getElementById('unit-suggestions-list');

        if (!baseSelect || !container || !addButton || !box || !list) {
            return;
        }

        // Plain-language labels: "1 BOX = 12 PIECE" fills in as the form changes.
        function plainName(select) {
            var option = select && select.value ? select.options[select.selectedIndex] : null;

            return option ? option.text.replace(/\s*\([^)]*\)\s*$/, '').replace(/\s+/g, ' ').trim() : '';
        }

        var hint = document.getElementById('base-unit-hint');

        if (!hint) {
            hint = document.createElement('small');
            hint.id = 'base-unit-hint';
            baseSelect.parentNode.insertBefore(hint, baseSelect.nextSibling);
        }

        // Step 2 only lists the OTHER units; the counting unit is implied and kept as a hidden row.
        var tableWrapper = container.closest('.unit-table-wrapper');
        var emptyNote = document.createElement('p');
        emptyNote.id = 'other-units-empty';
        emptyNote.className = 'unit-empty';
        tableWrapper.parentNode.insertBefore(emptyNote, tableWrapper);

        function refreshLabels() {
            var base = plainName(baseSelect);

            hint.textContent = base
                ? 'Stock and prices for this product are kept per ' + base + '. Other units below are worked out from it.'
                : 'Not sure? If you sell it one by one, choose Piece. If you sell by weight, choose Gram or Kilogram.';

            document.querySelectorAll('.u-base-head').forEach(function (el) {
                el.textContent = base || el.getAttribute('data-fallback') || 'your counting unit';
            });

            container.querySelectorAll('.unit-row').forEach(function (row) {
                var select = row.querySelector('.unit-select');
                var name = plainName(select);

                row.querySelectorAll('.u-name').forEach(function (el) { el.textContent = name || 'unit'; });
                row.querySelectorAll('.u-base').forEach(function (el) { el.textContent = base || 'counting unit'; });
                row.classList.toggle('is-base', !!baseSelect.value && !!select && select.value === baseSelect.value);
            });

            var visibleRows = Array.from(container.querySelectorAll('.unit-row')).filter(function (row) {
                return !row.classList.contains('is-base');
            }).length;

            tableWrapper.hidden = visibleRows === 0;
            emptyNote.hidden = visibleRows !== 0;
            emptyNote.textContent = (base ? 'Only ' + base + ' for now. ' : '')
                + 'Add a unit below if you also buy or sell this product by the box, dozen, kilogram and so on.';
        }

        function unitById(id) {
            return units.find(function (unit) { return unit.id === String(id); }) || null;
        }

        function selectedIds() {
            return Array.from(container.querySelectorAll('.unit-select'))
                .map(function (select) { return select.value; })
                .filter(Boolean);
        }

        // How many base units one of `unit` holds; null when it has to be typed.
        function factorFor(unit, base) {
            if (!unit.size || !base.size) {
                return null;
            }

            return Math.round((unit.size / base.size) * 10000) / 10000;
        }

        function trimNumber(value) {
            return String(value).replace(/(\.\d*?[1-9])0+$/, '$1').replace(/\.0+$/, '');
        }

        function addUnit(unitId, factor, implicit) {
            var row = Array.from(container.querySelectorAll('.unit-row')).find(function (candidate) {
                return !candidate.querySelector('.unit-select').value;
            });

            if (!row) {
                addButton.click();
                var rows = container.querySelectorAll('.unit-row');
                row = rows[rows.length - 1];
            }

            var select = row.querySelector('.unit-select');
            var conversion = row.querySelector('.conversion-input');

            if (implicit) {
                row.dataset.implicit = '1';
            }

            select.value = String(unitId);
            select.dispatchEvent(new Event('change', { bubbles: true }));

            if (!conversion.readOnly) {
                conversion.value = factor === null ? '' : trimNumber(factor);
                conversion.dispatchEvent(new Event('input', { bubbles: true }));

                if (factor === null) {
                    conversion.focus();
                }
            }

            render();
        }

        function render() {
            var base = unitById(baseSelect.value);
            list.innerHTML = '';

            if (!base || !base.family) {
                box.hidden = true;
                return;
            }

            var taken = selectedIds();

            var options = units
                .filter(function (unit) {
                    return unit.family === base.family && unit.id !== base.id && taken.indexOf(unit.id) === -1;
                })
                .map(function (unit) {
                    return { unit: unit, factor: factorFor(unit, base) };
                })
                .filter(function (option) {
                    // The form stores four decimal places; smaller conversions can't be saved.
                    return option.factor === null || option.factor >= 0.0001;
                })
                .sort(function (a, b) {
                    if ((a.factor === null) !== (b.factor === null)) {
                        return a.factor === null ? 1 : -1;
                    }

                    return (a.factor || 0) - (b.factor || 0) || a.unit.name.localeCompare(b.unit.name);
                });

            options.forEach(function (option) {
                var button = document.createElement('button');
                var name = document.createElement('strong');
                var detail = document.createElement('span');

                button.type = 'button';
                button.className = 'unit-suggestion';
                name.textContent = option.unit.name;
                detail.textContent = option.factor === null
                    ? ' (you set the quantity)'
                    : ' = ' + trimNumber(option.factor) + ' ' + base.name;

                button.appendChild(name);
                button.appendChild(detail);
                button.addEventListener('click', function () {
                    addUnit(option.unit.id, option.factor);
                });

                list.appendChild(button);
            });

            box.hidden = options.length === 0;
        }

        baseSelect.addEventListener('change', function () {
            // Drop the counting-unit row we added for the PREVIOUS choice, so it
            // does not turn into a stray 'old unit = 1 new unit' line.
            container.querySelectorAll('.unit-row[data-implicit="1"]').forEach(function (row) {
                var select = row.querySelector('.unit-select');

                if (!select || select.value !== baseSelect.value) {
                    row.remove();
                }
            });

            if (baseSelect.value && selectedIds().indexOf(baseSelect.value) === -1) {
                // The counting unit is always saved with the product, but not shown as a row.
                addUnit(baseSelect.value, 1, true);
            } else if (baseSelect.value) {
                // A row for it already exists: just let the page recalculate (factor = 1).
                var existing = Array.from(container.querySelectorAll('.unit-select')).find(function (s) { return s.value === baseSelect.value; });

                if (existing) {
                    existing.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            render();
            refreshLabels();
        });

        container.addEventListener('change', function () { render(); refreshLabels(); });
        container.addEventListener('input', refreshLabels);
        baseSelect.addEventListener('change', refreshLabels);
        new MutationObserver(function () { render(); refreshLabels(); }).observe(container, { childList: true });

        render();
        refreshLabels();

        // Rows that already stand for the counting unit (an edited product) are implicit too.
        container.querySelectorAll('.unit-row').forEach(function (row) {
            var select = row.querySelector('.unit-select');

            if (select && select.value && select.value === baseSelect.value) {
                row.dataset.implicit = '1';
            }
        });

        // A new product opens with Piece preselected: add its (hidden) counting-unit row now.
        if (baseSelect.value && selectedIds().indexOf(baseSelect.value) === -1) {
            addUnit(baseSelect.value, 1, true);
        }

        refreshLabels();
    });
</script>


<script>
    /*
     * Searchable dropdown for unit-of-measure selects. The real <select> stays
     * in the form (hidden) and remains the source of truth, so every existing
     * script that reads select.value or listens for "change" keeps working.
     */
    (function () {
        function enhance(select) {
            if (select.dataset.searchable === '1' || select.multiple) {
                return;
            }

            select.dataset.searchable = '1';

            var wrap = document.createElement('span');
            wrap.className = 'ss';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            select.classList.add('ss-native');
            select.tabIndex = -1;

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'ss-button';
            button.setAttribute('aria-haspopup', 'listbox');
            button.setAttribute('aria-expanded', 'false');
            button.innerHTML = '<span class="ss-label"></span><span class="ss-arrow" aria-hidden="true">&#9662;</span>';
            wrap.appendChild(button);

            var panel = document.createElement('div');
            panel.className = 'ss-panel';
            panel.hidden = true;
            panel.innerHTML = '<input type="text" class="ss-search" placeholder="Search units..." autocomplete="off" aria-label="Search units"><ul class="ss-list" role="listbox"></ul>';
            wrap.appendChild(panel);

            var label = button.querySelector('.ss-label');
            var search = panel.querySelector('.ss-search');
            var list = panel.querySelector('.ss-list');
            var shown = [];
            var active = -1;

            function optionData() {
                return Array.prototype.filter.call(select.options, function (o) { return o.value !== ''; }).map(function (o) {
                    var match = o.text.replace(/\s+/g, ' ').trim().match(/^(.*?)(?:\s*\(([^)]*)\))?$/);

                    return { value: o.value, name: match ? match[1] : o.text.trim(), code: match && match[2] ? match[2] : '', disabled: o.disabled };
                });
            }

            function syncLabel() {
                var option = select.options[select.selectedIndex];
                var hasValue = option && option.value !== '';

                label.textContent = hasValue ? option.text.replace(/\s+/g, ' ').trim() : ((select.options[0] && select.options[0].text.replace(/\s+/g, ' ').trim()) || '-- Select --');
                label.classList.toggle('ss-placeholder', !hasValue);
                button.disabled = select.disabled;
            }

            function render() {
                var q = search.value.trim().toLowerCase();

                shown = optionData().filter(function (o) {
                    return !o.disabled && (q === '' || o.name.toLowerCase().indexOf(q) !== -1 || o.code.toLowerCase().indexOf(q) !== -1);
                });

                list.innerHTML = '';

                if (shown.length === 0) {
                    list.innerHTML = '<li class="ss-empty">No unit matches "' + search.value.replace(/[<>&"]/g, '') + '".</li>';
                    active = -1;
                    return;
                }

                active = Math.max(0, shown.findIndex(function (o) { return o.value === select.value; }));

                shown.forEach(function (o, index) {
                    var li = document.createElement('li');
                    li.className = 'ss-option' + (o.value === select.value ? ' is-selected' : '') + (index === active ? ' is-active' : '');
                    li.setAttribute('role', 'option');
                    li.innerHTML = '<span></span><small></small>';
                    li.firstChild.textContent = o.name;
                    li.lastChild.textContent = o.code;
                    li.addEventListener('mousedown', function (event) { event.preventDefault(); choose(o.value); });
                    li.addEventListener('mousemove', function () { setActive(index); });
                    list.appendChild(li);
                });

                if (!panel.hidden) { place(); }
            }

            function setActive(index) {
                var items = list.querySelectorAll('.ss-option');

                if (!items.length) { return; }

                active = (index + items.length) % items.length;
                items.forEach(function (item, i) { item.classList.toggle('is-active', i === active); });
                items[active].scrollIntoView({ block: 'nearest' });
            }

            // The panel is position: fixed so a scrolling table cannot clip it;
            // place it under the button, or above when there is no room below.
            function place() {
                var r = button.getBoundingClientRect();
                var h = panel.offsetHeight;
                var below = window.innerHeight - r.bottom;
                var top = (below < h + 12 && r.top > below) ? Math.max(8, r.top - h - 4) : r.bottom + 4;

                panel.style.left = r.left + 'px';
                panel.style.width = Math.max(r.width, 240) + 'px';
                panel.style.top = top + 'px';
            }

            function open() {
                if (select.disabled) { return; }

                search.value = '';
                panel.hidden = false;
                render();
                place();
                wrap.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
                search.focus({ preventScroll: true });

                var selected = list.querySelector('.is-selected');

                if (selected) { selected.scrollIntoView({ block: 'nearest' }); }
            }

            function close() {
                panel.hidden = true;
                wrap.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
            }

            function choose(value) {
                close();
                button.focus();

                if (select.value !== value) {
                    select.value = value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }

                syncLabel();
            }

            button.addEventListener('click', function () { panel.hidden ? open() : close(); });
            search.addEventListener('input', render);
            search.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown') { event.preventDefault(); setActive(active + 1); }
                else if (event.key === 'ArrowUp') { event.preventDefault(); setActive(active - 1); }
                else if (event.key === 'Enter') { event.preventDefault(); if (shown[active]) { choose(shown[active].value); } }
                else if (event.key === 'Escape') { event.preventDefault(); close(); button.focus(); }
                else if (event.key === 'Tab') { close(); }
            });
            document.addEventListener('mousedown', function (event) { if (!wrap.contains(event.target)) { close(); } });
            // The panel is fixed to the screen, so keep it attached to its button.
            window.addEventListener('scroll', function (event) { if (!panel.hidden && !panel.contains(event.target)) { place(); } }, true);
            window.addEventListener('resize', function () { if (!panel.hidden) { place(); } });
            select.addEventListener('change', syncLabel);
            new MutationObserver(syncLabel).observe(select, { attributes: true, attributeFilter: ['disabled'] });

            syncLabel();
        }

        function enhanceAll(root) {
            root.querySelectorAll('#base_unit_id, #units-container .unit-select').forEach(enhance);
        }

        document.addEventListener('DOMContentLoaded', function () {
            enhanceAll(document);

            var container = document.getElementById('units-container');

            if (container) {
                // Rows added later ("+ Add another unit", a suggestion click) get the same search box.
                new MutationObserver(function () { enhanceAll(document); }).observe(container, { childList: true, subtree: true });
            }
        });
    })();
</script>