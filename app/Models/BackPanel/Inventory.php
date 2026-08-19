<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\SubCategory;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Inventory extends Model
{
    public static function saveData($post)
    {
        try {
            $dataArray = [
                'item_id'            => $post['itemid'],
                'variation_id'       => $post['variationid'],
                'vendor_id'          => $post['vendorid'],
                'quantity_available' => $post['quantity_available'],
                'expirymonth'   => $post['expirymonth'] ?? null,
                'reorder_level'      => $post['reorder_level'],
                'unit_cost'          => $post['unit_cost'],
                'selling_price'      => $post['selling_price'],
                'manufacturedatead'   => $post['manufacturedatead'],
                'expirydatead'        => $post['expirydatead'],
                'orgid'              => $post['orgid'],
                'postedby'           => Auth::id(),
                'updatedby'          => Auth::id(),
                'updated_at'         => Carbon::now(),
            ];

            if (!empty($post['id'])) {
                // ── UPDATE ───────────────────────────────────────────────
                return DB::table('inventories')
                    ->where('id', $post['id'])
                    ->update($dataArray);
            } else {
                // ── INSERT ───────────────────────────────────────────────
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['created_at'] = Carbon::now();
                return DB::table('inventories')->insert($dataArray);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * Base query yielding exactly one row per (item_id, variation_id), with
     * purchase quantity and sold quantity already summed per variation
     * (pvi.total_qty, o.total_sold) — never per raw ledger/order row.
     *
     * purchase_voucher_items is an immutable purchase ledger (a new row is
     * inserted per purchase), and order_details is an immutable sales ledger,
     * so both must be pre-aggregated before joining, otherwise the join
     * produces a cartesian product per variation (one row per purchase x
     * per sale) with duplicated/incorrect totals.
     */
    public static function stockAggregateQuery()
    {
        $purchaseAgg = DB::table('purchase_voucher_items')
            ->select('item_id', 'variation_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('item_id', 'variation_id');

        $salesAgg = DB::table('order_details')
            ->select('variation_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('status', 'Y')
            ->whereNull('deleted_at')
            ->groupBy('variation_id');

        $returnAgg = DB::table('purchase_return_voucher_items as prvi')
            ->join('purchase_return_vouchers as prv', 'prv.id', '=', 'prvi.purchase_return_voucher_id')
            ->select('prvi.variation_id', DB::raw('SUM(prvi.qty) as total_returned'))
            ->where('prv.status', 'Y')
            ->where('prv.return_status', 'Approved')
            ->groupBy('prvi.variation_id');

        return DB::table('items as i')
            ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
            ->joinSub($purchaseAgg, 'pvi', function ($join) {
                $join->on('pvi.item_id', '=', 'i.id')
                    ->on('pvi.variation_id', '=', 'iv.id');
            })
            ->leftJoinSub($salesAgg, 'o', 'o.variation_id', '=', 'iv.id')
            ->leftJoinSub($returnAgg, 'pr', 'pr.variation_id', '=', 'iv.id');
    }

    public static function list($post)
    {
        try {
            $orgid    = $post['orgid'] ?? '';
            $columns  = $post['columns'] ?? [];
            $cond     = "i.orgid = ?";
            $bindings = [$orgid];

            if (!empty($columns[1]['search']['value'])) {
                $val        = strtolower(trim($columns[1]['search']['value']));
                $cond      .= " and lower(i.title) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($columns[2]['search']['value'])) {
                $val        = strtolower(trim($columns[2]['search']['value']));
                $cond      .= " and lower(iv.value) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($columns[3]['search']['value'])) {
                $val        = strtolower(trim($columns[3]['search']['value']));
                $cond      .= " and pvi.total_qty::text like ?";
                $bindings[] = "%{$val}%";
            }

            $limit  = isset($post['length']) ? (int) $post['length'] : 15;
            $offset = isset($post['start'])  ? (int) $post['start']  : 0;

            $baseQuery = self::stockAggregateQuery()->where('i.orgid', $orgid);

            // Total count (unfiltered) — one row per (item, variation) already
            $totalrecs = (clone $baseQuery)->count();

            $query = (clone $baseQuery)
                ->selectRaw("
        i.id,
        pvi.total_qty as stock,
        COALESCE(pr.total_returned, 0) as returnqty,
        GREATEST(pvi.total_qty - COALESCE(pr.total_returned, 0) - COALESCE(o.total_sold, 0), 0) AS remainingqty,
        COALESCE(o.total_sold, 0) as soldqty,
        i.title,
        iv.attribute,
        iv.value as variation_value
    ")
                ->whereRaw($cond, $bindings)
                ->orderBy('i.id', 'desc');

            $filteredCount = (clone $query)->count();

            $result = $limit > -1
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            $ndata                      = $result;
            $ndata['totalrecs']         = $totalrecs;
            $ndata['totalfilteredrecs'] = $filteredCount;

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // public static function list($post)
    // {
    //     try {
    //         $get = $_GET;
    //         foreach ($get as $key => $value) {
    //             $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
    //         }
    //         $cond = "1=1";

    //         $columns = $post['columns'] ?? [];

    //         if (!empty($columns[1]['search']['value'])) {
    //             $val    = strtolower(trim($columns[1]['search']['value']));
    //             $cond  .= " and lower(c.title) like '%{$val}%'";
    //         }

    //         if (!empty($columns[2]['search']['value'])) {
    //             $val    = strtolower(trim($columns[2]['search']['value']));
    //             $cond  .= " and lower(s.title) like '%{$val}%'";
    //         }

    //         if (!empty($columns[3]['search']['value'])) {
    //             $val    = strtolower(trim($columns[3]['search']['value']));
    //             $cond  .= " and lower(i.title) like '%{$val}%'";
    //         }

    //         $limit = 15;
    //         $offset = 0;
    //         if (!empty($get["length"]) && $get["length"]) {
    //             $limit = $get['length'];
    //             $offset = $get["start"];
    //         }

    //         $query = DB::table('inventories as inv')
    //             ->join('items as i',           'i.id',  '=', 'inv.item_id')
    //             ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')

    //             ->leftJoin('order_details as o',      function ($join) {
    //                 $join->on('o.variation_id', '=', 'inv.variation_id');
    //             })
    //             ->leftJoin('profiles as p',    'p.id',  '=', 'o.userid')
    //             ->selectRaw("
    //     (SELECT COUNT(*) FROM inventories as inv2
    //         JOIN items i2          ON i2.id  = inv2.item_id
    //         JOIN itemvariations iv2 ON iv2.id = inv2.variation_id

    //         WHERE {$cond}
    //     ) as totalrecs,
    //     inv.id,
    //     inv.quantity_available           as stock,
    //     inv.quantity_available - COALESCE(SUM(o.quantity), 0) AS remainingqty,
    //     inv.selling_price              as price,
    //     inv.unit_cost,
    //     inv.reorder_level,
    //     SUM(o.quantity)                     as soldqty,

    //     i.title,
    //     iv.attribute,
    //     iv.value                       as variation_value
    // ")
    //             ->whereRaw($cond)
    //             ->groupBy(
    //                 'inv.id',
    //                 'inv.quantity_in_hand',
    //                 'inv.quantity_available',
    //                 'inv.selling_price',
    //                 'inv.unit_cost',
    //                 'inv.reorder_level',
    //                 'o.id',
    //                 'i.title',
    //                 'iv.attribute',
    //                 'iv.value'
    //             );

    //         if ($limit > -1) {
    //             $result = $query->orderby('o.id', 'desc')->offset($offset)->limit($limit)->get();
    //         } else {
    //             $result = $query->orderby('o.id', 'desc')->get();
    //         }
    //         if ($result) {
    //             $ndata = $result;
    //             $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //             $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //         } else {
    //             $ndata = array();
    //         }
    //         return $ndata;
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }


    // public static function getData($post)
    // {
    //     try {
    //         $id = $post['id'] ?? 0;



    //         $result = DB::table('inventories as inv')
    //             ->join('items as i',           'i.id',  '=', 'inv.item_id')
    //             ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')
    //             ->join('categories as c',      'c.id',  '=', 'i.category_id')
    //             ->join('sub_categories as s',  's.id',  '=', 'i.subcategory_id')
    //             ->leftJoin('orders as o',      function ($join) {
    //                 $join->on('o.item_id',      '=', 'inv.item_id')
    //                     ->on('o.variation_id', '=', 'inv.variation_id');
    //             })
    //             ->leftJoin('profiles as p',    'p.id',  '=', 'o.customer_id')
    //             ->where('inv.id', $id)
    //             ->select(
    //                 'inv.id',
    //                 // 'inv.quantity_in_hand    as stock',
    //                 'inv.quantity_available  as remainingqty',
    //                 'inv.selling_price       as price',
    //                 'inv.unit_cost',
    //                 'inv.reorder_level',
    //                 'i.title',
    //                 'iv.attribute',
    //                 'iv.value                as variation_value',
    //                 'c.title                 as categorytitle',
    //                 's.title                 as subcategorytitle',
    //                 DB::raw('SUM(o.qty)      as soldqty')
    //             )
    //             ->groupBy(
    //                 'inv.id',
    //                 'inv.quantity_in_hand',
    //                 'inv.quantity_available',
    //                 'inv.selling_price',
    //                 'inv.unit_cost',
    //                 'inv.reorder_level',
    //                 'i.title',
    //                 'iv.attribute',
    //                 'iv.value',
    //                 'c.title',
    //                 's.title'
    //             )
    //             ->first();

    //         dd($result);

    //         return $result;
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }

    public static function lowStockAlerts($orgid)
    {
        try {
            $rows = self::stockAggregateQuery()
                ->where('i.orgid', $orgid)
                ->selectRaw("
        iv.id as variation_id,
        i.id as item_id,
        i.title,
        iv.attribute,
        iv.value as variation_value,
        CAST(iv.threshold AS INTEGER) as threshold,
        GREATEST(pvi.total_qty - COALESCE(pr.total_returned, 0) - COALESCE(o.total_sold, 0), 0) AS remainingqty

    ")
                ->whereRaw('(pvi.total_qty - COALESCE(pr.total_returned, 0) - COALESCE(o.total_sold, 0)) < CAST(iv.threshold AS INTEGER)')
                ->orderByRaw('(pvi.total_qty - COALESCE(pr.total_returned, 0) - COALESCE(o.total_sold, 0)) asc')
                ->get();

            return $rows;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getData($post)
    {
        try {
            $id = $post['id'] ?? 0;

            $result = DB::table('inventories as inv')  // ✅ fixed: was 'inventories'
                ->join('items as i',           'i.id',  '=', 'inv.item_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')
                // ->join('categories as c',      'c.id',  '=', 'i.category_id')
                // ->join('sub_categories as s',  's.id',  '=', 'i.subcategory_id')
                ->leftJoin('vendors as v',     'v.id',  '=', 'inv.vendor_id')
                ->where('inv.id', $id)
                ->select(
                    'inv.id',
                    'inv.expirymonth',
                    'inv.item_id',
                    'inv.variation_id',
                    'inv.vendor_id',
                    'inv.quantity_available',
                    // 'inv.quantity_in_hand      as stock',
                    'inv.reorder_level',
                    'inv.unit_cost',
                    'inv.selling_price',
                    'inv.manufacturedatead',
                    'inv.expirydatead',
                    'i.title                   as item_title',
                    'iv.attribute',
                    'iv.id as variationid',
                    'iv.value                  as variation_value',
                    // 'c.title                   as categorytitle',
                    // 's.title                   as subcategorytitle',
                    'v.name                    as vendor_name'
                )
                ->first();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
