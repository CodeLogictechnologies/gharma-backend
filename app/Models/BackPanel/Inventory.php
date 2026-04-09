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
                // 'quantity_in_hand'   => $post['quantity_available'],
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

    public static function list($post)
    {
        try {
            $get = $_GET;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = "1=1";

            // if ($get['sSearch_1']) {
            //     $cond .= "and lower(p.username) like'%" . $get['sSearch_1'] . "%'";
            // }

            // if ($get['sSearch_3']) {
            //     $cond .= "and lower(i.title) like'%" . $get['sSearch_3'] . "%'";
            // }
            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = DB::table('inventories as inv')
                ->join('items as i',           'i.id',  '=', 'inv.item_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')
                ->join('categories as c',      'c.id',  '=', 'i.category_id')
                ->join('sub_categories as s',  's.id',  '=', 'i.subcategory_id')
                ->leftJoin('orders as o',      function ($join) {
                    $join->on('o.item_id',      '=', 'inv.item_id')
                        ->on('o.variation_id', '=', 'inv.variation_id');
                })
                ->leftJoin('profiles as p',    'p.id',  '=', 'o.customer_id')
                ->selectRaw("
        (SELECT COUNT(*) FROM inventories as inv2
            JOIN items i2          ON i2.id  = inv2.item_id
            JOIN itemvariations iv2 ON iv2.id = inv2.variation_id
            JOIN categories c2     ON c2.id  = i2.category_id
            JOIN sub_categories s2 ON s2.id  = i2.subcategory_id
            WHERE {$cond}
        ) as totalrecs,
        inv.id,
        inv.quantity_in_hand           as stock,
        inv.quantity_available         as remainingqty,
        inv.selling_price              as price,
        inv.unit_cost,
        inv.reorder_level,
        SUM(o.qty)                     as soldqty,
        c.title                        as categorytitle,
        s.title                        as subcategorytitle,
        i.title,
        iv.attribute,
        iv.value                       as variation_value
    ")
                ->whereRaw($cond)
                ->groupBy(
                    'inv.id',
                    'inv.quantity_in_hand',
                    'inv.quantity_available',
                    'inv.selling_price',
                    'inv.unit_cost',
                    'inv.reorder_level',
                    'c.title',
                    's.title',
                    'i.title',
                    'iv.attribute',
                    'iv.value'
                );

            if ($limit > -1) {
                $result = $query->orderby('o.id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderby('o.id', 'desc')->get();
            }
            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = array();
            }
            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }
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

    public static function getData($post)
    {
        try {
            $id = $post['id'] ?? 0;

            $result = DB::table('inventories as inv')  // ✅ fixed: was 'inventories'
                ->join('items as i',           'i.id',  '=', 'inv.item_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')
                ->join('categories as c',      'c.id',  '=', 'i.category_id')
                ->join('sub_categories as s',  's.id',  '=', 'i.subcategory_id')
                ->leftJoin('vendors as v',     'v.id',  '=', 'inv.vendor_id')
                ->where('inv.id', $id)
                ->select(
                    'inv.id',
                    'inv.item_id',
                    'inv.variation_id',
                    'inv.vendor_id',
                    'inv.quantity_available',
                    'inv.quantity_in_hand      as stock',
                    'inv.reorder_level',
                    'inv.unit_cost',
                    'inv.selling_price',
                    'inv.manufacturedatead',
                    'inv.expirydatead',
                    'i.title                   as item_title',
                    'iv.attribute',
                    'iv.id as variationid',
                    'iv.value                  as variation_value',
                    'c.title                   as categorytitle',
                    's.title                   as subcategorytitle',
                    'v.name                    as vendor_name'
                )
                ->first();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}