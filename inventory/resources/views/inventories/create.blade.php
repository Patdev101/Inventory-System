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

        {{-- Product --}}
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="product_id">
                <strong>Product</strong>
            </label>

            <select
                id="product_id"
                name="product_id"
                required
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Product --</option>

                @foreach ($products as $product)
                    <option
                        value="{{ $product->id }}"
                        {{ old('product_id') == $product->id ? 'selected' : '' }}
                    >
                        {{ $product->name }} ({{ $product->code }})
                    </option>
                @endforeach
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
                style="width: 100%; box-sizing: border-box; padding: 9px; border: 1px solid #ccc; border-radius: 5px;"
            >
                <option value="">-- Select Location --</option>

                @foreach ($locations as $location)
                    <option
                        value="{{ $location->id }}"
                        {{ old('location_id') == $location->id ? 'selected' : '' }}
                    >
                        {{ $location->name }} ({{ $location->code }})
                    </option>
                @endforeach
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

    const products = @json($products);

    const productSelect = document.getElementById('product_id');
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
            conversion.toFixed(4);


        if (!isNaN(quantity) && quantity > 0) {

            const total =
                quantity * conversion;

            totalContainer.style.display = 'block';

            totalInput.value =
                total.toFixed(4);

        } else {

            totalContainer.style.display = 'none';

            totalInput.value = '';
        }
    }


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
                productUnit.conversion_factor;


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


    updateUnits();

</script>

@endsection