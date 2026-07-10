<?php

namespace App\Models\API;

use App\Models\API\Cart as APICart;
use App\Models\Cart;
use App\Models\API\OrderDetail;
use App\Models\API\OrderMaster;
use App\Models\OrderStatus;
use App\Models\BackPanel\Order as BackPanelOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class Order extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {
            // ── Order Master ───────────────────────────────────

            $orderMasterId = (string) Str::uuid();

            // ── Build Order Details + Compute Real Prices ──────
            $insertOrderDetails = [];
            $variationIds       = [];

            $grandSubtotal = 0;
            $grandExcise   = 0;
            $grandVat      = 0;
            $grandTotal    = 0;

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
                        'i.vat_percent'
                    )
                    ->first();

                if (!$variation) {
                    throw new \Exception("Invalid item variation.");
                }

                $qty       = (float) $item['quantity'];
                $unitPrice = (float) preg_replace('/[^0-9.\-]/', '', $variation->price);

                $baseAmount = round($unitPrice * $qty, 2);
                // Excise duty
                $exciseAmount = 0.0;
                if ($variation->excise_status === 'Y') {
                    if ($variation->excise_type === 'percentage') {
                        $exciseAmount = round($baseAmount * ((float) $variation->excise_percentage / 100), 2);
                    } elseif ($variation->excise_type === 'fixed') {
                        $exciseAmount = round((float) $variation->excise_value * $qty, 2);
                    }
                }
                $amountAfterExcise = round($baseAmount + $exciseAmount, 2);
                $amountAfterExcise = round($baseAmount + $exciseAmount, 2);
                // VAT (applied on price + excise)
                $vatAmount = 0.0;
                $vatRate   = (float) ($variation->vat_percent ?? 0);
                if ($variation->vat_status === 'Y') {
                    $vatAmount = round($amountAfterExcise * ($vatRate / 100), 2);
                }

                $lineTotal = round($amountAfterExcise + $vatAmount, 2);

                $insertOrderDetails[] = [
                    'id'                        => (string) Str::uuid(),
                    'ordermasterid'             => $orderMasterId,
                    'variation_id'              => $variation->variation_id,
                    'quantity'                  => $qty,
                    'userid'                    => $post['userid'],
                    'price'                     => $unitPrice,
                    'excise_type'               => $variation->excise_status === 'Y' ? $variation->excise_type : null,
                    'excise_percent'            => $variation->excise_type === 'percentage' ? $variation->excise_percentage : null,
                    'excise_amount'             => $exciseAmount,
                    'vat_percent'               => $variation->vat_status === 'Y' ? $vatRate : 0,
                    'vat_amount'                => $vatAmount,
                    'order_detail_total_price'  => $lineTotal,
                    'created_at'                => Carbon::now(),
                ];

                $variationIds[] = $variation->variation_id;

                $grandSubtotal += $baseAmount;
                $grandExcise   += $exciseAmount;
                $grandVat      += $vatAmount;
                $grandTotal    += $lineTotal;
            }

            $voucherNumber = 'VCH-' . strtoupper(Str::random(8));

            $insertOrderMaster = [
                'id'                        => $orderMasterId,
                'orgid'                     => $post['orgid'],
                'payment_method' => $post['paymentmethod'] ?? 'COD',
                'voucher_number'            => $voucherNumber,
                'userid'                    => $post['userid'],
                'addressid'                 => $post['addressid'],
                'order_master_subtotal'     => round($grandSubtotal, 2),
                'order_master_excise_total' => round($grandExcise, 2),
                'order_master_vat_total'    => round($grandVat, 2),
                'order_master_total_price'  => round($grandTotal, 2),
                'created_at'                => Carbon::now(),
            ];
            if (!OrderMaster::insert($insertOrderMaster)) {
                throw new \Exception("Couldn't save order.");
            }

            // $insertOrderStatusArray = [
            //     'id'             => (string) Str::uuid(),
            //     'orgid'          => $post['orgid'],
            //     'payment_method' => $post['paymentmethod'] ?? 'COD',
            //     'customerid'     => $post['userid'],
            //     'ordermasterid'  => $orderMasterId,
            //     'created_at'     => Carbon::now(),
            //     'postedby'       => $post['userid'],
            // ];

            // if (!OrderStatus::insert($insertOrderStatusArray)) {
            //     throw new \Exception("Couldn't save order.");
            // }

            if (!OrderDetail::insert($insertOrderDetails)) {
                throw new \Exception("Couldn't save order details.");
            }

            APICart::where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->whereIn('variation_id', $variationIds)
                ->where('status', 'Y')
                ->delete();

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

            // ── Generate Invoice PDF ────────────────────────────
            // $invoiceData = [
            //     'orgid'  => $post['orgid'],
            //     'userid' => $post['userid'],
            //     'id'     => $orderMasterId,
            // ];

            // $orderDetail = BackPanelOrder::getDataInvoice($invoiceData);

            // $adminUserId = DB::table('userorganizations as uo')
            //     ->join('model_has_roles as mr', 'mr.model_id', '=', 'uo.userid')
            //     ->where('mr.role_id', '550e8400-e29b-41d4-a716-446655440001')
            //     ->where('uo.orgid', $post['orgid'])
            //     ->where('uo.status', 'Y')
            //     ->value('uo.userid');

            // if ($adminUserId) {
            //     $token = DB::table('userdevicetokens')
            //         ->where('userid', $adminUserId)
            //         ->value('devicetoken');

            //     if (!empty($token)) {
            //         try {
            //             app(\App\Services\FirebaseService::class)->sendNotification(
            //                 $token,
            //                 'New Order Received',
            //                 'A new order has been placed. Please check the admin panel for the order details.'
            //             );
            //         } catch (\Exception $e) {
            //             \Log::error('Failed to send order notification.', [
            //                 'user_id'  => $adminUserId,
            //                 'order_id' => $orderMasterId,
            //                 'message'  => $e->getMessage(),
            //             ]);
            //         }
            //     }
            // }

            return [
                'ordermasterid' => $orderMasterId,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}