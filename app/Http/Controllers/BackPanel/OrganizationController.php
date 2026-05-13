<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Post;
use App\Models\Common;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    //function to redirect to organization list page
    public function index()
    {
        return view('backend.organization.index');
    }

    //function to save organizations
    public function save(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|min:5|max:255',
                'phone' => 'required|min:5|max:5000',
                'address' => 'required',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('organizations', 'email')->ignore($request->id, 'id')
                ],
                'username' => 'required',
            ];

            if (empty($request->id)) {
                $rules['image'] = 'required:mimes:jpg,jpeg,png:max:2048';
            }

            $message = [
                'name.required' => 'Please enter organization name',
                'phone.required' => 'Phone number is required',
                'address.required' => 'Address is required',
                'email.required' => 'Email is required',
                'username.required' => 'User Name is required',
            ];

            $validate = Validator::make($request->all(), $rules, $message);

            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post = $request->all();
            $type = 'success';
            $message = 'Organization saved successfully';

            DB::beginTransaction();

            if (!Organization::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message]);
    }


    //function to get list of organizations
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $data = Organization::list($post);
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
                $array[$i]["logo"]    = $row->logo;
                $array[$i]["created_at"]    = $row->created_at;


                if (!empty($row->logo)) {

                    $imagePath = public_path('uploads/organizations/' . $row->logo);

                    if (file_exists($imagePath)) {
                        $imageUrl = asset('uploads/organizations/' . $row->logo);
                    } else {
                        $imageUrl = asset('no-image.jpg');
                    }
                } else {
                    $imageUrl = asset('no-image.jpg');
                }
                $array[$i]["logo"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';

                $action = '';
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteOrg px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editOrg" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
                $array[$i]["action"]  = $action;
                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs) $totalrecs = 0;
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }


    //function to get value while editing
    public function form(Request $request)
    {
        try {
            $data = [];

            if (!empty($request->id)) {
                $post = $request->all();
                $result = Organization::getData($post);
                if (!$result) {
                    throw new Exception("Organization not found", 1);
                }

                $data['id']     = $result->id;
                $data['userid'] = $result->userid;
                $data['username'] = $result->username;
                $data['name']   = $result->name;
                $data['phone']  = $result->phone;
                $data['address'] = $result->address;
                $data['email']  = $result->email;

                if ($result->logo) {
                    $data['logo'] =  $result->logo;
                } else {
                    $data['logo'] = '<img src="' . asset('/no-image.jpg') . '" class="_image" height="160px" width="160px" alt="No image"/>';
                }
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }
        return view('backend.organization.adduser', $data);
    }

    // function to delete organization
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";
            $post = $request->all();

            DB::beginTransaction();
            $result = Organization::deleteDate($post);
            if (!$result) {
                throw new Exception("Organization not delete", 1);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    //function to view specific organization
    public function view(Request $request)
    {
        try {
            $post = $request->all();

            $orgDetails = Organization::getData($post);

            $data = [
                'orgDetails' => $orgDetails,
            ];

            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of Organization.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.organization.view', $data);
    }
}