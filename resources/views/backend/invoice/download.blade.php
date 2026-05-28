<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            background: #f8f8f8;
        }

        .invoice-wrapper {
            background: #fff;
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .page {
            padding: 40px 48px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }

        .header-left h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111;
            margin-bottom: 8px;
        }

        .header-left .contact-info {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .header-right {
            text-align: right;
        }

        .header-right h2 {
            font-size: 28px;
            font-weight: 300;
            color: #ddd;
            text-transform: uppercase;
            line-height: 1;
        }

        .meta-group {
            margin-top: 10px;
        }

        .meta-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-value {
            font-size: 16px;
            font-weight: 600;
            color: #111;
            margin-bottom: 6px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fcd34d;
        }

        .badge-confirmed {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .badge-packed {
            background: #f5f3ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
        }

        .badge-shipped {
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #fdba74;
        }

        .badge-delivered {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #6ee7b7;
        }

        .badge-cancelled {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        /* General Utils */
        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        /* Info Row */
        .info-row {
            display: flex;
            gap: 24px;
            margin-bottom: 40px;
        }

        .info-box {
            flex: 1;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 16px;
        }

        .info-box.title {
            font-size: 10px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }

        .info-box p {
            font-size: 13px;
            color: #444;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .info-box p strong {
            color: #111;
            display: block;
            font-size: 14px;
            margin-bottom: 2px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        thead tr {
            background: #222;
            color: #fff;
        }

        thead th {
            padding: 12px 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #eee;
        }

        tbody td {
            padding: 14px 10px;
            color: #444;
        }

        .product-name {
            font-weight: 600;
            color: #111;
            display: block;
        }

        .product-sku {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 280px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            font-size: 13px;
            color: #555;
        }

        .totals-table .label {
            text-align: left;
            color: #888;
            font-weight: 500;
        }

        .totals-table .value {
            text-align: right;
            font-weight: 600;
            color: #111;
        }

        .grand-total td {
            background: #222;
            color: #fff;
            font-size: 15px;
            padding: 12px;
        }

        .grand-total .label {
            color: #ccc;
        }

        .grand-total .value {
            font-size: 18px;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            text-align: center;
            color: #999;
            font-size: 11px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        @media print {
            body {
                background: #fff;
            }

            .invoice-wrapper {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="page">

            {{-- Header --}}
            <div class="header">
                <div class="header-left">
                    <h1>YOUR STORE</h1>
                    <div class="contact-info">
                        support@yourstore.com<br>
                        +977-9800000000
                    </div>
                </div>

                <div class="header-right">
                    <h2>Invoice</h2>
                    <div class="meta-group">
                        <div class="meta-label">Invoice No</div>
                        <div class="meta-value">#{{ $invoiceNumber }}</div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Date</div>
                        <div class="meta-value" style="font-weight: 400; color: #666;">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                        </div>
                    </div>

                    @php
                        $statusClass = 'badge-pending'; // default
                        $status = strtolower($order->order_status);
                        if (str_contains($status, 'confirm')) {
                            $statusClass = 'badge-confirmed';
                        }
                        if (str_contains($status, 'pack')) {
                            $statusClass = 'badge-packed';
                        }
                        if (str_contains($status, 'ship')) {
                            $statusClass = 'badge-shipped';
                        }
                        if (str_contains($status, 'deliver')) {
                            $statusClass = 'badge-delivered';
                        }
                        if (str_contains($status, 'cancel')) {
                            $statusClass = 'badge-cancelled';
                        }
                    @endphp

                    <span class="badge {{ $statusClass }}">
                        {{ $order->order_status }}
                    </span>
                </div>
            </div>

            {{-- Info Row --}}
            <div class="info-row">
                <div class="info-box">
                    <div class="title">Customer Details</div>
                    <p><strong>{{ $order->name }}</strong></p>
                    <p>Email: {{ $order->email }}</p>
                    <p>Phone: {{ $order->phone }}</p>
                </div>

                <div class="info-box">
                    <div class="title">Shipping Address</div>
                    <p>{{ $order->address_name ?? 'N/A' }}</p>
                </div>

                <div class="info-box">
                    <div class="title">Order Summary</div>
                    <p><strong>Status:</strong> {{ $order->order_status }}</p>
                    <p><strong>Placed On:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                    </p>
                </div>
            </div>

            {{-- Product Table --}}
            <table>
                <thead>
                    <tr>
                        <th class="text-left" style="width: 45%;">Product</th>
                        <th class="text-center" style="width: 15%;">Qty</th>
                        <th class="text-right" style="width: 20%;">Unit Price</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $detail)
                        <tr>
                            <td>
                                <div class="product-name">{{ $detail->title }}</div>
                                <div class="product-sku">Variation: {{ $detail->value }}</div>
                            </td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-right">Rs. {{ number_format($detail->price, 2) }}</td>
                            <td class="text-right">Rs. {{ number_format($detail->order_detail_total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">
                            Rs. {{ number_format(collect($order->items)->sum('order_detail_total_price'), 2) }}
                        </td>
                    </tr>
                    {{-- Add Shipping if applicable --}}
                    {{-- <tr> <td class="label">Shipping</td> <td class="value">Rs. 0.00</td> </tr> --}}
                    <tr class="grand-total">
                        <td class="label">Grand Total</td>
                        <td class="value">
                            Rs. {{ number_format($order->order_master_total_price, 2) }}
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p><strong>Thank you for shopping with us!</strong></p>
                <p>For queries, contact support@yourstore.com</p>
                <p>Ref: {{ strtoupper(substr($order->id, 0, 8)) }} | Generated: {{ now()->format('M d, Y') }}</p>
            </div>

        </div>
    </div>
</body>

</html>
