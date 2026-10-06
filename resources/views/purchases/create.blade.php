@extends('layouts.app')

@section('title', __('app.new_purchase'))
@section('page-title', __('app.new_purchase'))

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

    <form method="POST" action="{{ route('purchases.store') }}" id="purchase-form">
        @csrf

        <div class="stat-card mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.supplier') }}</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->business_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.warehouse') }}</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.date') }}</label>
                    <input type="date" name="purchase_date" class="form-control"
                        value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.supplier_invoice_no') }} ({{ __('app.optional') }})</label>
                    <input type="text" name="supplier_invoice_no" class="form-control"
                        value="{{ old('supplier_invoice_no') }}">
                </div>
            </div>
        </div>

        <div class="stat-card p-0 mb-3">
            <div class="table-responsive-card">
                <table class="table mb-0 align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px">{{ __('app.product') }}</th>
                            <th style="width:100px">{{ __('app.quantity') }}</th>
                            <th style="width:130px">{{ __('app.unit_cost') }}</th>
                            <th style="width:110px">{{ __('app.discount') }}</th>
                            <th style="width:120px" class="text-end">{{ __('app.subtotal') }}</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-row"><i class="bi bi-plus-lg"></i>
                    {{ __('app.add_item') }}</button>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="stat-card">
                    <div class="d-flex justify-content-between mb-2"><span>{{ __('app.subtotal') }}</span><strong
                            id="sum-subtotal">0.00</strong></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">{{ __('app.overall_discount') }}</label>
                        <input type="number" step="0.01" min="0" name="discount" id="overall-discount"
                            class="form-control form-control-sm" style="width:130px" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-5"><strong>{{ __('app.total') }}</strong><strong
                            id="sum-total">0.00</strong></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">{{ __('app.paid_amount') }}</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" id="paid-amount"
                            class="form-control form-control-sm" style="width:130px" value="{{ old('paid_amount', 0) }}">
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-danger">
                        <strong>{{ __('app.due') }}</strong><strong id="sum-due">0.00</strong>
                    </div>
                    <textarea name="note" class="form-control form-control-sm mb-3" rows="2" placeholder="{{ __('app.note') }}">{{ old('note') }}</textarea>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg"></i>
                        {{ __('app.save_purchase') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@php
    $productsForJs = $products
        ->map(
            fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'cost' => (float) $p->purchase_price,
                'unit' => $p->unit?->short_code,
            ],
        )
        ->values();
@endphp

@push('scripts')
    <script>
        (function() {
            var products = @json($productsForJs);

            var tbody = document.getElementById('items-body');
            var rowCount = 0;

            function addRow() {
                rowCount++;
                var tr = document.createElement('tr');

                var options = '<option value="">{{ __('app.select') }}</option>';
                products.forEach(function(p) {
                    options += '<option value="' + p.id + '" data-cost="' + p.cost + '">' + p.name + ' (' + p
                        .sku + ')</option>';
                });

                tr.innerHTML =
                    '<td><select class="form-select form-select-sm product-select" name="product_id[]" required>' +
                    options + '</select></td>' +
                    '<td><input type="number" min="1" class="form-control form-control-sm qty-input" name="quantity[]" value="1" required></td>' +
                    '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm cost-input" name="unit_cost[]" required></td>' +
                    '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm disc-input" name="item_discount[]" value="0"></td>' +
                    '<td class="text-end row-subtotal">0.00</td>' +
                    '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>';

                tbody.appendChild(tr);
                bindRow(tr);
            }

            function bindRow(tr) {
                var select = tr.querySelector('.product-select');
                var qty = tr.querySelector('.qty-input');
                var cost = tr.querySelector('.cost-input');
                var disc = tr.querySelector('.disc-input');

                select.addEventListener('change', function() {
                    var opt = select.options[select.selectedIndex];
                    cost.value = opt.dataset.cost || 0;
                    recalc();
                });
                [qty, cost, disc].forEach(function(el) {
                    el.addEventListener('input', recalc);
                });
                tr.querySelector('.remove-row').addEventListener('click', function() {
                    tr.remove();
                    recalc();
                });
            }

            function recalc() {
                var subtotal = 0;
                tbody.querySelectorAll('tr').forEach(function(tr) {
                    var qty = parseFloat(tr.querySelector('.qty-input').value || 0);
                    var cost = parseFloat(tr.querySelector('.cost-input').value || 0);
                    var disc = parseFloat(tr.querySelector('.disc-input').value || 0);
                    var lineSub = Math.max(0, (qty * cost) - disc);
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

            addRow();
        })();
    </script>
@endpush
