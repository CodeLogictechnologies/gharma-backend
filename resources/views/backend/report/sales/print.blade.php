<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a2e; padding: 30px; }
        h2 { font-size: 20px; margin-bottom: 4px; }
        .sub { color: #6c757d; font-size: 11px; margin-bottom: 20px; }

        .summary { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat-box { flex: 1; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px 16px; }
        .stat-box .val { font-size: 18px; font-weight: 700; }
        .stat-box .lbl { font-size: 10px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f8f9fa; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; color: #6c757d; border-bottom: 2px solid #dee2e6; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tfoot td { padding: 8px 10px; font-weight: 700; background: #f0f4ff; border-top: 2px solid #dee2e6; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .badge-success { background: #d1f5f0; color: #0a6a5a; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger  { background: #fde8ea; color: #a02030; }
        .badge-secondary { background: #e9ecef; color: #495057; }

        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #aaa; }

        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <h2>Sales Report</h2>
    <div class="sub">
        Generated: {{ now()->format('d M Y, h:i A') }}
        @if(($post['filter']??'month') === 'day')
            &nbsp;|&nbsp; Period: {{ $post['from_date'] ?? '' }} to {{ $post['to_date'] ?? '' }}
        @elseif(($post['filter']??'month') === 'month')
            &nbsp;|&nbsp; Month: {{ $post['month'] ?? '' }}
        @else
            &nbsp;|&nbsp; Year: {{ $post['year'] ?? '' }}
        @endif
    </div>

    {{-- Summary --}}
    @php $s = $result['summary']; @endphp
    <div class="summary">
        <div class="stat-box">
            <div class="val">{{ number_format($s['total_sales'], 2) }}</div>
            <div class="lbl">Total Revenue</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ number_format($s['total_orders']) }}</div>
            <div class="lbl">Total Orders</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ number_format($s['total_qty']) }}</div>
            <div class="lbl">Units Sold</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ number_format($s['avg_order_value'], 2) }}</div>
            <div class="lbl">Avg Order Value</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($result['rows'] as $i => $row)
            @php
                $statusColor = match(strtolower($row->status ?? '')) {
                    'y', 'completed' => 'success',
                    'pending'        => 'warning',
                    'n', 'cancelled' => 'danger',
                    default          => 'secondary'
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>#{{ strtoupper(substr($row->order_id, 0, 8)) }}</td>
                <td>{{ $row->product_name }}</td>
                <td>{{ $row->quantity }}</td>
                <td>{{ number_format($row->price, 2) }}</td>
                <td>{{ number_format($row->total_price, 2) }}</td>
                <td>{{ $row->payment_method ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d M Y') }}</td>
                <td><span class="badge badge-{{ $statusColor }}">{{ $row->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;padding:20px;color:#aaa">No data available.</td></tr>
            @endforelse
        </tbody>
        @if($result['rows']->count())
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td>{{ number_format($s['total_qty']) }}</td>
                <td>—</td>
                <td>{{ number_format($s['total_sales'], 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">This report was generated automatically. &copy; {{ now()->year }}</div>

    <script>window.onload = function () { window.print(); }</script>
</body>
</html>