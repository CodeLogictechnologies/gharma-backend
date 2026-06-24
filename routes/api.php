<?php

use App\Http\Controllers\API\ApiAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryListController;
use App\Http\Controllers\API\ChatController as APIChatController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\RecentlyViewedController;
use App\Http\Controllers\API\HomeTabController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\HelloController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\RefundReturnController;
use App\Http\Controllers\API\LocationTrackerController;
use App\Http\Controllers\API\LoyaltyController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\UserAddressController;
use App\Http\Controllers\BackPanel\OtpController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\API\SearchHistoryController;
use App\Http\Controllers\AssignDriverController;
use App\Http\Controllers\BackPanel\AssignDriverController as BackPanelAssignDriverController;
use App\Http\Controllers\EsewaController;
use App\Http\Controllers\EsewaPaymentController;
use App\Http\Controllers\TransactionController;
use App\Models\Esewa;

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello']);
}); // Public routes
Route::post('login',    [ApiAuthController::class, 'login']);
Route::post('refresh', [ApiAuthController::class, 'refresh']);
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
Route::get('/item/by-product-code/{product_code}',   [ItemController::class, 'getByProductCode']);

// Brand list
Route::get('/brands/{brandid?}', [BrandController::class, 'index']);

// Store public routes{searchid}
Route::get('stores',       [App\Http\Controllers\API\StoreController::class, 'list']);
// category list
Route::get('/categories/list', [CategoryListController::class, 'getCategoryList']);
// Sub category list
Route::get('/subcategories/list', [CategoryListController::class, 'getSubCategoryList']);
// Home tab list with categories
Route::get('/home', [HomeTabController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('/items/search/save', [SearchHistoryController::class, 'searchSave']);
    Route::delete('/items/search/delete/', [SearchHistoryController::class, 'searchDelete']);

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
    Route::delete('user/address/delete/{id}', [UserAddressController::class, 'deleteAddress']);

    Route::get('/user/location/{customerid}', [UserAddressController::class, 'getLocation']);
    Route::post('/save/tracking/location', [LocationTrackerController::class, 'saveLocation']);
    Route::get('/driver/lastest/location', [LocationTrackerController::class, 'getOrderLocation']);

    Route::post('/user/order/status', [OrderController::class, 'orderStatus']);
    Route::get('/user/order/history', [ItemController::class, 'getUserOrderHistory']);

    // 1. Personalised items based on user's last N orders
    Route::get('/items/recommendation', [ItemController::class, 'recommended']);

    // 2. Latest items (newest created_at)

    // 3. Search — name, category, variant, anything related
    // Route::get('/variations/search', [ItemController::class, 'searchVariation']);
    // Route::get('/categories/search', [ItemController::class, 'searchCategory']);

    Route::post('/save/favourite', [FavouriteController::class, 'saveData']);
    Route::get('/user/favourite/list', [FavouriteController::class, 'getFavouriteList']);
    Route::delete('/user/favourite/delete/{variationid}', [FavouriteController::class, 'deleteFavourite']);



    Route::put('/addtocart',    [CartController::class, 'saveAddToCart']);
    Route::get('/cart/list',    [CartController::class, 'getList']);
    Route::delete('/cart/delete/{variationid}',    [CartController::class, 'deleteCart']);
    Route::delete('/cart/remove/{variationid}',    [CartController::class, 'removeCart']);

    Route::post('/order/save',    [OrderController::class, 'save']);

    Route::post('/order/sendOtp', [OtpController::class, 'sendOrderOtp']);

    Route::post('/order/checkOtp', [OtpController::class, 'verifyOtp']);

    Route::get('/uses/order/history', [ItemController::class, 'getUserOrderHistory']);

    // Recently Viewed
    Route::get('/recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('/recently-viewed/save', [RecentlyViewedController::class, 'store']);

    // Invoice
    Route::get('/invoice', [InvoiceController::class, 'show']);

    Route::get('/loyalty/list',    [LoyaltyController::class, 'getList']);
    Route::get('/coupon/list',    [CouponController::class, 'getCouponList']);

    // Refund-Return Policy
    Route::get('/refund-return/policy', [RefundReturnController::class, 'getReturnRefundPolicy']);

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

    Route::get('/driver/orderlist/', [BackPanelAssignDriverController::class, 'getOrderListAll']);
    // Route::get('/driver/orderlist/datewise',       [BackPanelAssignDriverController::class, 'getOrderListDatewise']);
    Route::get('/driver/getorderlist', [BackPanelAssignDriverController::class, 'getOrderList']);
    Route::get('/driver/orders', [BackPanelAssignDriverController::class, 'getOrderDetail']);
    Route::get('/driver/order/customerdetail', [BackPanelAssignDriverController::class, 'getCustomerDetail']);
    Route::post('/driver/order/status/change', [BackPanelAssignDriverController::class, 'changeOrderStatus']);
    Route::post('customer/order/checkotp', [BackPanelAssignDriverController::class, 'verifyOtp']);
});

Route::prefix('esewa')->name('esewa.')->group(function () {

    // Public — eSewa server POSTs to this (no auth token)
    Route::post('/callback', [EsewaController::class, 'callback'])
        ->name('callback');

    // Protected routes (add ->middleware('auth:sanctum') if using Sanctum)
    // Route::middleware([])->group(function () {

    // Initiate a new payment
    Route::post('/initiate', [EsewaPaymentController::class, 'initiate'])
        ->name('initiate');


    // Check payment status
    Route::get('/status/{bookingId}', [EsewaPaymentController::class, 'status'])
        ->name('status');

    // Cancel a booked payment
    Route::post('/cancel/{bookingId}', [EsewaPaymentController::class, 'cancel'])
        ->name('cancel');

    // List all transactions
    Route::get('/transactions', [EsewaPaymentController::class, 'index'])
        ->name('transactions.index');

    // Single transaction detail
    Route::get('/transactions/{esewaTransaction}', [EsewaPaymentController::class, 'show'])
        ->name('transactions.show');
    // });
});
