<?php

use App\Http\Controllers\API\ApiAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\SocialAuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\HelloController;

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello']);
}); // Public routes
Route::post('login',    [ApiAuthController::class, 'login']);
Route::post('register', [ApiAuthController::class, 'register']);


// Redirect to provider
Route::get('auth/{provider}/redirect',  [SocialAuthController::class, 'redirect']);

// Callback from provider
Route::get('auth/{provider}/call-back',  [SocialAuthController::class, 'callback']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout',          [ApiAuthController::class, 'logout']);
    Route::post('roleCheck',          [ApiAuthController::class, 'roleCheck']);
    Route::post('refresh',         [ApiAuthController::class, 'refresh']);
    Route::get('me',               [ApiAuthController::class, 'me']);
    Route::put('me/update',        [ApiAuthController::class, 'updateProfile']);
    Route::put('me/password',      [ApiAuthController::class, 'changePassword']);
});
