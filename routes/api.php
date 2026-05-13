<?php

use App\Http\Controllers\API\ApiAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryListController;
use App\Http\Controllers\API\ChatController as APIChatController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\SocialAuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\HelloController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\LocationTrackerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\UserAddressController;
use App\Http\Controllers\BackPanel\OtpController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\API\SearchHistoryController;
use App\Http\Controllers\TransactionController;

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello']);
}); // Public routes
Route::post('login',    [ApiAuthController::class, 'login']);
Route::post('retailer/register', [ApiAuthController::class, 'retailerRegister']);
Route::post('wholesaler/register', [ApiAuthController::class, 'wholesalerRegister']);



// Redirect to provider\
Route::get('auth/{provider}/redirect',  [SocialAuthController::class, 'redirect']);

// Callback from provider
Route::get('auth/{provider}/call-back',  [SocialAuthController::class, 'callback']);


//save user address 
Route::post('user/address/save', [UserAddressController::class, 'save']);


Route::post('/send-otp', [ApiAuthController::class, 'sendOtp']);
Route::post('/verify-otp', [ApiAuthController::class, 'verifyOtp']);
Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);
Route::get('/items/latest', [ItemController::class, 'latest']);

Route::get('/items/search', [ItemController::class, 'search']);
Route::get('/item/detail/{variationid}', [ItemController::class, 'getDetails']);


Route::middleware('auth:api')->group(function () {
    Route::post('/items/search/save', [SearchHistoryController::class, 'searchSave']);
    Route::delete('/items/search/delete/{searchid}', [SearchHistoryController::class, 'searchDelete']);

    Route::put('/change/password',      [ApiAuthController::class, 'changePassword']);
    Route::post('logout',          [ApiAuthController::class, 'logout']);
    Route::post('roleCheck',          [ApiAuthController::class, 'roleCheck']);
    Route::post('refresh',         [ApiAuthController::class, 'refresh']);
    Route::get('user/detail',               [ApiAuthController::class, 'userDetail']);
    Route::put('user/update',        [ApiAuthController::class, 'updateProfile']);

    Route::post('/eswea/transaction', [TransactionController::class, 'saveEsewaTransaction']);
    Route::post('/khalti/transaction', [TransactionController::class, 'saveKhaltiTransaction']);

    Route::post('user/address/save', [UserAddressController::class, 'save']);
    Route::put('user/address/update', [UserAddressController::class, 'updateAddress']);
    Route::get('user/address/fetch', [UserAddressController::class, 'fetchAddress']);
    Route::put('user/address/update-active', [UserAddressController::class, 'updateAddressActive']);

    Route::get('/user/location/{customerid}', [UserAddressController::class, 'getLocation']);
    Route::post('/save/tracking/location', [LocationTrackerController::class, 'saveLocation']);

    Route::post('/user/order/status', [OrderController::class, 'orderStatus']);


    // 1. Personalised items based on user's last N orders
    Route::get('/items/recommendation', [ItemController::class, 'recommended']);

    // 2. Latest items (newest created_at)

    // 3. Search — name, category, variant, anything related
    // Route::get('/variations/search', [ItemController::class, 'searchVariation']);
    // Route::get('/categories/search', [ItemController::class, 'searchCategory']);

    Route::post('/save/favourite', [FavouriteController::class, 'saveData']);
    Route::get('/user/favourite/list', [FavouriteController::class, 'getFavouriteList']);
    Route::delete('/user/favourite/delete/{variationid}', [FavouriteController::class, 'deleteFavourite']);

    Route::get('/categories/list', [CategoryListController::class, 'getCategoryList']);


    Route::post('/addtocart',    [CartController::class, 'saveAddToCart']);
    Route::get('/cart/list',    [CartController::class, 'getList']);
    Route::delete('/cart/delete/{variationid}',    [CartController::class, 'deleteCart']);
    Route::delete('/cart/remove/{variationid}',    [CartController::class, 'removeCart']);


    Route::post('/order/save',    [OrderController::class, 'save']);

    Route::post('/order/sendOtp', [OtpController::class, 'sendOrderOtp']);

    Route::post('/order/checkOtp', [OtpController::class, 'verifyOtp']);

    Route::get('/uses/order/history', [ItemController::class, 'getUserOrderHistory']);


    Route::get('/items/search/history', [ItemController::class, 'searchHistory']);


    Route::get('/success', [PaymentController::class, 'successEsewa']);
    Route::get('/failure', [PaymentController::class, 'failure']);

    // Route::post('/chat', [ChatController::class, 'message']);

    Route::get('/chat',       [APIChatController::class, 'index']);
    Route::post('/chat/user', [APIChatController::class, 'store']);

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/chat/conversations', [APIChatController::class, 'conversations']);
        Route::get('/admin/chat/thread/{user}', [APIChatController::class, 'thread']);
        Route::post('/admin/chat/reply',        [APIChatController::class, 'store']);
    });
});
