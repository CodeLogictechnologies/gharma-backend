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
                'i.description'
            )
            ->where('iv.id', $post['variationid'])
            ->where('i.status', 'Y');

        // ✅ Favourite logic (optional join)
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

        // ✅ Get images safely
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
                'p.price'
            )
            ->where('iv.item_id', $result->productid)
            ->get();


        $result->images = collect($images)->map(function ($img) {
            return url('uploads/items/' . $img);
        })->values();
        $result->variations = collect($variations)->map(function ($v) {
            return [
                'variationid' => $v->variationid,
                'productid'   => $v->productid,
                'name'        => $v->name,
                'price'       => $v->price,
            ];
        })->values();
        return $result;
    }

    public static function getUserOrderHistory($post)
    {
        try {

            $result = DB::table('order_details as od')
                ->join('itemvariations as isv', 'iv.id', '=', 'od.variation_id')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->leftJoin(DB::raw('(
                SELECT item_id, MIN(image) as image
                FROM item_images
                GROUP BY item_id
            ) as im'), 'im.item_id', '=', 'i.id')
                ->select(
                    DB::raw("CONCAT(i.title, ' ', iv.value) as productname"),
                    DB::raw("CONCAT('" . url('uploads/items') . "/', im.image) as image"),
                    'od.quantity',
                    'od.order_detail_total_price'
                )
                ->where('od.userid', $post['userid'])
                ->where('od.status', 'Y')
                ->get();

            if ($result->isEmpty()) {
                throw new \Exception('No order history found');
            }

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getUserRecommendation($post)
    {
        try {
            $result = DB::table('search_histories')
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->select('id as searchid', 'text')
                ->groupBy('text', 'id')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}