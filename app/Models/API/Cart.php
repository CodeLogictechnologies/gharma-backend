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
            $insertArray = [];
            // dd($post);
            if (!empty($post['roleid']) && $post['roleid'] == 2) {
                $price = DB::table('wholesaler_price_details as wd')
                    ->join('wholesaler_prices as wp', 'wp.id', '=', 'wd.wholesalermasterid')
                    ->where('wd.status', 'Y')
                    ->where('wp.status', 'Y')
                    ->where('wp.orgid', $post['orgid'])
                    ->where('wp.variation_id', $post['variationid'])
                    ->where('wd.min_qty', '<=', $post['qty'])
                    ->where('wd.max_qty', '>=', $post['qty'])
                    ->select('wd.price')
                    ->first();

                if (!$price) {
                    $price = DB::table('retailer_prices')
                        ->where('variation_id', $post['variationid'])
                        ->where('status', 'Y')
                        ->where('orgid', $post['orgid'])
                        ->select('price')
                        ->first();
                    if (!$price) {
                        throw new \Exception("Price not found for this product.");
                    }
                }
                if ($post['qty'] == 0 || $post['qty'] == "0") {
                    $delete = Cart::where('variation_id', $post['variationid'])
                        ->where('orgid', $post['orgid'])
                        ->where('userid', $post['userid'])
                        ->delete();

                    if (!$delete) {
                        throw new \Exception("Couldn't update product in cart.");
                    }

                    return true;
                }
                // Check if this item already exists in the cart
                $existingCart = Cart::where('variation_id', $post['variationid'])
                    ->where('orgid', $post['orgid'])
                    ->where('userid', $post['userid'])
                    ->first();

                if ($existingCart) {
                    // Item exists — update quantity and recalculate total_price
                    $newQty = $post['qty'];

                    $updated = Cart::where('id', $existingCart->id)
                        ->update([
                            'quantity'    => $newQty,
                            'total_price' => $newQty * $price->price,
                        ]);

                    if (!$updated) {
                        throw new \Exception("Couldn't update product in cart.");
                    }
                } else {
                    // Item doesn't exist — insert new row
                    $insertArray = [
                        'id'           => (string) Str::uuid(),
                        'orgid'        => $post['orgid'],
                        'userid'       => $post['userid'],
                        'variation_id' => $post['variationid'],
                        'unit_price'   => $price->price,
                        'total_price'  => $post['qty'] * $price->price,
                        'quantity'     => $post['qty'],
                    ];

                    if (!Cart::insert($insertArray)) {
                        throw new \Exception("Couldn't save product to cart.");
                    }
                }
            } else {
                $price = DB::table('retailer_prices')
                    ->where('variation_id', $post['variationid'])
                    ->where('status', 'Y')
                    ->where('orgid', $post['orgid'])
                    ->select('price')
                    ->first();

                if (!$price) {
                    throw new \Exception("Price not found for this variation.");
                }

                if ($post['qty'] == 0 || $post['qty'] == "0") {
                    $delete = Cart::where('variation_id', $post['variationid'])
                        ->where('orgid', $post['orgid'])
                        ->where('userid', $post['userid'])
                        ->delete();

                    if (!$delete) {
                        throw new \Exception("Couldn't update product in cart.");
                    }

                    return true;
                }
                // Check if this item already exists in the cart
                $existingCart = Cart::where('variation_id', $post['variationid'])
                    ->where('orgid', $post['orgid'])
                    ->where('userid', $post['userid'])
                    ->first();

                if ($existingCart) {
                    // Item exists — update quantity and recalculate total_price
                    $newQty = $post['qty'];

                    $updated = Cart::where('id', $existingCart->id)
                        ->update([
                            'quantity'    => $newQty,
                            'total_price' => $newQty * $price->price,
                        ]);

                    if (!$updated) {
                        throw new \Exception("Couldn't update product in cart.");
                    }
                } else {
                    // Item doesn't exist — insert new row
                    $insertArray = [
                        'id'           => (string) Str::uuid(),
                        'orgid'        => $post['orgid'],
                        'userid'       => $post['userid'],
                        'variation_id' => $post['variationid'],
                        'unit_price'   => $price->price,
                        'total_price'  => $post['qty'] * $price->price,
                        'quantity'     => $post['qty'],
                    ];

                    if (!Cart::insert($insertArray)) {
                        throw new \Exception("Couldn't save product to cart.");
                    }
                }
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
                    'i.id as productid',
                    DB::raw("CONCAT(i.title, ' ', it.value) as title"),
                    'c.unit_price as productprice',
                    'c.total_price as total_price',
                    'c.quantity as total_quantity'
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
                    'it.value',
                    'c.unit_price',
                    'c.total_price',
                    'c.quantity'
                )
                ->get();

            $result->map(function ($item) {

                $image = DB::table('item_images')
                    ->where('item_id', $item->productid)
                    ->value('image');

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

            $carts = Cart::where('variation_id', $post['variationid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->orderBy('id', 'asc')
                ->get();

            if ($carts->isEmpty()) {
                throw new \Exception("Cart not found");
            }

            $removeQty = (int) $post['qty'];

            // Total available qty
            $totalQty = $carts->sum('quantity');

            if ($removeQty > $totalQty) {
                throw new \Exception("Requested qty exceeds cart quantity");
            }

            foreach ($carts as $cart) {
                $unitPrice = $cart->total_price / $cart->quantity;

                if ($removeQty <= 0) {
                    break;
                }

                if ($cart->quantity <= $removeQty) {
                    $removeQty -= $cart->quantity;
                    $cart->delete();
                } else {
                    $cart->quantity -= $removeQty;
                    $cart->total_price = $cart->quantity * $unitPrice;

                    $cart->save();
                    $removeQty = 0;
                }
            }

            return $totalQty;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
