<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OTPRequest;
use App\Models\API\Userdevicetoken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;
use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\DB;

class ApiAuthController extends Controller
{
    // -----------------------------------------------------------------------
    // Helper: greeting + redirect based on Spatie role
    // -----------------------------------------------------------------------
    private function getRoleResponse(User $user): array
    {
        if ($user->hasRole('admin')) {
            return [
                'role'         => 'admin',
                'greeting'     => 'Hello Admin! Welcome back.',
                'redirect_url' => '/admin/dashboard',
            ];
        }

        if ($user->hasRole('user')) {
            return [
                'role'         => 'user',
                'greeting'     => 'Hello User! Welcome back.',
                'redirect_url' => '/user/dashboard',
            ];
        }

        // Fallback — no role assigned
        return [
            'role'         => null,
            'greeting'     => 'Hello! Welcome back.',
            'redirect_url' => '/home',
        ];
    }

    // -----------------------------------------------------------------------
    // POST /api/register
    // -----------------------------------------------------------------------
    public function retailerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string|max:255|unique:users',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:6|confirmed',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required',
            'address'     => 'required',
            'phone'       => 'required',
            'image'       => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $post = $request->all();
        $post['type'] = 'retailer';
        User::saveData($post);

        $user  = User::where('email', $post['email'])->first();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'type'    => 'success',
            'message'    => 'Retailer registered successfully.',
            'token'      => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
        ], 201);
    }

    public function wholesalerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:6|confirmed',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required',
            'address'     => 'required',
            'phone'       => 'required',
            'image'       => 'required',
            'company_name'       => 'required',
            'tax_number'       => 'required',
            'registration_number'       => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $post = $request->all();
        $post['type'] = 'wholesaler';
        User::saveData($post);

        $user  = User::where('email', $post['email'])->first();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'    => true,
            'message'    => 'Wholesaler registered successfully.',
            'token'      => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
        ], 201);
    }

    // -----------------------------------------------------------------------
    // POST /api/login
    // -----------------------------------------------------------------------
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'    => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('phone', 'password');
        $post = $request->all();
        try {
            if (!$token = JWTAuth::attempt($credentials)) {


                return response()->json([
                    'type' => 'error',
                    'message' => 'Invalid phone number or password.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Could not create token. Please try again.',
            ], 500);
        }

        $user = auth()->user();
        $post['userid'] = $user->id;
        $deviceToke = Userdevicetoken::saveDate($post);

        // Merge base response with role-based greeting + redirect
        return response()->json(array_merge([
            'type'    => 'success',
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
            // 'roles'      => $user->getRoleNames(),
        ]));
    }

    // -----------------------------------------------------------------------
    // POST /api/logout  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'type' => 'success',
                'message' => 'Successfully logged out.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Failed to logout. Token may already be invalid.',
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // POST /api/refresh  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success'    => true,
                'token'      => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token cannot be refreshed. Please login again.',
            ], 401);
        }
    }

    // -----------------------------------------------------------------------
    // GET /api/me  (requires Bearer token)
    // -----------------------------------------------------------------------

    public function userDetail(Request $request)
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                throw new Exception("Unauthorized user");
            }

            $post = $request->all();
            $post['userid'] = $user->id;

            $result = User::getDataUser($post);

            if (!$result) {
                throw new Exception("User not found");
            }

            return response()->json([
                'type' => 'success',
                'message' => 'User fetched successfully',
                'data' => $result
            ]);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong'
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    // -----------------------------------------------------------------------
    // PUT /api/me/update  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function updateProfile(Request $request)
    {
        // try {

            $user = auth('api')->user();

            if (!$user) {
                throw new Exception("Unauthorized user");
            }

            $post = $request->all();
            $post['userid'] = $user->id;

            $result = User::updateUser($post);

            // if (!$result) {
            //     throw new Exception("User not updated");
            // }

            return response()->json([
                'type' => 'success',
                'message' => 'User updated successfully',
            ]);
        // } catch (QueryException $e) {

        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Something went wrong'
        //     ], 500);
        // } catch (Exception $e) {

        //     return response()->json([
        //         'type' => 'error',
        //         'message' => $e->getMessage()
        //     ], 400);
        // }
    }
    // -----------------------------------------------------------------------
    // PUT /api/me/password  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function changePassword(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = DB::table('users')->where('id', $user->id)->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'type' => "error",
                'message' => 'Current password is incorrect.',
            ], 400);
        }
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($request->password),
            ]);


        return response()->json([
            'type' => "success",
            'message' => 'Password changed successfully.',
        ]);
    }


    public function roleCheck(Request $request)
    {
        $post = $request->all();

        $checkUser = DB::table('model_has_roles as mr')
            ->join('users as u', 'u.id', '=', 'mr.model_id')
            ->join('roles as r', 'r.id', '=', 'mr.role_id')
            ->where('u.id', $post['userid'])
            ->where('r.id', $post['roleid'])
            ->select('r.name')
            ->first();

        if ($checkUser) {
            $message = 'Hello ' . $checkUser->name;
        } else {
            $message = 'User does not have this role';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function sendOtp(OTPRequest $request)
    {
        try {
            $type = 'success';
            $message = 'OTP send successfully';

            $post = $request->all();

            DB::beginTransaction();
            if (!Otp::sendOtp($post)) {
                throw new Exception('Could not send opt', 1);
            }
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

    // 2. VERIFY OTP
    public function verifyOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        $record = DB::table('otps')
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return response()->json([
                'type' => 'error',
                'message' => 'Invalid or expired OTP'
            ]);
        }

        return response()->json([
            'type' => 'success',
            'message' => 'OTP verified'
        ]);
    }

    // 3. RESET PASSWORD
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        $record = DB::table('otps')
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return response()->json([
                'type' => 'error',
                'message' => 'Invalid OTP or expired OTP'
            ]);
        }

        $email = $record->email;
        $user = User::where('email', $email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        // delete OTP
        DB::table('otps')->where('email', $request->email)->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Password reset successful'
        ]);
    }
}