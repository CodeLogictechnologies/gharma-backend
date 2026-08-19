<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SessionController;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends SessionController
{
    function index()
    {
        return view('backend.auth.login');
    }

    function resetPasswordForm()
    {
        return view('backend.auth.reset_password');
    }

    function loginUser(Request $request)
    {
        $post = $request->all();

        $rules = [
            'email'    => 'required|max:50',
            'password' => 'required|max:50',
        ];
        $messages = [
            'email.required'    => 'Email or Username field is required',
            'password.required' => 'Password field is required',
        ];

        $validate = Validator::make($post, $rules, $messages);
        if ($validate->fails()) {
            throw new Exception($validate->errors()->first());
        }

        $user = User::where('email', $post['email'])
            ->orWhere('name', $post['email'])
            ->first();

        if (!$user) {
            throw new Exception('Invalid email/username or password.');
        }

        $credentials = [
            'email'    => $user->email,
            'password' => $post['password'],
        ];

        if (!Auth::attempt($credentials)) {
            throw new Exception('Invalid email/username or password.');
        }

        if ($user->user_status !== 'Approve') {
            Auth::logout();
            throw new Exception('Your account is ' . strtolower($user->user_status) . '. Please contact the administrator.');
        }
        
        // dd(auth()->user()->getRoleNames());
        $org = DB::table('userorganizations')
            ->where('userid', $user->id)
            ->first();

        if (!$org) {
            throw new Exception('Organization not found for this user.');
        }

        // Get organization details with current_fiscal_year_id
        $organization = DB::table('organizations')
            ->where('id', $org->orgid)
            ->select('id', 'current_fiscal_year_id')
            ->first();

        // Get fiscal year from organizations.current_fiscal_year_id
        $fiscalYear = null;
        if (!empty($organization->current_fiscal_year_id)) {
            $fiscalYear = DB::table('fiscal_years')
                ->where('id', $organization->current_fiscal_year_id)
                ->where('status', 'Y')
                ->select('id', 'code')
                ->first();
        }


        $this->setSession($user);

        session([
            'orgid'            => $org->orgid,
            'fiscal_year_id'   => $fiscalYear->id ?? null,
            'fiscal_year_code' => $fiscalYear->code ?? null,
        ]);

        return response()->json([
            'type'    => 'success',
            'message' => 'Login successful',
            'url'     => '/admin/dashboard',
        ]);
    }

    public function logOut()
    {
        if (!Auth::user()) {
            return redirect('/')->with('error', 'Please login first.');
        }
        if (!Auth::logout()) {
            return redirect('/')->with('success', 'Successfully logged out.');
        } else {
            return redirect()->back()->with('error', 'Not able to logout.');
        }
    }

    public function profile()
    {
        $userData = DB::table('users as u')
            ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
            ->where('u.id', auth()->id())
            ->select('u.id', 'u.name', 'u.email', 'p.image', 'p.address')
            ->first();

        return view('backend.profile.index', ['userData' => $userData]);
    }

    public function getTabContent(Request $request)
    {
        $post = $request->all();
        if ($post['id'] == "editprofile") {
            $user = DB::table('users')
                ->leftJoin('registrations', 'users.id', '=', 'registrations.user_id')
                ->where('users.id', auth()->id())
                ->select('users.name', 'users.email', 'registrations.email as registration_email', 'registrations.full_name')
                ->first();
            $data['userData'] = $user;
            return view('backend.profile.editprofile', $data);
        } else {
            $user = User::where(['id' => auth()->id()])->first();
            return view('backend.profile.setting');
        }
    }

    // reset password (redirect-based)
    public function resetPassword(Request $request)
    {
        try {
            $rules = [
                'current_password' => 'required|max:250',
                'password'         => 'required|max:250',
                'confirm_password' => 'required|max:250',
            ];
            $message = [
                'current_password.required' => 'Please enter current password',
                'password.required'         => 'Please enter new password',
                'confirm_password.required' => 'Please enter confirm password',
            ];

            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post    = $request->all();
            $type    = 'success';
            $message = 'Password is updated successfully.';

            DB::beginTransaction();
            if (!User::updatepassword($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        return redirect('/admin/login')->with('success', 'password changed successfully');
    }

    // update password (AJAX-based)
    public function updatePassword(Request $request)
    {
        try {
            $rules = [
                'current_password' => 'required|max:250',
                'password'         => 'required|max:250',
                'confirm_password' => 'required|max:250',
            ];
            $message = [
                'current_password.required' => 'Please enter current password',
                'password.required'         => 'Please enter new password',
                'confirm_password.required' => 'Please enter confirm password',
            ];

            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                return json_encode(['type' => 'error', 'message' => $validation->errors()->first()]);
            }

            $post    = $request->all();
            $type    = 'success';
            $message = 'Password is updated successfully.';

            DB::beginTransaction();
            if (!User::updatepassword($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message =  'Something went wrong. Please try again.';
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    // update profile name/email/address
    public function updateProfileAll(Request $request)
    {
        try {
            $rules = ['email' => 'required|email'];
            $message = [
                'email.required' => 'Email field is required',
                'email.email'    => 'Email is not valid',
            ];

            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                return json_encode(['type' => 'error', 'message' => $validation->errors()->first()]);
            }

            $type    = 'success';
            $message = 'Record saved successfully';

            DB::beginTransaction();

            // Update users table
            DB::table('users')->where('id', auth()->id())->update([
                'name'       => $request->name,
                'updated_at' => now(),
            ]);

            // Update profiles table (find by username to handle user_id mismatch)
            $profile = DB::table('profiles')
                ->where('username', auth()->user()->name)
                ->first();

            if ($profile) {
                DB::table('profiles')->where('id', $profile->id)->update([
                    'address'    => $request->address,
                    'user_id'    => auth()->id(), // ✅ fix mismatch
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            $type    = 'error';
            $message = 'Database error occurred.';
        } catch (Exception $e) {
            DB::rollback();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    // upload profile image
    public function uploadImage(Request $request)
    {
        try {
            $rules = [
                'image' => 'required|file|mimes:jpg,jpeg,png|max:2048'
            ];
            $message = [
                'image.required' => 'Please select file.',
                'image.mimes'    => 'Supported files are (JPEG, JPG, PNG) only.'
            ];

            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $type    = 'success';
            $message = 'Profile picture updated successfully';

            DB::beginTransaction();

            $file     = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $fileName, 'public');

            //  Find profile by username (bypasses user_id mismatch)
            // $profile = DB::table('profiles')->where('user_id', auth()->id())->first();

            // if (!$profile) {
            //     throw new Exception('Profile not found.');
            // }

            $profile = DB::table('profiles')->where('user_id', auth()->id())->first();

            if (!$profile) {
                $newId = (string) Str::uuid();
                DB::table('profiles')->insert([
                    'id'         => $newId,
                    'user_id'    => auth()->id(),
                    'username'   => auth()->user()->name,
                    'first_name' => auth()->user()->name,
                    'last_name'  => '',
                    'status'     => 'Y',
                    'orgid'      => session('orgid'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $profile = DB::table('profiles')->where('id', $newId)->first();
            }

            //  Delete old image
            if (!empty($profile->image)) {
                $oldPath = storage_path('app/public/profiles/' . $profile->image);
                if (file_exists($oldPath)) unlink($oldPath);
            }

            //  Update image + fix user_id mismatch permanently
            DB::table('profiles')
                ->where('id', $profile->id)
                ->update([
                    'image'      => $fileName,
                    'user_id'    => auth()->id(), // ✅ auto-fix broken user_id
                    'updated_at' => now(),
                ]);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = 'Database error occurred.';
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function esewa(Request $request)
    {
        return view('backend.organization.index');
    }

    public function success(Request $request)
    {
        $decoded = base64_decode($request->data);

        $verified = $this->verifyEsewa($txn);

        if ($verified) {
            Transaction::where('txncode', $txn)->update(['method' => 'SUCCESS']);
            return view('esewa.success');
        }

        return view('esewa.failed');
    }

    public function failure(Request $request)
    {
        $txn = $request->transaction_uuid;

        Transaction::where('txncode', $txn)->update(['status' => 'FAILED']);

        return view('esewa.failed');
    }
}