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
            if (!empty($post['roleid']) && $post['roleid'] == '550e8400-e29b-41d4-a716-446655440002') {
                $post['type'] = 'W';
            } else {
                $post['type'] = 'R';
            }
            if (!empty($post['roleid']) && $post['roleid'] == '550e8400-e29b-41d4-a716-446655440002') {

                // Fetch wholesaler price with min_qty
                $price = DB::table('wholesaler_price_details as wd')
                    ->join('wholesaler_prices as wp', 'wp.id', '=', 'wd.wholesalermasterid')
                    ->where('wd.status', 'Y')
                    ->where('wp.status', 'Y')
                    ->where('wp.orgid', $post['orgid'])
                    ->where('wp.variation_id', $post['variationid'])
                    ->where('wd.min_qty', '<=', $post['qty'])
                    ->where('wd.max_qty', '>=', $post['qty'])
                    ->select('wd.price', 'wd.min_qty')
                    ->first();

                // No retailer fallback — price stays null if not found
                $resolvedPrice = $price?->price ?? null;
                $resolvedMinQty = $price?->min_qty ?? null;

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

                    $updateData = [
                        'quantity'    => $newQty,
                        'total_price' => $resolvedPrice !== null ? $newQty * $resolvedPrice : null,
                        'unit_price'  => $resolvedPrice,  // save null if no wholesaler price
                    ];

                    // Include min_qty only if price is null
                    // if ($resolvedPrice === null) {
                    //     $updateData['min_qty'] = $resolvedMinQty;
                    // }

                    $updated = Cart::where('id', $existingCart->id)->update($updateData);

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
                        'unit_price'   => $resolvedPrice,
                        'total_price'  => $resolvedPrice !== null ? $post['qty'] * $resolvedPrice : null,
                        'quantity'     => $post['qty'],
                        'type'         => $post['type'],
                    ];

                    // Include min_qty only if price is null
                    // if ($resolvedPrice === null) {
                    //     $insertArray['min_qty'] = $resolvedMinQty;
                    // }

                    if (!Cart::insert($insertArray)) {
                        throw new \Exception("Couldn't save product to cart.");
                    }
                }
            } else {
                $price = DB::table('retailer_prices as p')
                    // ->leftJoin('discounts as d', function ($join) use ($post) {
                    //     $join->on(function ($q) {
                    //         $q->where('d.applies_to', 'entire')
                    //             ->where('d.status', 'Y')
                    //             ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                    //     })->orOn(function ($q) use ($post) {
                    //         $q->where('d.applies_to', 'item')
                    //             ->whereColumn('d.item_id', 'p.itemid')
                    //             ->where('d.status', 'Y')
                    //             ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                    //     })->orOn(function ($q) use ($post) {
                    //         $q->where('d.applies_to', 'variation')
                    //             ->where('d.variation_id', $post['variationid'])
                    //             ->where('d.status', 'Y')
                    //             ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                    //     });
                    // })
                    ->where('p.variation_id', $post['variationid'])
                    ->where('p.status', 'Y')
                    ->where('p.orgid', $post['orgid'])
                    ->select(
                        'p.price_after_discount as original_price'
                    )
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
                            'total_price' => $newQty * $price->original_price,
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
                        'unit_price'   => $price->original_price,
                        'total_price'  => $post['qty'] * $price->original_price,
                        'quantity'     => $post['qty'],
                        'type'     => $post['type'],
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
            if (!empty($post['roleid'] && $post['roleid'] == '550e8400-e29b-41d4-a716-446655440002')) {
                $result = DB::table('carts as c')
                    ->select(
                        'c.variation_id',
                        'i.id as productid',
                        DB::raw("CONCAT(i.title, ' ', it.value) as title"),
                        'c.unit_price as productprice',
                        'c.total_price as total_price',
                        'c.quantity as total_quantity',
                        DB::raw('(
                            SELECT MIN(wd2.min_qty)
                            FROM wholesaler_price_details as wd2
                            INNER JOIN wholesaler_prices as wp2 ON wp2.id = wd2.wholesalermasterid
                            WHERE wp2.variation_id = c.variation_id
                            AND wd2.status = \'Y\'
                            AND wp2.status = \'Y\'
                        ) as min_qty')
                    )
                    ->join('itemvariations as it', 'it.id', '=', 'c.variation_id')
                    ->join('items as i', 'i.id', '=', 'it.item_id')
                    ->where('c.userid', $post['userid'])
                    ->where('c.orgid', $post['orgid'])
                    ->where('c.status', 'Y')
                    ->whereNull('c.deleted_at')
                    ->where('c.type', 'W')
                    ->groupBy(
                        'c.variation_id',
                        'i.id',
                        'i.title',
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
                        ? url('storage/items/' . $image)
                        : null;

                    if ($item->productprice !== null) {
                        unset($item->min_qty);
                    }

                    $item->original_price_per_unit      = null;

                    $item->discount_type                =  null;

                    $item->discount_value_per_unit      =  null;

                    $item->discount_percentage_per_unit =  null;

                    return $item;
                });

                return $result;
            } else {
                $result = DB::table('carts as c')
                    ->select(
                        'c.variation_id',
                        'i.id as productid',
                        DB::raw("CONCAT(i.title, ' ', it.value) as title"),
                        'c.unit_price as productprice',
                        'c.total_price as total_price',
                        'c.quantity as total_quantity',

                        // original price before discount
                        'p.price as original_price_per_unit',

                        // final price after discount applied
                        // DB::raw("
                        //     CASE
                        //         WHEN d.id IS NULL THEN p.price
                        //         WHEN d.type = 'percentage' THEN ROUND(p.price - (p.price * d.percentage / 100), 2)
                        //         WHEN d.type = 'fixed'      THEN ROUND(p.price - d.value, 2)
                        //         ELSE p.price
                        //     END as discounted_price
                        // "),

                        DB::raw("CASE WHEN d.id IS NULL THEN NULL ELSE d.type       END as discount_type"),
                        DB::raw("CASE WHEN d.id IS NULL THEN NULL ELSE d.value      END as discount_value_per_unit"),
                        // DB::raw("CASE WHEN d.id IS NULL THEN NULL ELSE d.value      END as discount_value_per_unit"),
                        DB::raw("CASE WHEN d.id IS NULL THEN NULL ELSE d.percentage END as discount_percentage_per_unit"),
                    )
                    ->join('itemvariations as it', 'it.id', '=', 'c.variation_id')
                    ->join('retailer_prices as p', 'p.variation_id', '=', 'it.id')
                    ->join('items as i', 'i.id', '=', 'it.item_id')
                    ->leftJoin('discounts as d', function ($join) {
                        $join->on(function ($q) {
                            $q->where('d.applies_to', 'entire')
                                ->where('d.status', 'Y')
                                ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                        })->orOn(function ($q) {
                            $q->where('d.applies_to', 'item')
                                ->whereColumn('d.item_id', 'i.id')
                                ->where('d.status', 'Y')
                                ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                        })->orOn(function ($q) {
                            $q->where('d.applies_to', 'variation')
                                ->whereColumn('d.variation_id', 'it.id')
                                ->where('d.status', 'Y')
                                ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                        });
                    })
                    ->where('c.userid', $post['userid'])
                    ->where('c.orgid', $post['orgid'])
                    ->where('c.status', 'Y')
                    ->whereNull('c.deleted_at')
                    ->where('c.type', 'R')
                    ->groupBy(
                        'c.variation_id',
                        'i.id',
                        'i.title',
                        'it.value',
                        'p.price',
                        'c.unit_price',
                        'c.total_price',
                        'c.quantity',
                        'd.id',
                        'd.type',
                        'd.value',
                        'd.percentage',
                    )
                    ->get();
                $result->map(function ($item) {

                    $image = DB::table('item_images')
                        ->where('item_id', $item->productid)
                        ->value('image');

                    $item->image = $image
                        ? url('storage/items/' . $image)
                        : null;
                    $item->min_qty      = null;


                    return $item;
                });
                return $result;
            }
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