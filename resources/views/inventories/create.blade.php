@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')

<div class="container">

    <h1>Add Inventory</h1>

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventories.store') }}" method="POST">
        @csrf

        {{-- Company --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="company_id">
                <strong>Company</strong>
            </label>

            <select
                id="company_id"
                name="company_id_filter"
                required
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Company --</option>

                @foreach ($companies as $company)
                    <option
                        value="{{ $company->id }}"
                        {{ old('company_id_filter') == $company->id ? 'selected' : '' }}
                    >
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>

            <small style="color: #6b7280;">
                Not all companies carry the same products or locations —
                choose a company first to narrow the choices below.
            </small>
        </div>


        {{-- Product --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="product_id">
                <strong>Product</strong>
            </label>

            <select
                id="product_id"
                name="product_id"
                required
                disabled
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Company First --</option>
            </select>
        </div>


        {{-- Location --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="location_id">
                <strong>Location</strong>
            </label>

            <select
                id="location_id"
                name="location_id"
                required
                disabled
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Company First --</option>
            </select>
        </div>


        {{-- Unit of Measure --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="product_unit_id">
                <strong>Unit of Measure</strong>
            </label>

            <select
                id="product_unit_id"
                name="product_unit_id"
                required
                disabled
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Product First --</option>
            </select>
        </div>


        {{-- Quantity --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="quantity">
                <strong>Quantity</strong>
            </label>

            <input
                type="number"
                id="quantity"
                name="quantity"
                value="{{ old('quantity') }}"
                min="0.0001"
                step="0.0001"
                required
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
        </div>


        {{-- Conversion Factor --}}
        <div
            id="conversion-container"
            class="form-group"
            style="margin-bottom: 15px; display: none;"
        >
            <label for="conversion_factor">
                <strong>Conversion Factor</strong>
            </label>

            <input
                type="text"
                id="conversion_factor"
                readonly
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px; background-color: #f5f5f5;"
            >
        </div>


        {{-- Total Base Quantity --}}
        <div
            id="total-container"
            class="form-group"
            style="margin-bottom: 15px; display: none;"
        >
            <label for="total_quantity">
                <strong>Total Base Quantity</strong>
            </label>

            <input
                type="text"
                id="total_quantity"
                readonly
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px; background-color: #f5f5f5;"
            >
        </div>


        <button type="submit" class="btn btn-primary">
            Save Inventory
        </button>

        <a
            href="{{ route('inventories.index') }}"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </form>
</div>


<script>

    function formatQty(value) {
        const num = Number(value);
        if (!isFinite(num)) {
            return String(value);
        }
        return parseFloat(num.toFixed(2)).toString();
    }

    const products = @json($products);
    const locations = @json($locations);

    const oldProductId = @json(old('product_id'));
    const oldLocationId = @json(old('location_id'));

    const companySelect = document.getElementById('company_id');
    const productSelect = document.getElementById('product_id');
    const locationSelect = document.getElementById('location_id');
    const unitSelect = document.getElementById('product_unit_id');
    const quantityInput = document.getElementById('quantity');

    const conversionContainer =
        document.getElementById('conversion-container');

    const conversionInput =
        document.getElementById('conversion_factor');

    const totalContainer =
        document.getElementById('total-container');

    const totalInput =
        document.getElementById('total_quantity');


    function resetConversion() {

        conversionContainer.style.display = 'none';
        totalContainer.style.display = 'none';

        conversionInput.value = '';
        totalInput.value = '';
    }


    function calculateTotal() {

        const selectedOption =
            unitSelect.options[unitSelect.selectedIndex];

        if (!selectedOption || !selectedOption.dataset.conversion) {
            resetConversion();
            return;
        }

        const conversion =
            parseFloat(selectedOption.dataset.conversion);

        const quantity =
            parseFloat(quantityInput.value);

        if (isNaN(conversion)) {
            resetConversion();
            return;
        }

        conversionContainer.style.display = 'block';

        conversionInput.value =
            formatQty(conversion);


        if (!isNaN(quantity) && quantity > 0) {

            const total =
                quantity * conversion;

            totalContainer.style.display = 'block';

            totalInput.value =
                formatQty(total);

        } else {

            totalContainer.style.display = 'none';

            totalInput.value = '';
        }
    }


    function updateCompanyScopedOptions(preselect) {

        preselect = preselect || {};

        const companyId = companySelect.value;

        productSelect.innerHTML = '';
        locationSelect.innerHTML = '';

        if (!companyId) {

            productSelect.disabled = true;
            locationSelect.disabled = true;

            const productPlaceholder = document.createElement('option');
            productPlaceholder.value = '';
            productPlaceholder.textContent = '-- Select Company First --';
            productSelect.appendChild(productPlaceholder);

            const locationPlaceholder = document.createElement('option');
            locationPlaceholder.value = '';
            locationPlaceholder.textContent = '-- Select Company First --';
            locationSelect.appendChild(locationPlaceholder);

            updateUnits();

            return;
        }

        productSelect.disabled = false;
        locationSelect.disabled = false;

        const productPlaceholder = document.createElement('option');
        productPlaceholder.value = '';
        productPlaceholder.textContent = '-- Select Product --';
        productSelect.appendChild(productPlaceholder);

        products
            .filter(function (product) {
                return String(product.company_id) === String(companyId);
            })
            .forEach(function (product) {

                const option = document.createElement('option');
                option.value = product.id;

                const identifier = product.item_code || product.sku;

                option.textContent =
                    product.name +
                    (identifier ? ' (' + identifier + ')' : '');

                if (
                    preselect.productId &&
                    String(preselect.productId) === String(product.id)
                ) {
                    option.selected = true;
                }

                productSelect.appendChild(option);
            });

        if (
            productSelect.querySelectorAll('option[value]:not([value=""])').length === 0
        ) {
            productPlaceholder.textContent = '-- No Products for This Company --';
        }

        const locationPlaceholder = document.createElement('option');
        locationPlaceholder.value = '';
        locationPlaceholder.textContent = '-- Select Location --';
        locationSelect.appendChild(locationPlaceholder);

        locations
            .filter(function (location) {
                return String(location.company_id) === String(companyId);
            })
            .forEach(function (location) {

                const option = document.createElement('option');
                option.value = location.id;

                option.textContent =
                    location.name +
                    (location.code ? ' (' + location.code + ')' : '');

                if (
                    preselect.locationId &&
                    String(preselect.locationId) === String(location.id)
                ) {
                    option.selected = true;
                }

                locationSelect.appendChild(option);
            });

        if (
            locationSelect.querySelectorAll('option[value]:not([value=""])').length === 0
        ) {
            locationPlaceholder.textContent = '-- No Locations for This Company --';
        }

        updateUnits();
    }


    companySelect.addEventListener('change', function () {
        updateCompanyScopedOptions();
    });


    function updateUnits() {

        const productId =
            productSelect.value;

        unitSelect.innerHTML = '';

        resetConversion();


        if (!productId) {

            unitSelect.disabled = true;

            const option =
                document.createElement('option');

            option.value = '';

            option.textContent =
                '-- Select Product First --';

            unitSelect.appendChild(option);

            return;
        }


        const product =
            products.find(function (product) {

                return String(product.id) ===
                    String(productId);

            });


        if (
            !product ||
            !product.product_units ||
            product.product_units.length === 0
        ) {

            unitSelect.disabled = true;

            const option =
                document.createElement('option');

            option.value = '';

            option.textContent =
                '-- No Units Configured --';

            unitSelect.appendChild(option);

            return;
        }


        unitSelect.disabled = false;


        const placeholder =
            document.createElement('option');

        placeholder.value = '';

        placeholder.textContent =
            '-- Select Unit --';

        unitSelect.appendChild(placeholder);


        product.product_units.forEach(function (productUnit) {

            if (!productUnit.unit_of_measure) {
                return;
            }


            const option =
                document.createElement('option');


            option.value =
                productUnit.id;


            option.dataset.conversion =
                productUnit.conversion_factor;


            option.textContent =
                productUnit.unit_of_measure.name +
                ' (' +
                productUnit.unit_of_measure.code +
                ') - 1 unit = ' +
                formatQty(productUnit.conversion_factor);


            unitSelect.appendChild(option);

        });

    }


    productSelect.addEventListener(
        'change',
        updateUnits
    );


    unitSelect.addEventListener(
        'change',
        calculateTotal
    );


    quantityInput.addEventListener(
        'input',
        calculateTotal
    );


    updateCompanyScopedOptions({
        productId: oldProductId,
        locationId: oldLocationId,
    });

</script>

@endsection