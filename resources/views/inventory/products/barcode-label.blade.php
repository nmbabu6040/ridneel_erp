<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('app.barcode_labels') }} — {{ $product->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
        }

        .toolbar {
            margin-bottom: 14px;
        }

        .toolbar form {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .sheet {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .label {
            width: 190px;
            border: 1px dashed #999;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
            page-break-inside: avoid;
        }

        .label .name {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .price {
            font-size: 11px;
            margin-top: 2px;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .label {
                border: none;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <form method="GET">
            <label>{{ __('app.number_of_labels') }}:
                <input type="number" name="qty" value="{{ $qty }}" min="1" max="200"
                    style="width:70px;">
            </label>
            <button type="submit">{{ __('app.update') }}</button>
            <button type="button" onclick="window.print()">🖨️ {{ __('app.print') }}</button>
        </form>
    </div>

    <div class="sheet">
        @for ($i = 0; $i < $qty; $i++)
            <div class="label">
                <div class="name">{{ $product->name }}</div>
                <svg class="barcode" data-code="{{ $code }}"></svg>
                <div class="price">৳ {{ number_format($product->sale_price, 2) }}</div>
            </div>
        @endfor
    </div>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.12.0/JsBarcode.all.min.js"></script> --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.12.3/JsBarcode.all.min.js"
        integrity="sha512-nMnXAGKzA0wZ4YsriudrdnAKSFYXwdCvSt1Auwz6q7XXtSRoRcMVud13Q1LnQnobA2hRhLtHUv9tbVR5pyStPw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // If the CDN is ever unreachable (offline printer PC, blocked network,
        // etc.), JsBarcode never loads and the <svg> would stay empty with no
        // visible error — this fallback prints the raw code as plain text
        // instead, so a label is never silently blank.
        if (typeof JsBarcode === 'undefined') {
            document.querySelectorAll('.barcode').forEach(function(el) {
                var span = document.createElement('div');
                span.style.cssText = 'font-family:monospace; font-size:13px; letter-spacing:1px; padding:6px 0;';
                span.textContent = el.dataset.code;
                el.replaceWith(span);
            });
        } else {
            document.querySelectorAll('.barcode').forEach(function(el) {
                JsBarcode(el, el.dataset.code, {
                    format: 'CODE128',
                    width: 1.4,
                    height: 34,
                    fontSize: 11,
                    margin: 2
                });
            });
        }
    </script>
</body>

</html>
