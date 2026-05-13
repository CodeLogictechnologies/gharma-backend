<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Inventory;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\SubCategory;

use Illuminate\Support\Facades\DB;
use App\Models\BackPanel\Vendor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        return view('backend.inventory.index');
    } // InventoryController.php
    public function getVariations(Request $request)
    {
        $variations = DB::table('itemvariations')
            ->where('item_id', $request->item_id)
            // ->where('status', 'Y')
            ->select('id', 'attribute', 'value')
            ->get();

        return response()->json($variations);
    }

    public function save(Request $request)
    {
        // try {
        $rules = [
            'itemid'             => 'required',
            'variationid'        => 'required',
            'vendorid'           => 'required',
            'quantity_available' => 'required|numeric|min:0',
            'reorder_level'      => 'required|numeric|min:0',
            'unit_cost'          => 'required|numeric|min:0',
            'selling_price'      => 'required|numeric|min:0',
            'manufacturedatead'  => 'required|date',
            'expirydatead'       => 'required|date|after:manufacturedatead',
        ];

        $messages = [
            'itemid.required'             => 'Please select a product.',
            'variationid.required'        => 'Please select a variation.',
            'vendorid.required'           => 'Please select a vendor.',
            'quantity_available.required' => 'Quantity is required.',
            'quantity_available.numeric'  => 'Quantity must be a number.',
            'reorder_level.required'      => 'Threshold is required.',
            'unit_cost.required'          => 'Unit cost is required.',
            'selling_price.required'      => 'Selling price is required.',
            'manufacturedatead.required'  => 'Manufacture date is required.',
            'expirydatead.required'       => 'Expiry date is required.',
            'expirydatead.after'          => 'Expiry date must be after manufacture date.',
        ];

        $validation = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            return response()->json([
                'type'    => 'error',
                'message' => $validation->errors()->first()
            ]);
        }

        $post          = $request->all();
        $post['orgid'] = session('orgid');

        DB::beginTransaction();

        if (!Inventory::saveData($post)) {
            throw new Exception('Could not save inventory.');
        }

        DB::commit();

        return response()->json([
            'type'    => 'success',
            'message' => 'Inventory saved successfully.'
        ]);
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        // }
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
            $array[$i]["variation_value"]    = $row->variation_value;
            $array[$i]["stock"]    = $row->stock;
            $array[$i]["remainingqty"]    = $row->remainingqty ?? 0;
            $array[$i]["soldqty"]    = $row->soldqty ?? 0;
            $action = '';
            $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewInventory" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';
            $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editInventory" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';

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

    public function form(Request $request)
    {
        // try {
        $post            = $request->all();
        $post['orgid']   = session('orgid');

        $items   = Item::getItem($post);
        $vendors = Vendor::getVendor($post);

        $data = [
            'items'   => $items,
            'vendors' => $vendors,
        ];

        if (!empty($request->id)) {
            $result = Inventory::getData($post);
            if (!$result) {
                throw new Exception("Inventory not found", 1);
            }

            $data['id']               = $result->id;
            $data['itemid']           = $result->item_id;
            $data['expirymonth']           = $result->expirymonth;
            $data['variationid']      = $result->variation_id;
            $data['vendorid']         = $result->vendor_id;
            $data['quantity_available'] = $result->quantity_available;
            $data['reorder_level']    = $result->reorder_level;
            $data['unit_cost']        = $result->unit_cost;
            $data['selling_price']    = $result->selling_price;
            $data['manufacturedatead'] = $result->manufacturedatead ?? '';
            $data['expirydatead']     = $result->expirydatead ?? '';
        }
        // } catch (QueryException $e) {
        //     $data['error'] = $this->queryMessage;
        // } catch (Exception $e) {
        //     $data['error'] = $e->getMessage();
        // }

        return view('backend.inventory.form', $data);
    }
}