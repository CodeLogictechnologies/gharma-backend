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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;

class DriverController extends Controller
{
    public function index()
    {
        return view('backend.driver.list.index');
    }

    public function assignIndex(Request $request)
    {
        $ordermasterid = $request->input('id');
        $orgid = session('orgid');

        $drivers = DB::table('users')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->join('userorganizations as u', 'u.userid', '=', 'users.id')
            ->where('profiles.status', 'Y')
            ->where('u.orgid', $orgid)
            ->where('users.user_status', 'Approve')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('model_has_roles as mhr')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('mhr.model_id', 'users.id')
                    ->whereRaw('LOWER(r.name) LIKE ?', ['%driver%']);
            })
            ->select('users.id', 'users.name')
            ->get();

        $existing = DB::table('assign_drivers')
            ->where('ordermasterid', $ordermasterid)
            ->first();

        return view('backend.order.assign_driver', [
            'ordermasterid'   => $ordermasterid,
            'drivers'         => $drivers,
            'assigned_driver' => $existing->driverid ?? null,
            'assigned_date'   => $existing->delivery_date ?? date('Y-m-d'),
            'is_assigned'     => !empty($existing),
        ]);
    }

    public function save(Request $request)
    {
        // try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $rules = [
                'driver_id' => 'required|min:3|max:255',
            ];

            $message = [
                'driver_id.required' => 'Please select driver.',
            ];
            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }
            $type = 'success';
            $message = 'Driver assign successfully';

            DB::beginTransaction();
            if (!FirebaseService::AssignDriverNotice($post)) {
                throw new Exception('Could not assign driver', 1);
            }
            DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = 'Something went wrong';
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $e->getMessage();
        // }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function list(Request $request)
    {
        try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');

            $limit  = (int) ($post['iDisplayLength'] ?? 15);
            $offset = (int) ($post['iDisplayStart']  ?? 0);

            $query = User::query()
                ->select(
                    'users.id',
                    'users.user_status',
                    'users.name',
                    'users.email',
                    'profiles.phone',
                    'profiles.address',
                )
                ->join('profiles', 'profiles.user_id', '=', 'users.id')
                ->join('userorganizations as u', 'u.userid', '=', 'users.id')
                ->where('profiles.status', 'Y')
                ->where('u.orgid', $post['orgid'])
                ->where('users.user_status', 'Approve')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('model_has_roles as mhr')
                        ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                        ->whereColumn('mhr.model_id', 'users.id')
                        ->whereRaw('LOWER(r.name) LIKE ?', ['%driver%']);
                });

            if (!empty($post['sSearch_1'])) {
                $query->whereRaw('LOWER(users.name) LIKE ?', ['%' . strtolower($post['sSearch_1']) . '%']);
            }
            if (!empty($post['sSearch_2'])) {
                $query->whereRaw('LOWER(profiles.phone) LIKE ?', ['%' . strtolower($post['sSearch_2']) . '%']);
            }
            if (!empty($post['sSearch_3'])) {
                $query->whereRaw('LOWER(users.email) LIKE ?', ['%' . strtolower($post['sSearch_3']) . '%']);
            }

            $totalrecs = (clone $query)->count();

            $result = $limit > -1
                ? $query->orderBy('users.id', 'asc')->offset($offset)->limit($limit)->get()
                : $query->orderBy('users.id', 'asc')->get();

            $i     = 0;
            $array = [];
            foreach ($result as $row) {
                $array[$i]['sno']     = $offset + $i + 1;
                $array[$i]['name']    = $row->name    ?? '-';
                $array[$i]['email']   = $row->email   ?? '-';
                $array[$i]['address'] = $row->address ?? '-';
                $array[$i]['phone']   = $row->phone   ?? '-';

                $action  = '';
                $action .= '<a href="javascript:;" class="deleteDriver px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editDriver px-2" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';

                $array[$i]['action'] = $action;
                $i++;
            }
        } catch (QueryException $e) {
            $array     = [];
            $totalrecs = 0;
        } catch (Exception $e) {
            $array     = [];
            $totalrecs = 0;
        }

        return json_encode([
            'recordsFiltered' => $totalrecs,
            'recordsTotal'    => $totalrecs,
            'data'            => $array,
        ]);
    }

    public function form(Request $request)
    {
        try {
            $data = [];
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $allRoles = DB::table('roles')->where('id', '550e8400-e29b-41d4-a716-446655440004')->get();
            $data['rolesList'] = $allRoles;
            if (!empty($request->id)) {
                $result = User::getData($post);
                if (!$result) {
                    throw new Exception("User not found", 1);
                }
                $data['id']          = $result->id;
                $data['username']    = $result->username;
                $data['first_name']  = $result->first_name;
                $data['middle_name'] = $result->middle_name;
                $data['last_name']   = $result->last_name;
                $data['gender']      = $result->gender;
                $data['phone']       = $result->phone;
                $data['address']     = $result->address;
                $data['email']       = $result->email;
                $data['userRoles']   = $result->roles;

                if ($result->image) {
                    $data['image'] = $result->image;
                }
            }
            return view('backend.driver.list.form', $data);
        } catch (QueryException $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function saveDriver(Request $request)
    {
        // try {
            $post = $request->all();
            $rules = [
                'first_name' => 'required|min:3|max:255',
                'phone'      => 'required|min:5|max:5000',
                'address'    => 'required',
                'email'      => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($request->id)
                ],
                'username'   => 'required',
            ];

            if (empty($request->id)) {
                $rules['image'] = 'nullable:mimes:jpg,jpeg,png:max:2048';
            }

            $message = [
                'first_name.required' => 'Please enter first name',
                'first_name.min'      => 'First name must be at least 3 characters',
                'phone.required'      => 'Phone number is required',
                'address.required'    => 'Address is required',
                'email.required'      => 'Email is required',
                'username.required'   => 'User Name is required',
            ];

            $validate = Validator::make($request->all(), $rules, $message);

            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post          = $request->all();
            $post['type']  = 'driver';
            $type          = 'success';
            $post['orgid'] = session('orgid');

            if (!empty($post['id'])) {
                $message = 'Driver updated successfully';
            } else {
                $message = 'Driver saved successfully';
            }

            DB::beginTransaction();

            if (!Driver::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type    = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type    = 'error';
        //     $message = $e->getMessage();
        // }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function delete(Request $request)
    {
        try {
            $post = $request->all();
            if (empty($post['id'])) throw new Exception("No ID provided", 1);
            DB::beginTransaction();
            DB::rollBack();
            $type    = 'success';
            $message = 'Driver deleted successfully';
            $result  = Common::deleteUser($post);
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
}