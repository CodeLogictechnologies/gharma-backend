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
            $page    = (int) ($post['page']     ?? 1);
            $perPage = (int) ($post['per_page'] ?? 10);
            $offset  = ($page - 1) * $perPage;

            // FIX: operator precedence bug
            if (!empty($post['type']) && $post['type'] === 'all') {

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
                    )
                    ->where('m.orgid', $post['orgid'])
                    ->orderBy('m.created_at', 'desc');

                $total   = $query->count();
                $results = $query->offset($offset)->limit($perPage)->get();

                // FIX: was checking 'assignmasterid' but should be 'assignorderid'
            } elseif (!empty($post['type']) && $post['type'] === 'datewise') {

                // ── Driver summary grouped by date ─────────────────────────────
                $query = DB::table('assign_drivers as d')
                    ->join('order_masters as m', 'm.id', '=', 'd.ordermasterid')
                    ->select(
                        DB::raw('DATE(d.delivery_date) as delivery_date'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(m.order_master_total_price) as total_price'), 
                        DB::raw("SUM(CASE WHEN m.status = 'Completed' THEN 1 ELSE 0 END) as completed_orders"),
                        DB::raw("SUM(CASE WHEN m.status = 'Pending'   THEN 1 ELSE 0 END) as pending_orders"),
                    )
                    ->where('d.driverid', $post['userid'])
                    ->where('d.orgid',    $post['orgid'])
                    ->groupBy(DB::raw('DATE(d.delivery_date)'))
                    ->orderBy(DB::raw('DATE(d.delivery_date)'), 'desc');

                $total   = $query->count();
                $results = $query->offset($offset)->limit($perPage)->get();
            } else {

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
                            ->where('d.id', $post['assignorderid']);
                    })
                    ->select(
                        'm.id as order_id',
                        DB::raw("CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name,''), p.last_name) AS customer_name"),
                        'u.phone',
                        'a.address_name',
                        'a.longitude',
                        'a.latitude',
                        'm.created_at as order_date',
                        'd.id as assignorderid',
                        'd.created_at as assigned_at',
                        'd.order_status as order_status',
                    )
                    ->where('m.orgid', $post['orgid'])
                    ->orderBy('m.created_at', 'desc');

                $total   = $query->count();
                $results = $query->offset($offset)->limit($perPage)->get();
            }

            return [
                'list'  => $results,
                'total' => $total,
                'page'  => $page,
                'per_page' => $perPage,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}