@extends('layouts.main')
@section('title', 'Inventory Report')

@push('styles')
    <style>
        :root {
            --accent: #4361ee;
            --accent-soft: #eef0fd;
            --success: #2ec4b6;
            --warning: #ff9f1c;
            --danger: #e63946;
            --text-main: #1a1a2e;
            --text-muted: #6c757d;
            --border: #e9ecef;
            --card-shadow: 0 2px 12px rgba(67, 97, 238, .08);
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: transform .2s, box-shadow .2s;
            min-height: 90px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(67, 97, 238, .13);
        }

        .stat-card .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card .stat-value {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-card .stat-label {
            font-size: .75rem;
            color: var(--text-muted);
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .chart-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .chart-card .card-title {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .badge-in-stock {
            background: #d1f5f0;
            color: #0a6a5a;
        }

        .badge-low-stock {
            background: #fff3cd;
            color: #856404;
        }

        .badge-out {
            background: #fde8ea;
            color: #a02030;
        }

        tr.row-low td {
            background: #fffbec !important;
        }

        tr.row-out td {
            background: #fff5f5 !important;
        }

        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .sk-card {
            height: 90px;
        }

        .sk-chart {
            height: 260px;
        }

        .sk-table {
            height: 220px;
        }

        #invTable thead th {
            font-size: .75rem;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }

        .alert-item:last-child {
            border-bottom: none !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .chart-card,
            .stat-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Page header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <div>
                <h4 class="fw-bold mb-0" style="color:var(--text-main)">Inventory Report</h4>
                <small class="text-muted">Monitor stock levels, sold quantities and low stock alerts</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm no-print" id="stockFilter" style="width:150px">
                    <option value="all">All Products</option>
                    <option value="in">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
                <button class="btn btn-outline-success btn-sm no-print" id="btnExcelExport">
                    <i class="bx bx-spreadsheet me-1"></i> Excel
                </button>
                <button class="btn btn-outline-danger btn-sm no-print" id="btnPdfExport">
                    <i class="bx bx-file-pdf me-1"></i> PDF
                </button>
            </div>
        </div>

        {{-- Summary cards skeleton --}}
        <div class="row g-3 mb-4" id="summarySection">
            @foreach (range(1, 5) as $sk)
                <div class="col-6 col-md" style="min-width:160px">
                    <div class="card stat-card p-3 skeleton sk-card"></div>
                </div>
            @endforeach
        </div>

        {{-- Stock alerts — separate container, hidden until data loads --}}
        <div class="mb-4" id="alertSection" style="display:none"></div>

        {{-- Charts --}}
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <div class="card chart-card p-3 h-100" id="statusChartSection">
                    <div class="skeleton sk-chart w-100"></div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card chart-card p-3 h-100" id="stockChartSection">
                    <div class="skeleton sk-chart w-100"></div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card chart-card p-3" id="tableSection">
            <div class="skeleton sk-table w-100"></div>
        </div>

    </div>
@endsection

@section('main-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {

            var statusChart, stockChart;
            var allRows = [];

            // ── Load data ──────────────────────────────────────────────
            function loadReport() {
                $.get('{{ route('report.inventory.data') }}')
                    .done(function(res) {
                        if (res.type !== 'success') {
                            showNotification('Failed to load inventory data.', 'error');
                            return;
                        }
                        allRows = res.rows; // set allRows FIRST
                        renderSummary(res.summary);
                        renderAlerts(); // separate function, uses allRows
                        renderStatusChart(res.summary);
                        renderStockChart(res.rows);
                        renderTable(res.rows);
                    })
                    .fail(function() {
                        showNotification('Failed to load report. Please try again.', 'error');
                    });
            }

            // ── Stock status helper ────────────────────────────────────
            function getStatus(row) {
                var qty = parseFloat(row.available_qty);
                var thr = parseFloat(row.threshold);
                if (qty <= 0) return 'out';
                if (qty <= thr) return 'low';
                return 'in';
            }

            // ── Summary cards (cards only, no alerts mixed in) ─────────
            function renderSummary(s) {
                var cards = [{
                        label: 'Total Products',
                        value: s.total,
                        icon: 'bx-box',
                        bg: '#eef0fd',
                        ic: 'text-primary',
                        is: ''
                    },
                    {
                        label: 'In Stock',
                        value: s.in_stock,
                        icon: 'bx-check-circle',
                        bg: '#e8faf8',
                        ic: 'text-success',
                        is: ''
                    },
                    {
                        label: 'Low Stock',
                        value: s.low_stock,
                        icon: 'bx-error',
                        bg: '#fff5e6',
                        ic: '',
                        is: 'color:#ff9f1c'
                    },
                    {
                        label: 'Out of Stock',
                        value: s.out_of_stock,
                        icon: 'bx-x-circle',
                        bg: '#fdecea',
                        ic: '',
                        is: 'color:#e63946'
                    },
                    {
                        label: 'Total Sold',
                        value: s.total_sold,
                        icon: 'bx-trending-up',
                        bg: '#f0ebff',
                        ic: '',
                        is: 'color:#7209b7'
                    },
                ];

                var html = '';
                cards.forEach(function(c) {
                    html += `
                    <div class="col-6 col-md" style="min-width:160px">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box" style="background:${c.bg}">
                                    <i class="bx ${c.icon} ${c.ic}" style="font-size:24px;${c.is}"></i>
                                </div>
                                <div>
                                    <div class="stat-value">${c.value}</div>
                                    <div class="stat-label">${c.label}</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });

                $('#summarySection').html(html);
            }

            // ── Stock alerts panel (rendered into #alertSection) ───────
            function renderAlerts() {
                var lowRows = allRows.filter(r => getStatus(r) === 'low');
                var outRows = allRows.filter(r => getStatus(r) === 'out');

                if (!lowRows.length && !outRows.length) {
                    $('#alertSection').hide().html('');
                    return;
                }

                var allAlerts = [...outRows, ...lowRows];
                var items = '';

                allAlerts.forEach(function(r) {
                    var st = getStatus(r);
                    var isOut = st === 'out';
                    var stock = parseFloat(r.stock);
                    var available = parseFloat(r.available_qty);
                    var sold = parseFloat(r.sold_qty);
                    var threshold = parseFloat(r.threshold);

                    // Progress bar percentages
                    var availPct = stock > 0 ? Math.min((available / stock) * 100, 100).toFixed(1) : 0;
                    var soldPct = stock > 0 ? Math.min((sold / stock) * 100, 100).toFixed(1) : 0;

                    var badgeHtml = isOut ?
                        `<span class="badge badge-out">Out of Stock</span>` :
                        `<span class="badge badge-low-stock">Low Stock</span>`;

                    var barColor = isOut ? '#e63946' : (availPct < 30 ? '#ff9f1c' : '#2ec4b6');
                    var cardBorder = isOut ? '#fde8ea' : '#fff3cd';

                    items += `
        <div class="col-md-4 col-sm-6">
            <div class="p-3 rounded-3 h-100" style="border:1.5px solid ${cardBorder};background:#fff;">

                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="fw-semibold" style="font-size:.88rem;line-height:1.3;max-width:70%">${r.product_name}</div>
                    ${badgeHtml}
                </div>

                {{-- Stats row --}}
                <div class="d-flex gap-3 mb-3">
                    <div class="text-center">
                        <div style="font-size:1.1rem;font-weight:700;color:var(--text-main)">${stock.toLocaleString()}</div>
                        <div style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Total Stock</div>
                    </div>
                    <div style="width:1px;background:var(--border)"></div>
                    <div class="text-center">
                        <div style="font-size:1.1rem;font-weight:700;color:${barColor}">${available.toLocaleString()}</div>
                        <div style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Available</div>
                    </div>
                    <div style="width:1px;background:var(--border)"></div>
                    <div class="text-center">
                        <div style="font-size:1.1rem;font-weight:700;color:#4361ee">${sold.toLocaleString()}</div>
                        <div style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Sold</div>
                    </div>
                    <div style="width:1px;background:var(--border)"></div>
                    <div class="text-center">
                        <div style="font-size:1.1rem;font-weight:700;color:#6c757d">${threshold.toLocaleString()}</div>
                        <div style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Threshold</div>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:4px">
                    Stock level: <strong style="color:${barColor}">${availPct}%</strong> remaining
                </div>
                <div style="height:8px;background:#f0f0f0;border-radius:99px;overflow:hidden">
                    <div style="height:100%;width:${availPct}%;background:${barColor};border-radius:99px;transition:width .6s ease"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.68rem;color:var(--text-muted);margin-top:3px">
                    <span>0</span>
                    <span>Threshold: ${threshold}</span>
                    <span>${stock.toLocaleString()}</span>
                </div>

            </div>
        </div>`;
                });

                $('#alertSection').html(`
        <div class="card chart-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="card-title">
                    <i class="bx bx-error-circle me-1" style="color:#ff9f1c"></i>
                    Stock Alerts
                </span>
                <div>
                    <span class="badge me-1" style="background:#fde8ea;color:#a02030">
                        <i class="bx bx-x-circle me-1"></i>${outRows.length} Out of Stock
                    </span>
                    <span class="badge" style="background:#fff3cd;color:#856404">
                        <i class="bx bx-error me-1"></i>${lowRows.length} Low Stock
                    </span>
                </div>
            </div>
            <div class="row g-3">
                ${items}
            </div>
        </div>
    `).show();
            }

            // ── Status doughnut chart ──────────────────────────────────
            function renderStatusChart(s) {
                $('#statusChartSection').html(`
                    <div class="mb-3"><span class="card-title">Stock Status Overview</span></div>
                    <canvas id="statusChart" height="240"></canvas>
                `);
                if (statusChart) statusChart.destroy();
                statusChart = new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                        datasets: [{
                            data: [s.in_stock, s.low_stock, s.out_of_stock],
                            backgroundColor: ['#2ec4b6', '#ff9f1c', '#e63946'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ── Stock levels horizontal bar ────────────────────────────
            function renderStockChart(rows) {
                var top = rows.slice(0, 10);
                $('#stockChartSection').html(`
                    <div class="mb-3"><span class="card-title">Stock vs Available Qty (Top 10)</span></div>
                    <canvas id="stockChart" height="200"></canvas>
                `);
                if (stockChart) stockChart.destroy();
                stockChart = new Chart(document.getElementById('stockChart'), {
                    type: 'bar',
                    data: {
                        labels: top.map(r => r.product_name.length > 22 ? r.product_name.substring(0, 22) +
                            '…' : r.product_name),
                        datasets: [{
                                label: 'Total Stock',
                                data: top.map(r => r.stock),
                                backgroundColor: 'rgba(67,97,238,.25)',
                                borderColor: '#4361ee',
                                borderWidth: 1.5,
                                borderRadius: 4
                            },
                            {
                                label: 'Available',
                                data: top.map(r => r.available_qty),
                                backgroundColor: 'rgba(46,196,182,.6)',
                                borderColor: '#2ec4b6',
                                borderWidth: 1.5,
                                borderRadius: 4
                            },
                            {
                                label: 'Sold',
                                data: top.map(r => r.sold_qty),
                                backgroundColor: 'rgba(230,57,70,.5)',
                                borderColor: '#e63946',
                                borderWidth: 1.5,
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f0f0f0'
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // ── Table ──────────────────────────────────────────────────
            function renderTable(rows) {
                var tbody = '';
                if (!rows || rows.length === 0) {
                    tbody =
                        '<tr><td colspan="7" class="text-center text-muted py-4">No inventory data found.</td></tr>';
                } else {
                    rows.forEach(function(row, i) {
                        var st = getStatus(row);
                        var badgeClass = st === 'in' ? 'badge-in-stock' : (st === 'low' ?
                            'badge-low-stock' : 'badge-out');
                        var badgeLabel = st === 'in' ? 'In Stock' : (st === 'low' ? 'Low Stock' :
                            'Out of Stock');
                        var rowClass = st === 'low' ? 'row-low' : (st === 'out' ? 'row-out' : '');
                        tbody += `
                        <tr class="${rowClass}" data-status="${st}">
                            <td>${i + 1}</td>
                            <td class="fw-semibold">${row.product_name}</td>
                            <td>${parseFloat(row.stock).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                            <td>${parseFloat(row.available_qty).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                            <td>${row.sold_qty}</td>
                            <td>${row.threshold}</td>
                            <td><span class="badge ${badgeClass}">${badgeLabel}</span></td>
                        </tr>`;
                    });
                }

                $('#tableSection').html(`
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="card-title">Product Inventory Details</span>
                        <div>
                            <span class="badge badge-in-stock me-1">In Stock</span>
                            <span class="badge badge-low-stock me-1">Low Stock</span>
                            <span class="badge badge-out">Out of Stock</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover w-100" id="invTable">
                            <thead class="table-light">
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
                            <tbody>${tbody}</tbody>
                        </table>
                    </div>
                `);

                if (rows && rows.length) {
                    $('#invTable').DataTable({
                        dom: '<"d-flex align-items-center justify-content-between mb-2"lf>tip',
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, -1],
                            [10, 25, 50, 'All']
                        ],
                        order: [
                            [3, 'asc']
                        ],
                        columnDefs: [{
                            orderable: false,
                            targets: [0, 6]
                        }],
                        destroy: true
                    });
                }
            }

            // ── Stock status filter dropdown ───────────────────────────
            $('#stockFilter').on('change', function() {
                var val = $(this).val();
                if (!allRows.length) return;
                var filtered = val === 'all' ? allRows : allRows.filter(r => getStatus(r) === val);
                renderTable(filtered);
                renderStockChart(filtered);
            });

            // ── Export Excel ───────────────────────────────────────────
            $('#btnExcelExport').on('click', function() {
                window.location.href = '{{ route('report.inventory.export.excel') }}';
            });

            // ── Export PDF ─────────────────────────────────────────────
            $('#btnPdfExport').on('click', function() {
                window.open('{{ route('report.inventory.export.pdf') }}', '_blank');
            });

            // ── Auto load ──────────────────────────────────────────────
            loadReport();

        });
    </script>
@endsection
