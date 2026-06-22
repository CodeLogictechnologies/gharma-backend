<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Models\BackPanel\Order;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'ordermasterid',
        'orgid',
        'invoicenumber',
        'storagepath',
        'postedby',
        'updatedby',
    ];
    public $incrementing = false;
    protected $keyType = 'string';

    // public static function list($post)
    // {
    //     try {
    //         $get = $_GET;
    //         foreach ($get as $key => $value) {
    //             $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
    //         }
    //         $cond = "1=1";

    //         if ($get['sSearch_1']) {
    //             $cond .= " and lower(u.name) like '%" . strtolower($get['sSearch_1']) . "%'";
    //         }

    //         if ($get['sSearch_2']) {
    //             $cond .= " and lower(u.email) like '%" . strtolower($get['sSearch_2']) . "%'";
    //         }
    //         $limit = 15;
    //         $offset = 0;
    //         if (!empty($get["length"]) && $get["length"]) {
    //             $limit = $get['length'];
    //             $offset = $get["start"];
    //         }

    //         if (!empty($post['type']) && $post['type'] == 'invoice') {
    //             $cond .= " and om.order_status = 'Delivered'";
    //         }

    //         $query = Order::from('order_masters as om')
    //             ->join('users as u', 'u.id', '=', 'om.userid')
    //             ->leftJoin('invoices as i', 'i.ordermasterid', '=', 'om.id')
    //             ->selectRaw("
    //                     om.id,
    //                     i.invoicenumber,
    //                     om.order_status,
    //                     u.name as username,
    //                     om.created_at,
    //                     u.email,
    //                     (SELECT COUNT(*) FROM order_masters WHERE {$cond}) as totalrecs
    //                 ")
    //             ->whereRaw($cond);

    //         if ($limit > -1) {
    //             $result = $query->orderby('om.id', 'desc')->offset($offset)->limit($limit)->get();
    //         } else {
    //             $result = $query->orderby('om.id', 'desc')->get();
    //         }
    //         if ($result) {
    //             $ndata = $result;
    //             $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //             $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //         } else {
    //             $ndata = array();
    //         }
    //         return $ndata;
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }

    public static function list($post)
    {
        try {
            $cond     = "1=1";
            $bindings = [];

            if (!empty($post['sSearch_1'])) {
                $val        = strtolower(trim($post['sSearch_1']));
                $cond      .= " and lower(u.name) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['sSearch_2'])) {
                $val        = strtolower(trim($post['sSearch_2']));
                $cond      .= " and lower(u.email) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['type']) && $post['type'] == 'invoice') {
                $cond .= " and om.order_status = 'Delivered'";
            }

            $limit  = isset($post['iDisplayLength']) ? (int) $post['iDisplayLength'] : 15;
            $offset = isset($post['iDisplayStart'])  ? (int) $post['iDisplayStart']  : 0;

            $totalrecs = DB::table('order_masters as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->whereRaw($cond, $bindings)
                ->count();

            $query = Order::from('order_masters as om')
                ->join('users as u', 'u.id', '=', 'om.userid')
                ->leftJoin('invoices as i', 'i.ordermasterid', '=', 'om.id')
                ->selectRaw("om.id, i.invoicenumber, om.order_status, u.name as username, om.created_at, u.email")
                ->whereRaw($cond, $bindings)
                ->orderBy('om.created_at', 'desc');

            $filteredCount = (clone $query)->count();

            if ($limit > -1) {
                $result = $query->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->get();
            }

            $ndata                      = $result;
            $ndata['totalrecs']         = $totalrecs;
            $ndata['totalfilteredrecs'] = $filteredCount;

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
