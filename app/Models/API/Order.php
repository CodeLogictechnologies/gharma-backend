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

            // ── Generate Voucher Number ──────────────────────────
            $lastOrder = DB::table('order_masters')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->orderByDesc('voucher_number')
                ->first();

            if (!$lastOrder) {
                $voucherNumber = 1;
            } else {
                // Cast to integer before adding
                $voucherNumber = (int) $lastOrder->voucher_number + 1;
            }

            // Format as string with leading zeros (optional)
            $voucherNumberFormatted = str_pad($voucherNumber, 6, '0', STR_PAD_LEFT);

            $insertOrderMaster = [
                'id'                        => $orderMasterId,
                'orgid'                     => $post['orgid'],
                'payment_method'            => $post['paymentmethod'] ?? 'COD',
                'voucher_number'            => $voucherNumberFormatted,
                'userid'                    => $post['userid'],
                'addressid'                 => $post['addressid'],
                'order_master_subtotal'     => round($grandSubtotal, 2),
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
                ->select('om.id as ordermasterid', 'i.title', 'v.value', 'od.price', 'od.quantity', 'od.order_detail_total_price', 'u.name', 'od.excise_type', 'od.excise_percent', 'od.excise_amount', 'od.vat_amount', 'i.vat_percent', 'v.discount_type', 'v.discount_amount', 'om.discount_amount as extra_discount')
                ->get();

            $voucherDetail = DB::table('order_masters as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->where('om.id', $orderMasterId)
                ->select('om.id as ordermasterid', 'om.voucher_number', 'om.created_at', 'u.name')
                ->first();

            // Get vendor details
            $vendors = DB::table('vendors')
                ->select('id as vendorid', 'name as vendorname', 'tax_number as vendorpan')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->first();

            // Get user address
            $address = DB::table('user_addresses')
                ->where('id', $post['addressid'])
                ->where('userid', $post['userid'])
                ->first();

            // Prepare data for PDF
            $pdfData = [
                'orderDetail'   => $orderDetail,
                'voucherDetail'        => $voucherDetail,
            ];

            // ── Generate PDF Invoice ────────────────────────────
            $fileName = 'invoice_' . $voucherNumberFormatted . '_' . time() . '.pdf';
            $filePath = 'invoices/' . $fileName;

            // Generate and store the PDF
            $pdf = Pdf::loadView('backend.order.invoice_pdf', $pdfData)
                ->setPaper('a4', 'portrait');

            // Store in public disk under invoices folder
            Storage::disk('public')->put($filePath, $pdf->output());

            // Get full URL for the PDF
            $pdfUrl = asset('storage/' . $filePath);

            // ── Return success with invoice data ────────────────
            return $pdfUrl;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
