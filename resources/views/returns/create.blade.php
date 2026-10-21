@extends('layouts.app')

@section('title', __('app.new_return'))
@section('page-title', $type === 'sales_return' ? __('app.sales_return') : __('app.purchase_return'))

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('returns.store') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="stat-card mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ $type === 'sales_return' ? __('app.dealer_or_retailer') : __('app.supplier') }}</label>
                    <select name="partner_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($partners as $p)
                            <option value="{{ $p->id }}" {{ old('partner_id') == $p->id ? 'selected' : '' }}>[{{ $p->typeLabel() }}] {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.warehouse') }}</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.date') }}</label>
                    <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.reason') }} ({{ __('app.optional') }})</label>
                    <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="{{ __('app.reason_placeholder') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.original_invoice_ref') }} ({{ __('app.optional') }})</label>
                    <input type="text" name="reference_no_text" class="form-control" value="{{ old('reference_no_text') }}" placeholder="{{ $type === 'sales_return' ? 'PS-20260115-0003' : 'PO-20260115-0003' }}">
                </div>
            </div>
        </div>

        <div class="stat-card mb-3">
            <label class="form-label mb-1"><i class="bi bi-upc-scan"></i> {{ __('app.scan_barcode') }}</label>
            <input type="text" id="barcode-scan" class="form-control" placeholder="{{ __('app.scan_barcode_placeholder') }}" autocomplete="off">
            <div id="scan-feedback" class="small mt-1"></div>
        </div>

        <div class="stat-card p-0 mb-3">
            <div class="table-responsive-card">
                <table class="table mb-0 align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px">{{ __('app.product') }}</th>
                            <th style="width:100px">{{ __('app.quantity') }}</th>
                            <th style="width:130px">{{ __('app.unit_price') }}</th>
                            <th style="width:120px" class="text-end">{{ __('app.subtotal') }}</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-row"><i class="bi bi-plus-lg"></i> {{ __('app.add_item') }}</button>
                <div class="fs-5"><strong>{{ __('app.total') }}: </strong><strong id="grand-total">0.00</strong></div>
            </div>
        </div>

        <div class="mb-4">
            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="{{ __('app.note') }}">{{ old('note') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save_return') }}</button>
        <a href="{{ route('returns.index', ['type' => $type]) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
    </form>
@endsection

{{-- See sr/live-map.blade.php for why this is built in @php rather
     than inline inside @json(). --}}
@php
    $productsForJs = $products->map(fn ($p) => [
        'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'barcode' => $p->barcode,
        'price' => (float) ($type === 'sales_return' ? $p->sale_price : $p->purchase_price),
    ])->values();
@endphp

@push('scripts')
<script>
(function () {
    var products = @json($productsForJs);

    var tbody = document.getElementById('items-body');

    function addRow() {
        var tr = document.createElement('tr');
        var options = '<option value="">{{ __("app.select") }}</option>';
        products.forEach(function (p) {
            options += '<option value="' + p.id + '" data-price="' + p.price + '">' + p.name + ' (' + p.sku + ')</option>';
        });

        tr.innerHTML =
            '<td><select class="form-select form-select-sm product-select" name="product_id[]" required>' + options + '</select></td>' +
            '<td><input type="number" min="1" class="form-control form-control-sm qty-input" name="quantity[]" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm price-input" name="unit_price[]" required></td>' +
            '<td class="text-end row-subtotal">0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>';

        tbody.appendChild(tr);
        bindRow(tr);
    }

    function bindRow(tr) {
        var select = tr.querySelector('.product-select');
        var qty = tr.querySelector('.qty-input');
        var price = tr.querySelector('.price-input');

        select.addEventListener('change', function () {
            price.value = select.options[select.selectedIndex].dataset.price || 0;
            recalc();
        });
        [qty, price].forEach(function (el) { el.addEventListener('input', recalc); });
        tr.querySelector('.remove-row').addEventListener('click', function () { tr.remove(); recalc(); });
    }

    function recalc() {
        var total = 0;
        tbody.querySelectorAll('tr').forEach(function (tr) {
            var qty = parseFloat(tr.querySelector('.qty-input').value || 0);
            var price = parseFloat(tr.querySelector('.price-input').value || 0);
            var sub = qty * price;
            tr.querySelector('.row-subtotal').textContent = sub.toFixed(2);
            total += sub;
        });
        document.getElementById('grand-total').textContent = total.toFixed(2);
    }

    document.getElementById('add-row').addEventListener('click', addRow);

    // Barcode scan support — see sales/create.blade.php for the full explanation.
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

    addRow();
    scanInput.focus();
})();
</script>
@endpush
