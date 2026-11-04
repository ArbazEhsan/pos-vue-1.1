<!DOCTYPE html>
<html>
<head>
    <title>Print Label</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    @php
        // Normalize data for legacy support
        $printItems = $items ?? [];
        if (empty($printItems) && isset($product)) {
            $printItems[] = [
                'name' => $product->name,
                'variationName' => '',
                'barcode' => $product->barcode,
                'retail' => $product->retail,
                'labels' => $quantity ?? 1,
            ];
        }

        $settings = $settings ?? [
            'showBusinessName' => false,
            'showProductName' => true,
            'showPrice' => true,
            'businessNameSize' => 10,
            'productNameSize' => 14,
            'priceSize' => 17,
        ];

        // MUST be in mm. If your controller passes inches/px, change it there.
        $dimensions   = $dimensions ?? ['width' => '50mm', 'height' => '25mm'];
        $businessName = $businessName ?? 'Business Name';

        // Physical layout: measure these with a ruler
        $columns = 2;
        $gap     = '3mm';

        // Expand every item into one entry per label, then group into rows of $columns
        $labels = collect($printItems)->flatMap(function ($item) {
            return array_fill(0, max(0, (int) ($item['labels'] ?? 1)), $item);
        });
        $rows = $labels->chunk($columns);
    @endphp

    <style>
        :root {
            --label-w: {{ $dimensions['width'] }};
            --label-h: {{ $dimensions['height'] }};
            --gap: {{ $gap }};
            --cols: {{ $columns }};
        }

        /* One page = one row of labels. No A4 anywhere. */
        @page { size: calc(var(--label-w) * var(--cols) + var(--gap) * (var(--cols) - 1)) var(--label-h); margin: 0; }

        html, body { margin: 0; padding: 0; font-family: Arial, sans-serif; }

        .sheet {
            width: calc(var(--label-w) * var(--cols) + var(--gap) * (var(--cols) - 1));
            /* a hair under the label height prevents blank trailing labels from rounding */
            height: calc(var(--label-h) - 0.3mm);
            display: grid;
            grid-template-columns: repeat(var(--cols), var(--label-w));
            column-gap: var(--gap);
            overflow: hidden;
            page-break-after: always;
            break-after: page;
        }
        .sheet:last-child { page-break-after: auto; break-after: auto; }

        .label {
            width: var(--label-w);
            height: var(--label-h);
            box-sizing: border-box;
            padding: 1mm 1.5mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            break-inside: avoid;
        }

        .business-name {
            text-align: center;
            font-size: 11px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .product-name {
            text-align: left;
            font-size: 14px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .product-name { font-weight: bold; }

        .barcode {
            display: block;
            width: 30mm;      /* widen toward 46mm to fill more of the label */
            height: 9mm;
            margin: 0 auto;
        }

        .bottom-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: end;
            line-height: 1;
        }
        .price { font-size: 14px; text-align: left; }
        .code  { font-size: 15px; text-align: left; }

        /* Screen preview only */
        @media screen {
            body { padding: 10mm; background: #eee; }
            .sheet { background: #fff; margin-bottom: 4mm; outline: 1px dashed #999; }
            .label { outline: 1px dotted #ccc; }
        }
    </style>
</head>
<body>

    @foreach($rows as $row)
        <div class="sheet">
            @foreach($row as $item)
                <div class="label">
                    @if(!empty($settings['showBusinessName']))
                        <div class="business-name">{{ $businessName }}</div>
                    @endif

                    @if(!empty($settings['showProductName']))
                        <div class="product-name">
                            {{ $item['name'] }}@if(!empty($item['variationName']))({{ $item['variationName'] }})@endif
                        </div>
                    @endif

                    <svg class="barcode" data-barcode="{{ $item['barcode'] }}"></svg>

                    <div class="bottom-row">
                        @if(!empty($settings['showPrice']))
                            <span class="price">Rs. {{ number_format($item['retail'], 0) }}.00</span>
                        @endif
                        <span class="code">{{ $item['barcode'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <script>
        document.querySelectorAll('.barcode').forEach(function (el) {
            const code = el.getAttribute('data-barcode');
            if (!code) return;

            JsBarcode(el, code, {
                format: "CODE128",
                width: 2,
                height: 24,
                displayValue: false,
                fontSize: 14,
                fontOptions: "bold",
                margin: 0,
                textMargin: 0
            });

            // Make the SVG scale to the label instead of staying tiny
            const w = el.getAttribute('width'), h = el.getAttribute('height');
            if (!el.getAttribute('viewBox')) el.setAttribute('viewBox', `0 0 ${parseFloat(w)} ${parseFloat(h)}`);
            el.setAttribute('preserveAspectRatio', 'none');
            el.removeAttribute('width');
            el.removeAttribute('height');
        });
    </script>
</body>
</html>