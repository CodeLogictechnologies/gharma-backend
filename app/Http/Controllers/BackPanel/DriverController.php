<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\AssignDriverRequest;
use App\Models\BackPanel\AssignDriver;
use App\Models\BackPanel\Driver;
use App\Models\BackPanel\Order;
use App\Models\BackPanel\Role;
use App\Models\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;
use FontLib\Table\Type\post;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;          // ← add this

class DriverController extends Controller
{
    //function to redirect page to driver list
    public function index()
    {
        return view('backend.driver.list.index');
    }

    //function to redirect page to driver list
    public function assignIndex(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $data['drivers'] = Driver::getDrivers($post);
        $data['ordermasterid'] = $post['id'] ?? null;
        return view('backend.order.form', $data);
    }

    //function to assign driver
    public function save(AssignDriverRequest $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] =  session('orgid');
            $type = 'success';
            $message = 'Order Assigned to driver successfully';
            DB::beginTransaction();
            if (!FirebaseService::AssignDriverNotice($post)) {
                throw new Exception('Could not assign driver', 1);
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

    //function to get driver list
    public function list(Request $request)
    {
        try {
            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['role']   = 4;
            $data           = User::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs    = $data["totalrecs"];

            foreach ($data["data"] as $row) {
                $array[$i]["sno"]     = $i + 1;
                $array[$i]["name"]    = $row->name;
                $array[$i]["email"]   = $row->email;
                $array[$i]["address"] = $row->address;
                $array[$i]["phone"]   = $row->phone;
                $action  = '';
                $action .= '<a href="javascript:;" class="deleteDriver px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editDriver" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';

                $array[$i]["action"] = $action;

                $i++;
            }
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }
        return json_encode([
            "recordsFiltered" => $filtereddata ?? 0,
            "recordsTotal"    => $totalrecs ?? 0,
            "data"            => $array
        ]);
    }

    //function to redirect to form page
    public function form(Request $request)
    {
        try {
            $data = [];
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $allRoles = DB::table('roles')->where('id', 4)->get();
            $data['rolesList'] = $allRoles;
            if (!empty($request->id)) {
                $post = $request->all();
                $post['orgid'] = session('orgid');
                $result = User::getData($post);
                if (!$result) {
                    throw new Exception("User not found", 1);
                }
                $data['id']         = $result->id;
                $data['username']   = $result->username;
                $data['first_name'] = $result->first_name;
                $data['middle_name'] = $result->middle_name;
                $data['last_name']  = $result->last_name;
                $data['gender']     = $result->gender;
                $data['phone']      = $result->phone;
                $data['address']    = $result->address;
                $data['email']      = $result->email;
                $data['userRoles']  = $result->roles;

                if ($result->image) {
                    $data['image'] = $result->image;
                }
            }
            return view('backend.driver.list.form', $data);
        } catch (QueryException $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        } catch (\Exception $e) {
            // Log::error('Order form error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    //function to save driver
    public function saveDriver(Request $request)
    {
        try {
            $post = $request->all();
            $rules = [
                'first_name' => 'required|min:3|max:255',
                'phone' => 'required|min:5|max:5000',
                'address' => 'required',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($request->id)
                ],
                'username' => 'required',
            ];

            if (empty($request->id)) {
                $rules['image'] = 'nullable:mimes:jpg,jpeg,png:max:2048';
            }

            $message = [
                'first_name.required' => 'Please enter first name',
                'first_name.min'      => 'First name must be at least 3 characters',
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
            $post['type'] = 'driver';
            $type = 'success';
            if (!empty($post['id'])) {
                $message = 'Driver updated successfully';
            } else {
                $message = 'Driver saved successfully';
            }
            $post['orgid'] = session('orgid');
            DB::beginTransaction();

            if (!Driver::saveData($post)) {
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

    //function to remove driver
    public function delete(Request $request)
    {
        try {
            $post = $request->all();
            if (empty($post['id'])) throw new Exception("No ID provided", 1);
            DB::beginTransaction();
            DB::rollBack();
            $type    = 'success';
            $message = 'Driver deleted successfully';
            $result = Common::deleteUser($post);
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
}
