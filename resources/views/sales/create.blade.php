@extends('layouts.app')

@section('title', __('app.new_invoice'))
@section('page-title', $type === 'primary' ? __('app.primary_sales') : __('app.secondary_sales'))

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.store') }}" id="sale-form">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="stat-card mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ $type === 'primary' ? __('app.dealer') : __('app.retailer') }}</label>
                    <select name="partner_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($partners as $p)
                            <option value="{{ $p->id }}" {{ old('partner_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->business_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.warehouse') }}</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.date') }}</label>
                    <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div class="stat-card mb-3">
            <label class="form-label mb-1"><i class="bi bi-upc-scan"></i> {{ __('app.scan_barcode') }}</label>
            <input type="text" id="barcode-scan" class="form-control" placeholder="{{ __('app.scan_barcode_placeholder') }}" autocomplete="off">
            <div id="scan-feedback" class="small mt-1"></div>
            <div class="form-text">{{ __('app.scan_barcode_hint') }}</div>
        </div>

        <div class="stat-card p-0 mb-3">
            <div class="table-responsive-card">
                <table class="table mb-0 align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px">{{ __('app.product') }}</th>
                            <th style="width:90px">{{ __('app.available') }}</th>
                            <th style="width:100px">{{ __('app.quantity') }}</th>
                            <th style="width:120px">{{ __('app.unit_price') }}</th>
                            <th style="width:110px">{{ __('app.discount') }}</th>
                            <th style="width:120px" class="text-end">{{ __('app.subtotal') }}</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">
                    <i class="bi bi-plus-lg"></i> {{ __('app.add_item') }}
                </button>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="stat-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('app.subtotal') }}</span>
                        <strong id="sum-subtotal">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">{{ __('app.overall_discount') }}</label>
                        <input type="number" step="0.01" min="0" name="discount" id="overall-discount" class="form-control form-control-sm" style="width:130px" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-5">
                        <strong>{{ __('app.total') }}</strong>
                        <strong id="sum-total">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">{{ __('app.paid_amount') }}</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" id="paid-amount" class="form-control form-control-sm" style="width:130px" value="{{ old('paid_amount', 0) }}">
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-danger">
                        <strong>{{ __('app.due') }}</strong>
                        <strong id="sum-due">0.00</strong>
                    </div>
                    <textarea name="note" class="form-control form-control-sm mb-3" rows="2" placeholder="{{ __('app.note') }}">{{ old('note') }}</textarea>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg"></i> {{ __('app.save_invoice') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection

{{-- Built here, not inline inside @json(), so Blade's directive parser
     never has to deal with a multi-line closure — see the longer note
     in sr/live-map.blade.php for why that combination is unsafe. --}}
@php
    $productsForJs = $products->map(fn ($p) => [
        'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'barcode' => $p->barcode,
        'price' => (float) $p->sale_price, 'unit' => $p->unit?->short_code,
    ])->values();
@endphp

