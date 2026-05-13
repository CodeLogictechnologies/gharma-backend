<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HeatmapController extends Controller
{
    /**
     * Shared base query builder.
     */
    private function baseQuery(string $orderStatus, string $dateFrom, string $dateTo)
    {
        $query = DB::table('order_details as od')
            ->join('order_masters as om', 'od.ordermasterid', '=', 'om.id')
            ->join('user_addresses as ua', 'om.addressid', '=', 'ua.id')
            ->where('od.status', 'Y')
            ->where('om.status', 'Y')
            ->whereNull('od.deleted_at')
            ->whereNull('om.deleted_at')
            ->whereNull('ua.deleted_at')
            ->whereNotNull('ua.latitude')
            ->whereNotNull('ua.longitude')
            ->whereBetween('om.created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo   . ' 23:59:59',
            ]);

        if ($orderStatus !== 'all') {
            $query->where('om.order_status', $orderStatus);
        }

        return $query;
    }

    /**
     * Collect all data needed by both the page-load view and the AJAX endpoint.
     */
    private function collectData(string $orderStatus, string $dateFrom, string $dateTo): array
    {
        $base = $this->baseQuery($orderStatus, $dateFrom, $dateTo);

        // ── Point-level data for heatmap & markers ──────────────────
        $orderPoints = (clone $base)
            ->select([
                'ua.id as address_id',
                'ua.address_name',
                'ua.latitude',
                'ua.longitude',
                'ua.type as address_type',
                'om.order_status',
                DB::raw('COUNT(od.id) as order_count'),
                DB::raw('SUM(od.order_detail_total_price) as total_revenue'),
                DB::raw('AVG(od.order_detail_total_price) as avg_order_value'),
            ])
            ->groupBy(
                'ua.id',
                'ua.address_name',
                'ua.latitude',
                'ua.longitude',
                'ua.type',
                'om.order_status'
            )
            ->get();

        // ── Locality-level summary ───────────────────────────────────
        $localityStats = (clone $base)
            ->select([
                'ua.address_name as locality',
                'ua.type as address_type',
                DB::raw('COUNT(od.id) as order_count'),
                DB::raw('SUM(od.order_detail_total_price) as total_revenue'),
                DB::raw('AVG(ua.latitude) as lat'),
                DB::raw('AVG(ua.longitude) as lng'),
            ])
            ->groupBy('ua.address_name', 'ua.type')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        // ── Order status breakdown ───────────────────────────────────
        $statusBreakdown = (clone $base)
            ->select([
                'om.order_status',
                DB::raw('COUNT(od.id) as count'),
            ])
            ->groupBy('om.order_status')
            ->get()
            ->keyBy('order_status');

        // ── Address-type breakdown ───────────────────────────────────
        $typeBreakdown = (clone $base)
            ->select([
                'ua.type',
                DB::raw('COUNT(od.id) as count'),
            ])
            ->groupBy('ua.type')
            ->get();

        // ── Summary stats ────────────────────────────────────────────
        $totalOrders   = $orderPoints->sum('order_count');
        $totalRevenue  = $orderPoints->sum('total_revenue');
        $topLocality   = $localityStats->first()?->locality ?? 'N/A';
        $avgOrderValue = $totalOrders > 0
            ? round($totalRevenue / $totalOrders, 2)
            : 0;

        return compact(
            'orderPoints',
            'localityStats',
            'statusBreakdown',
            'typeBreakdown',
            'totalOrders',
            'totalRevenue',
            'topLocality',
            'avgOrderValue'
        );
    }

    // ── Page load ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $orderStatus = $request->get('order_status', 'all');
        $dateFrom    = $request->get('date_from', now()->subYear()->toDateString());
        $dateTo      = $request->get('date_to', now()->toDateString());

        $data = $this->collectData($orderStatus, $dateFrom, $dateTo);

        $orderStatuses = [
            'Pending', 'Confirmed', 'Packed', 'Shipped',
            'Delivered', 'Cancelled', 'Returned', 'Refunded',
        ];

        return view('backend.heatmap.index', array_merge($data, compact(
            'orderStatuses',
            'orderStatus',
            'dateFrom',
            'dateTo'
        )));
    }

    // ── AJAX data endpoint ───────────────────────────────────────────
    public function data(Request $request)
    {
        $orderStatus = $request->get('order_status', 'all');
        $dateFrom    = $request->get('date_from', now()->subYear()->toDateString());
        $dateTo      = $request->get('date_to', now()->toDateString());

        $d = $this->collectData($orderStatus, $dateFrom, $dateTo);

        // status_breakdown is a keyed collection — convert to a plain list
        // so JSON comes out as an array, not an object
        $statusList = $d['statusBreakdown']->values()->map(fn($row) => [
            'order_status' => $row->order_status,
            'count'        => $row->count,
        ]);

        return response()->json([
            'points'          => $d['orderPoints'],
            'locality_stats'  => $d['localityStats'],
            'status_breakdown'=> $statusList,
            'type_breakdown'  => $d['typeBreakdown'],
            'total_orders'    => $d['totalOrders'],
            'total_revenue'   => $d['totalRevenue'],
            'top_locality'    => $d['topLocality'],
            'avg_order_value' => $d['avgOrderValue'],
        ]);
    }
}