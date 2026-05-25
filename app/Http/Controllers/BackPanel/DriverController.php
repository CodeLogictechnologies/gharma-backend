<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    public function index()
    {
        return view('backend.driver.main');
    }

    public function save(Request $request)
    {
        try {
            $rules = [
                'first_name' => 'required|min:3|max:255',
                'phone'      => 'required|min:5|max:5000',
                'address'    => 'required',
                'email'      => [
                    'required', 'email',
                    Rule::unique('users')->ignore($request->id)
                ],
                'username'   => 'required',
            ];

            $validate = Validator::make($request->all(), $rules);
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post           = $request->all();
            $post['type']   = 'driver';
            $post['role']   = 4;          // ← Driver role ID hardcoded
            $post['orgid']  = session('orgid');

            DB::beginTransaction();
            if (!User::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();

            $type    = 'success';
            $message = 'Driver saved successfully';

        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function list(Request $request)
    {
        $post           = $request->all();
        $post['orgid']  = session('orgid');
        $post['role']   = 4; // filter drivers only
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

            $array[$i]["status"] = '
            <select class="form-select statusDropdown" data-id="' . $row->id . '">
                <option value="Pending" ' . ($row->user_status == "Pending" ? "selected" : "") . '>Pending</option>
                <option value="Approve" ' . ($row->user_status == "Approve" ? "selected" : "") . '>Approve</option>
                <option value="Reject"  ' . ($row->user_status == "Reject"  ? "selected" : "") . '>Reject</option>
            </select>';

            $action  = '';
            $action .= '<a href="javascript:;" class="deleteOrg px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
            $array[$i]["action"] = $action;

            $i++;
        }

        return json_encode([
            "recordsFiltered" => $filtereddata ?? 0,
            "recordsTotal"    => $totalrecs ?? 0,
            "data"            => $array
        ]);
    }

    public function form(Request $request)
    {
        $data = [];

        if (!empty($request->id)) {
            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $result        = User::getData($post);

            if (!$result) throw new Exception("Driver not found", 1);

            $data['id']          = $result->id;
            $data['username']    = $result->username;
            $data['first_name']  = $result->first_name;
            $data['middle_name'] = $result->middle_name;
            $data['last_name']   = $result->last_name;
            $data['gender']      = $result->gender;
            $data['phone']       = $result->phone;
            $data['address']     = $result->address;
            $data['email']       = $result->email;
            if ($result->image) $data['image'] = $result->image;
        }

        return view('backend.driver.form', $data);
    }

    public function delete(Request $request)
    {
        try {
            $post = $request->all();
            if (empty($post['id'])) throw new Exception("No ID provided", 1);

            DB::beginTransaction();
            $profile = DB::table('profiles')->where('user_id', $post['id'])->first();
            if ($profile && !empty($profile->image)) {
                $path = storage_path('app/public/profiles/' . $profile->image);
                if (file_exists($path)) unlink($path);
            }
            DB::table('profiles')->where('user_id', $post['id'])->delete();
            DB::table('userorganizations')->where('userid', $post['id'])->delete();
            DB::table('model_has_roles')->where('model_id', $post['id'])->delete();
            DB::table('users')->where('id', $post['id'])->delete();
            DB::commit();

            $type    = 'success';
            $message = 'Driver deleted successfully';
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function updateStatus(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if (!$user) return response()->json(['type' => 'error', 'message' => 'Driver not found']);

            $user->user_status = $request->status;
            $user->save();

            return response()->json(['type' => 'success', 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function tabs(Request $request)
    {
        $tabid = $request->input('tabid');
        switch ($tabid) {
            case 'active':   return view('backend.driver.index');
            default:         return '<div class="alert alert-warning">Invalid tab</div>';
        }
    }
}