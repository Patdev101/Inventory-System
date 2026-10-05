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
    .unit-suggestions { margin: 4px 0 18px; }
    .unit-suggestions[hidden] { display: none; }
    .unit-suggestions-title { font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; }
    .unit-suggestions-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .unit-suggestion {
        border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a;
        border-radius: 999px; padding: 6px 12px; font-size: 13px; cursor: pointer;
    }
    .unit-suggestion:hover, .unit-suggestion:focus-visible { border-color: #2563eb; background: #eff6ff; }
    .unit-suggestion strong { font-weight: 700; }
    .unit-suggestion span { color: #64748b; }
    .unit-suggestions-note { margin-top: 8px; font-size: 12px; color: #64748b; }
</style>

<div class="unit-suggestions" id="unit-suggestions" hidden>
    <div class="unit-suggestions-title">Suggested units for this base unit (click to add)</div>
    <div class="unit-suggestions-list" id="unit-suggestions-list"></div>
    <div class="unit-suggestions-note">Conversions are filled in for you. For packaging such as a box or case, type how many base units it holds.</div>
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

        function addUnit(unitId, factor) {
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
            // A product always sells in its base unit, so add that row straight away.
            if (baseSelect.value && selectedIds().indexOf(baseSelect.value) === -1) {
                addUnit(baseSelect.value, 1);
            }

            render();
        });

        container.addEventListener('change', render);
        new MutationObserver(render).observe(container, { childList: true });

        render();
    });
</script>
