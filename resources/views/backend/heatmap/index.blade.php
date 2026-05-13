{{--
    resources/views/backend/heatmap/index.blade.php
--}}

@extends('layouts.main')
@section('title', 'Order Heatmap')
@section('content')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .hm-wrap { font-family: 'Plus Jakarta Sans', sans-serif; }

        .hm-page-header {
            background: linear-gradient(135deg, #0a2342 0%, #1a5276 100%);
            border-radius: 16px; padding: 24px 28px;
            margin-bottom: 20px; color: #fff;
            position: relative; overflow: hidden;
        }
        .hm-page-header::after {
            content: ''; position: absolute;
            right: -30px; top: -40px;
            width: 220px; height: 220px;
            border-radius: 50%; background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        .hm-page-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 4px; letter-spacing: -0.3px; }
        .hm-page-header p  { font-size: 13px; opacity: 0.7; margin: 0; }

        .hm-stat-grid {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 12px; margin-bottom: 20px;
        }
        @media(max-width:768px){ .hm-stat-grid { grid-template-columns: repeat(2,1fr); } }

        .hm-stat-card {
            background: #fff; border: 1px solid #e8edf2;
            border-radius: 12px; padding: 16px 18px;
            position: relative; overflow: hidden;
            transition: transform .15s, box-shadow .15s;
        }
        .hm-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.07); }
        .hm-stat-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            border-radius: 12px 12px 0 0;
        }
        .hm-stat-card.teal::before  { background: #1D9E75; }
        .hm-stat-card.blue::before  { background: #185FA5; }
        .hm-stat-card.amber::before { background: #BA7517; }
        .hm-stat-card.coral::before { background: #D85A30; }

        .hm-stat-label { font-size: 11px; font-weight: 600; color: #8896a5; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 6px; }
        .hm-stat-value { font-size: 26px; font-weight: 700; color: #0d1f2d; line-height: 1; transition: opacity .3s; }
        .hm-stat-sub   { font-size: 12px; color: #8896a5; margin-top: 4px; }

        .hm-filters {
            background: #fff; border: 1px solid #e8edf2;
            border-radius: 12px; padding: 14px 18px;
            margin-bottom: 16px; display: flex;
            gap: 12px; flex-wrap: wrap; align-items: flex-end;
        }
        .hm-filter-group { display: flex; flex-direction: column; gap: 4px; }
        .hm-filter-group label { font-size: 11px; font-weight: 600; color: #8896a5; text-transform: uppercase; letter-spacing: .4px; }
        .hm-filter-group select,
        .hm-filter-group input[type=date] {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px; padding: 7px 12px;
            border: 1px solid #dde3ea; border-radius: 8px;
            color: #0d1f2d; background: #f7f9fc;
            outline: none; cursor: pointer; min-width: 140px;
            transition: border-color .15s, box-shadow .15s;
        }
        .hm-filter-group select:focus,
        .hm-filter-group input:focus {
            border-color: #1D9E75; background: #fff;
            box-shadow: 0 0 0 3px rgba(29,158,117,.1);
        }
        .hm-apply-btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px; font-weight: 600;
            padding: 8px 20px; background: #1D9E75;
            color: #fff; border: none; border-radius: 8px;
            cursor: pointer; transition: background .15s, transform .1s;
            align-self: flex-end;
        }
        .hm-apply-btn:hover  { background: #0F6E56; }
        .hm-apply-btn:active { transform: scale(.97); }

        .hm-map-card {
            background: #fff; border: 1px solid #e8edf2;
            border-radius: 16px; overflow: hidden; margin-bottom: 20px;
        }
        .hm-map-toolbar {
            padding: 14px 18px; border-bottom: 1px solid #f0f4f8;
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 10px;
        }
        .hm-toolbar-title { font-size: 14px; font-weight: 700; color: #0d1f2d; }
        .hm-toolbar-sub   { font-size: 12px; color: #8896a5; margin-top: 1px; }

        .hm-mode-btns { display: flex; gap: 4px; }
        .hm-mode-btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px; font-weight: 600;
            padding: 6px 16px; border-radius: 8px;
            border: 1px solid #dde3ea; background: #f7f9fc;
            color: #8896a5; cursor: pointer; transition: all .15s;
        }
        .hm-mode-btn.active             { background: #0a2342; color: #fff; border-color: #0a2342; }
        .hm-mode-btn:hover:not(.active) { background: #eef2f7; color: #0d1f2d; }

        #nepal-map { height: 500px; width: 100%; }

        .hm-legend {
            padding: 10px 18px; border-top: 1px solid #f0f4f8;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .hm-legend-bar {
            width: 150px; height: 8px; border-radius: 4px;
            background: linear-gradient(to right, #ffffd4, #41b6c4, #225ea8, #0c2c84);
        }
        .hm-legend-label { font-size: 11px; color: #8896a5; font-weight: 500; }
        .hm-legend-dot   { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: #5a6a7a; font-weight: 500; }
        .hm-legend-dot span { width: 10px; height: 10px; border-radius: 50%; display: inline-block; border: 1.5px solid rgba(0,0,0,.1); }

        .hm-bottom-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 16px; margin-bottom: 20px;
        }
        @media(max-width:768px){ .hm-bottom-grid { grid-template-columns: 1fr; } }

        .hm-panel        { background: #fff; border: 1px solid #e8edf2; border-radius: 12px; overflow: hidden; }
        .hm-panel-header { padding: 14px 18px; border-bottom: 1px solid #f0f4f8; font-size: 13px; font-weight: 700; color: #0d1f2d; }
        .hm-panel-body   { padding: 14px 18px; }

        .hm-loc-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .hm-loc-table th {
            text-align: left; padding: 0 8px 8px 0;
            font-size: 10px; font-weight: 700; color: #8896a5;
            text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 1px solid #f0f4f8;
        }
        .hm-loc-table td { padding: 8px 8px 8px 0; border-bottom: 1px solid #f7f9fc; color: #2c3e50; vertical-align: middle; }
        .hm-loc-table tr:last-child td { border-bottom: none; }
        .hm-loc-bar-wrap { background: #f0f4f8; border-radius: 4px; height: 6px; min-width: 60px; }
        .hm-loc-bar      { background: #1D9E75; border-radius: 4px; height: 6px; transition: width .4s ease; }

        .hm-badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 20px; letter-spacing: .2px; }
        .hm-badge.Pending   { background: #FEF3E2; color: #BA7517; }
        .hm-badge.Confirmed { background: #E1F5EE; color: #0F6E56; }
        .hm-badge.Packed    { background: #E6F1FB; color: #185FA5; }
        .hm-badge.Shipped   { background: #EAF3DE; color: #3B6D11; }
        .hm-badge.Delivered { background: #E1F5EE; color: #085041; }
        .hm-badge.Cancelled { background: #FCEBEB; color: #A32D2D; }
        .hm-badge.Returned  { background: #FBEAF0; color: #993556; }
        .hm-badge.Refunded  { background: #F1EFE8; color: #444441; }

        .hm-type-chip        { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 6px; text-transform: capitalize; }
        .hm-type-chip.home   { background: #E6F1FB; color: #185FA5; }
        .hm-type-chip.work   { background: #EAF3DE; color: #3B6D11; }
        .hm-type-chip.campus { background: #EEEDFE; color: #534AB7; }
        .hm-type-chip.other  { background: #F1EFE8; color: #5F5E5A; }

        .hm-status-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid #f7f9fc; font-size: 12px; }
        .hm-status-row:last-child { border-bottom: none; }
        .hm-status-count { margin-left: auto; font-weight: 700; color: #0d1f2d; min-width: 30px; text-align: right; }

        #hm-loading {
            display: none; position: absolute; inset: 0;
            background: rgba(255,255,255,.75); z-index: 9999;
            align-items: center; justify-content: center; border-radius: 16px;
        }
        #hm-loading.show { display: flex; }
        .hm-spinner {
            width: 36px; height: 36px;
            border: 3px solid #e8edf2; border-top-color: #1D9E75;
            border-radius: 50%; animation: hm-spin .7s linear infinite;
        }
        @keyframes hm-spin { to { transform: rotate(360deg); } }

        @keyframes hm-shimmer { 0%,100%{ opacity:1 } 50%{ opacity:.4 } }
        .hm-updating { animation: hm-shimmer .6s ease-in-out infinite; }
    </style>

    <div class="hm-wrap px-3 px-md-4 py-4">

        {{-- Page Header --}}
        <div class="hm-page-header">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h1>🗺️ Nepal Order Heatmap</h1>
                    <p>Visualise order density &amp; revenue across customer delivery locations</p>
                </div>
                <div class="ms-auto text-end">
                    <div style="font-size:11px;opacity:.6;">Data range</div>
                    <div id="header-date-range" style="font-size:13px;font-weight:600;">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                        – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="hm-stat-grid">
            <div class="hm-stat-card teal">
                <div class="hm-stat-label">Total Orders</div>
                <div class="hm-stat-value" id="stat-orders">{{ number_format($totalOrders) }}</div>
                <div class="hm-stat-sub">with known delivery location</div>
            </div>
            <div class="hm-stat-card blue">
                <div class="hm-stat-label">Total Revenue</div>
                <div class="hm-stat-value" id="stat-revenue">NPR {{ number_format($totalRevenue / 1000, 1) }}K</div>
                <div class="hm-stat-sub">from mapped orders</div>
            </div>
            <div class="hm-stat-card amber">
                <div class="hm-stat-label">Top Location</div>
                <div class="hm-stat-value" id="stat-top" style="font-size:16px;padding-top:4px;">
                    {{ Str::limit($topLocality, 20) }}
                </div>
                <div class="hm-stat-sub">highest order area</div>
            </div>
            <div class="hm-stat-card coral">
                <div class="hm-stat-label">Avg Order Value</div>
                <div class="hm-stat-value" id="stat-avg">NPR {{ number_format($avgOrderValue) }}</div>
                <div class="hm-stat-sub">per order</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="hm-filters">
            <div class="hm-filter-group">
                <label>Order Status</label>
                <select id="f-status">
                    <option value="all" {{ $orderStatus === 'all' ? 'selected' : '' }}>All Statuses</option>
                    @foreach ($orderStatuses as $st)
                        <option value="{{ $st }}" {{ $orderStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="hm-filter-group">
                <label>Date From</label>
                <input type="date" id="f-from" value="{{ $dateFrom }}">
            </div>
            <div class="hm-filter-group">
                <label>Date To</label>
                <input type="date" id="f-to" value="{{ $dateTo }}">
            </div>
            <button class="hm-apply-btn" onclick="applyFilters()">Apply Filters</button>
            <button class="hm-apply-btn" style="background:#8896a5;" onclick="resetFilters()">Reset</button>
        </div>

        {{-- Map Card --}}
        <div class="hm-map-card" style="position:relative;">
            <div id="hm-loading"><div class="hm-spinner"></div></div>
            <div class="hm-map-toolbar">
                <div>
                    <div class="hm-toolbar-title">Delivery Location Map</div>
                    <div class="hm-toolbar-sub" id="toolbar-subtitle">
                        {{ number_format($totalOrders) }} orders plotted
                        @if ($orderStatus !== 'all')· filtered by <strong>{{ $orderStatus }}</strong>@endif
                    </div>
                </div>
                <div class="hm-mode-btns">
                    <button class="hm-mode-btn active" onclick="setMode('heat',this)">Heatmap</button>
                    <button class="hm-mode-btn" onclick="setMode('dots',this)">Markers</button>
                    <button class="hm-mode-btn" onclick="setMode('cluster',this)">Clusters</button>
                </div>
            </div>
            <div id="nepal-map"></div>
            <div class="hm-legend">
                <div id="legend-heat" style="display:flex;align-items:center;gap:8px;">
                    <span class="hm-legend-label">Low</span>
                    <div class="hm-legend-bar"></div>
                    <span class="hm-legend-label">High density</span>
                </div>
                <div id="legend-dots" style="display:none;align-items:center;gap:12px;flex-wrap:wrap;">
                    <span class="hm-legend-dot"><span style="background:#0c2c84"></span>Very high</span>
                    <span class="hm-legend-dot"><span style="background:#225ea8"></span>High</span>
                    <span class="hm-legend-dot"><span style="background:#41b6c4"></span>Medium</span>
                    <span class="hm-legend-dot"><span style="background:#a1dab4"></span>Low</span>
                </div>
                <span class="ms-auto hm-legend-label">Click any marker for details &nbsp;·&nbsp; Scroll to zoom</span>
            </div>
        </div>

        {{-- Bottom Panels --}}
        <div class="hm-bottom-grid">

            <div class="hm-panel">
                <div class="hm-panel-header">📍 Top Delivery Localities</div>
                <div style="padding:0;">
                    <table class="hm-loc-table">
                        <thead>
                            <tr>
                                <th style="padding-left:18px;">#</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th style="padding-right:18px;">Share</th>
                            </tr>
                        </thead>
                        <tbody id="locality-tbody">
                            @php $maxLoc = $localityStats->max('order_count') ?: 1; @endphp
                            @forelse ($localityStats->take(10) as $i => $loc)
                                <tr>
                                    <td style="padding-left:18px;color:#8896a5;font-weight:600;">{{ $i + 1 }}</td>
                                    <td>
                                        <div style="font-weight:600;color:#0d1f2d;font-size:12px;">{{ Str::limit($loc->locality, 22) }}</div>
                                        <div style="font-size:10px;color:#8896a5;">{{ number_format($loc->lat, 4) }}, {{ number_format($loc->lng, 4) }}</div>
                                    </td>
                                    <td><span class="hm-type-chip {{ $loc->address_type }}">{{ $loc->address_type }}</span></td>
                                    <td style="font-weight:700;">{{ number_format($loc->order_count) }}</td>
                                    <td style="color:#1D9E75;font-weight:600;">NPR {{ number_format($loc->total_revenue / 1000, 1) }}K</td>
                                    <td style="padding-right:18px;min-width:70px;">
                                        <div class="hm-loc-bar-wrap">
                                            <div class="hm-loc-bar" style="width:{{ round(($loc->order_count / $maxLoc) * 100) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:20px 18px;color:#8896a5;text-align:center;">
                                        No location data — ensure addresses have latitude &amp; longitude.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:16px;">

                <div class="hm-panel">
                    <div class="hm-panel-header">📦 Orders by Status</div>
                    <div class="hm-panel-body" id="status-panel-body">
                        @php
                            $statusColors = [
                                'Pending'   => '#BA7517', 'Confirmed' => '#0F6E56',
                                'Packed'    => '#185FA5', 'Shipped'   => '#3B6D11',
                                'Delivered' => '#085041', 'Cancelled' => '#A32D2D',
                                'Returned'  => '#993556', 'Refunded'  => '#444441',
                            ];
                        @endphp
                        @foreach ($orderStatuses as $st)
                            @php $cnt = $statusBreakdown[$st]->count ?? 0; @endphp
                            @if ($cnt > 0)
                                <div class="hm-status-row">
                                    <span class="hm-badge {{ $st }}">{{ $st }}</span>
                                    <div style="flex:1;margin:0 10px;">
                                        <div style="background:#f0f4f8;border-radius:4px;height:5px;">
                                            <div style="background:{{ $statusColors[$st] ?? '#888' }};border-radius:4px;height:5px;
                                                        width:{{ $totalOrders > 0 ? round(($cnt/$totalOrders)*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    <span class="hm-status-count">{{ number_format($cnt) }}</span>
                                </div>
                            @endif
                        @endforeach
                        @if ($statusBreakdown->isEmpty())
                            <p style="font-size:12px;color:#8896a5;margin:0;">No order data found.</p>
                        @endif
                    </div>
                </div>

                <div class="hm-panel">
                    <div class="hm-panel-header">🏠 Delivery Address Types</div>
                    <div class="hm-panel-body" id="type-panel-body">
                        @php $maxType = $typeBreakdown->max('count') ?: 1; @endphp
                        @forelse ($typeBreakdown as $type)
                            <div class="hm-status-row">
                                <span class="hm-type-chip {{ $type->type }}">{{ $type->type }}</span>
                                <div style="flex:1;margin:0 10px;">
                                    <div style="background:#f0f4f8;border-radius:4px;height:5px;">
                                        <div style="background:#1D9E75;border-radius:4px;height:5px;
                                                    width:{{ round(($type->count/$maxType)*100) }}%;"></div>
                                    </div>
                                </div>
                                <span class="hm-status-count">{{ number_format($type->count) }}</span>
                            </div>
                        @empty
                            <p style="font-size:12px;color:#8896a5;margin:0;">No address type data.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection

@section('main-scripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>

    {{--
        ╔══════════════════════════════════════════════════════════════╗
        ║  SCRIPT 1 — Global filter functions                         ║
        ║                                                             ║
        ║  Must be plain `function` declarations (NOT arrow funcs,    ║
        ║  NOT assigned to var/let/const, NOT inside an IIFE) so      ║
        ║  they are hoisted to window scope and available to          ║
        ║  onclick="" attributes the instant the browser parses them. ║
        ╚══════════════════════════════════════════════════════════════╝
    --}}
    <script>
        var HM_DATA_URL     = '{{ route("admin.heatmap.data") }}';
        var HM_DEFAULT_FROM = '{{ now()->subYear()->toDateString() }}';
        var HM_DEFAULT_TO   = '{{ now()->toDateString() }}';

        var HM_STATUS_COLORS = {
            Pending:'#BA7517', Confirmed:'#0F6E56', Packed:'#185FA5',
            Shipped:'#3B6D11', Delivered:'#085041', Cancelled:'#A32D2D',
            Returned:'#993556', Refunded:'#444441'
        };

        // Delegated to map engine once it initialises (Script 2)
        function setMode(mode, btn) {
            if (window.HM) HM.setMode(mode, btn);
        }

        function applyFilters() {
            var status = document.getElementById('f-status').value;
            var from   = document.getElementById('f-from').value;
            var to     = document.getElementById('f-to').value;

            if (!from || !to) { alert('Please select both dates.'); return; }
            if (from > to)    { alert('Date From cannot be after Date To.'); return; }

            var params = new URLSearchParams({ order_status:status, date_from:from, date_to:to });

            document.getElementById('hm-loading').classList.add('show');
            ['stat-orders','stat-revenue','stat-top','stat-avg'].forEach(function(id) {
                document.getElementById(id).classList.add('hm-updating');
            });

            fetch(HM_DATA_URL + '?' + params.toString())
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {

                    // ── Stats ──────────────────────────────────────────
                    document.getElementById('stat-orders').textContent =
                        Number(data.total_orders).toLocaleString();
                    document.getElementById('stat-revenue').textContent =
                        'NPR ' + (Number(data.total_revenue) / 1000).toFixed(1) + 'K';
                    var top = data.top_locality || 'N/A';
                    document.getElementById('stat-top').textContent =
                        top.length > 20 ? top.slice(0, 20) + '…' : top;
                    document.getElementById('stat-avg').textContent =
                        'NPR ' + Number(data.avg_order_value).toLocaleString();

                    // ── Toolbar subtitle ───────────────────────────────
                    document.getElementById('toolbar-subtitle').innerHTML =
                        Number(data.total_orders).toLocaleString() + ' orders plotted' +
                        (status !== 'all' ? ' · filtered by <strong>' + status + '</strong>' : '');

                    // ── Header date range ──────────────────────────────
                    function fmtDate(d) {
                        return new Date(d + 'T00:00:00')
                            .toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                    }
                    document.getElementById('header-date-range').textContent =
                        fmtDate(from) + ' – ' + fmtDate(to);

                    // ── Map ────────────────────────────────────────────
                    if (window.HM) HM.loadPoints(data.points);

                    // ── Locality table ─────────────────────────────────
                    var tbody = document.getElementById('locality-tbody');
                    var stats = data.locality_stats || [];
                    if (!stats.length) {
                        tbody.innerHTML = '<tr><td colspan="6" style="padding:20px 18px;color:#8896a5;text-align:center;">'
                            + 'No location data — ensure addresses have latitude &amp; longitude.</td></tr>';
                    } else {
                        var maxOrd = Math.max.apply(null, stats.map(function(s){ return s.order_count; }).concat([1]));
                        tbody.innerHTML = stats.map(function(loc, i) {
                            var name = loc.locality || '';
                            var disp = name.length > 22 ? name.slice(0, 22) + '…' : name;
                            var lat  = parseFloat(loc.lat  || 0).toFixed(4);
                            var lng  = parseFloat(loc.lng  || 0).toFixed(4);
                            var rev  = (Number(loc.total_revenue) / 1000).toFixed(1);
                            var pct  = Math.round((loc.order_count / maxOrd) * 100);
                            return '<tr>'
                                + '<td style="padding-left:18px;color:#8896a5;font-weight:600;">' + (i+1) + '</td>'
                                + '<td><div style="font-weight:600;color:#0d1f2d;font-size:12px;">' + disp + '</div>'
                                + '<div style="font-size:10px;color:#8896a5;">' + lat + ', ' + lng + '</div></td>'
                                + '<td><span class="hm-type-chip ' + loc.address_type + '">' + loc.address_type + '</span></td>'
                                + '<td style="font-weight:700;">' + Number(loc.order_count).toLocaleString() + '</td>'
                                + '<td style="color:#1D9E75;font-weight:600;">NPR ' + rev + 'K</td>'
                                + '<td style="padding-right:18px;min-width:70px;">'
                                + '<div class="hm-loc-bar-wrap"><div class="hm-loc-bar" style="width:' + pct + '%"></div></div>'
                                + '</td></tr>';
                        }).join('');
                    }

                    // ── Status panel ───────────────────────────────────
                    var sPanel    = document.getElementById('status-panel-body');
                    var breakdown = (data.status_breakdown || []).filter(function(s){ return s.count > 0; });
                    if (!breakdown.length) {
                        sPanel.innerHTML = '<p style="font-size:12px;color:#8896a5;margin:0;">No order data found.</p>';
                    } else {
                        sPanel.innerHTML = breakdown.map(function(s) {
                            var pct   = data.total_orders > 0 ? Math.round((s.count / data.total_orders) * 100) : 0;
                            var color = HM_STATUS_COLORS[s.order_status] || '#888';
                            return '<div class="hm-status-row">'
                                + '<span class="hm-badge ' + s.order_status + '">' + s.order_status + '</span>'
                                + '<div style="flex:1;margin:0 10px;">'
                                + '<div style="background:#f0f4f8;border-radius:4px;height:5px;">'
                                + '<div style="background:' + color + ';border-radius:4px;height:5px;width:' + pct + '%;"></div>'
                                + '</div></div>'
                                + '<span class="hm-status-count">' + Number(s.count).toLocaleString() + '</span>'
                                + '</div>';
                        }).join('');
                    }

                    // ── Type panel ─────────────────────────────────────
                    var tPanel = document.getElementById('type-panel-body');
                    var types  = data.type_breakdown || [];
                    if (!types.length) {
                        tPanel.innerHTML = '<p style="font-size:12px;color:#8896a5;margin:0;">No address type data.</p>';
                    } else {
                        var maxT = Math.max.apply(null, types.map(function(t){ return t.count; }).concat([1]));
                        tPanel.innerHTML = types.map(function(t) {
                            var pct = Math.round((t.count / maxT) * 100);
                            return '<div class="hm-status-row">'
                                + '<span class="hm-type-chip ' + t.type + '">' + t.type + '</span>'
                                + '<div style="flex:1;margin:0 10px;">'
                                + '<div style="background:#f0f4f8;border-radius:4px;height:5px;">'
                                + '<div style="background:#1D9E75;border-radius:4px;height:5px;width:' + pct + '%;"></div>'
                                + '</div></div>'
                                + '<span class="hm-status-count">' + Number(t.count).toLocaleString() + '</span>'
                                + '</div>';
                        }).join('');
                    }
                })
                .catch(function(err) {
                    console.error('Heatmap AJAX error:', err);
                    alert('Failed to load data. Please try again.');
                })
                .finally(function() {
                    document.getElementById('hm-loading').classList.remove('show');
                    ['stat-orders','stat-revenue','stat-top','stat-avg'].forEach(function(id) {
                        document.getElementById(id).classList.remove('hm-updating');
                    });
                });
        }

        function resetFilters() {
            document.getElementById('f-status').value = 'all';
            document.getElementById('f-from').value   = HM_DEFAULT_FROM;
            document.getElementById('f-to').value     = HM_DEFAULT_TO;
            applyFilters();
        }

        // Wire up dropdown auto-apply (safe here — DOM is ready, function is hoisted)
        document.getElementById('f-status').addEventListener('change', applyFilters);
    </script>

    {{--
        ╔══════════════════════════════════════════════════════════════╗
        ║  SCRIPT 2 — Map engine                                      ║
        ║                                                             ║
        ║  Runs after Leaflet CDN scripts are loaded.                 ║
        ║  Exposes window.HM so Script 1 functions can call into it.  ║
        ╚══════════════════════════════════════════════════════════════╝
    --}}
    <script>
        window.HM = (function () {

            var RAW_POINTS = @json($orderPoints);
            var activeMode = 'heat';
            var heatLayer    = null;
            var dotLayer     = null;
            var clusterLayer = null;

            // ── Map init ───────────────────────────────────────────────
            var map = L.map('nepal-map', { center:[28.1,84.1], zoom:7 });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://carto.com">CARTO</a>',
                subdomains: 'abcd', maxZoom: 19
            }).addTo(map);

            L.rectangle([[26.3,80.1],[30.5,88.2]], {
                color:'#1D9E75', weight:1.5, fillOpacity:0, dashArray:'6 4'
            }).addTo(map);

            // ── Helpers ────────────────────────────────────────────────
            function intensityColor(r) {
                if (r > 0.75) return '#0c2c84';
                if (r > 0.50) return '#225ea8';
                if (r > 0.25) return '#41b6c4';
                return '#a1dab4';
            }
            function addrTypeColor(t) {
                return { home:'#185FA5', work:'#3B6D11', campus:'#534AB7', other:'#5F5E5A' }[t] || '#1D9E75';
            }
            function popupHtml(p) {
                return '<div style="font-family:\'Plus Jakarta Sans\',sans-serif;min-width:200px;">'
                    + '<div style="font-weight:700;font-size:13px;color:#0d1f2d;margin-bottom:6px;'
                    + 'border-bottom:1px solid #f0f4f8;padding-bottom:6px;">📍 ' + p.address_name + '</div>'
                    + '<table style="font-size:12px;width:100%;border-collapse:collapse;">'
                    + '<tr><td style="color:#8896a5;padding:3px 0;">Type</td>'
                    + '<td style="text-align:right;font-weight:600;text-transform:capitalize;">' + p.address_type + '</td></tr>'
                    + '<tr><td style="color:#8896a5;padding:3px 0;">Orders</td>'
                    + '<td style="text-align:right;font-weight:700;color:#1D9E75;">' + Number(p.order_count).toLocaleString() + '</td></tr>'
                    + '<tr><td style="color:#8896a5;padding:3px 0;">Revenue</td>'
                    + '<td style="text-align:right;font-weight:700;color:#185FA5;">NPR ' + Number(p.total_revenue).toLocaleString() + '</td></tr>'
                    + '<tr><td style="color:#8896a5;padding:3px 0;">Status</td>'
                    + '<td style="text-align:right;font-weight:600;">' + p.order_status + '</td></tr>'
                    + '<tr><td style="color:#8896a5;font-size:10px;">Coords</td>'
                    + '<td style="text-align:right;font-size:10px;color:#8896a5;">'
                    + parseFloat(p.latitude).toFixed(6) + ', ' + parseFloat(p.longitude).toFixed(6)
                    + '</td></tr></table></div>';
            }

            // ── Layer management ───────────────────────────────────────
            function clearLayers() {
                if (heatLayer)    { map.removeLayer(heatLayer);    heatLayer    = null; }
                if (dotLayer)     { map.removeLayer(dotLayer);     dotLayer     = null; }
                if (clusterLayer) { map.removeLayer(clusterLayer); clusterLayer = null; }
            }
            function renderHeat() {
                var mx  = Math.max.apply(null, RAW_POINTS.map(function(p){ return p.order_count; }).concat([1]));
                var pts = RAW_POINTS.map(function(p) {
                    return [parseFloat(p.latitude), parseFloat(p.longitude), p.order_count / mx];
                });
                heatLayer = L.heatLayer(pts, {
                    radius:35, blur:22, maxZoom:13,
                    gradient:{ 0.0:'#ffffd4', 0.3:'#a1dab4', 0.5:'#41b6c4', 0.7:'#225ea8', 1.0:'#0c2c84' }
                }).addTo(map);
            }
            function renderDots() {
                var mx = Math.max.apply(null, RAW_POINTS.map(function(p){ return p.order_count; }).concat([1]));
                dotLayer = L.layerGroup();
                RAW_POINTS.forEach(function(p) {
                    var ratio  = p.order_count / mx;
                    var radius = Math.max(7, Math.min(26, 5 + p.order_count * 0.2));
                    L.circleMarker([parseFloat(p.latitude), parseFloat(p.longitude)], {
                        radius:radius, fillColor:intensityColor(ratio),
                        color:'#fff', weight:1.5, fillOpacity:0.82
                    }).bindPopup(popupHtml(p), { maxWidth:260 }).addTo(dotLayer);
                });
                dotLayer.addTo(map);
            }
            function renderCluster() {
                clusterLayer = L.markerClusterGroup({
                    maxClusterRadius: 60,
                    iconCreateFunction: function(cluster) {
                        var count = cluster.getChildCount();
                        var size  = count > 100 ? 48 : count > 20 ? 38 : 30;
                        var bg    = count > 100 ? '#0c2c84' : count > 20 ? '#225ea8' : '#41b6c4';
                        return L.divIcon({
                            html: '<div style="width:'+size+'px;height:'+size+'px;background:'+bg+';color:#fff;'
                                + 'border-radius:50%;display:flex;align-items:center;justify-content:center;'
                                + 'font-size:'+(size>40?13:11)+'px;font-weight:700;'
                                + 'border:2px solid rgba(255,255,255,.6);box-shadow:0 2px 8px rgba(0,0,0,.2);">'+count+'</div>',
                            className:'', iconSize:[size,size], iconAnchor:[size/2,size/2]
                        });
                    }
                });
                RAW_POINTS.forEach(function(p) {
                    var color = addrTypeColor(p.address_type);
                    var icon  = L.divIcon({
                        html: '<div style="width:12px;height:12px;background:'+color+';'
                            + 'border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.25);"></div>',
                        className:'', iconSize:[12,12], iconAnchor:[6,6]
                    });
                    L.marker([parseFloat(p.latitude), parseFloat(p.longitude)], { icon:icon })
                     .bindPopup(popupHtml(p), { maxWidth:260 })
                     .addTo(clusterLayer);
                });
                clusterLayer.addTo(map);
            }
            function render() {
                clearLayers();
                if (activeMode === 'heat')         renderHeat();
                else if (activeMode === 'dots')    renderDots();
                else if (activeMode === 'cluster') renderCluster();
                document.getElementById('legend-heat').style.display = activeMode === 'heat' ? 'flex' : 'none';
                document.getElementById('legend-dots').style.display = activeMode === 'dots' ? 'flex' : 'none';
            }
            function fitMap() {
                if (!RAW_POINTS.length) return;
                var lats = RAW_POINTS.map(function(p){ return parseFloat(p.latitude); });
                var lngs = RAW_POINTS.map(function(p){ return parseFloat(p.longitude); });
                map.fitBounds([
                    [Math.min.apply(null,lats)-0.3, Math.min.apply(null,lngs)-0.3],
                    [Math.max.apply(null,lats)+0.3, Math.max.apply(null,lngs)+0.3]
                ]);
            }

            fitMap();
            render();

            // ── Public API (used by Script 1) ──────────────────────────
            return {
                setMode: function(mode, btn) {
                    activeMode = mode;
                    document.querySelectorAll('.hm-mode-btn').forEach(function(b){
                        b.classList.remove('active');
                    });
                    btn.classList.add('active');
                    render();
                },
                loadPoints: function(points) {
                    RAW_POINTS = points;
                    render();
                    fitMap();
                }
            };

        }());
    </script>

@endsection