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
        return Inventory::stockAggregateQuery()
            ->where('i.orgid', $orgid)
            ->where('i.status', 'Y')
            ->whereRaw('(pvi.total_qty - COALESCE(o.total_sold, 0)) > 0')
            ->pluck('iv.id')
            ->unique()
            ->values();
    }

    /* ── Numeric remaining stock (stock - sold) for one variation, same basis as the Inventory > Stock page ──
       When $excludeOrderMasterId is set (editing an existing voucher), that voucher's own already-recorded
       qty for this variation is added back, since saveData() doesn't remove/replace old order_details on edit. */
    public static function remainingStock($variationId, $orgid, $excludeOrderMasterId = null)
    {
        $row = Inventory::stockAggregateQuery()
            ->where('i.orgid', $orgid)
            ->where('iv.id', $variationId)
            ->select(DB::raw('pvi.total_qty - COALESCE(o.total_sold, 0) as remaining'))
            ->first();

        $remaining = $row ? (float) $row->remaining : 0.0;

        if ($excludeOrderMasterId) {
            $ownQty = DB::table('order_details')
                ->where('ordermasterid', $excludeOrderMasterId)
                ->where('variation_id', $variationId)
                ->where('status', 'Y')
                ->whereNull('deleted_at')
                ->sum('quantity');
            $remaining += (float) $ownQty;
        }

        return $remaining;
    }
}
