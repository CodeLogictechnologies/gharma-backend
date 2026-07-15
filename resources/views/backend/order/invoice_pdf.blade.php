<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $voucherDetail->voucher_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .invoice-header h2 {
            margin: 0 0 4px 0;
            font-size: 20px;
        }

        .invoice-header .sub {
            font-size: 11px;
            color: #666;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .meta-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .meta-label {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }

        .meta-value {
            font-weight: bold;
            font-size: 13px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.items th,
        table.items td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        table.items th {
            background: #f5f5f5;
            text-transform: uppercase;
            font-size: 10px;
        }

        table.items td.num,
        table.items th.num {
            text-align: right;
        }

        table.totals {
            width: 100%;
            margin-top: 0;
            border-collapse: collapse;
        }

        table.totals td {
            padding: 5px 8px;
            font-size: 11px;
        }

        table.totals td.label {
            text-align: right;
            border: none;
        }

        table.totals td.value {
            text-align: right;
            width: 140px;
            border: none;
        }

        table.totals tr.grand td {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 13px;
            padding-top: 8px;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="invoice-header">
        <h2>Invoice</h2>
        <div class="sub">Voucher No: {{ $voucherDetail->voucher_number }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="33%">
                <div class="meta-label">Date</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($voucherDetail->created_at)->format('Y-m-d') }}</div>
            </td>
            <td width="33%">
                <div class="meta-label">Bill / Voucher No.</div>
                <div class="meta-value">{{ $voucherDetail->voucher_number }}</div>
            </td>
            <td width="34%">
                <div class="meta-label">Customer</div>
                <div class="meta-value">{{ $voucherDetail->name }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Variation</th>
                <th class="num">Qty</th>
                <th class="num">Rate</th>
                <th class="num">Amount</th>
                <th class="num">Excise</th>
                <th class="num">VAT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderDetail as $i => $line)
                @php
                    $amount = $line->price * $line->quantity;
                    $exciseLabel =
                        $line->excise_type === 'percentage'
                            ? number_format($line->excise_percent, 2) . '%'
                            : number_format($line->excise_amount, 2);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->title }}</td>
                    <td>{{ $line->value ?? '-' }}</td>
                    <td class="num">{{ $line->quantity }}</td>
                    <td class="num">{{ number_format($line->price, 2) }}</td>
                    <td class="num">{{ number_format($amount, 2) }}</td>
                    <td class="num">{{ $exciseLabel }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($line->vat_percent, 2), '0'), '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $beforeDiscountTotal = 0;
        $discountTotal = 0;
        $extraDiscountTotal = 0;
        $afterDiscountTotal = 0;
        $exciseTotal = 0;
        $vatTotal = 0;
        $grandTotal = 0;

        foreach ($orderDetail as $line) {
            $qty = (float) $line->quantity;
            $price = (float) $line->price;

            // Price before discount
            $beforeDiscount = $price * $qty;

            // Main Discount
            $discount = 0;

            if ($line->discount_type == 'percentage') {
                $discount = ($beforeDiscount * (float) ($line->discount_amount ?? 0)) / 100;
            } elseif ($line->discount_type == 'fixed') {
                $discount = (float) ($line->discount_amount ?? 0) * $qty;
            }

            // Extra Discount
            $extraDiscount = (float) ($line->extra_discount ?? 0);

            // Price after all discounts
            $afterDiscount = $beforeDiscount - $discount - $extraDiscount;

            // Stored values
            $excise = (float) $line->excise_amount;
            $vat = (float) $line->vat_amount;

            // Final Total
            $lineTotal = (float) $line->order_detail_total_price;

            // Sum
            $beforeDiscountTotal += $beforeDiscount;
            $discountTotal += $discount;
            $extraDiscountTotal += $extraDiscount;
            $afterDiscountTotal += $afterDiscount;
            $exciseTotal += $excise;
            $vatTotal += $vat;
            $grandTotal += $lineTotal;
        }
    @endphp

    <table class="totals">
        <tr>
            <td class="label" colspan="7">Before Discount</td>
            <td class="value">{{ number_format($beforeDiscountTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label" colspan="7">Discount</td>
            <td class="value">- {{ number_format($discountTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label" colspan="7">Extra Discount</td>
            <td class="value">- {{ number_format($extraDiscountTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label" colspan="7">After Discount</td>
            <td class="value">{{ number_format($afterDiscountTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label" colspan="7">Excise</td>
            <td class="value">{{ number_format($exciseTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label" colspan="7">VAT</td>
            <td class="value">{{ number_format($vatTotal, 2) }}</td>
        </tr>
        <tr class="grand">
            <td class="label" colspan="7">Grand Total</td>
            <td class="value">{{ number_format($grandTotal, 2) }}</td>
        </tr>
    </table>

    <div class="footer-note">
        This is a computer-generated invoice.
    </div>

</body>

</html>