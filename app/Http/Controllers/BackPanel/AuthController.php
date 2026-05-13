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
        // try {
        $post = $request->all();

        // Validation
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

        // Find user by email or username
        $user = User::where('email', $post['email'])
            ->orWhere('name', $post['email'])
            ->first();

        if (!$user) {
            throw new Exception('Invalid email/username or password.');
        }

        // ✅ Attempt login using the found user's actual email
        $credentials = [
            'email'    => $user->email, // always use email for Auth::attempt
            'password' => $post['password'],
        ];

        if (!Auth::attempt($credentials)) {
            throw new Exception('Invalid email/username or password.');
        }
        $org = DB::table('userorganizations')
            ->where('userid', $user->id)
            ->first();

        if (!$org) {
            throw new Exception('Organization not found for this user.');
        }

        // Save session
        $this->setSession($user);

        session([
            'orgid' => $org->orgid
        ]);

        return response()->json([
            'type'    => 'success',
            'message' => 'Login successful',
            'url'     => '/admin/dashboard',
        ]);
        // } catch (QueryException $e) {
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => 'Database error: ' . $e->getMessage(),
        //     ], 500);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => $e->getMessage(),
        //     ], 401);
        // }
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
        $user = User::where(['id' => auth()->id()])->first();
        $data['userData'] = $user;
        return view('backend.profile.index', $data);
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



    //  //reset password-start
    public function resetPassword(Request $request)
    {
        try {

            $rules = [
                'current_password' => 'required|max:250',
                'password' => 'required|max:250',
                'confirm_password' => 'required|max:250',

            ];
            $message = [
                'current_password.required' => 'Please enter current password',
                'password.required' => 'Please enter new password',
                'confirm_password.required' => 'Please enter confirm password',

            ];

            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post = $request->all();
            $type = 'success';
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
    //reset password-end


    //update new password
    public function updatePassword(Request $request)
    {
        try {

            $rules = [
                'current_password' => 'required|max:250',
                'password' => 'required|max:250',
                'confirm_password' => 'required|max:250',

            ];
            $message = [
                'current_password.required' => 'Please enter current password',
                'password.required' => 'Please enter new password',
                'confirm_password.required' => 'Please enter confirm password',

            ];

            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                return json_encode(['type' => 'error', 'message' => $validation->errors()->first()]);
            }

            $post = $request->all();
            $type = 'success';
            $message = 'Password is updated successfully.';

            DB::beginTransaction();

            if (!User::updatepassword($post)) {
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


    //update profile name details
    public function updateProfileAll(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email',
            ];

            $message = [
                'email.required' => 'Email field is required',
                'email.email' => 'Email is not valid',

            ];
            $post = $request->all();
            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                return json_encode(['type' => 'error', 'message' => $validation->errors()->first()]);
            }

            $post = $request->all();
            $type = 'success';
            $message = 'Record saved successfully';

            DB::beginTransaction();

            if (!User::updatedata($post))
                throw new Exception("Couldn't Save Records", 1);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollback();
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message]);
    }



    //profile image upload
    public function uploadImage(Request $request)
    {
        try {
            $rules = [
                'image' => 'nullable|file|mimes:jpg,jpeg,png'
            ];

            $message = [
                'image.required' => 'Please select file.',
                'image.mimes' => 'Supported files are (JPEG,JPG,PNG) only.'
            ];
            $validation = Validator::make($request->all(), $rules, $message);
            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }
            $post = $request->all();
            $type = 'success';
            $message = 'Profile picture updated successfylly';

            DB::beginTransaction();

            if (!User::saveProfileImage($post)) {
                throw new Exception("error", 1);
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

    public function esewa(Request $request)
    {
        return view('backend.organization.index');
    }

    public function success(Request $request)
    {
        // $txn = $request->transaction_uuid;
        $decoded = base64_decode($request->data);
        dd($decoded);
        // STEP 1: verify with eSewa (IMPORTANT)
        $verified = $this->verifyEsewa($txn);

        if ($verified) {
            // STEP 2: update DB
            Transaction::where('txncode', $txn)->update([
                'method' => 'SUCCESS'
            ]);

            return view('esewa.success');
        }

        return view('esewa.failed');
    }

    public function failure(Request $request)
    {
        $txn = $request->transaction_uuid;

        Transaction::where('txncode', $txn)->update([
            'status' => 'FAILED'
        ]);

        return view('esewa.failed');
    }
}
