<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Role;
use Illuminate\Http\Request;
use App\Models\Common;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Profiler\Profile;

class UserController extends Controller
{
    public function index()
    {
        return view('backend.users.index');
    }

    public function save(Request $request)
    {
        // try {
        $post = $request->all();
        $rules = [
            'first_name' => 'required|min:5|max:255',
            'phone' => 'required|min:5|max:5000',
            'address' => 'required',
            'email' => 'required',
            'username' => 'required',
        ];

        if (empty($request->id)) {
            $rules['image'] = 'required:mimes:jpg,jpeg,png:max:2048';
        }

        $message = [
            'first_name.required' => 'Please enter first name',
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
        $message = 'User saved successfully';

        DB::beginTransaction();

        if (!User::saveData($post)) {
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
        $data = User::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["name"] = $row->name;
            $array[$i]["email"] = $row->email;
            $array[$i]["address"] = $row->address;
            $array[$i]["phone"] = $row->phone;

            // ✅ DROPDOWN STATUS
            $array[$i]["user_status"] = '
    <select class="form-select changeStatus" data-id="' . $row->id . '">
        <option value="Pending" ' . ($row->user_status == "Pending" ? "selected" : "") . '>Pending</option>
        <option value="Approve" ' . ($row->user_status == "Approve" ? "selected" : "") . '>Approve</option>
        <option value="Reject" ' . ($row->user_status == "Reject" ? "selected" : "") . '>Reject</option>
    </select>';

            $array[$i]["profile"] = $row->profile;
            $array[$i]["created_at"] = $row->created_at;

            // Image
            if (!empty($row->logo)) {
                $imagePath = storage_path('app/public/profile/' . $row->logo);

                if (file_exists($imagePath)) {
                    $imageUrl = asset('storage/profile/' . $row->logo);
                } else {
                    $imageUrl = asset('no-image.jpg');
                }
            } else {
                $imageUrl = asset('no-image.jpg');
            }

            $array[$i]["logo"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';

            // Actions
            $action = '';
            $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteOrg px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';

            $array[$i]["action"] = $action;

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
        $data = [];
        $allRoles = Role::getRole();
        $data['rolesList'] = $allRoles;

        if (!empty($request->id)) {
            $post = $request->all();
            $result = User::getData($post);
            if (!$result) {
                throw new Exception("User not found", 1);
            }

            // User info
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

        return view('backend.users.form', $data);
    }


    // Delete
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";
            $directory = storage_path('app/public/profile');
            $post = $request->all();
            $class = new User();

            DB::beginTransaction();
            if (!Common::deleteSingleData($post, $class, $directory)) {
                throw new Exception("Record does not deleted", 1);
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

    //view
    public function view(Request $request)
    {
        try {
            $post = $request->all();

            $userDetails = User::getData($post);

            $data = [
                'userDetails' => $userDetails,
            ];

            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of user.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = 'Something went wrong.';
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.organization.view', $data);
    }

    public function updateStatus(Request $request)
    {
        try {
            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'User not found'
                ]);
            }

            $user->user_status = $request->status;
            $user->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong'
            ]);
        }
    }
}
