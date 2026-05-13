<?php

namespace App\Models\BackPanel\Report;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    public static function getData($post)
    {

        $result = DB::table('inventories as inv')
            ->join('itemvariations as iv', 'iv.id', '=', 'inv.variation_id')
            ->join('items as it', 'it.id', '=', 'iv.item_id')
            ->leftJoin('order_details as od', 'od.variation_id', '=', 'iv.id')

            ->select([
                'iv.threshold',
                DB::raw("CONCAT(it.title, ' - ', iv.value) as product_name"),
                'inv.quantity_available as stock',
                DB::raw("inv.quantity_available - COALESCE(SUM(od.quantity),0) as available_qty"),
                DB::raw("COALESCE(SUM(od.quantity),0) as sold_qty")
            ])

            ->where('inv.orgid', $post['orgid'])

            ->groupBy('iv.threshold', 'it.title', 'iv.value', 'inv.quantity_available')

            ->get();

        return $result;
    }
}