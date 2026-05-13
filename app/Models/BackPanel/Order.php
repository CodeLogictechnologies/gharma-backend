<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;


class Order extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    public static function list($post)
    {
        try {
            $get = $_GET;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = "1=1";

            if ($get['sSearch_1']) {
                $cond .= " and lower(u.name) like '%" . strtolower($get['sSearch_1']) . "%'";
            }

            if ($get['sSearch_2']) {
                $cond .= " and lower(u.email) like '%" . strtolower($get['sSearch_2']) . "%'";
            }
            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Order::from('order_masters as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->selectRaw("
        om.id,
        om.order_status,
        u.name as username,
        om.created_at,
        u.email,
        (SELECT COUNT(*) FROM order_masters WHERE {$cond}) as totalrecs
    ")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderby('om.id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderby('om.id', 'desc')->get();
            }
            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = array();
            }
            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getData($post)
    {
        $result = DB::table('order_details as od')
            ->join('order_masters as om', 'om.id', '=', 'od.ordermasterid')
            ->join('itemvariations as v', 'v.id', '=', 'od.variation_id')
            ->join('items as i', 'i.id', '=', 'v.item_id')
            ->where('od.ordermasterid', $post['id'])
            ->select('i.title', 'v.value', 'od.price', 'od.quantity', 'od.order_detail_total_price')
            ->get();
        return  $result;
    }
}