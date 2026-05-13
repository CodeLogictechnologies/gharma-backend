<?php

namespace App\Models\API;

use App\Models\API\Cart as APICart;
use App\Models\Cart;
use App\Models\API\OrderDetail;
use App\Models\API\OrderMaster;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

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
            $variationIds       = []; // collect variation ids for cart cleanup

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

                $variationIds[] = $item['variation_id']; // collect for cart delete
            }

            // ── Insert Order Details (outside loop) ────────────
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
                ->update($cartArray);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}