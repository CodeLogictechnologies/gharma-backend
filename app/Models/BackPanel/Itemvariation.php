<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class Itemvariation extends Model
{
    public static function getDate($post)
    {
        try {
            $result = DB::table('itemvariations')->where('item_id', $post['id'])->get();
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getProductCodes($post, $inStockOnly = false)
    {
        try {
            $query = DB::table('itemvariations as iv')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->select(
                    'iv.id as variationid',
                    'iv.item_id as itemid',
                    'iv.product_code',
                    'iv.attribute',
                    'iv.value',
                    'i.title as itemname',
                    'i.is_wholesale'
                )
                ->where('i.orgid', $post['orgid'])
                ->where('i.status', 'Y')
                ->whereNotNull('iv.product_code')
                ->where('iv.product_code', '!=', '');

            if ($inStockOnly) {
                $query->whereIn('iv.id', self::inStockVariationIds($post['orgid']));
            }

            return $query->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Variation ids with remaining stock > 0 (stock - sold), same basis as the Inventory > Stock page ── */
    public static function inStockVariationIds($orgid)
    {
        return DB::table('items as i')
            ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
            ->join('purchase_voucher_items as pvi', 'pvi.variation_id', '=', 'iv.id')
            ->leftJoin('order_details as o', 'o.variation_id', '=', 'iv.id')
            ->where('i.orgid', $orgid)
            ->where('i.status', 'Y')
            ->groupBy('iv.id', 'pvi.qty')
            ->havingRaw('pvi.qty - COALESCE(SUM(o.quantity), 0) > 0')
            ->pluck('iv.id')
            ->unique()
            ->values();
    }
}
