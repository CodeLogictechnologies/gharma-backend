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
            $grandDiscount = 0;
            $grandExcise   = 0;
            $grandVat      = 0;
            $grandTotal    = 0;

            foreach ($post['items'] as $item) {

                // Only trust variation_id + quantity from frontend
                $variationId = $item['variation_id'];
                $qty         = (float) $item['quantity'];

                if ($qty <= 0) {
                    throw new \Exception("Invalid quantity for one of the items.");
                }

                // ── Pull variation + item pricing/tax info from DB ──
                $variation = DB::table('itemvariations as iv')
                    ->join('items as i', 'i.id', '=', 'iv.item_id')
                    ->where('iv.id', $variationId)
                    ->where('iv.orgid', $post['orgid'])
                    ->where('iv.status', 'Y')
                    ->select(
                        'iv.id as variation_id',
                        'iv.price',
                        'iv.discount as variation_discount_value',
                        'iv.discount_type as variation_discount_type',
                        'iv.discount_amount as variation_discount_amount_raw',
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

                $unitPrice  = (float) preg_replace('/[^0-9.\-]/', '', $variation->price);
                $baseAmount = round($unitPrice * $qty, 2);

                // ── Variation-level discount (from itemvariations) ──
                $variationDiscountAmount = 0.0;
                if ($variation->variation_discount_type === 'percentage' && $variation->variation_discount_value !== null) {
                    $variationDiscountAmount = round($baseAmount * ((float) $variation->variation_discount_value / 100), 2);
                } elseif ($variation->variation_discount_type === 'fixed' && $variation->variation_discount_amount_raw !== null) {
                    $variationDiscountAmount = round((float) $variation->variation_discount_amount_raw * $qty, 2);
                }

                // ── Campaign-level discount (active discount_masters + discount_details) ──
                $campaignDiscount = DB::table('discount_details as dd')
                    ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
                    ->where('dd.variation_id', $variationId)
                    ->where('dd.status', 'Y')
                    ->where('dm.status', 'Y')
                    ->where('dm.orgid', $post['orgid'])
                    ->where(function ($q) {
                        $q->whereNull('dm.start_date_ad')
                            ->orWhere('dm.start_date_ad', '<=', now()->toDateString());
                    })
                    ->where(function ($q) {
                        $q->whereNull('dm.end_date_ad')
                            ->orWhere('dm.end_date_ad', '>=', now()->toDateString());
                    })
                    ->orderByDesc('dd.created_at')
                    ->select('dd.discount_type', 'dd.discount_value')
                    ->first();

                $campaignDiscountAmount = 0.0;
                if ($campaignDiscount) {
                    if ($campaignDiscount->discount_type === 'percentage') {
                        $campaignDiscountAmount = round($baseAmount * ((float) $campaignDiscount->discount_value / 100), 2);
                    } elseif ($campaignDiscount->discount_type === 'amount') {
                        $campaignDiscountAmount = round((float) $campaignDiscount->discount_value * $qty, 2);
                    }
                }

                $totalDiscount   = round($variationDiscountAmount + $campaignDiscountAmount, 2);
                $amountAfterDisc = round($baseAmount - $totalDiscount, 2);
                if ($amountAfterDisc < 0) {
                    $amountAfterDisc = 0; // never let discounts push price negative
                }

                // ── Excise (applied on discounted amount) ──
                $exciseAmount = 0.0;
                if ($variation->excise_status === 'Y') {
                    if ($variation->excise_type === 'percentage') {
                        $exciseAmount = round($amountAfterDisc * ((float) $variation->excise_percentage / 100), 2);
                    } elseif ($variation->excise_type === 'fixed') {
                        $exciseAmount = round((float) $variation->excise_value * $qty, 2);
                    }
                }
                $amountAfterExcise = round($amountAfterDisc + $exciseAmount, 2);

                // ── VAT (applied on price + excise) ──
                $vatAmount = 0.0;
                $vatRate   = (float) ($variation->vat_percent ?? 0);
                if ($variation->vat_status === 'Y') {
                    $vatAmount = round($amountAfterExcise * ($vatRate / 100), 2);
                }

                $lineTotal = round($amountAfterExcise + $vatAmount, 2);

                $insertOrderDetails[] = [
                    'id'                         => (string) Str::uuid(),
                    'ordermasterid'              => $orderMasterId,
                    'variation_id'               => $variation->variation_id,
                    'quantity'                   => $qty,
                    'userid'                     => $post['userid'],
                    'price'                      => $unitPrice,
                    'variation_discount_type'    => $variation->variation_discount_type,
                    'variation_discount_amount'  => $variationDiscountAmount,
                    'campaign_discount_type'     => $campaignDiscount->discount_type ?? null,
                    'campaign_discount_amount'   => $campaignDiscountAmount,
                    'total_discount_amount'      => $totalDiscount,
                    'excise_type'                => $variation->excise_status === 'Y' ? $variation->excise_type : null,
                    'excise_percent' => $variation->excise_type === 'percentage' ? $variation->excise_percentage : 0,
                    'excise_amount'              => $exciseAmount,
                    'vat_percent'                => $variation->vat_status === 'Y' ? $vatRate : 0,
                    'vat_amount'                 => $vatAmount,
                    'order_detail_total_price'   => $lineTotal,
                    'created_at'                 => Carbon::now(),
                ];

                $variationIds[] = $variation->variation_id;

                $grandSubtotal += $baseAmount;
                $grandDiscount += $totalDiscount;
                $grandExcise   += $exciseAmount;
                $grandVat      += $vatAmount;
                $grandTotal    += $lineTotal;
            }

            // ── Generate Voucher Number ──────────────────────────
            $lastOrder = DB::table('order_masters')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderByDesc('voucher_number')
                ->first();

            $voucherNumber = $lastOrder ? ((int) $lastOrder->voucher_number + 1) : 1;
            $voucherNumberFormatted = str_pad($voucherNumber, 6, '0', STR_PAD_LEFT);

            if (!empty($post['paymentmethod'])) {
                if ($post['paymentmethod'] == 'COD') {
                    $payment_status = 'Unpaid';
                } else {
                    $payment_status = 'Paid';
                }
            }

            $insertOrderMaster = [
                'id'                        => $orderMasterId,
                'orgid'                     => $post['orgid'],
                'payment_method'            => $post['paymentmethod'] ?? 'COD',
                'payment_status'            => $payment_status ?? 'Unpaid',
                'voucher_number'            => $voucherNumberFormatted,
                'userid'                    => $post['userid'],
                'addressid'                 => $post['addressid'],
                'order_master_subtotal'     => round($grandSubtotal, 2),
                'order_master_discount_total' => round($grandDiscount, 2),
                'order_master_excise_total' => round($grandExcise, 2),
                'order_master_vat_total'    => round($grandVat, 2),
                'order_master_total_price'  => round($grandTotal, 2),
                'status'                    => 'Y',
                'created_at'                => Carbon::now(),
            ];

            if (!OrderMaster::insert($insertOrderMaster)) {
                throw new \Exception("Couldn't save order.");
            }

            if (!OrderDetail::insert($insertOrderDetails)) {
                throw new \Exception("Couldn't save order details.");
            }

            // ── Clear Cart ──────────────────────────────────────
            APICart::where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->whereIn('variation_id', $variationIds)
                ->where('status', 'Y')
                ->delete();

            // ── Loyalty Points ──────────────────────────────────
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

            $orderDetail = DB::table('order_details as od')
                ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->join('itemvariations as v', 'v.id', '=', 'od.variation_id')
                ->join('items as i', 'i.id', '=', 'v.item_id')
                ->where('od.ordermasterid', $orderMasterId)
                ->select(
                    'om.id as ordermasterid',
                    'i.title',
                    'v.value',
                    'od.price',
                    'od.quantity',
                    'od.order_detail_total_price',
                    'u.name',
                    'od.excise_type',
                    'od.excise_percent',
                    'od.excise_amount',
                    'od.vat_amount',
                    'i.vat_percent',
                    'od.variation_discount_type',
                    'od.variation_discount_amount',
                    'od.campaign_discount_type',
                    'od.campaign_discount_amount',
                    'od.total_discount_amount'
                )
                ->get();

            $voucherDetail = DB::table('order_masters as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->where('om.id', $orderMasterId)
                ->select('om.id as ordermasterid', 'om.voucher_number', 'om.created_at', 'u.name')
                ->first();

            $vendors = DB::table('vendors')
                ->select('id as vendorid', 'name as vendorname', 'tax_number as vendorpan')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->first();

            $address = DB::table('user_addresses')
                ->where('id', $post['addressid'])
                ->where('userid', $post['userid'])
                ->first();

            $pdfData = [
                'orderDetail'    => $orderDetail,
                'voucherDetail'  => $voucherDetail,
            ];

            $fileName = 'invoice_' . $voucherNumberFormatted . '_' . time() . '.pdf';
            $filePath = 'invoices/' . $fileName;

            $pdf = Pdf::loadView('backend.order.invoice_pdf', $pdfData)
                ->setPaper('a4', 'portrait');

            Storage::disk('public')->put($filePath, $pdf->output());

            $pdfUrl = asset('storage/' . $filePath);

            return $pdfUrl;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
