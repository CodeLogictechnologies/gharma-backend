<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Mail\UserApprovedMail;
use App\Mail\UserRejectedMail;
use App\Models\BackPanel\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return view('backend.users.main');
    }

    public function save(Request $request)
    {
        try {
            $isEdit = !empty($request->id);

            $isWholesaler = false;
            if (!empty($request->roles)) {
                $isWholesaler = DB::table('roles')
                    ->whereIn('id', $request->roles)
                    ->whereRaw('LOWER(name) LIKE ?', ['%wholesaler%'])
                    ->exists();
            }

            $rules = [
                'first_name' => 'required|min:3|max:255',
                'last_name'  => 'required|max:255',
                'phone'      => 'required|min:5|max:20',
                'address'    => 'required',
                'email'      => [
                    'required',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DB::table('users')
                            ->where('email', $value)
                            ->where('id', '!=', $request->id)
                            ->exists();
                        if ($exists) $fail('The email has already been taken.');
                    },
                ],
                'username' => 'required',
                'gender'   => 'required',
                'roles'    => 'required|array|min:1',
                'image'    => 'nullable|mimes:jpg,jpeg,png|max:2048',
            ];

            $messages = [
                'first_name.required'             => 'Please enter first name',
                'first_name.min'                  => 'First name must be at least 3 characters',
                'last_name.required'              => 'Please enter last name',
                'phone.required'                  => 'Phone number is required',
                'address.required'                => 'Address is required',
                'email.required'                  => 'Email is required',
                'email.regex' => 'Email must be a valid @gmail.com address',
                'username.required'               => 'Username is required',
                'gender.required'                 => 'Gender is required',
                'roles.required'                  => 'Please select at least one role',
                'image.mimes'                     => 'Profile image must be jpg, jpeg, or png',
                'image.max'                       => 'Profile image must not exceed 2MB',
                'company_name.required'           => 'Company name is required for Wholesaler',
                'tax_number.required'             => 'Tax number is required for Wholesaler',
                'registration_number.required'    => 'Registration number is required for Wholesaler',
                'pan_number.required'             => 'PAN number is required for Wholesaler',
                'pan_image.mimes'                 => 'PAN image must be jpg, jpeg, png, or pdf',
                'pan_image.max'                   => 'PAN image must not exceed 2MB',
                'registration_number_image.mimes' => 'Registration image must be jpg, jpeg, png, or pdf',
                'registration_number_image.max'   => 'Registration image must not exceed 2MB',
            ];

            if ($isWholesaler) {
                $rules['company_name']        = 'required|max:255';
                // $rules['tax_number']          = 'required|max:100';
                // $rules['registration_number'] = 'required|max:100';
                $rules['pan_number']          = 'required|max:100';

                // Check existing images if editing
                $existingPanImage = null;
                $existingRegImage = null;

                if ($isEdit) {
                    $profile          = DB::table('profiles')->where('user_id', $request->id)->first();
                    $existingPanImage = $profile->pan_image ?? null;
                    // $existingRegImage = $profile->registration_number_image ?? null;
                }

                // PAN image: required only if new user OR edit with no existing image
                if (!$isEdit || empty($existingPanImage)) {
                    $rules['pan_image']                 = 'required|mimes:jpg,jpeg,png,pdf|max:2048';
                    $messages['pan_image.required']     = 'PAN image is required';
                } else {
                    $rules['pan_image'] = 'nullable|mimes:jpg,jpeg,png,pdf|max:2048';
                }

                // Registration image: required only if new user OR edit with no existing image
                // if (!$isEdit || empty($existingRegImage)) {
                //     $rules['registration_number_image']             = 'required|mimes:jpg,jpeg,png,pdf|max:2048';
                //     $messages['registration_number_image.required'] = 'Registration image is required';
                // } else {
                //     $rules['registration_number_image'] = 'nullable|mimes:jpg,jpeg,png,pdf|max:2048';
                // }
            }

            $validate = Validator::make($request->all(), $rules, $messages);
            if ($validate->fails()) throw new Exception($validate->errors()->first(), 1);

            $post          = $request->all();
            $post['orgid'] = session('orgid');

            if ($request->hasFile('image'))                     $post['image']                     = $request->file('image');
            if ($request->hasFile('pan_image'))                 $post['pan_image']                 = $request->file('pan_image');
            // if ($request->hasFile('registration_number_image')) $post['registration_number_image'] = $request->file('registration_number_image');

            DB::beginTransaction();

            if ($isEdit) {
                $post['userid'] = $post['id'];
                User::updateUser($post);

                DB::table('users')->where('id', $post['id'])->update([
                    'name'       => $post['username'],
                    'email'      => $post['email'],
                    'updated_at' => now(),
                ]);

                if (!empty($post['roles'])) {
                    $user = User::find($post['id']);
                    if ($user) {
                        $roleNames = DB::table('roles')->whereIn('id', $post['roles'])->pluck('name')->toArray();
                        if (!empty($roleNames)) $user->syncRoles($roleNames);
                    }
                }

                $message = 'User updated successfully';
            } else {
                $post['type'] = 'user';
                if (!User::saveData($post)) throw new Exception('Could not save record', 1);
                $message = 'User saved successfully';
            }

            DB::commit();
            $type = 'success';
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

    public function customerForm(Request $request)
    {
        return view('backend.users.customer_form');
    }

    public function customerSave(Request $request)
    {
        try {
            $isWholesaler = $request->role_type === 'Wholesaler';

            $rules = [
                'first_name' => 'required|min:3|max:255',
                'last_name'  => 'required|max:255',
                'phone'      => 'required|min:5|max:20',
                'address'    => 'required',
                'email'      => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) {
                        if (DB::table('users')->where('email', $value)->exists()) {
                            $fail('The email has already been taken.');
                        }
                    },
                ],
                'username'  => 'required',
                'gender'    => 'required',
                'role_type' => 'required|in:Wholesaler,Retailer',
                'image'     => 'nullable|mimes:jpg,jpeg,png|max:2048',
            ];

            $messages = [
                'first_name.required' => 'Please enter first name',
                'first_name.min'      => 'First name must be at least 3 characters',
                'last_name.required'  => 'Please enter last name',
                'phone.required'      => 'Phone number is required',
                'address.required'    => 'Address is required',
                'email.required'      => 'Email is required',
                'email.email'         => 'Please enter a valid email',
                'username.required'   => 'Username is required',
                'gender.required'     => 'Gender is required',
                'role_type.required'  => 'Please select Wholesaler or Retailer',
                'role_type.in'        => 'Please select Wholesaler or Retailer',
                'image.mimes'         => 'Profile image must be jpg, jpeg, or png',
                'image.max'           => 'Profile image must not exceed 2MB',
            ];

            if ($isWholesaler) {
                $rules['company_name'] = 'required|max:255';
                $rules['pan_number']   = 'required|max:100';
                $rules['pan_image']    = 'required|mimes:jpg,jpeg,png,pdf|max:2048';

                $messages['company_name.required'] = 'Company name is required for Wholesaler';
                $messages['pan_number.required']   = 'PAN number is required for Wholesaler';
                $messages['pan_image.required']    = 'PAN image is required for Wholesaler';
            }

            $validate = Validator::make($request->all(), $rules, $messages);
            if ($validate->fails()) throw new Exception($validate->errors()->first(), 1);

            $roleId = DB::table('roles')->where('name', $request->role_type)->value('id');
            if (!$roleId) throw new Exception('Selected role is not configured.', 1);

            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $post['type']  = 'user';
            $post['roles'] = [$roleId];

            if ($request->hasFile('image'))     $post['image']     = $request->file('image');
            if ($request->hasFile('pan_image')) $post['pan_image'] = $request->file('pan_image');

            $newUuid = User::saveData($post);
            if (!$newUuid) throw new Exception('Could not save customer', 1);

            $fullName = trim($post['first_name'] . ' ' . ($post['middle_name'] ?? '') . ' ' . $post['last_name']);
            $fullName = preg_replace('/\s+/', ' ', $fullName);

            $type    = 'success';
            $message = 'Customer created successfully';
            $customer = ['id' => $newUuid, 'username' => $fullName];
        } catch (QueryException $e) {
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(array_merge(
            ['type' => $type, 'message' => $message],
            isset($customer) ? ['customer' => $customer] : []
        ));
    }

    public function list(Request $request)
    {
        try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $data          = User::list($post);
            // dd($data);

            $i            = 0;
            $array        = [];
            $totalrecs    = $data["totalrecs"];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $totalrecs);

            foreach ($data["data"] as $row) {
                $array[$i]["sno"]     = $i + 1;
                $array[$i]["name"]    = $row->name;
                $array[$i]["email"]   = $row->email;
                $array[$i]["address"] = $row->address ?? '-';
                $array[$i]["phone"]   = $row->phone ?? '-';

                $array[$i]["status"] = '
                <select class="form-select form-select-sm changeActiveStatus" data-id="' . $row->id . '" data-prev="' . $row->user_status . '" style="min-width:100px;">
                    <option value="Approve" ' . ($row->user_status == "Approve" ? "selected" : "") . '>Active</option>
                    <option value="Pending" ' . ($row->user_status != "Approve" ? "selected" : "") . '>Inactive</option>
                </select>';

                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewOrg   " style="color:green;font-size:18px;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a> ';
                $action .= '<a href="javascript:;" title="Edit"   class="tooltipdiv editOrg   " style="color:#696cff;font-size:18px;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a> ';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteOrg " style="color:red;font-size:18px;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                $array[$i]["action"] = $action;

                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;
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
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array,
        ]);
    }

    public function inActivelist(Request $request)
    {
        try {
            $post                 = $request->all();
            $post['orgid']        = session('orgid');
            $post['inactiveuser'] = 'Y';
            $data                 = User::list($post);

            $i            = 0;
            $array        = [];
            $totalrecs    = $data["totalrecs"];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $totalrecs);

            foreach ($data["data"] as $row) {
                $array[$i]["sno"]     = $i + 1;
                $array[$i]["name"]    = $row->name;
                $array[$i]["email"]   = $row->email;
                $array[$i]["address"] = $row->address ?? '-';
                $array[$i]["phone"]   = $row->phone ?? '-';
                $array[$i]["type"]    = $row->type ?? '-';

                $array[$i]["user_status"] = '
                <select class="form-select form-select-sm changeStatus" data-id="' . $row->id . '" data-original="' . $row->user_status . '" style="min-width:100px;">
                    <option value="Pending" ' . ($row->user_status == "Pending" ? "selected" : "") . '>Pending</option>
                    <option value="Approve" ' . ($row->user_status == "Approve" ? "selected" : "") . '>Approve</option>
                    <option value="Reject"  ' . ($row->user_status == "Reject"  ? "selected" : "") . '>Reject</option>
                </select>';

                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewOrgInactive  " style="color:green;font-size:18px;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a> ';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteOrgInactive" style="color:red;font-size:18px;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                $array[$i]["action"] = $action;

                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;
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
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array,
        ]);
    }

    public function form(Request $request)
    {
        try {
            $data              = [];
            $data['rolesList'] = Role::getRole();
            if (!empty($request->id)) {
                $result = DB::table('users as u')
                    ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
                    ->select(
                        'u.id',
                        'u.name as username',
                        'u.email',
                        'p.first_name',
                        'p.middle_name',
                        'p.last_name',
                        'p.gender',
                        'p.phone',
                        'p.address',
                        'p.image',
                        'p.company_name',
                        // 'p.tax_number',
                        // 'p.registration_number',
                        'p.pan_number',
                        'p.pan_image',
                        // 'p.registration_number_image'
                    )
                    ->where('u.id', $request->id)
                    ->first();

                if (!$result) throw new Exception("User not found", 1);

                $userRoles = DB::table('model_has_roles')
                    ->where('model_id', $result->id)
                    ->pluck('role_id')
                    ->toArray();

                $data['id']                        = $result->id;
                $data['username']                  = $result->username;
                $data['first_name']                = $result->first_name;
                $data['middle_name']               = $result->middle_name;
                $data['last_name']                 = $result->last_name;
                $data['gender']                    = $result->gender;
                $data['phone']                     = $result->phone;
                $data['address']                   = $result->address;
                $data['email']                     = $result->email;
                $data['userRoles']                 = $userRoles;
                $data['company_name']              = $result->company_name;
                // $data['tax_number']                = $result->tax_number;
                // $data['registration_number']       = $result->registration_number;
                $data['pan_number']                = $result->pan_number;
                $data['pan_image']                 = $result->pan_image;
                // $data['registration_number_image'] = $result->registration_number_image;

                if (!empty($result->image)) $data['image'] = $result->image;
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.users.form', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = "Record deleted successfully";
            $post    = $request->all();

            if (empty($post['id'])) throw new Exception("No ID provided", 1);

            DB::beginTransaction();

            $profile = DB::table('profiles')->where('user_id', $post['id'])->first();
            if ($profile) {
                foreach (['image', 'pan_image', 'registration_number_image'] as $field) {
                    if (!empty($profile->$field)) {
                        $path = storage_path('app/public/profiles/' . $profile->$field);
                        if (file_exists($path)) unlink($path);
                    }
                }
            }

            DB::table('profiles')->where('user_id', $post['id'])->delete();
            DB::table('userorganizations')->where('userid', $post['id'])->delete();
            DB::table('model_has_roles')->where('model_id', $post['id'])->delete();
            DB::table('users')->where('id', $post['id'])->delete();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function view(Request $request)
    {
        try {
            if (empty($request->id)) throw new Exception("No ID provided");

            $userDetails = DB::table('users as u')
                ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
                ->select(
                    'u.id',
                    'u.name',
                    'u.email',
                    'u.phone',
                    'u.user_status',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'p.username',
                    'p.gender',
                    'p.phone as profile_phone',
                    'p.address',
                    'p.image',
                    'p.type',
                    'p.company_name',
                    // 'p.tax_number',
                    // 'p.registration_number',
                    'p.pan_number',
                    'p.pan_image',
                    // 'p.registration_number_image'
                )
                ->where('u.id', $request->id)
                ->first();

            if (!$userDetails) throw new Exception("User not found");

            $fullName = trim(
                ($userDetails->first_name ?? '') . ' ' .
                    ($userDetails->middle_name ?? '') . ' ' .
                    ($userDetails->last_name ?? '')
            );
            if ($fullName) $userDetails->name = $fullName;

            $userRoles = DB::table('model_has_roles as mhr')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('mhr.model_id', $userDetails->id)
                ->pluck('r.name')
                ->toArray();

            $userDetails->role_names    = $userRoles;
            $userDetails->is_wholesaler = collect($userRoles)
                ->contains(fn($r) => str_contains(strtolower($r), 'wholesaler'));

            $data = ['orgDetails' => $userDetails, 'type' => 'success', 'message' => 'Successfully fetched user data.'];
        } catch (QueryException $e) {
            $data = ['type' => 'error', 'message' => 'Something went wrong.'];
        } catch (Exception $e) {
            $data = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return view('backend.users.view', $data);
    }

    public function updateStatus(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if (!$user) return response()->json(['type' => 'error', 'message' => 'User not found']);

            $user->user_status = $request->status;
            $user->remarks     = $request->remark;
            $user->save();

            if ($request->status == 'Approve') {
                Mail::to($user->email)->queue(new UserApprovedMail($user));
            } elseif ($request->status == 'Reject') {
                Mail::to($user->email)->send(new UserRejectedMail($user));
            }

            return response()->json(['type' => 'success', 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function tabs(Request $request)
    {
        switch ($request->input('tabid')) {
            case 'active':
                return view('backend.users.index');
            case 'inactive':
                return view('backend.users.inactiveuser');
            default:
                return '<div class="alert alert-warning">Invalid tab</div>';
        }
    }
}