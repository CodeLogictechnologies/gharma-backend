<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cart extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {

            $price = DB::table('retailer_prices')
                ->where('variation_id', $post['variationid'])
                ->where('status', 'Y')
                ->where('orgid', $post['orgid'])
                ->select('price')
                ->first();

            $insertArray = [
                'id'            => (string) Str::uuid(),
                'orgid' => $post['orgid'],
                'userid' => $post['userid'],
                'variation_id' => $post['variationid'],
                'unit_price' => $price->price,
                'total_price' => $price->price,
                'quantity' => 1,
            ];

            if (!Cart::insert($insertArray)) {
                throw new \Exception("Couldn't save product to cart.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getData($post)
    {
        try {

            $result = DB::table('carts as c')
                ->select(
                    'c.variation_id',
                    'p.price as productprice',
                    'i.id as productid',
                    DB::raw("CONCAT(i.title, ' ', it.value) as title"),
                    DB::raw('SUM(c.quantity) as total_quantity'),
                    DB::raw('SUM(c.total_price) as total_price')
                )
                ->join('itemvariations as it', 'it.id', '=', 'c.variation_id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'it.id')
                ->join('items as i', 'i.id', '=', 'it.item_id')
                ->where('c.userid', $post['userid'])
                ->where('c.orgid', $post['orgid'])
                ->where('c.status', 'Y')
                ->whereNull('c.deleted_at')
                ->groupBy(
                    'c.variation_id',
                    'i.id',
                    'i.title',
                    'p.price',
                    'it.value'
                )
                ->get();

            // Attach single image
            $result->map(function ($item) {

                $image = DB::table('item_images')
                    ->where('item_id', $item->productid)
                    ->value('image'); // 👈 only one image

                $item->image = $image
                    ? url('uploads/items/' . $image)
                    : null;

                return $item;
            });

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function deleteCart($post)
    {
        try {

            $result = Cart::where('variation_id', $post['variationid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->delete();

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function removeCart($post)
    {
        try {

            $result = Cart::where('variation_id', $post['variationid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->select('id')
                ->first();

            $deleteItem = Cart::where('variation_id', $post['variationid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->where('id', $result->id)
                ->delete();

            return $deleteItem;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
