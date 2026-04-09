<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class SocialAuthController extends Controller
{
    protected $providers = ['google', 'facebook', 'github'];

    // ✅ Step 1: Redirect to provider
    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported.',
            ], 422);
        }

        $url = Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        // ✅ Return unescaped JSON
        return response()->json([
            'success' => true,
            'url'     => $url,
        ], 200, [], JSON_UNESCAPED_SLASHES); // ✅ this fixes \/  to /
    }

    // ✅ Step 2: Handle callback
    public function callback($provider)
    {

        if (!in_array($provider, $this->providers)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not supported.',
            ], 422);
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or token expired.',
            ], 401);
        }

        // dd($socialUser);
        // ✅ Find or create user
        // $user = User::updateOrCreate(
        //     [
        //         'email' => $socialUser->getEmail(),
        //     ],
        //     [
        //         'id'             => (string) Str::uuid(),
        //         'name'           => $socialUser->getName(),
        //         'provider'       => $provider,
        //         'provider_id'    => $socialUser->getId(),
        //         'provider_token' => $socialUser->token,
        //         'password'       => null,
        //     ]
        // );


        $user = User::updateOrCreate(
            [
                // ✅ Search by email OR provider_id
                'email' => $socialUser->getEmail(),
            ],
            [
                // ✅ No id here — boot() auto-generates UUID on create only
                'name'           => $socialUser->getName(),
                'provider'       => $provider,
                'provider_id'    => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'avatar'         => $socialUser->getAvatar(), // ✅ save avatar too
                'password'       => null,
                'orgid'          => 'a20a5adb-1679-474d-a30c-3455d030d8e6',
            ]
        );

        // ✅ Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'       => $user,
        ]);
    }
}
