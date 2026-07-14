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
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Carbon\Carbon;
use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\JsonResponse;
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
        $post = $request->all();

        // Normalize gender casing 
        if (!empty($post['gender'])) {
            $post['gender'] = ucfirst(strtolower(trim($post['gender'])));
        }
        $validator = Validator::make($post, [
            'username'    => 'required|string|max:255|unique:users,name',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:6|confirmed',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required|in:Male,Female,Other',
            'address'     => 'required',
            'phone'       => 'required',
            'image'       => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type'    => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $post['type'] = 'retailer';

        User::saveData($post);

        $user  = User::where('email', $post['email'])->first();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'type'       => 'success',
            'message'    => 'Retailer registered successfully.',
            'token'      => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
        ], 201);
    }

    public function wholesalerRegister(Request $request)
    {
        $post = $request->all();

        // Normalize gender casing
        if (!empty($post['gender'])) {
            $post['gender'] = ucfirst(strtolower(trim($post['gender'])));
        }
        $validator = Validator::make($post, [ 
            'username'            => 'required|string|max:255',
            'email'               => 'required|string|email|max:255|unique:users',
            'password'            => 'required|string|min:6|confirmed',
            'first_name'          => 'required|string|max:255',
            'middle_name'         => 'nullable|string|max:255',
            'last_name'           => 'required|string|max:255',
            'gender'              => 'required|in:Male,Female,Other',
            'address'             => 'required',
            'phone'               => 'required',
            'image'               => 'required',
            'company_name'        => 'required',
            'tax_number'          => 'required',
            'registration_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type'    => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // $post         = $request->all();
            $post['type'] = 'wholesaler';
            User::saveData($post);

            $user = User::where('email', $post['email'])->first();

            if (!$user) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'User registration failed. Please try again.',
                ], 500);
            }

            $token = JWTAuth::fromUser($user);

            $refreshToken = JWTAuth::claims([
                'type' => 'refresh',
                'exp'  => now()->addDays(30)->timestamp,
            ])->fromUser($user);

            return response()->json([
                'type'          => 'success',
                'message'       => 'Wholesaler registered successfully.',
                'token'         => $token,
                'token_type'    => 'bearer',
                'refresh_token' => $refreshToken,
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something Wrong',
            ], 500);
        } catch (JWTException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Token generation failed: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
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
                'type' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('phone', 'password');
        $post = $request->all();
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Invalid phone number or password.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Could not create token. Please try again.',
            ], 500);
        }

        $user           = auth()->user();
        $post['userid'] = $user->id;

        // ── Get Spatie roles ───────────────────────────────────────────
        $roles     = $user->getRoleNames();           // collection of role names
        $firstRole = $roles->first() ?? null;
        // $deviceToke = Userdevicetoken::saveDate($post);

        $roleData = DB::table('roles')
            ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->select('roles.id as role_id', 'roles.name as role_name')
            ->get();

        $refreshToken = JWTAuth::claims([
            'type' => 'refresh',
            'exp'  => now()->addDays(30)->timestamp,
        ])->fromUser($user);


        // Merge base response with role-based greeting + redirect
        return response()->json(array_merge([
            'type'    => 'success',
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'bearer',
            'refresh_token' => $refreshToken,
            // 'rolenamenote'         => $roles,                // e.g. ["Driver"]
            // 'expires_in'    => config('jwt.ttl') * 60, 
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
            // 'roles'      => $user->getRoleNames(),
            'roles'         => $roleData->map(fn($r) => [   // ← roles with id and name
                'roleid'   => $r->role_id,
                'rolename' => $r->role_name,
            ]),
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
            $oldToken = JWTAuth::getToken();
            if (!$oldToken) {
                return new JsonResponse([
                    "type"    => "error",
                    "message" => "Token not provided."
                ], 400);
            }

            // ── Decode and verify this is actually a refresh token ─────────
            $payload = JWTAuth::setToken($oldToken)->getPayload();

            if ($payload->get('type') !== 'refresh') {
                return new JsonResponse([
                    "type"    => "error",
                    "message" => "Invalid token type. Please provide the refresh token."
                ], 401);
            }

            // ── Check refresh token not expired ────────────────────────────
            if ($payload->get('exp') < now()->timestamp) {
                return new JsonResponse([
                    "type"    => "error",
                    "message" => "Refresh token expired. Please login again."
                ], 401);
            }

            // ── Issue a fresh access token for the user ────────────────────
            $user        = JWTAuth::setToken($oldToken)->toUser();
            $newAccess   = JWTAuth::fromUser($user);

            // ── Optionally issue a new refresh token too ───────────────────
            $newRefresh  = JWTAuth::claims([
                'type' => 'refresh',
                'exp'  => now()->addDays(30)->timestamp,
            ])->fromUser($user);

            return new JsonResponse([
                "type"          => "success",
                "token"         => $newAccess,
                "token_type"    => "bearer",
                "refresh_token" => $newRefresh,
            ]);
        } catch (TokenExpiredException $e) {
            return new JsonResponse([
                "type"    => "error",
                "message" => "Refresh token expired. Please login again."
            ], 401);
        } catch (JWTException $e) {
            return new JsonResponse([
                "type"    => "error",
                "message" => "Token cannot be refreshed."
            ], 401);
        }
    }


    // -----------------------------------------------------------------------
    // GET /api/me  (requires Bearer token)
    // -----------------------------------------------------------------------

    public function userDetail(Request $request)
    {
        // try {

        $user = auth('api')->user();

        if (!$user) {
            throw new Exception("Unauthorized user");
        }
        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');
        $post = $request->all();
        $post['userid'] = $profile['userid'];
        $post['orgid']  = $profile['orgid'];

        $result = User::getDataUser($post);

        if (!$result) {
            throw new Exception("User not found");
        }

        return response()->json([
            'type' => 'success',
            'message' => 'User fetched successfully',
            'data' => $result
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