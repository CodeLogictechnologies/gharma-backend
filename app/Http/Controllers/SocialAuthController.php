<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class SocialAuthController extends Controller
{
    protected $providers = ['google', 'facebook', 'github'];

    // ✅ Step 1: Redirect to provider
    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return response()->json([
                'type' => 'error',
                'message' => 'Provider not supported.',
            ], 422);
        }

        $url = Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        // ✅ Return unescaped JSON
        return response()->json([
            'type' => 'success',
            'url'     => $url,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // ✅ Step 2: Handle callback
    public function callback($provider)
    {
        // ✅ Check provider
        if (!in_array($provider, $this->providers)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported.',
            ], 422);
        }

        try {
            // ✅ Get Google user
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or token expired.',
            ], 401);
        }
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();
        // ✅ CREATE / UPDATE USER (DO NOT CHANGE ID)

        if (!$user) {
            // 🟢 CREATE NEW USER WITH UUID
            $user = User::create([
                'id'              => (string) Str::uuid(), // ✅ ONLY HERE
                'email'           => $socialUser->getEmail(),
                'name'            => $socialUser->getName(),
                'provider'        => $provider,
                'provider_id'     => $socialUser->getId(),
                'provider_token'  => $socialUser->token,
                'avatar'          => $socialUser->getAvatar(),
                'password'        => 'testing',
                'phone'           => null,
            ]);
        } else {
            // 🟡 UPDATE EXISTING USER (NO ID CHANGE)
            $user->update([
                'name'            => $socialUser->getName(),
                'email'           => $socialUser->getEmail(),
                'provider_token'  => $socialUser->token,
                'avatar'          => $socialUser->getAvatar(),
            ]);
        }

        // ✅ Get first organization
        $firstOrg = DB::table('userorganizations')->value('orgid');

        if (!$firstOrg) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found.',
            ], 422);
        }

        // ✅ Check if mapping already exists (avoid duplicates)
        $exists = DB::table('userorganizations')
            ->where('userid', $user->id)
            ->where('orgid', $firstOrg)
            ->exists();

        if (!$exists) {
            DB::table('userorganizations')->insert([
                'id'         => (string) Str::uuid(),
                'userid'     => $user->id,
                'orgid'      => $firstOrg,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // ✅ Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            // 'user'       => $user,
        ]);
    }
}