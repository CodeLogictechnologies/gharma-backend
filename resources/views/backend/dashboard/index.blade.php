@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- Welcome Card -->
            <div class="col-lg-8 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Dashboard Overview</h5>
                                <p class="mb-4">
                                    Welcome to your dashboard! Here's a summary of your sales and inventory for
                                    <strong>{{ date('F Y') }}</strong>.
                                </p>
                                <div class="d-flex gap-4">
                                    <div>
                                        <small class="text-muted">Total Orders</small>
                                        <h4 class="mb-0">{{ $getSalesReport ? $getSalesReport->count() : 0 }}</h4>
                                    </div>
                                    {{-- <div>
                                        <small class="text-muted">Total Products</small>
                                        <h4 class="mb-0">{{ $getInventory ? $getInventory->count() : 0 }}</h4>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="../assets/img/illustrations/man-with-laptop-light.png" height="140"
                                    alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="col-lg-4 col-md-4 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success"
                                            class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">Total Sales</span>
                                <h3 class="card-title mb-2">
                                    NPR {{ number_format($getSalesReport ? $getSalesReport->sum('total_price') : 0) }}
                                </h3>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-up-arrow-alt"></i>
                                    {{ $getSalesReport ? $getSalesReport->count() : 0 }} orders
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="Credit Card"
                                            class="rounded" />
                                    </div>
                                </div>
                                <span>Inventory Items</span>
                                <h3 class="card-title text-nowrap mb-1">
                                    {{ $getInventory ? $getInventory->count() : 0 }}
                                </h3>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-up-arrow-alt"></i>
                                    {{ $getInventory ? $getInventory->sum('stock') : 0 }} total stock
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="col-12 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Sales Overview ({{ date('F Y') }})</h5>
                        <div class="btn-group" role="group">
                            <button type="button" id="btn-month" class="btn btn-sm btn-primary"
                                onclick="updateChart('month')">Month</button>
                            {{-- <button type="button" id="btn-week" class="btn btn-sm btn-outline-primary"
                                onclick="updateChart('week')">Week</button> --}}
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
                <div class="row">
                    <div class="col-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/paypal.png" alt="Credit Card"
                                            class="rounded" />
                                    </div>
                                </div>
                                <span class="d-block mb-1">Total Products</span>
                                <h3 class="card-title text-nowrap mb-2">
                                    {{ $getInventory ? $getInventory->groupBy('product_name')->count() : 0 }}
                                </h3>
                                <small class="text-success fw-semibold">Unique items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/cc-primary.png" alt="Credit Card"
                                            class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">Low Stock Items</span>
                                <h3 class="card-title mb-2">
                                    {{ $getInventory
                                        ? $getInventory->filter(function ($item) {
                                                return isset($item->available_qty) && isset($item->threshold) && $item->available_qty < $item->threshold;
                                            })->count()
                                        : 0 }}
                                </h3>
                                <small class="text-danger fw-semibold">Below threshold</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                    <div
                                        class="d-flex flex-sm-column flex-row align-items-start justify-content-between w-100">
                                        <div class="card-title">
                                            <h5 class="text-nowrap mb-2">Available Stock</h5>
                                            <span class="badge bg-label-primary rounded-pill">Current Inventory</span>
                                        </div>
                                        <div class="mt-sm-auto">
                                            <small class="text-success text-nowrap fw-semibold">
                                                <i class="bx bx-chevron-up"></i>
                                                {{ $getInventory ? $getInventory->sum('sold_qty') : 0 }} units sold
                                            </small>
                                            <h3 class="mb-0">
                                                {{ number_format($getInventory ? $getInventory->sum('available_qty') : 0) }}
                                                <small class="fs-6 text-muted">units available</small>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Inventory Status Chart -->
            {{-- <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Inventory Status</h5>
                            <small class="text-muted">{{ $getInventory ? $getInventory->count() : 0 }} items tracked</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="inventoryChart" height="200"></canvas>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span>Total Stock: <strong>{{ $getInventory ? $getInventory->sum('stock') : 0 }}</strong></span>
                                <span>Available: <strong class="text-success">{{ $getInventory ? $getInventory->sum('available_qty') : 0 }}</strong></span>
                                <span>Low Stock: <strong class="text-danger">{{ $getInventory ? $getInventory->filter(function ($item) {
                                    return isset($item->available_qty) && isset($item->threshold) && $item->available_qty < $item->threshold;
                                })->count() : 0 }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Top Selling Products Chart -->
            {{-- <div class="col-md-6 col-lg-4 order-1 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 me-2">Top Selling Products</h5>
                        <small class="text-muted">By quantity sold</small>
                    </div>
                    <div class="card-body">
                        <canvas id="topProductsChart" height="200"></canvas>
                    </div>
                </div>
            </div> --}}

            <!-- Recent Sales -->
            <div class="col-md-6 col-lg-4 order-2 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Recent Sales</h5>
                        <span class="text-muted small">{{ $getSalesReport ? $getSalesReport->count() : 0 }} total</span>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <ul class="p-0 m-0" style="list-style: none;">
                            @if ($getSalesReport && $getSalesReport->count() > 0)
                                @foreach ($getSalesReport->sortByDesc('order_date')->take(6) as $sale)
                                    <li class="d-flex mb-3 pb-1 border-bottom">
                                        <div
                                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <small
                                                    class="text-muted d-block mb-1">{{ isset($sale->order_date) ? \Carbon\Carbon::parse($sale->order_date)->format('d M Y') : 'N/A' }}</small>
                                                <h6 class="mb-0">
                                                    {{ isset($sale->product_name) ? Str::limit($sale->product_name, 20) : 'Unknown Product' }}
                                                </h6>
                                                <small class="text-muted">Qty: {{ $sale->quantity ?? 0 }}</small>
                                            </div>
                                            <div class="user-progress d-flex align-items-center gap-1">
                                                <h6 class="mb-0 text-success">NPR
                                                    {{ number_format($sale->total_price ?? 0, 2) }}</h6>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-center text-muted py-4">No recent sales</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get data from PHP
            const salesData = @json($getSalesReport ? $getSalesReport->values() : []);
            const inventoryData = @json($getInventory ? $getInventory->values() : []);

            // ── Helpers ──────────────────────────────────────────────
            function buildSalesByDate(data) {
                const byDate = {};
                data.forEach(function(item) {
                    if (item.order_date) {
                        const date = new Date(item.order_date);
                        const dateStr = date.toLocaleDateString('en-US', {
                            day: 'numeric',
                            month: 'short'
                        });
                        byDate[dateStr] = (byDate[dateStr] || 0) + parseFloat(item.total_price || 0);
                    }
                });
                return byDate;
            }

            function last7DaysFilter(data) {
                const cutoff = new Date();
                cutoff.setDate(cutoff.getDate() - 7);
                return data.filter(function(item) {
                    return item.order_date && new Date(item.order_date) >= cutoff;
                });
            }

            // ── Sales Chart ──────────────────────────────────────────────
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            let currentSalesData = salesData;
            let salesByDate = buildSalesByDate(currentSalesData);
            let salesLabels = Object.keys(salesByDate);
            let salesValues = Object.values(salesByDate);

            if (salesLabels.length === 0) {
                salesLabels = ['No Data'];
                salesValues = [0];
            }

            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: salesLabels,
                    datasets: [{
                        label: 'Daily Sales (NPR)',
                        data: salesValues,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4CAF50',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'NPR ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'NPR ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // ── Inventory Chart (Doughnut) ──────────────────────────────
            const invCtx = document.getElementById('inventoryChart').getContext('2d');

            let inStockCount = 0;
            let lowStockCount = 0;
            let outStockCount = 0;

            inventoryData.forEach(function(item) {
                const available = parseFloat(item.available_qty || 0);
                const threshold = parseFloat(item.threshold || 0);

                if (available <= 0) {
                    outStockCount++;
                } else if (available < threshold) {
                    lowStockCount++;
                } else {
                    inStockCount++;
                }
            });

            if (inStockCount === 0 && lowStockCount === 0 && outStockCount === 0) {
                inStockCount = 1;
            }

            new Chart(invCtx, {
                type: 'doughnut',
                data: {
                    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        data: [inStockCount, lowStockCount, outStockCount],
                        backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });

            // ── Top Products Chart ──────────────────────────────────────
            const prodCtx = document.getElementById('topProductsChart').getContext('2d');

            const productGroups = {};
            salesData.forEach(function(item) {
                const name = item.product_name || 'Unknown';
                const qty = parseFloat(item.quantity || 0);
                productGroups[name] = (productGroups[name] || 0) + qty;
            });

            const sortedProducts = Object.entries(productGroups)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            let productLabels = sortedProducts.map(function(p) {
                return p[0].length > 15 ? p[0].substring(0, 15) + '...' : p[0];
            });
            let productData = sortedProducts.map(function(p) {
                return p[1];
            });

            if (productLabels.length === 0) {
                productLabels = ['No Data'];
                productData = [0];
            }

            new Chart(prodCtx, {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Quantity Sold',
                        data: productData,
                        backgroundColor: [
                            'rgba(76, 175, 80, 0.8)',
                            'rgba(33, 150, 243, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(255, 87, 34, 0.8)',
                            'rgba(156, 39, 176, 0.8)'
                        ],
                        borderColor: ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // ── Period Toggle (Month / Week) ────────────────────────────
            window.updateChart = function(period) {
                document.getElementById('btn-month').classList.toggle('btn-primary', period === 'month');
                document.getElementById('btn-month').classList.toggle('btn-outline-primary', period !==
                'month');
                document.getElementById('btn-week').classList.toggle('btn-primary', period === 'week');
                document.getElementById('btn-week').classList.toggle('btn-outline-primary', period !== 'week');

                const filtered = period === 'week' ? last7DaysFilter(salesData) : salesData;
                const grouped = buildSalesByDate(filtered);
                let labels = Object.keys(grouped);
                let values = Object.values(grouped);

                if (labels.length === 0) {
                    labels = ['No Data'];
                    values = [0];
                }

                salesChart.data.labels = labels;
                salesChart.data.datasets[0].data = values;
                salesChart.update();
            };
        });
    </script>

    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-title {
            font-weight: 600;
            color: #1a1a2e;
        }

        .avatar {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 50%;
        }

        .avatar img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        .badge.bg-label-primary {
            background: #e8eaf6;
            color: #3f51b5;
        }

        .badge.bg-label-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge.bg-label-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .badge.bg-label-danger {
            background: #fce4ec;
            color: #c62828;
        }
    </style>

@endsection
