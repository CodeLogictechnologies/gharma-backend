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
                        'c.id as cart_id',
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
            ) as min_qty'),

                        'i.excise_status',
                        'i.excise_type',
                        'i.excise_percentage',
                        'i.excise_value',
                        DB::raw("
                CASE
                    WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN
                        (CAST(c.total_price AS numeric)) * (i.excise_percentage / 100)
                    WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
                    ELSE 0
                END as excise_amount
            "),

                        'i.vat_percent'
                    )
                    ->join('itemvariations as it', 'it.id', '=', 'c.variation_id')
                    ->join('items as i', 'i.id', '=', 'it.item_id')
                    ->where('c.userid', $post['userid'])
                    ->where('c.orgid', $post['orgid'])
                    ->where('c.status', 'Y')
                    ->whereNull('c.deleted_at')
                    ->where('c.type', 'W')
                    ->groupBy(
                        'c.id',
                        'c.variation_id',
                        'i.id',
                        'i.title',
                        'it.value',
                        'c.unit_price',
                        'c.total_price',
                        'c.quantity',
                        'i.excise_status',
                        'i.excise_type',
                        'i.excise_percentage',
                        'i.excise_value',
                        'i.vat_percent'
                    )
                    ->get();

                $result = $result->map(function ($item) {

                    $image = DB::table('item_images')
                        ->where('item_id', $item->productid)
                        ->value('image');

                    $item->image = $image
                        ? url('storage/items/' . $image)
                        : null;

                    if ($item->productprice !== null) {
                        unset($item->min_qty);
                    }

                    // no per-unit / campaign discount in this branch
                    $item->original_price_per_unit      = null;
                    $item->discount_type                = null;
                    $item->discount_value_per_unit      = null;
                    $item->discount_percentage_per_unit = null;

                    $item->variation_discount_value  = null;
                    $item->variation_discount_type   = null;
                    $item->variation_discount_amount = null;
                    $item->variation_discount_label  = 'No item discount';

                    $item->campaign_discount_type    = null;
                    $item->campaign_discount_value   = null;
                    $item->campaign_discount_amount  = 0;
                    $item->campaign_discount_label   = 'No campaign discount';

                    $item->min_value = null;
                    $item->max_value = null;
                    $item->min_qty   = null;

                    // price_after_discount = total_price since no discounts apply here
                    $item->price_after_discount = round((float) $item->total_price, 2);

                    $priceAfterExcise = $item->price_after_discount + $item->excise_amount;
                    $vatAmount        = $priceAfterExcise * ($item->vat_percent / 100);
                    $finalTotal       = $priceAfterExcise + $vatAmount;

                    $item->price_after_excise = round($priceAfterExcise, 2);
                    $item->vat_amount         = round($vatAmount, 2);
                    $item->final_total        = round($finalTotal, 2);

                    $item->excise_label = 'No excise';
                    if ($item->excise_status === 'Y') {
                        $item->excise_label = $item->excise_type === 'percentage'
                            ? $item->excise_percentage . '% excise'
                            : 'Rs. ' . number_format($item->excise_value, 2) . ' fixed excise';
                    }

                    $item->pricebeforediscount = $item->total_price + $vatAmount + $priceAfterExcise + $vatAmount;

                    $item->vat_label = $item->vat_percent . '% VAT';

                    return $item;
                });

                return $result;
            } else {
                $activeDiscount = DB::table('discount_details as dd')
                    ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
                    ->where('dd.status', 'Y')
                    ->where('dm.status', 'Y')
                    ->where(function ($q) {
                        $q->whereNull('dm.start_date_ad')
                            ->orWhere('dm.start_date_ad', '<=', now()->toDateString());
                    })
                    ->where(function ($q) {
                        $q->whereNull('dm.end_date_ad')
                            ->orWhere('dm.end_date_ad', '>=', now()->toDateString());
                    })
                    ->select(
                        'dd.variation_id',
                        'dm.min_value',
                        'dm.max_value',
                        'dd.discount_type as campaign_discount_type',
                        'dd.discount_value as campaign_discount_value',
                        'dd.discount_amount as campaign_discount_amount',
                        DB::raw('ROW_NUMBER() OVER (PARTITION BY dd.variation_id ORDER BY dd.created_at DESC) as rn')
                    );

                $result = DB::table('carts as c')
                    ->join('itemvariations as iv', 'iv.id', '=', 'c.variation_id')
                    ->join('items as i', 'i.id', '=', 'iv.item_id')
                    ->leftJoinSub($activeDiscount, 'ad', function ($join) {
                        $join->on('ad.variation_id', '=', 'iv.id')
                            ->where('ad.rn', '=', 1);
                    })
                    ->select([
                        'c.id as cart_id',
                        'c.variation_id',
                        'c.quantity as total_quantity',
                        'iv.item_id as productid',
                        // 'i.title as item_title',
                        // 'iv.attribute',
                        // 'iv.value',
                        DB::raw("CONCAT(i.title, ' ', iv.value) as title"),

                        'ad.min_value',
                        'ad.max_value',
                        'iv.threshold as min_qty_raw',
                        DB::raw('CAST(iv.price AS numeric) as unit_price'),
                        DB::raw('CAST(iv.price AS numeric) * c.quantity as subtotal_amount'),

                        'iv.discount as variation_discount_value',
                        'iv.discount_type as variation_discount_type',
                        DB::raw("
                                    CASE
                                        WHEN iv.discount_type = 'percentage' THEN (CAST(iv.price AS numeric) * c.quantity) * (iv.discount / 100)
                                        WHEN iv.discount_type = 'fixed' THEN iv.discount_amount
                                        ELSE 0
                                    END as variation_discount_amount
                                "),

                        'ad.campaign_discount_type',
                        'ad.campaign_discount_value',
                        DB::raw('COALESCE(ad.campaign_discount_amount, 0) as campaign_discount_amount'),

                        DB::raw("
                                    (CAST(iv.price AS numeric) * c.quantity)
                                    - CASE
                                        WHEN iv.discount_type = 'percentage' THEN (CAST(iv.price AS numeric) * c.quantity) * (iv.discount / 100)
                                        WHEN iv.discount_type = 'fixed' THEN iv.discount_amount
                                        ELSE 0
                                    END
                                    - COALESCE(ad.campaign_discount_amount, 0)
                                    as price_after_discount
                                "),

                        'i.excise_status',
                        'i.excise_type',
                        'i.excise_percentage',
                        'i.excise_value',
                        DB::raw("
                                    CASE
                                        WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN
                                            (
                                                (CAST(iv.price AS numeric) * c.quantity)
                                                - CASE
                                                    WHEN iv.discount_type = 'percentage' THEN (CAST(iv.price AS numeric) * c.quantity) * (iv.discount / 100)
                                                    WHEN iv.discount_type = 'fixed' THEN iv.discount_amount
                                                    ELSE 0
                                                END
                                                - COALESCE(ad.campaign_discount_amount, 0)
                                            ) * (i.excise_percentage / 100)
                                        WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
                                        ELSE 0
                                    END as excise_amount
                                "),

                        'i.vat_percent',
                    ])
                    ->get();


                $result = $result->map(function ($item) {

                    $image = DB::table('item_images')
                        ->where('item_id', $item->productid)
                        ->value('image');

                    $item->image = $image
                        ? url('storage/items/' . $image)
                        : null;

                    $priceAfterExcise = $item->price_after_discount + $item->excise_amount;
                    $vatAmount        = $priceAfterExcise * ($item->vat_percent / 100);
                    $finalTotal       = $priceAfterExcise + $vatAmount;

                    $item->price_after_excise = round($priceAfterExcise, 2);
                    $item->vat_amount         = round($vatAmount, 2);
                    $item->final_total        = round($finalTotal, 2);

                    $item->variation_discount_label = 'No item discount';
                    if ($item->variation_discount_type === 'percentage') {
                        $item->variation_discount_label = $item->variation_discount_value . '% off (item)';
                    } elseif ($item->variation_discount_type === 'fixed') {
                        $item->variation_discount_label = 'Rs. ' . number_format($item->variation_discount_amount, 2) . ' off (item)';
                    }

                    $item->campaign_discount_label = 'No campaign discount';
                    if ($item->campaign_discount_type === 'percentage') {
                        $item->campaign_discount_label = $item->campaign_discount_value . '% off (campaign)';
                    } elseif ($item->campaign_discount_type === 'amount') {
                        $item->campaign_discount_label = 'Rs. ' . number_format($item->campaign_discount_amount, 2) . ' off (campaign)';
                    }

                    $item->excise_label = 'No excise';
                    $item->pricebeforediscount = $item->subtotal_amount + $vatAmount + $priceAfterExcise + $vatAmount;

                    $item->min_qty = $item->min_value ?? null;
                    if ($item->excise_status === 'Y') {
                        $item->excise_label = $item->excise_type === 'percentage'
                            ? $item->excise_percentage . '% excise'
                            : 'Rs. ' . number_format($item->excise_value, 2) . ' fixed excise';
                    }

                    $item->vat_label = $item->vat_percent . '% VAT';

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
