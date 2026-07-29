<?php

namespace App\Models;

namespace App\Models\BackPanel;

use App\Models\API\LocationTracker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Services\FirebaseService;
use Locale;

class AssignDriver extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    public static function AssignDriver($post)
    {
        try {
            $dataArray = [
                'status' => 'Y',
                'orgid' => $post['orgid'],
                'deivery_date' => $post['deivery_date']
            ];
            // if (!empty($post['id'])) {
            //     $dataArray['updated_at'] = Carbon::now();
            // } else {
            $dataArray['id'] = (string) Str::uuid();
            $dataArray['ordermasterid'] = $post['ordermasterid'];
            $dataArray['driverid'] = $post['driver_id'];
            $dataArray['created_at'] = Carbon::now();

            if (!AssignDriver::insert($dataArray)) {
                throw new \Exception("Couldn't Assign Driver");
            }
            // }

            $updateOrderArray = [
                'order_status' => 'Shipped'
            ];

            $updateOrder = DB::table('order_masters')->where('id', $post['ordermasterid'])->update($updateOrderArray);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getOrderListApi($post)
    {
        try {
            $page    = (int) ($post['page']     ?? 1);
            $perPage = (int) ($post['per_page'] ?? 10);
            $offset  = ($page - 1) * $perPage;

            $query = DB::table('order_masters as m')
                ->join('users as u', 'u.id', '=', 'm.userid')
                ->join('profiles as p', 'p.user_id', '=', 'u.id')
                ->join('user_addresses as a', function ($join) {
                    $join->on('a.userid', '=', 'm.userid')
                        ->on('a.id', '=', 'm.addressid');
                })
                ->join('assign_drivers as d', function ($join) use ($post) {
                    $join->on('d.ordermasterid', '=', 'm.id')
                        ->where('d.driverid', $post['userid']);
                })
                ->select(
                    'm.id as order_id',
                    DB::raw("CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name,''), p.last_name) AS customer_name"),
                    'u.phone',
                    'a.address_name',
                    'a.longitude',
                    'a.latitude',
                    'm.created_at as order_date',
                    'm.order_master_total_price as total_price',
                    'd.id as assignorderid',
                    'd.created_at as assigned_at',
                    'd.order_status as order_status',
                    DB::raw('DATE(d.delivery_date) as delivery_date'),
                    'm.payment_status as payment_status',
                    'm.payment_method as payment_method',
                )
                ->where('m.orgid', $post['orgid']);

            if ($post['type'] === 'pending') {
                $query->whereIn('d.order_status', ['Pending', 'Start']);
            } else {
                $query->where('d.order_status', $post['type']);
            }

            $query->orderBy(DB::raw('DATE(d.delivery_date)'), 'desc');

            $total = DB::table(DB::raw("({$query->toSql()}) as sub"))
                ->mergeBindings($query)
                ->distinct()
                ->count(DB::raw('DATE(delivery_date)'));

            $results = $query->offset($offset)->limit($perPage)->get();

            $grouped = [];
            foreach ($results as $row) {
                $date = $row->delivery_date;
                if (!isset($grouped[$date])) {
                    $grouped[$date] = [
                        'delivery_date' => $date,
                        'orders'        => [],
                    ];
                }
                $grouped[$date]['orders'][] = [
                    'order_id'       => $row->order_id,
                    'customer_name'  => $row->customer_name,
                    'phone'          => $row->phone,
                    'address_name'   => $row->address_name,
                    'longitude'      => $row->longitude,
                    'latitude'       => $row->latitude,
                    'order_date'     => $row->order_date,
                    'total_price'    => $row->total_price,
                    'assignorderid'  => $row->assignorderid,
                    'assigned_at'    => $row->assigned_at,
                    'order_status'   => $row->order_status,
                    'payment_status'   => $row->payment_status,
                    'payment_method'   => $row->payment_method,
                ];
            }

            return [
                'total' => $total,
                'list'  => array_values($grouped),
            ];

            // return [
            //     'list'  => $results,
            //     'total' => $total,
            //     'page'  => $page,
            //     'per_page' => $perPage,
            // ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getCustomerDetail($post)
    {
        try {


            $query = DB::table('order_masters as m')
                ->join('users as u', 'u.id', '=', 'm.userid')
                ->join('profiles as p', 'p.user_id', '=', 'u.id')
                ->join('user_addresses as a', function ($join) {
                    $join->on('a.userid', '=', 'm.userid')
                        ->on('a.id', '=', 'm.addressid');
                })
                ->join('assign_drivers as d', function ($join) use ($post) {
                    $join->on('d.ordermasterid', '=', 'm.id')
                        ->where('d.driverid', $post['userid'])
                        ->whereIn('d.ordermasterid', $post['orderids']);
                })
                ->select(
                    'm.id as order_id',
                    DB::raw("CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name,''), p.last_name) AS customer_name"),
                    'u.phone',
                    'a.address_name',
                    'a.longitude',
                    'a.latitude',
                    'm.created_at as order_date',
                    'm.order_master_total_price as total_price',
                    'd.id as assignorderid',
                    'd.created_at as assigned_at',
                    'd.order_status as order_status',
                    'm.payment_status as payment_status',
                    'm.payment_method as payment_method',
                )
                ->where('m.orgid', $post['orgid'])
                ->orderBy('m.created_at', 'desc')->get();

            return $query;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function changeOrderStatus($post, $firebase)
    {
        try {
            $data = [];
            if ($post['status'] != 'Complete') {
                $result = DB::table('assign_drivers')
                    ->where('id', $post['assignorderid'])
                    ->where('orgid', $post['orgid'])
                    ->where('driverid', $post['userid'])
                    ->update([
                        'order_status' => $post['status']
                    ]);
            }

            if ($post['status'] === 'Start') {
                $insertOrderMaster = [
                    'id'         => (string) Str::uuid(),
                    'orgid'      => $post['orgid'],
                    'riderid'     => $post['userid'],
                    'latitude'      => $post['latitude'],
                    'longitude'      => $post['longitude'],
                    'orderid'      => $post['order_id'],
                    'created_at' => Carbon::now(),
                ];

                if (!LocationTracker::insert($insertOrderMaster)) {
                    throw new \Exception("Couldn't save location.");
                }

                $data = DB::table('location_trackers')
                    ->select('longitude', 'latitude')
                    ->where('riderid', $post['userid'])
                    ->first();
            }

            // if ($result === 0) {
            //     throw new \Exception('Order not found or no changes made.');
            // }

            if ($post['status'] === 'Complete') {

                $post['ordermasterid'] = $post['order_id'];

                $userid = DB::table('order_masters')->where('id', $post['ordermasterid'])->value('userid');

                $otp = (string) random_int(1000, 9999);

                DB::table('order_notification_otps')->insert([
                    'id'            => (string) Str::uuid(),
                    'orgid'         => $post['orgid'],
                    'customerid'    => $userid,
                    'ordermasterid' => $post['ordermasterid'],
                    'title'         => $post['title'] ?? 'Order confirmation',
                    'message'       => $post['message'] ?? 'Your Order confirmation code is',
                    'otp'           => $otp,
                    'postedby'      => $post['userid'],
                    'created_at'    => Carbon::now(),
                ]);

                $token = DB::table('userdevicetokens')
                    ->where('userid', $userid)
                    ->value('devicetoken');


                if ($token) {
                    try {
                        $firebase->sendNotification(
                            $token,
                            "Order Update",
                            "Your order confirmation OTP is " . $otp
                        );
                    } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                        \Log::warning('Stale FCM token removed.', ['userid' => $userid, 'token' => $token]);

                        DB::table('userdevicetokens')
                            ->where('userid', $userid)
                            ->where('devicetoken', $token)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('FCM send failed: ' . $e->getMessage(), ['userid' => $userid]);
                    }
                }

                // DB::table('order_masters')
                //     ->where('id', $post['order_id'])
                //     ->update([
                //         'order_status' => $post['Delivered']
                //     ]);
                return true;
            }

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
