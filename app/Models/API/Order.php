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
            $insertOrderMaster = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'userid'     => $post['userid'],
                'addressid'     => $post['addressid'],
                'order_master_total_price'      => $post['total'],
                'created_at' => Carbon::now(),
            ];

            if (!OrderMaster::insert($insertOrderMaster)) {
                throw new \Exception("Couldn't save order.");
            }

            $insertOrderStatusArray = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'customerid'     => $post['userid'],
                'ordermasterid' => $insertOrderMaster['id'],
                'created_at' => Carbon::now(),
                'postedby'     => $post['userid'],
            ];

            if (!OrderStatus::insert($insertOrderStatusArray)) {
                throw new \Exception("Couldn't save order.");
            }
            // ── Build Order Details Array ──────────────────────
            $insertOrderDetails = [];
            $variationIds       = [];

            foreach ($post['items'] as $item) {
                $insertOrderDetails[] = [
                    'id'            => (string) Str::uuid(),
                    'ordermasterid' => $insertOrderMaster['id'],
                    'variation_id'  => $item['variation_id'],
                    'quantity'      => $item['quantity'],
                    'userid'     => $post['userid'],
                    'price'         => $item['price'],
                    'order_detail_total_price'         => $item['quantity'] * $item['price'],
                    'created_at'    => Carbon::now(),
                ];

                $variationIds[] = $item['variation_id'];
            }

            if (!OrderDetail::insert($insertOrderDetails)) {
                throw new \Exception("Couldn't save order details.");
            }

            $cartArray = [
                'status' => 'N',
                'updated_at'    => Carbon::now(),
            ];

            APICart::where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->whereIn('variation_id', $variationIds)
                ->where('status', 'Y')
                ->delete();
            // ->update($cartArray);
            
            $setup = DB::table('loyalty_setups')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->where('minprice', '<=', $post['total'])
                ->where('maxprice', '>=', $post['total'])
                ->first();

            if ($setup) {

                // Calculate points
                $earnedPoint = ($post['total'] * $setup->percentage) / 100;

                // Check existing loyalty
                $existingLoyalty = DB::table('loyalties')
                    ->where('userid', $post['userid'])
                    ->where('orgid', $post['orgid'])
                    ->first();

                if ($existingLoyalty) {

                    // Update existing point
                    DB::table('loyalties')
                        ->where('id', $existingLoyalty->id)
                        ->update([
                            'loyaltypoint' => $existingLoyalty->loyaltypoint + $earnedPoint,
                            'updated_at'   => Carbon::now(),
                            'updatedby'    => $post['userid'],
                        ]);
                } else {

                    // Insert new loyalty
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
$invoiceData = [
    'orgid'  => $post['orgid'],
    'userid' => $post['userid'],
    'id'     => $insertOrderMaster['id'],
];

$orderDetail = BackPanelOrder::getDataInvoice($invoiceData);

$invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
$storagePath   = 'pdf/invoice-' . $invoiceNumber . '.pdf';

if (Storage::disk('public')->exists($storagePath)) {
    $pdfContent = Storage::disk('public')->get($storagePath);
} else {
    $pdf = Pdf::loadView('backend.invoice.download', [
        'order'         => $orderDetail,
        'invoiceNumber' => $invoiceNumber,
    ])->setPaper('a4', 'portrait');

    $pdfContent = $pdf->output();
    Storage::disk('public')->put($storagePath, $pdfContent);
}

$invoiceId = (string) Str::uuid();

DB::table('invoices')->insert([
    'id'            => $invoiceId,
    'ordermasterid' => $insertOrderMaster['id'],
    'orgid'         => $post['orgid'],
    'invoicenumber' => $invoiceNumber,
    'storagepath'   => $storagePath,
    'postedby'      => $post['userid'],
    'created_at'    => Carbon::now(),
    'updated_at'    => Carbon::now(),
]);

return [
    'ordermasterid' => $insertOrderMaster['id'],
    'invoicenumber' => $invoiceNumber,
    'storagepath'   => $storagePath,
    'storage_url'   => url('storage/' . $storagePath),
];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}