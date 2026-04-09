<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Order;
use Illuminate\Database\QueryException;
use Exception;

class OrderController extends Controller
{
    public function index()
    {
        return view('backend.order.index');
    }

    public function list(Request $request)
    {
        // try {
            $post = $request->all();
            $data = Order::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $i + 1;
                $array[$i]["username"]    = $row->username;
                $array[$i]["phone"]    = $row->phone;
                $array[$i]["title"]    = $row->title;
                $array[$i]["qty"]    = $row->qty;
                $array[$i]["price"]    = $row->price;
                $action = '';
                $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewOrder" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';
                $array[$i]["action"]  = $action;
                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs) $totalrecs = 0;
        // } catch (QueryException $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // } catch (Exception $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // }
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }

    public function view(Request $request)
    {
        try {
            $post = $request->all();

            $orderDetails = Order::getData($post);
            $data = [
                'orderDetails' => $orderDetails,
            ];
            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of order.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.order.view', $data);
    }
}