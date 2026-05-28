<?php

namespace App\Models;

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class AssignDriver extends Model
{
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
            if (!empty($post['type'] == 'all')) {
                $result = DB::table('order_masters as m')
                    ->join('users as u', 'u.id', '=', 'm.userid')
                    ->join('profiles as p', 'p.user_id', '=', 'u.id')
                    ->join('user_addresses as a', 'a.userid', '=', 'm.userid')
                    ->join('assign_drivers as d', 'd.ordermasterid', '=', 'm.id')
                    ->select(
                        DB::raw("CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS customer_name"),
                        'u.phone',
                        'a.address_name',
                        'a.longitude',
                        'a.latitude'
                    )
                    ->where('d.driverid', $post['userid'])
                    ->where('m.orgid', $post['orgid'])
                    ->get();
            } else {
                $result = DB::table('assign_drivers as d')
                    ->select(
                        DB::raw('DATE(delivi) as assign_date'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw("SUM(CASE WHEN order_status = 'Completed' THEN 1 ELSE 0 END) as completed_orders")
                    )
                    ->where('driverid', $post['userid'])
                    ->where('orgid', $post['orgid'])
                    ->groupBy(DB::raw('DATE(del)'))
                    ->orderBy('assign_date', 'desc')
                    ->get();
            }
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}