@push('scripts')
<script>
(function () {
    var products = @json($productsForJs);
    var stockMap = @json($stockMap);

    var tbody = document.getElementById('items-body');
    var warehouseSelect = document.getElementById('warehouse_id');
    var rowCount = 0;

    function availableFor(productId) {
        var wid = warehouseSelect.value;
        if (!wid || !productId) return null;
        var key = productId + '_' + wid;
        return stockMap.hasOwnProperty(key) ? stockMap[key] : null;
    }

    function addRow() {
        rowCount++;
        var tr = document.createElement('tr');
        tr.dataset.rowId = rowCount;

        var options = '<option value="">{{ __("app.select") }}</option>';
        products.forEach(function (p) {
            options += '<option value="' + p.id + '" data-price="' + p.price + '" data-unit="' + (p.unit || '') + '">' + p.name + ' (' + p.sku + ')</option>';
        });

        tr.innerHTML =
            '<td><select class="form-select form-select-sm product-select" name="product_id[]" required>' + options + '</select></td>' +
            '<td class="available-cell text-secondary small">—</td>' +
            '<td><input type="number" min="1" class="form-control form-control-sm qty-input" name="quantity[]" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm price-input" name="unit_price[]" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm disc-input" name="item_discount[]" value="0"></td>' +
            '<td class="text-end row-subtotal">0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>';

        tbody.appendChild(tr);
        bindRow(tr);
    }

    function bindRow(tr) {
        var select = tr.querySelector('.product-select');
        var qty = tr.querySelector('.qty-input');
        var price = tr.querySelector('.price-input');
        var disc = tr.querySelector('.disc-input');
        var availableCell = tr.querySelector('.available-cell');

        function onProductChange() {
            var opt = select.options[select.selectedIndex];
            price.value = opt.dataset.price || 0;
            updateAvailable();
            recalc();
        }

        function updateAvailable() {
            var avail = availableFor(select.value);
            availableCell.textContent = avail === null ? '—' : avail;
            if (avail !== null && parseInt(qty.value || 0, 10) > avail) {
                availableCell.classList.add('text-danger', 'fw-semibold');
            } else {
                availableCell.classList.remove('text-danger', 'fw-semibold');
            }
        }

        select.addEventListener('change', onProductChange);
        [qty, price, disc].forEach(function (el) {
            el.addEventListener('input', function () { updateAvailable(); recalc(); });
        });
        tr.querySelector('.remove-row').addEventListener('click', function () {
            tr.remove();
            recalc();
        });
    }

    function recalc() {
        var subtotal = 0;
        tbody.querySelectorAll('tr').forEach(function (tr) {
            var qty = parseFloat(tr.querySelector('.qty-input').value || 0);
            var price = parseFloat(tr.querySelector('.price-input').value || 0);
            var disc = parseFloat(tr.querySelector('.disc-input').value || 0);
            var lineSub = Math.max(0, (qty * price) - disc);
            tr.querySelector('.row-subtotal').textContent = lineSub.toFixed(2);
            subtotal += lineSub;
        });

        var overallDiscount = parseFloat(document.getElementById('overall-discount').value || 0);
        var total = Math.max(0, subtotal - overallDiscount);
        var paid = parseFloat(document.getElementById('paid-amount').value || 0);
        var due = Math.max(0, total - paid);

        document.getElementById('sum-subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('sum-total').textContent = total.toFixed(2);
        document.getElementById('sum-due').textContent = due.toFixed(2);
    }

    document.getElementById('add-row').addEventListener('click', addRow);
    document.getElementById('overall-discount').addEventListener('input', recalc);
    document.getElementById('paid-amount').addEventListener('input', recalc);
    warehouseSelect.addEventListener('change', function () {
        tbody.querySelectorAll('tr').forEach(function (tr) {
            tr.querySelector('.product-select').dispatchEvent(new Event('change'));
        });
    });

    /**
     * Barcode scanning support. A USB/Bluetooth barcode scanner is
     * just a keyboard emulator — it "types" the scanned code into
     * whichever input is focused, very fast, then sends Enter. So all
     * this needs is: keep focus on this input, and on Enter look the
     * value up against each product's barcode (falling back to SKU,
     * since many shops scan a printed SKU label instead of a real
     * barcode) and auto-add that row — no camera or extra library
     * required. A phone camera scanner app that also "types + Enter"
     * into whatever's focused works the same way, for free.
     */
    var scanInput = document.getElementById('barcode-scan');
    var scanFeedback = document.getElementById('scan-feedback');

    function addRowForProduct(product) {
        addRow();
        var tr = tbody.lastElementChild;
        var select = tr.querySelector('.product-select');
        select.value = product.id;
        select.dispatchEvent(new Event('change'));
        var qty = tr.querySelector('.qty-input');
        qty.focus();
        qty.select();
    }

    scanInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        var code = scanInput.value.trim();
        scanInput.value = '';
        if (!code) return;

        var match = products.find(function (p) {
            return (p.barcode && p.barcode === code) || p.sku === code;
        });

        if (match) {
            addRowForProduct(match);
            scanFeedback.className = 'small mt-1 text-success';
            scanFeedback.textContent = '✓ ' + match.name + ' {{ __("app.scan_added") }}';
        } else {
            scanFeedback.className = 'small mt-1 text-danger';
            scanFeedback.textContent = '✗ {{ __("app.scan_not_found") }}: ' + code;
        }
    });

    // Start with one empty row
    addRow();
    scanInput.focus();
})();
</script>
@endpush
