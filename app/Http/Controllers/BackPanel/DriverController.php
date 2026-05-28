<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\AssignDriverRequest;
use App\Models\BackPanel\AssignDriver;
use App\Models\BackPanel\Driver;
use App\Models\BackPanel\Order;
use App\Models\BackPanel\Role;
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
    public function index()
    {
        return view('backend.driver.list.index');
    }

    public function assignIndex()
    {
        return view('backend.driver.assign.index');
    }

    public function save(AssignDriverRequest $request)
    {
        // try {


        $post = $request->all();
        $post['orgid'] =  session('orgid');
        $type = 'success';
        $message = 'Records saved successfully';
        DB::beginTransaction();

        if (!FirebaseService::AssignDriverNotice($post)) {
            throw new Exception('Could not assign driver', 1);
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
        try {

            $data = [];

            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data['drivers'] = Driver::getDrivers($post);
            // dd($post);
            $data['ordermasterid'] = $post['id'] ?? null;

            return view('backend.order.form', $data);
        } catch (\Exception $e) {

            // Log::error('Order form error: ' . $e->getMessage());

            return back()->with('error', 'Something went wrong. Please try again.');
        }
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
            case 'active':
                return view('backend.driver.index');
            default:
                return '<div class="alert alert-warning">Invalid tab</div>';
        }
    }
}