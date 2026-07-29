<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Item extends Model
{
    public static function getData($post)
    {
        $result = DB::table('items as i')
            ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->select(
                'iv.id as variationid',
                'i.id as productid',
                DB::raw("CONCAT(i.title) as title"),
                'p.price',
                'i.description',
                'i.excise_status',
                'i.excise_type',
                'i.excise_percentage',
                'i.excise_value',
                'i.vat_percent',
                'iv.discount as variation_discount_percent',
                'iv.discount_type as variation_discount_type',
                'iv.discount_amount as variation_discount_amount'
            )
            ->where('iv.id', $post['variationid'])
            ->where('i.status', 'Y');

        if (!empty($post['userid'])) {
            $result->leftJoin('favourites as f', function ($join) use ($post) {
                $join->on('f.variationid', '=', 'iv.id')
                    ->where('f.userid', '=', $post['userid']);
            });

            $result->addSelect(
                DB::raw("CASE WHEN f.id IS NOT NULL THEN true ELSE false END as is_favourite")
            );
        } else {
            $result->addSelect(DB::raw("false as is_favourite"));
        }

        $result = $result->first();

        if (!$result) {
            throw new \Exception('Product not found.');
        }
        $result->is_favourite = (bool) $result->is_favourite;

        $result->campaign_discount = self::getActiveCampaignDiscount($result->variationid);
        self::applyPricing($result);

        $images = DB::table('item_images')
            ->where('item_id', $result->productid)
            ->pluck('image');

        $variations = DB::table('itemvariations as iv')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->select(
                'iv.id as variationid',
                'i.id as productid',
                DB::raw("CONCAT(iv.value) as name"),
                'p.price',
                'i.excise_status',
                'i.excise_type',
                'i.excise_percentage',
                'i.excise_value',
                'i.vat_percent',
                'iv.discount as variation_discount_percent',
                'iv.discount_type as variation_discount_type',
                'iv.discount_amount as variation_discount_amount'
            )
            ->where('iv.item_id', $result->productid)
            ->get();

        $variationIds = $variations->pluck('variationid')->all();
        $campaignDiscounts = self::getActiveCampaignDiscounts($variationIds);

        $variations->each(function ($v) use ($campaignDiscounts) {
            $v->campaign_discount = $campaignDiscounts[$v->variationid] ?? null;
            self::applyPricing($v);
        });

        $result->images = collect($images)->map(function ($img) {
            return url('storage/items/' . $img);
        })->values();

        $result->variations = $variations->map(function ($v) {
            return [
                'variationid'              => $v->variationid,
                'productid'                => $v->productid,
                'name'                     => $v->name,
                'price'                    => $v->price,
                'price_before_discount'    => $v->price_before_discount,
                'price_after_discount'     => $v->price_after_discount,
                'excise_amount'            => $v->excise_amount,
                'vat_amount'               => $v->vat_amount,
                'variation_discount_label' => $v->variation_discount_label,
                'campaign_discount_label'  => $v->campaign_discount_label,
                'excise_label'             => $v->excise_label,
                'vat_label'                => $v->vat_label,
            ];
        })->values();

        return $result;
    }


    private static function getActiveCampaignDiscount($variationId)
    {
        $discounts = self::getActiveCampaignDiscounts([$variationId]);
        return $discounts[$variationId] ?? null;
    }


    private static function getActiveCampaignDiscounts(array $variationIds)
    {
        if (empty($variationIds)) {
            return [];
        }

        $today = now()->toDateString();

        $rows = DB::table('discount_details as dd')
            ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
            ->select(
                'dd.variation_id',
                'dd.discount_type',
                'dd.discount_value',
                'dd.discount_amount',
                'dd.original_amount',
                'dd.total_amount',
                'dd.created_at'
            )
            ->whereIn('dd.variation_id', $variationIds)
            ->where('dd.status', 'Y')
            ->where('dm.status', 'Y')
            ->where(function ($q) use ($today) {
                $q->whereNull('dm.start_date_ad')->orWhere('dm.start_date_ad', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('dm.end_date_ad')->orWhere('dm.end_date_ad', '>=', $today);
            })
            ->orderBy('dd.created_at', 'desc')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            if (!isset($map[$row->variation_id])) {
                $map[$row->variation_id] = $row;
            }
        }

        return $map;
    }

    private static function applyPricing($item)
    {
        $price = (float) $item->price;

        if (($item->excise_status ?? 'N') === 'Y') {
            $item->excise_amount = $item->excise_type === 'percentage'
                ? $price * ((float) $item->excise_percentage / 100)
                : (float) $item->excise_value;
        } else {
            $item->excise_amount = 0;
        }

        $vatPercent = (float) ($item->vat_percent ?? 0);

        $vatBeforeDiscount = ($price + $item->excise_amount) * ($vatPercent / 100);
        $item->price_before_discount = round($price + $item->excise_amount + $vatBeforeDiscount, 2);

        $variationDiscount = 0;
        if ($item->variation_discount_type === 'percentage') {
            $variationDiscount = $price * ((float) $item->variation_discount_percent / 100);
        } elseif ($item->variation_discount_type === 'fixed') {
            $variationDiscount = (float) $item->variation_discount_amount;
        }

        $campaignDiscount = 0;
        $campaign = $item->campaign_discount ?? null;
        if ($campaign) {
            $campaignDiscount = (float) $campaign->discount_amount;
        }

        $priceAfterDiscount = max($price - $variationDiscount - $campaignDiscount, 0);

        $vatAfterDiscount = ($priceAfterDiscount + $item->excise_amount) * ($vatPercent / 100);
        $item->vat_amount = round($vatAfterDiscount, 2);
        $item->price_after_discount = round($priceAfterDiscount + $item->excise_amount + $vatAfterDiscount, 2);

        $item->variation_discount_label = 'No item discount';
        if ($item->variation_discount_type === 'percentage') {
            $item->variation_discount_label = $item->variation_discount_percent . '% off (item)';
        } elseif ($item->variation_discount_type === 'fixed') {
            $item->variation_discount_label = 'Rs. ' . number_format($item->variation_discount_amount, 2) . ' off (item)';
        }

        $item->campaign_discount_label = 'No campaign discount';
        if ($campaign) {
            $item->campaign_discount_label = $campaign->discount_type === 'percentage'
                ? $campaign->discount_value . '% off (campaign)'
                : 'Rs. ' . number_format($campaign->discount_value, 2) . ' off (campaign)';
        }

        $item->excise_label = 'No excise';
        if (($item->excise_status ?? 'N') === 'Y') {
            $item->excise_label = $item->excise_type === 'percentage'
                ? $item->excise_percentage . '% excise'
                : 'Rs. ' . number_format($item->excise_value, 2) . ' fixed excise';
        }

        $item->vat_label = $vatPercent . '% VAT';
    }


    public static function getUserOrderHistory($post)
    {
        $perPage = isset($post['per_page']) ? (int)$post['per_page'] : 10;
        $page    = isset($post['page'])     ? (int)$post['page']     : 1;
        $offset  = ($page - 1) * $perPage;

        $query = DB::table('order_details as od')
            ->join('itemvariations as iv', 'iv.id', '=', 'od.variation_id')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
            ->leftJoin(DB::raw('(
            SELECT item_id, MIN(image) as image
            FROM item_images
            GROUP BY item_id
        ) as im'), 'im.item_id', '=', 'i.id')
            ->select(
                'iv.id as variationid',
                DB::raw("CONCAT(i.title, ' ', iv.value) as productname"),
                DB::raw("iv.value as variation"),
                DB::raw("CONCAT('" . url('storage/items') . "/', im.image) as image"),
                'od.quantity',
                'od.order_detail_total_price as price',
                'om.order_status',
                'od.id as orderid',
                'om.id as ordermasterid',
                'od.created_at as time'
            )
            ->where('od.userid', $post['userid'])
            ->orderBy('od.created_at', 'desc');

        $total   = (clone $query)->count();   // ← clone before count()
        $records = $query->offset($offset)->limit($perPage)->get();  // ← then fetch

        return [
            'data'       => $records,
            'pagination' => [
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
                'per_page'     => $perPage,
                'total'        => $total,
                'has_more'     => ($page * $perPage) < $total,
                'next_page'    => ($page * $perPage) < $total ? $page + 1 : null,
                'prev_page'    => $page > 1 ? $page - 1 : null,
            ]
        ];
    }

    public static function getUserRecommendation($post)
    {
        try {

            $perPage = isset($post['per_page']) ? (int)$post['per_page'] : 10;
            $page    = isset($post['page']) ? (int)$post['page'] : 1;
            $offset  = ($page - 1) * $perPage;


            $query = DB::table('search_histories')
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->select(
                    'id as searchid',
                    'text',
                    DB::raw('MAX(created_at) as created_at')
                )
                ->groupBy('text', 'searchid')
                ->orderByDesc(DB::raw('MAX(created_at)'));


            // total unique searches
            $total = (clone $query)->get()->count();


            $records = $query
                ->offset($offset)
                ->limit($perPage)
                ->get();


            return [
                'data' => $records,

                'pagination' => [
                    'current_page' => $page,
                    'last_page'    => $total > 0
                        ? (int)ceil($total / $perPage)
                        : 1,
                    'per_page'     => $perPage,
                    'total'        => $total,
                    'has_more'     => ($page * $perPage) < $total,
                    'next_page'    => ($page * $perPage) < $total
                        ? $page + 1
                        : null,
                    'prev_page'    => $page > 1
                        ? $page - 1
                        : null,
                ]
            ];
        } catch (\Exception $e) {

            throw $e;
        }
    }
}