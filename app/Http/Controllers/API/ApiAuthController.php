<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
    public function register(Request $request)
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $post = $request->all();
        User::saveData($post);

        $user  = User::where('email', $post['email'])->first();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'    => true,
            'message'    => 'User registered successfully.',
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'       => $user,
        ], 201);
    }

    // -----------------------------------------------------------------------
    // POST /api/login
    // -----------------------------------------------------------------------
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token. Please try again.',
            ], 500);
        }

        $user = auth()->user();

        // Merge base response with role-based greeting + redirect
        return response()->json(array_merge([
            'success'    => true,
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'       => $user,
            'roles'      => $user->getRoleNames(),
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
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
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

    public function me(Request $request)
    {
        $user = auth('api')->user();

        $payload = JWTAuth::parseToken()->getPayload();

        // ✅ get profile
        $profile = $payload->get('profile');

        // ✅ extract user_id
        $userId = $profile['first_name'];

        dd($userId);

        return response()->json([
            'success' => true,
            'user'    => $user,
            'roles'   => $user->getRoleNames(),
            'user_id' => $userId,
        ]);
    }
    // -----------------------------------------------------------------------
    // PUT /api/me/update  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update($request->only('name', 'email'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user'    => $user->fresh(),
        ]);
    }

    // -----------------------------------------------------------------------
    // PUT /api/me/password  (requires Bearer token)
    // -----------------------------------------------------------------------
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
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