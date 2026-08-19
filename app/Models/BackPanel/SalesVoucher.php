<?php

namespace App\Models\BackPanel;

use App\Models\API\OrderDetail;
use App\Models\API\OrderMaster;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BsdateController;


class SalesVoucher extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function list($post)
    {
        try {
            $cond     = "1=1";
            $bindings = [];

            if (!empty($post['sSearch_1'])) {
                $val        = strtolower(trim($post['sSearch_1']));
                $cond      .= " and lower(om.voucher_number) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['sSearch_3'])) {
                $val        = strtolower(trim($post['sSearch_3']));
                $cond      .= " and lower(u.name) like ?";
                $bindings[] = "%{$val}%";
            }

            $limit  = isset($post['iDisplayLength']) ? (int) $post['iDisplayLength'] : 15;
            $offset = isset($post['iDisplayStart'])  ? (int) $post['iDisplayStart']  : 0;

            $baseQuery = DB::table('order_masters as om')
                ->join('order_details as od', 'od.ordermasterid', '=', 'om.id')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->where('om.orgid', $post['orgid']);

            $totalrecs = (clone $baseQuery)
                ->select('om.id')
                ->groupBy('om.id')
                ->get()
                ->count();

            $query = (clone $baseQuery)
                ->select(
                    'om.id',
                    'u.name',
                    'om.sales_vouchers_date_nep as created_at',
                    'om.voucher_number',
                    'u.email',
                    'u.phone'
                )
                ->whereRaw($cond, $bindings)
                ->groupBy('om.id', 'u.name', 'om.created_at', 'om.voucher_number', 'u.email', 'u.phone')
                ->orderBy('om.created_at', 'desc');

            $filteredCount = DB::query()
                ->fromSub($query->clone(), 'grouped')
                ->count();

            $result = $limit > -1
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            return [
                'data'              => $result,
                'totalrecs'         => $totalrecs,
                'totalfilteredrecs' => $filteredCount,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* Generate a voucher number unique across sales_vouchers.voucher_no and order_masters.voucher_number for this org */
    public static function generateUniqueVoucherNo($post)
    {
        try {
            $lastVoucher = DB::table('order_masters')
                ->select('voucher_number')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderByDesc('voucher_number')
                ->first();


            // if (!$lastVoucher) {
            //     return 1;
            // }
            // return $lastVoucher->voucher_number + 1;
                        if (!$lastVoucher || $lastVoucher->voucher_number === null || $lastVoucher->voucher_number === '') {
                return 1;
            }

            $numericPart = (int) preg_replace('/[^0-9]/', '', (string) $lastVoucher->voucher_number);

            return $numericPart + 1;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /* Retailer (flat, discounted) and wholesaler (qty-tiered) pricing for a single item variation */
    public static function getItemPricing($post)
    {
        $itemId      = $post['item_id'] ?? null;
        $variationId = $post['variation_id'] ?? null;

        $retailer       = null;
        $wholesaleTiers = [];
        $remaining      = 0;

        if ($itemId && $variationId) {

            $variation = DB::table('itemvariations')
                ->where('id', $variationId)
                ->where('item_id', $itemId)
                ->where('status', 'Y')
                ->select('price', 'discount_type', 'discount', 'discount_amount')
                ->first();

            if ($variation) {
                $price = (float) $variation->price;

                // Active discount from the Discount module (handles BOTH percentage and fixed)
                $activeDiscount = DB::table('discount_details as dd')
                    ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
                    ->where('dd.variation_id', $variationId)
                    ->where('dd.orgid', $post['orgid'])
                    ->where('dd.status', 'Y')
                    ->where('dm.status', 'Y')
                    ->where('dm.start_date_ad', '<=', now())
                    ->where('dm.end_date_ad', '>=', now())
                    ->orderByDesc('dm.created_at')
                    ->select('dd.discount_type', 'dd.discount_value', 'dd.total_amount')
                    ->first();

                if ($activeDiscount) {
                    $mappedType = $activeDiscount->discount_type === 'percentage' ? 'percentage' : 'fixed';

                    $retailer = [
                        'price'               => round($price, 2),
                        'effective_price'     => round((float) $activeDiscount->total_amount, 2),
                        'discount_type'       => $mappedType,
                        'discount_percentage' => $mappedType === 'percentage' ? $activeDiscount->discount_value : null,
                        'discount_amount'     => $mappedType === 'fixed' ? $activeDiscount->discount_value : null,
                    ];
                } else {
                    $discount = 0;

                    if ($variation->discount_type === 'percentage' && $variation->discount !== null) {
                        $discount = $price * (float) $variation->discount / 100;
                    } elseif ($variation->discount_type === 'fixed' && $variation->discount_amount !== null) {
                        $discount = (float) $variation->discount_amount;
                    }

                    $retailer = [
                        'price'               => round($price, 2),
                        'effective_price'     => round(max($price - $discount, 0), 2),
                        'discount_type'       => $variation->discount_type,
                        'discount_percentage' => $variation->discount,
                        'discount_amount'     => $variation->discount_amount,
                    ];
                }
            }

            $wholesalerMasterId = DB::table('wholesaler_prices')
                ->where('itemid', $itemId)
                ->where('variation_id', $variationId)
                ->where('status', 'Y')
                ->value('id');

            if ($wholesalerMasterId) {
                $wholesaleTiers = DB::table('wholesaler_price_details')
                    ->where('wholesalermasterid', $wholesalerMasterId)
                    ->where('status', 'Y')
                    ->orderBy('min_qty')
                    ->select('min_qty', 'max_qty', 'price')
                    ->get();
            }

            $remaining = Itemvariation::remainingStock(
                $variationId,
                $post['orgid'],
                $post['exclude_ordermasterid'] ?? null
            );
        }

        return [
            'retailer'        => $retailer,
            'wholesale_tiers' => $wholesaleTiers,
            'remaining'       => $remaining,
        ];
    }

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            if (!empty($post['id'])) {
                $post['voucher_no'] = DB::table('order_masters')
                    ->where('id', $post['id'])
                    ->value('voucher_number');
            } else {
                $post['voucher_no'] = self::generateUniqueVoucherNo($post);
            }
            $orderMasterId = (string) Str::uuid();

            $insertOrderDetails = [];
            $variationIds       = [];

            $grandSubtotal = 0;
            $grandExcise   = 0;
            $grandVat      = 0;
            $grandTotal    = 0;

            $customer_id = $post['customer_id'];

            foreach ($post['items'] as $item) {

                $variation = DB::table('itemvariations as iv')
                    ->join('items as i', 'i.id', '=', 'iv.item_id')
                    ->where('iv.id', $item['variation_id'])
                    ->where('iv.orgid', $post['orgid'])
                    ->select(
                        'iv.id as variation_id',
                        'iv.price',
                        'i.excise_status',
                        'i.excise_type',
                        'i.excise_percentage',
                        'i.excise_value',
                        'i.vat_status',
                        'iv.discount_type',
                        'iv.discount_amount',
                        'i.vat_percent'
                    )
                    ->first();

                if (!$variation) {
                    throw new \Exception("Item or variation not found for item_id: {$item['item_id']}");
                }
                $qty = (float) $item['qty'];
                $unitPrice = (float) $item['unit_rate'];

                $baseAmount = round($unitPrice * $qty, 2);

                $listPrice = (float) preg_replace('/[^0-9.\-]/', '', $variation->price);

                /*
                 * Discount amount — prefer the exact discount that was shown/applied on the
                 * form at sale time (submitted from the row's discount_type/discount_value
                 * hidden inputs, sourced from getItemPricing()'s "retailer" block). This is
                 * the authoritative value because it reflects the actual Discount-module rule
                 * (or item-level discount) that was active when the sale happened.
                 *
                 * Falls back to reconstructing from (listPrice - unitPrice) * qty only when
                 * those fields aren't present in the submission — e.g. rows saved before this
                 * change, or wholesaler rows which don't carry a retailer discount at all.
                 * NOTE: that fallback can be inaccurate if itemvariations.price has since
                 * drifted from the price the discount was originally configured against.
                 */
                $lineDiscountType  = $item['discount_type'] ?? null;
                $lineDiscountValue = isset($item['discount_value']) ? (float) $item['discount_value'] : null;

                if ($lineDiscountType === 'percentage' && $lineDiscountValue !== null) {
                    $discountAmount = round(($listPrice * $lineDiscountValue / 100) * $qty, 2);
                } elseif ($lineDiscountType === 'fixed' && $lineDiscountValue !== null) {
                    $discountAmount = round($lineDiscountValue * $qty, 2);
                } else {
                    $discountAmount = round(max(($listPrice - $unitPrice) * $qty, 0), 2);
                }

                $amountAfterDiscount = $baseAmount;

                $exciseAmount = 0.00;

                if ($variation->excise_status === 'Y') {
                    if ($variation->excise_type === 'percentage') {
                        $exciseAmount = round(
                            $amountAfterDiscount * ((float) $variation->excise_percentage / 100),
                            2
                        );
                    } elseif ($variation->excise_type === 'fixed') {
                        $exciseAmount = round(
                            (float) ($variation->excise_value ?? 0) * $qty,
                            2
                        );
                    }
                }

                $amountAfterExcise = round($amountAfterDiscount + $exciseAmount, 2);

                $vatAmount = 0.00;
                $vatRate = (float) ($variation->vat_percent ?? 0);

                if ($variation->vat_status === 'Y') {
                    $vatAmount = round(
                        $amountAfterExcise * ($vatRate / 100),
                        2
                    );
                }

                $lineTotal = round($amountAfterExcise + $vatAmount, 2);

                $insertOrderDetails[] = [
                    'id'                             => (string) Str::uuid(),
                    'ordermasterid'                  => $orderMasterId,
                    'variation_id'                   => $variation->variation_id,
                    'quantity'                       => $qty,
                    'userid'                         => $customer_id,
                    'price'                          => $unitPrice,
                    'discount_type'                  => $lineDiscountType ?? $variation->discount_type,
                    'discount_amount'                => $lineDiscountValue ?? ($variation->discount_amount ?? 0),
                    'discount_amount_per_variation'  => $discountAmount,
                    'excise_type'                    => $variation->excise_status === 'Y'
                        ? $variation->excise_type
                        : null,
                    'excise_percent'                 => $variation->excise_type === 'percentage'
                        ? $variation->excise_percentage
                        : 0,
                    'excise_amount'                  => $exciseAmount,
                    'vat_percent'                    => $variation->vat_status === 'Y'
                        ? $vatRate
                        : 0,
                    'vat_amount'                     => $vatAmount,
                    'order_detail_total_price'       => $lineTotal,
                    'created_at'                     => Carbon::now(),
                ];

                $variationIds[] = $variation->variation_id;

                $grandSubtotal += $baseAmount;
                $grandExcise   += $exciseAmount;
                $grandVat      += $vatAmount;
                $grandTotal    += $lineTotal;
            }

            $bsdate = new BsdateController;
            $sales_vouchers_date_eng = $bsdate->nep_to_eng($post['voucher_date']);

            $insertOrderMaster = [
                'id'                        => $orderMasterId,
                'sales_vouchers_date_nep'   => $post['voucher_date'],
                'sales_vouchers_date_eng'   => $sales_vouchers_date_eng,
                'orgid'                     => $post['orgid'],
                'payment_method'            => $post['paymentmethod'] ?? 'COD',
                'voucher_number'            => $post['voucher_no'],
                'userid'                    => $post['customer_id'],
                'addressid'                 => $post['addressid'] ?? null,
                'order_master_subtotal'     => $post['subtotal'],
                'discount_amount'           => $post['discount_amount'] ?? 0,
                'order_master_excise_total' => $post['excise_amount'],
                'order_master_vat_total'    => $post['vat_amount'],
                'order_master_total_price'  => $post['total_amount'],
                'created_at'                => Carbon::now(),
            ];

            // Only stamp fiscal_year_id when creating a brand-new voucher, not on edit
            if (empty($post['id'])) {
                $orgFiscalYearId = DB::table('organizations')
                    ->where('id', $post['orgid'])
                    ->value('current_fiscal_year_id');

                $insertOrderMaster['fiscal_year_id'] = $orgFiscalYearId;
            }

            if (!OrderMaster::insert($insertOrderMaster)) {
                throw new \Exception("Couldn't save order.");
            }

            if (!OrderDetail::insert($insertOrderDetails)) {
                throw new \Exception("Couldn't save order details.");
            }

            $setup = DB::table('loyalty_setups')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->where('minprice', '<=', $grandTotal)
                ->where('maxprice', '>=', $grandTotal)
                ->first();

            if ($setup) {
                $earnedPoint = ($grandTotal * $setup->percentage) / 100;

                $existingLoyalty = DB::table('loyalties')
                    ->where('userid', $post['userid'])
                    ->where('orgid', $post['orgid'])
                    ->first();

                if ($existingLoyalty) {
                    DB::table('loyalties')
                        ->where('id', $existingLoyalty->id)
                        ->update([
                            'loyaltypoint' => $existingLoyalty->loyaltypoint + $earnedPoint,
                            'updated_at'   => Carbon::now(),
                            'updatedby'    => $post['userid'],
                        ]);
                } else {
                    DB::table('loyalties')->insert([
                        'id'              => (string) Str::uuid(),
                        'userid'          => $post['userid'],
                        'orgid'           => $post['orgid'],
                        'order_detail_id' => $insertOrderDetails[0]['id'],
                        'loyaltypoint'    => $earnedPoint,
                        'status'          => 'Y',
                        'postedby'        => $post['userid'],
                        'created_at'      => Carbon::now(),
                    ]);
                }
            }

            DB::commit();
            return $orderMasterId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getData($post)
    {
        $id = $post['id'] ?? null;
        if (empty($id)) {
            throw new Exception('Order ID is required.');
        }
        $result = DB::table('order_details as od')
            ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
            ->join('users as u', 'u.id', '=', 'om.userid')
            ->join('itemvariations as v', 'v.id', '=', 'od.variation_id')
            ->join('items as i', 'i.id', '=', 'v.item_id')
            ->where('od.ordermasterid', $post['id'])
            ->select(
                'om.id as ordermasterid',
                'i.title',
                'i.product_code as item_product_code',
                'v.value',
                'v.product_code as variation_product_code',
                'od.price',
                'od.quantity',
                'od.order_detail_total_price',
                'u.name',
                'od.excise_type',
                'od.excise_percent',
                'od.excise_amount',
                'od.vat_amount',
                'i.vat_percent',
                'od.discount_type',                   // stored at sale time
                'od.discount_amount',                 // stored % or fixed rate applied
                'od.discount_amount_per_variation',    // actual ₹ discount deducted
                'om.discount_amount as extra_discount'
            )
            ->get();

        return $result;
    }

    public static function getVoucherData($post)
    {
        $id = $post['id'] ?? null;
        if (empty($id)) {
            throw new Exception('Order ID is required.');
        }

        $result = DB::table('order_masters as om')
            ->join('users as u', 'u.id', '=', 'om.userid')
            ->where('om.id', $post['id'])
            ->select(
                'om.id as ordermasterid',
                'om.voucher_number',
                'om.created_at',
                'u.name',
                'om.order_master_subtotal as subtotal',
                'om.discount_amount as bill_discount_amount',
                'om.order_master_excise_total as excise_amount',
                'om.order_master_vat_total as vat_amount',
                'om.order_master_total_price as total_amount'
            )
            ->first();

        return $result;
    }

    public static function deleteDate($post)
    {
        try {
            $updated = DB::table('sales_vouchers')
                ->where('id', $post['id'])
                ->where('orgid', $post['orgid'])
                ->update([
                    'status'     => 'N',
                    'updatedby'  => $post['userid'],
                    'updated_at' => Carbon::now(),
                ]);

            if (!$updated) {
                throw new Exception("Couldn't delete record. Please try again", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Delivered orders for a customer without an existing sales voucher ── */
    public static function getCustomerOrders($post)
    {
        try {
            return DB::table('order_masters as om')
                ->select('om.id', 'om.created_at', 'om.order_master_total_price')
                ->where('om.orgid', $post['orgid'])
                ->where('om.userid', $post['customer_id'])
                ->where('om.order_status', 'Delivered')
                ->where('om.status', 'Y')
                ->whereNotExists(function ($query) use ($post) {
                    $query->select(DB::raw(1))
                        ->from('sales_vouchers as sv')
                        ->whereColumn('sv.order_id', 'om.id')
                        ->where('sv.status', 'Y')
                        ->when(!empty($post['exclude_voucher_id']), function ($q) use ($post) {
                            $q->where('sv.id', '!=', $post['exclude_voucher_id']);
                        });
                })
                ->orderBy('om.created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Sales vouchers for a customer without an existing sales return ── */
    public static function getCustomerVouchers($post)
    {
        try {
            return DB::table('sales_vouchers as sv')
                ->select('sv.id', 'sv.voucher_no', 'sv.voucher_date')
                ->where('sv.orgid', $post['orgid'])
                ->where('sv.customer_id', $post['customer_id'])
                ->where('sv.status', 'Y')
                ->whereNotExists(function ($query) use ($post) {
                    $query->select(DB::raw(1))
                        ->from('sales_return_vouchers as srv')
                        ->whereColumn('srv.against_voucher_id', 'sv.id')
                        ->where('srv.status', 'Y')
                        ->when(!empty($post['exclude_return_id']), function ($q) use ($post) {
                            $q->where('srv.id', '!=', $post['exclude_return_id']);
                        });
                })
                ->orderBy('sv.voucher_date', 'desc')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── Line items of a given order (to auto-populate the sales form) ── */
    public static function getOrderItems($post)
    {
        try {
            return DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('itemvariations as iv', 'iv.id', '=', 'od.variation_id')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->select(
                    'i.id as item_id',
                    'i.title as item_title',
                    'iv.id as variation_id',
                    'iv.attribute as variation_attribute',
                    'iv.value as variation_value',
                    'od.quantity as qty',
                    'od.price as unit_rate'
                )
                ->where('od.ordermasterid', $post['order_id'])
                ->where('om.orgid', $post['orgid'])
                ->where('od.status', 'Y')
                ->orderBy('od.created_at')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}