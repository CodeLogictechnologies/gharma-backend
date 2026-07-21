<?php

namespace App\Models\BackPanel\Report;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class Sales extends Model
{
    public static function salesReport($post)
    {
        try {
            $orgid = $post['orgid'];

            $query = DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('itemvariations as iv', 'iv.id', '=', 'od.variation_id')
                ->join('items as p', 'p.id', '=', 'iv.item_id')
                ->where('om.orgid', $orgid)
                ->select(
                    'om.id as order_id',
                    'om.created_at as order_date',
                    'om.payment_method',
                    'om.status',
                    'p.title',
                    DB::raw("CONCAT(p.title, ' - ', iv.value) as product_name"),
                    'od.quantity',
                    'od.price',
                    'od.order_detail_total_price as total_price'
                );

            $filter = $post['filter'] ?? 'month';

            if ($filter === 'day' && !empty($post['from_date']) && !empty($post['to_date'])) {
                $query->whereBetween(DB::raw('DATE(om.created_at)'), [
                    $post['from_date'],
                    $post['to_date'],
                ]);
            } elseif ($filter === 'month' && !empty($post['month'])) {
                $monthYear = explode('-', $post['month']);
                if (count($monthYear) == 2) {
                    $year = $monthYear[0];
                    $month = $monthYear[1];
                    $query->whereYear('om.created_at', $year)
                        ->whereMonth('om.created_at', $month);
                }
            } elseif ($filter === 'year' && !empty($post['year'])) {
                $query->whereYear('om.created_at', $post['year']);
            } else {
                $query->whereYear('om.created_at', date('Y'))
                    ->whereMonth('om.created_at', date('m'));
            }

            $rows = $query->get();

            $totalSales    = $rows->sum('total_price');
            $totalOrders   = $rows->pluck('order_id')->unique()->count();
            $totalQty      = $rows->sum('quantity');
            $avgOrderValue = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;

            $topProducts = $rows->groupBy('product_name')
                ->map(fn($g) => [
                    'product' => $g->first()->product_name,
                    'qty'     => $g->sum('quantity'),
                    'revenue' => $g->sum('total_price'),
                ])
                ->sortByDesc('revenue')
                ->values()
                ->take(5);

            $trendFormat = ($filter === 'year') ? 'M Y' : 'd M';

            $trend = $rows
                ->groupBy(fn($r) => \Carbon\Carbon::parse($r->order_date)->format($trendFormat))
                ->map(fn($g) => [
                    'label'   => \Carbon\Carbon::parse($g->first()->order_date)->format($trendFormat),
                    'revenue' => $g->sum('total_price'),
                    'orders'  => $g->pluck('order_id')->unique()->count(),
                ])
                ->values();

            $paymentBreakdown = $rows->groupBy('payment_method')
                ->map(fn($g) => [
                    'method'  => $g->first()->payment_method ?? 'N/A',
                    'revenue' => $g->sum('total_price'),
                    'count'   => $g->pluck('order_id')->unique()->count(),
                ])
                ->values();

            return [
                'summary'           => compact('totalSales', 'totalOrders', 'totalQty', 'avgOrderValue') + [
                    'total_sales'     => $totalSales,
                    'total_orders'    => $totalOrders,
                    'total_qty'       => $totalQty,
                    'avg_order_value' => $avgOrderValue,
                ],
                'rows'              => $rows,
                'top_products'      => $topProducts,
                'trend'             => $trend,
                'payment_breakdown' => $paymentBreakdown,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function saleReport($post)
    {
        try {
            $orgid = $post['orgid'];

            $currentMonth = date('m');
            $currentYear = date('Y');

            $month = $post['month'] ?? $currentMonth;
            $year = $post['year'] ?? $currentYear;

            $query = DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('itemvariations as iv', 'iv.id', '=', 'od.variation_id')
                ->join('items as p', 'p.id', '=', 'iv.item_id')
                ->where('om.orgid', $orgid)
                ->whereMonth('om.created_at', $month)
                ->whereYear('om.created_at', $year)
                ->select(
                    'om.id as order_id',
                    'om.created_at as order_date',
                    'om.status',
                    'p.title',
                    DB::raw("CONCAT(p.title, ' - ', iv.value) as product_name"),
                    'od.quantity',
                    'od.price',
                    'od.order_detail_total_price as total_price'
                )
                ->get();

            return $query;
        } catch (\Exception $e) {
            throw new Exception('Error fetching sales report: ' . $e->getMessage());
        }
    }
}