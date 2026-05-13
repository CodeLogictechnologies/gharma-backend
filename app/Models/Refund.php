<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class Refund extends Model
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

            $query = Refund::from('refunds as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->join('itemvariations as iv', 'iv.id', '=', 'om.variationid')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->join('order_details as od', 'od.id', '=', 'om.order_detail_id')
                ->selectRaw("
                        om.id,
                        i.title,
                        iv.value,
                        om.refund_status,
                        u.name as username,
                        om.reason,
                        u.email,
                        (SELECT COUNT(*) FROM refunds WHERE {$cond}) as totalrecs
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
        $result = Refund::from('refunds as om')
            ->join('users as u', 'u.id', '=', 'om.userid')
            ->join('itemvariations as iv', 'iv.id', '=', 'om.variationid')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('order_details as od', 'od.id', '=', 'om.order_detail_id')
            ->selectRaw("
        om.id,
        i.title,
        iv.value,
        od.quantity,
        od.order_detail_total_price,
        om.refund_status,
        u.name as username,
        om.reason,
        u.email
    ")
            ->first();

        return  $result;
    }
}