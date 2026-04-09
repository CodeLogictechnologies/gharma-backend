<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Inventory;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class InventoryController extends Controller
{
    public function index()
    {
        return view('backend.inventory.index');
    }
    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $data = Inventory::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["categorytitle"]    = $row->categorytitle;
            $array[$i]["subcategorytitle"]    = $row->subcategorytitle;
            $array[$i]["title"]    = $row->title;
            $array[$i]["attribute"]    = $row->attribute;
            $array[$i]["stock"]    = $row->stock;
            $array[$i]["remainingqty"]    = $row->remainingqty;
            $array[$i]["soldqty"]    = $row->soldqty;
            $action = '';
            $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewInventory" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';
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

            $inventoryDetails = Inventory::getData($post);
            $data = [
                'inventoryDetails' => $inventoryDetails,
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
        return view('backend.inventory.view', $data);
    }
}