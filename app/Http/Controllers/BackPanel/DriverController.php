<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BsdateController;
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

    /**
     * Standalone "Assign Drive" dashboard (Drive > Assign Drive in the sidebar).
     */
    public function assignIndex(Request $request)
    {
        $orgid = session('orgid');

        $unassigned = DB::table('order_masters as om')
            ->where('om.orgid', $orgid)
            ->whereIn('om.order_status', ['Confirmed', 'Packed'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('assign_drivers as ad')
                    ->whereColumn('ad.ordermasterid', 'om.id')
                    ->where('ad.status', 'Y');
            })
            ->count();

        $assignedToday = DB::table('assign_drivers')
            ->where('orgid', $orgid)
            ->where('status', 'Y')
            ->whereDate('delivery_date', date('Y-m-d'))
            ->count();

        $inTransit = DB::table('assign_drivers')
            ->where('orgid', $orgid)
            ->where('status', 'Y')
            ->where('order_status', 'Start')
            ->count();

        return view('backend.driver.assign.dashboard', [
            'drivers'  => $this->getActiveDrivers($orgid),
            'todayNep' => (new BsdateController())->eng_to_nep(date('Y-m-d')),
            'stats'    => [
                'unassigned'     => $unassigned,
                'assigned_today' => $assignedToday,
                'in_transit'     => $inTransit,
            ],
        ]);
    }

    /**
     * Single-order assign/reassign modal. Used both by the order list's
     * per-row "Assign Driver" icon and by row actions on the Assign Drive dashboard.
     */
    public function assignModal(Request $request)
    {
        $ordermasterid = $request->input('id');
        $orgid = session('orgid');

        $order = DB::table('order_masters as om')
            ->join('users as u', 'u.id', '=', 'om.userid')
            ->leftJoin('user_addresses as ua', 'ua.id', '=', 'om.addressid')
            ->where('om.id', $ordermasterid)
            ->select('om.id', 'om.order_status', 'u.name as customer_name', 'u.phone as customer_phone', 'ua.address_name')
            ->first();

        $existing = DB::table('assign_drivers')
            ->where('ordermasterid', $ordermasterid)
            ->where('status', 'Y')
            ->first();

        $bsdate = new BsdateController();
        if (!empty($existing->date_nep)) {
            $assignedDateNep = $existing->date_nep;
        } elseif (!empty($existing->delivery_date)) {
            $assignedDateNep = $bsdate->eng_to_nep($existing->delivery_date);
        } else {
            $assignedDateNep = $bsdate->eng_to_nep(date('Y-m-d'));
        }

        return view('backend.order.assign_driver', [
            'ordermasterid'   => $ordermasterid,
            'order'           => $order,
            'drivers'         => $this->getActiveDrivers($orgid),
            'assigned_driver' => $existing->driverid ?? null,
            'assigned_date'   => $assignedDateNep,
            'is_assigned'     => !empty($existing),
        ]);
    }

    /**
     * Server-side DataTable source for the Assign Drive dashboard.
     * Tabs: unassigned (Confirmed/Packed orders with no active driver), assigned, all.
     */
    public function assignList(Request $request)
    {
        $post   = $request->all();
        $orgid  = session('orgid');
        $tab    = $post['tab'] ?? 'unassigned';
        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);

        $query = DB::table('order_masters as om')
            ->join('users as u', 'u.id', '=', 'om.userid')
            ->leftJoin('user_addresses as ua', 'ua.id', '=', 'om.addressid')
            ->leftJoin('assign_drivers as ad', function ($join) {
                $join->on('ad.ordermasterid', '=', 'om.id')->where('ad.status', 'Y');
            })
            ->leftJoin('users as drv', 'drv.id', '=', 'ad.driverid')
            ->where('om.orgid', $orgid);

        if ($tab === 'unassigned') {
            $query->whereIn('om.order_status', ['Confirmed', 'Packed'])
                ->whereNull('ad.id');
        } elseif ($tab === 'assigned') {
            $query->whereNotNull('ad.id');
        }

        if (!empty($post['driver_id'])) {
            $query->where('ad.driverid', $post['driver_id']);
        }
        if (!empty($post['delivery_date'])) {
            $query->where('ad.date_nep', $post['delivery_date']);
        }
        if (!empty($post['sSearch_1'])) {
            $val = strtolower(trim($post['sSearch_1']));
            $query->whereRaw('LOWER(u.name) LIKE ?', ["%{$val}%"]);
        }

        $totalrecs = (clone $query)->count();

        $results = $query
            ->select(
                'om.id',
                'om.order_status',
                'om.order_master_total_price',
                'om.created_at as order_time',
                'u.name as customer_name',
                'u.phone as customer_phone',
                'ua.address_name',
                'ad.id as assignment_id',
                'drv.name as driver_name',
                'ad.delivery_date',
                'ad.date_nep',
                'ad.order_status as delivery_status'
            )
            ->orderBy('om.created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $i     = 0;
        $array = [];
        foreach ($results as $row) {
            $array[$i]['sno']           = $offset + $i + 1;
            $array[$i]['checkbox']      = '<input type="checkbox" class="form-check-input rowCheckbox" value="' . $row->id . '">';
            $array[$i]['customer_name'] = $row->customer_name . '<br><small class="text-muted">' . ($row->customer_phone ?? '-') . '</small>';
            $array[$i]['address_name']  = $row->address_name ?? '-';
            $array[$i]['order_time']    = $row->order_time;
            $array[$i]['order_status']  = '<span class="badge bg-label-info">' . $row->order_status . '</span>';
            $array[$i]['driver_name']   = $row->driver_name
                ? '<span class="badge bg-label-success">' . $row->driver_name . '</span>'
                : '<span class="badge bg-label-secondary">Unassigned</span>';
            $array[$i]['delivery_date'] = $row->date_nep ?? $row->delivery_date ?? '-';

            $label  = $row->assignment_id ? 'Reassign' : 'Assign';
            $action = '<a href="javascript:;" title="' . $label . ' Driver" class="tooltipdiv assignRow" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-user-plus"></i> ' . $label . '</a>';

            $array[$i]['action'] = $action;
            $i++;
        }

        return response()->json([
            'recordsFiltered' => $totalrecs,
            'recordsTotal'    => $totalrecs,
            'data'            => $array,
        ]);
    }

    /**
     * Assign the same driver/date to several orders at once.
     */
    public function bulkSave(Request $request)
    {
        try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');

            $rules = [
                'driver_id'       => 'required',
                'delivery_date'   => 'required|regex:/^\d{4}-\d{2}-\d{2}$/',
                'ordermasterids'  => 'required|array|min:1',
            ];
            $message = [
                'driver_id.required'      => 'Please select a driver.',
                'delivery_date.required'  => 'Please select an assign date.',
                'ordermasterids.required' => 'Please select at least one order.',
            ];
            $validation = Validator::make($post, $rules, $message);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            DB::beginTransaction();
            foreach ($post['ordermasterids'] as $ordermasterid) {
                if (!FirebaseService::AssignDriverNotice([
                    'orgid'         => $post['orgid'],
                    'ordermasterid' => $ordermasterid,
                    'driver_id'     => $post['driver_id'],
                    'delivery_date' => $post['delivery_date'],
                ])) {
                    throw new Exception('Could not assign driver to order ' . $ordermasterid, 1);
                }
            }
            DB::commit();

            $type    = 'success';
            $count   = count($post['ordermasterids']);
            $message = $count . ' order' . ($count === 1 ? '' : 's') . ' assigned successfully';
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = 'Something went wrong';
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    /**
     * Number of orders already assigned to each driver for a given delivery date.
     * Lets the dispatcher pick a driver who still has capacity.
     */
    public function driverWorkload(Request $request)
    {
        $orgid = session('orgid');
        $date  = $request->input('date') ?: (new BsdateController())->eng_to_nep(date('Y-m-d'));

        $counts = DB::table('assign_drivers')
            ->where('orgid', $orgid)
            ->where('status', 'Y')
            ->where('date_nep', $date)
            ->groupBy('driverid')
            ->selectRaw('driverid, count(*) as total')
            ->pluck('total', 'driverid');

        return response()->json($counts);
    }

    private function getActiveDrivers(?string $orgid)
    {
        return DB::table('users')
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
            ->orderBy('users.name')
            ->get();
    }

    public function save(Request $request)
    {
        try {
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
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = 'Something went wrong';
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }
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
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editDriver px-2" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" class="deleteDriver px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

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
