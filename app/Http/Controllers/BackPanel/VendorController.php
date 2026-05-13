<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Post;
use App\Models\BackPanel\Vendor;
use App\Models\Common;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        return view('backend.vendor.info.index');
    }

    public function save(Request $request)
    {
        // try {
        $rules = [
            'name' => 'required|min:5|max:255',
            'phone' => 'required|min:5|max:5000',
            'address' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('vendors', 'email')->ignore($post['id'] ?? null),
            ],
            'company' => 'required',
            'pan' => 'required',
            'registration_number' => 'required',
            'city' => 'required',
            'address' => 'required',
        ];

        $message = [
            'name.required' => 'Please enter organization name',
            'phone.required' => 'Phone number is required',
            'address.required' => 'Address is required',
            'registration_number.required' => 'Registration Number is required',
            'city.required' => 'City is required',
            'address.required' => 'Address is required',
            'pan.required' => 'Pan is required',
            'company.required' => 'Company is required',
            'email.required' => 'Email is required',
        ];

        $validate = Validator::make($request->all(), $rules, $message);

        if ($validate->fails()) {
            throw new Exception($validate->errors()->first(), 1);
        }

        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['userid'] = session('userid');
        $type = 'success';
        $message = 'Organization saved successfully';

        DB::beginTransaction();

        if (!Vendor::saveData($post)) {
            throw new Exception('Could not save record', 1);
        }
        DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $e->getMessage();
        // }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $data = Vendor::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["name"]    = $row->name;
            $array[$i]["email"]    = $row->email;
            $array[$i]["address"]    = $row->address;
            $array[$i]["phone"]    = $row->phone;
            $array[$i]["tax_number"]    = $row->tax_number;
            $array[$i]["registration_number"]    = $row->registration_number;
            $array[$i]["company_name"]    = $row->company_name;



            $action = '';

            // for edit
            // for delete
            $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteVendor px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';
            // for show
            $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewVendor" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';

            $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editVendor" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
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

    public function form(Request $request)
    {
        try {
            $data = [];
            if (!empty($request->id)) {
                $post = $request->all();
                $result = Vendor::getData($post);
                if (!$result) {
                    throw new Exception("Vendor not found", 1);
                }

                $data['id']     = $result->id;
                $data['name']   = $result->name;
                $data['phone']  = $result->phone;
                $data['address'] = $result->address;
                $data['email']  = $result->email;
                $data['address']  = $result->address;
                $data['city']  = $result->city;
                $data['pan']  = $result->tax_number;
                $data['registration_number']  = $result->registration_number;
                $data['company_name']  = $result->company_name;
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.vendor.info.form', $data);
    }


    // Delete
    public function delete(Request $request)
    {
        // try {
        $type = 'success';
        $message = "Record deleted successfully";
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['userid'] = session('userid');

        DB::beginTransaction();
        $result = Vendor::deleteDate($post);
        if (!$result) {
            throw new Exception("Vendor not delete", 1);
        }

        DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $e->getMessage();
        // }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    //view
    public function view(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $vendorDetail = Vendor::getData($post);

            $data = [
                'vendorDetail' => $vendorDetail,
            ];

            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of Vendor.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.vendor.info.view', $data);
    }
}
