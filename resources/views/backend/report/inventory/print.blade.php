
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a2e; padding: 30px; }
        h2  { font-size: 20px; margin-bottom: 4px; }
        .sub { color: #6c757d; font-size: 11px; margin-bottom: 20px; }

        .summary { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 110px; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 14px; }
        .stat-box .val { font-size: 18px; font-weight: 700; }
        .stat-box .lbl { font-size: 10px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f8f9fa; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; color: #6c757d; border-bottom: 2px solid #dee2e6; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tr.row-low  td { background: #fffbec !important; }
        tr.row-out  td { background: #fff5f5 !important; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .badge-in  { background: #d1f5f0; color: #0a6a5a; }
        .badge-low { background: #fff3cd; color: #856404; }
        .badge-out { background: #fde8ea; color: #a02030; }

        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #aaa; }

        @media print {
            body { padding: 10px; }
        }
    </style>
</head>
<body>

    <h2>Inventory Report</h2>
    <div class="sub">Generated: {{ now()->format('d M Y, h:i A') }}</div>

    {{-- Summary --}}
    <div class="summary">
        <div class="stat-box">
            <div class="val">{{ $summary['total'] }}</div>
            <div class="lbl">Total Products</div>
        </div>
        <div class="stat-box" style="border-color:#2ec4b6">
            <div class="val" style="color:#0a6a5a">{{ $summary['in_stock'] }}</div>
            <div class="lbl">In Stock</div>
        </div>
        <div class="stat-box" style="border-color:#ff9f1c">
            <div class="val" style="color:#856404">{{ $summary['low_stock'] }}</div>
            <div class="lbl">Low Stock</div>
        </div>
        <div class="stat-box" style="border-color:#e63946">
            <div class="val" style="color:#a02030">{{ $summary['out_of_stock'] }}</div>
            <div class="lbl">Out of Stock</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $summary['total_sold'] }}</div>
            <div class="lbl">Total Sold</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Total Stock</th>
                <th>Available Qty</th>
                <th>Sold Qty</th>
                <th>Threshold</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            @php
                $qty = floatval($row->available_qty);
                $thr = floatval($row->threshold);
                if ($qty <= 0)       { $st = 'out'; $label = 'Out of Stock'; $rowClass = 'row-out'; }
                elseif ($qty <= $thr){ $st = 'low'; $label = 'Low Stock';    $rowClass = 'row-low'; }
                else                 { $st = 'in';  $label = 'In Stock';     $rowClass = ''; }
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $row->product_name }}</strong></td>
                <td>{{ number_format($row->stock, 2) }}</td>
                <td>{{ number_format($row->available_qty, 2) }}</td>
                <td>{{ $row->sold_qty }}</td>
                <td>{{ $row->threshold }}</td>
                <td><span class="badge badge-{{ $st }}">{{ $label }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:20px;color:#aaa">No data available.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">This report was generated automatically. &copy; {{ now()->year }}</div>

    <script>window.onload = function () { window.print(); }</script>
</body>
</html>