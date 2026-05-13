@extends('layouts.main')
@section('title', 'Sales Report')

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

        .filter-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
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

        .filter-btn-group .btn {
            border-radius: 8px !important;
            font-size: .82rem;
            font-weight: 500;
            padding: 6px 16px;
        }

        .filter-btn-group .btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        #salesTableWrapper thead th {
            font-size: .75rem;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }

        #salesTableWrapper tbody tr:hover {
            background: var(--accent-soft);
        }

        /* Skeleton loader */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0
            }

            100% {
                background-position: -200% 0
            }
        }

        .skeleton-card {
            height: 90px;
        }

        .skeleton-chart {
            height: 260px;
        }

        .skeleton-table {
            height: 200px;
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
                <h4 class="fw-bold mb-0" style="color:var(--text-main)">Sales Report</h4>
                <small class="text-muted">Track revenue, orders and product performance</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success btn-sm no-print" id="btnExcelExport">
                    <i class="bx bx-spreadsheet me-1"></i> Excel
                </button>
                <button class="btn btn-outline-danger btn-sm no-print" id="btnPdfExport">
                    <i class="bx bx-file-pdf me-1"></i> PDF
                </button>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card filter-card p-3 mb-4 no-print">
            <div class="row g-3 align-items-end">
                {{-- Filter type buttons --}}
                <div class="col-auto">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Filter by</label>
                    <div class="btn-group filter-btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm active" data-filter="day">Day
                            Range</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-filter="month">Month</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-filter="year">Year</button>
                    </div>
                </div>

                {{-- Day range --}}
                <div class="col-auto" id="wrap_day">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem">From</label>
                    <input type="date" class="form-control form-control-sm" id="from_date"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-auto" id="wrap_day_to">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem">To</label>
                    <input type="date" class="form-control form-control-sm" id="to_date"
                        value="{{ now()->format('Y-m-d') }}">
                </div>

                {{-- Month picker --}}
                <div class="col-auto" id="wrap_month" style="display:none">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Month</label>
                    <input type="month" class="form-control form-control-sm" id="month_val"
                        value="{{ now()->format('Y-m') }}">
                </div>

                {{-- Year picker --}}
                <div class="col-auto" id="wrap_year" style="display:none">
                    <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Year</label>
                    <select class="form-select form-select-sm" id="year_val">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-auto">
                    <button type="button" class="btn btn-primary btn-sm px-4" id="btnApply">
                        <i class="bx bx-search me-1"></i> Apply
                    </button>
                </div>
            </div>
        </div>

        {{-- Summary cards (skeleton → real) --}}
        <div class="row g-3 mb-4" id="summarySection">
            @foreach (['Revenue', 'Orders', 'Units Sold', 'Avg Order'] as $sk)
                <div class="col-6 col-md-3">
                    <div class="card stat-card p-3 skeleton skeleton-card"></div>
                </div>
            @endforeach
        </div>

        {{-- Charts (skeleton → real) --}}
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card chart-card p-3 h-100" id="trendSection">
                    <div class="skeleton skeleton-chart w-100"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card chart-card p-3 h-100" id="paymentSection">
                    <div class="skeleton skeleton-chart w-100"></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card chart-card p-3" id="productSection">
                    <div class="skeleton skeleton-chart w-100"></div>
                </div>
            </div>
        </div>

        {{-- Table (skeleton → real) --}}
        {{-- <div class="card chart-card p-3" id="tableSection">
            <div class="skeleton skeleton-table w-100"></div>
        </div> --}}

    </div>
@endsection

@section('main-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {

            // ── State ──────────────────────────────────────────────────
            var activeFilter = 'day';
            var trendChart, paymentChart, productChart;
            var currentParams = {};

            var palette = ['#4361ee', '#2ec4b6', '#ff9f1c', '#e63946', '#7209b7', '#3a0ca3'];

            // ── Filter type toggle ─────────────────────────────────────
            $('[data-filter]').on('click', function() {
                activeFilter = $(this).data('filter');
                $('[data-filter]').removeClass('active');
                $(this).addClass('active');
                $('#wrap_day, #wrap_day_to').toggle(activeFilter === 'day');
                $('#wrap_month').toggle(activeFilter === 'month');
                $('#wrap_year').toggle(activeFilter === 'year');
            });

            // ── Build query params ─────────────────────────────────────
            function getParams() {
                var p = {
                    filter: activeFilter
                };
                if (activeFilter === 'day') {
                    p.from_date = $('#from_date').val();
                    p.to_date = $('#to_date').val();
                } else if (activeFilter === 'month') {
                    p.month = $('#month_val').val();
                } else {
                    p.year = $('#year_val').val();
                }
                return p;
            }

            // ── Load data via AJAX ─────────────────────────────────────
            function loadReport() {
                currentParams = getParams();

                $.get('{{ route('report.sales.data') }}', currentParams)
                    .done(function(res) {
                        if (res.type !== 'success') {
                            showNotification('Failed to load report data.', 'error');
                            return;
                        }
                        renderSummary(res.summary);
                        renderTrendChart(res.trend);
                        renderPaymentChart(res.payment_breakdown);
                        renderProductChart(res.top_products);
                        // renderTable(res.rows, res.summary);
                    })
                    .fail(function() {
                        showNotification('Failed to load report. Please try again.', 'error');
                    });
            }

            // ── Summary cards ──────────────────────────────────────────
            function renderSummary(s) {
                var cards = [{
                        label: 'Total Revenue',
                        value: parseFloat(s.total_sales).toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        }),
                        icon: 'bx-dollar-circle',
                        color: '#eef0fd',
                        iconColor: 'text-primary'
                    },
                    {
                        label: 'Total Orders',
                        value: parseInt(s.total_orders).toLocaleString(),
                        icon: 'bx-cart',
                        color: '#e8faf8',
                        iconColor: 'text-success'
                    },
                    {
                        label: 'Units Sold',
                        value: parseInt(s.total_qty).toLocaleString(),
                        icon: 'bx-package',
                        color: '#fff5e6',
                        iconColor: '',
                        iconStyle: 'color:#ff9f1c'
                    },
                    {
                        label: 'Avg Order Value',
                        value: parseFloat(s.avg_order_value).toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        }),
                        icon: 'bx-trending-up',
                        color: '#fdecea',
                        iconColor: '',
                        iconStyle: 'color:#e63946'
                    },
                ];

                var html = '';
                cards.forEach(function(c) {
                    html += `
            <div class="col-6 col-md-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-box" style="background:${c.color}">
                            <i class="bx ${c.icon} ${c.iconColor}" style="font-size:24px;${c.iconStyle||''}"></i>
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

            // ── Trend chart ────────────────────────────────────────────
            function renderTrendChart(trend) {
                $('#trendSection').html(`
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="card-title">Revenue Trend</span>
            </div>
            <canvas id="trendChart" height="110"></canvas>
        `);

                if (trendChart) trendChart.destroy();
                trendChart = new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: trend.map(d => d.label),
                        datasets: [{
                            label: 'Revenue',
                            data: trend.map(d => d.revenue),
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67,97,238,.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#4361ee',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Orders',
                            data: trend.map(d => d.orders),
                            borderColor: '#2ec4b6',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointBackgroundColor: '#2ec4b6',
                            pointRadius: 3,
                            tension: 0.4,
                            yAxisID: 'y2'
                        }]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f0f0f0'
                                },
                                ticks: {
                                    callback: v => v.toLocaleString()
                                }
                            },
                            y2: {
                                beginAtZero: true,
                                position: 'right',
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // ── Payment doughnut ───────────────────────────────────────
            function renderPaymentChart(breakdown) {
                $('#paymentSection').html(`
            <div class="mb-3"><span class="card-title">Payment Methods</span></div>
            <canvas id="paymentChart" height="220"></canvas>
        `);

                if (paymentChart) paymentChart.destroy();
                paymentChart = new Chart(document.getElementById('paymentChart'), {
                    type: 'doughnut',
                    data: {
                        labels: breakdown.map(d => d.method),
                        datasets: [{
                            data: breakdown.map(d => d.revenue),
                            backgroundColor: palette,
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
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' ' + ctx.label + ': ' + parseFloat(ctx.raw)
                                        .toLocaleString()
                                }
                            }
                        }
                    }
                });
            }

            // ── Top products bar ───────────────────────────────────────
            function renderProductChart(products) {
                // Dynamic height: 60px per product minimum
                var chartHeight = Math.max(products.length * 60, 200);

                $('#productSection').html(`
        <div class="mb-3"><span class="card-title">Top 5 Products by Revenue</span></div>
        <div style="position:relative; height:${chartHeight}px;">
            <canvas id="productChart"></canvas>
        </div>
    `);

                if (productChart) productChart.destroy();
                productChart = new Chart(document.getElementById('productChart'), {
                    type: 'bar',
                    data: {
                        labels: products.map(d => {
                            // Wrap long names into multiple lines (array = multiline in Chart.js)
                            var name = d.product || '';
                            if (name.length <= 25) return name;
                            // Split at space nearest to middle
                            var mid = Math.floor(name.length / 2);
                            var split = name.lastIndexOf(' ', mid);
                            if (split === -1) split = 25;
                            return [name.substring(0, split), name.substring(split + 1)];
                        }),
                        datasets: [{
                            label: 'Revenue',
                            data: products.map(d => d.revenue),
                            backgroundColor: palette,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 28, // fixed bar thickness so bars don't collapse
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false, // ← lets the wrapper div control height
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => '  Revenue: ' + parseFloat(ctx.raw).toLocaleString()
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f0f0f0'
                                },
                                ticks: {
                                    callback: v => v.toLocaleString(),
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    },
                                    crossAlign: 'far', // aligns label text to the right edge
                                }
                            }
                        },
                        layout: {
                            padding: {
                                left: 10,
                                right: 20,
                                top: 10,
                                bottom: 10
                            }
                        }
                    }
                });
            }

            // // ── Orders table ───────────────────────────────────────────
            // function renderTable(rows, summary) {
            //     var tbody = '';
            //     if (!rows || rows.length === 0) {
            //         tbody = '<tr><td colspan="9" class="text-center text-muted py-4">No data available for selected period.</td></tr>';
            //     } else {
            //         rows.forEach(function (row, i) {
            //             var statusMap = { 'Y': 'success', 'N': 'danger', 'completed': 'success', 'pending': 'warning', 'cancelled': 'danger' };
            //             var statusColor = statusMap[(row.status||'').toLowerCase()] || 'secondary';
            //             var date = row.order_date ? new Date(row.order_date).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : 'N/A';
            //             tbody += `<tr>
        //                 <td>${i+1}</td>
        //                 <td><span class="fw-semibold">#${row.order_id ? row.order_id.substring(0,8).toUpperCase() : 'N/A'}</span></td>
        //                 <td>${row.product_name || 'N/A'}</td>
        //                 <td>${row.quantity || 0}</td>
        //                 <td>${parseFloat(row.price||0).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
        //                 <td class="fw-semibold">${parseFloat(row.total_price||0).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
        //                 <td><span class="badge bg-label-info">${row.payment_method || 'N/A'}</span></td>
        //                 <td>${date}</td>
        //                 <td><span class="badge bg-label-${statusColor}">${row.status || 'N/A'}</span></td>
        //             </tr>`;
            //         });
            //     }

            //     var tfoot = rows && rows.length ? `
        //         <tfoot>
        //             <tr class="table-light fw-bold">
        //                 <td colspan="3">Total</td>
        //                 <td>${parseInt(summary.total_qty).toLocaleString()}</td>
        //                 <td>—</td>
        //                 <td>${parseFloat(summary.total_sales).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
        //                 <td colspan="3"></td>
        //             </tr>
        //         </tfoot>` : '';

            //     $('#tableSection').html(`
        //         <div class="d-flex align-items-center justify-content-between mb-3">
        //             <span class="card-title">Order Details</span>
        //         </div>
        //         <div class="table-responsive" id="salesTableWrapper">
        //             <table class="table table-hover w-100" id="salesTable">
        //                 <thead class="table-light">
        //                     <tr>
        //                         <th>#</th><th>Order ID</th><th>Product</th><th>Qty</th>
        //                         <th>Unit Price</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th>
        //                     </tr>
        //                 </thead>
        //                 <tbody>${tbody}</tbody>
        //                 ${tfoot}
        //             </table>
        //         </div>
        //     `);

            //     // Init DataTable only if rows exist
            //     if (rows && rows.length) {
            //         $('#salesTable').DataTable({
            //             dom: '<"d-flex align-items-center justify-content-between mb-2"lf>tip',
            //             pageLength: 10,
            //             lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
            //             order: [[7,'desc']],
            //             columnDefs: [{ orderable: false, targets: [0] }],
            //             destroy: true
            //         });
            //     }
            // }

            // ── Apply button ───────────────────────────────────────────
            $('#btnApply').on('click', loadReport);

            // ── Export Excel → controller ──────────────────────────────
            $('#btnExcelExport').on('click', function() {
                var url = '{{ route('report.sales.export.excel') }}?' + $.param(currentParams);
                window.location.href = url;
            });

            // ── Export PDF → controller (opens print page) ─────────────
            $('#btnPdfExport').on('click', function() {
                var url = '{{ route('report.sales.export.pdf') }}?' + $.param(currentParams);
                window.open(url, '_blank');
            });

            // ── Auto-load on page open ─────────────────────────────────
            loadReport();

        });
    </script>
@endsection
