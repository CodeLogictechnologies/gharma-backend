<?php

use App\Http\Controllers\BackPanel\AboutUsController;
use App\Http\Controllers\BackPanel\AuthController;
use App\Http\Controllers\BackPanel\BrandController;
use App\Http\Controllers\BackPanel\CategoryController as BackPanelCategoryController;
use App\Http\Controllers\BackPanel\ForgotPasswordController;
use App\Http\Controllers\BackPanel\HomeController;
use App\Http\Controllers\BackPanel\InventoryController;
use App\Http\Controllers\BackPanel\OtpController;
use App\Http\Controllers\BackPanel\ItemController;
use App\Http\Controllers\BackPanel\NotificationController;
use App\Http\Controllers\BackPanel\OrderController;
use App\Http\Controllers\BackPanel\OrganizationController;
use App\Http\Controllers\BackPanel\PermissionController;
use App\Http\Controllers\BackPanel\RetailerPriceController;
use App\Http\Controllers\BackPanel\RoleController;
use App\Http\Controllers\BackPanel\SiteSettingController;
use App\Http\Controllers\BackPanel\SubCategoryController;
use App\Http\Controllers\BackPanel\UserController;
use App\Http\Controllers\DatabaseDumpController;
use App\Http\Controllers\BackPanel\VendorController;
use App\Http\Controllers\BackPanel\WholesalerPriceController;
use App\Http\Controllers\SocialAuthController;
use App\Models\WholesalerPrice;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* Backend-start */



Route::get('/dump-db-sql', [DatabaseDumpController::class, 'dump']);

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::get('admin/login', [AuthController::class, 'index'])->name('admin.login');
Route::get('admin/forgotpassword', [ForgotPasswordController::class, 'index'])->name('admin.forgotpassword');
Route::post('admin/checkuser', [ForgotPasswordController::class, 'isRegisteredUser'])->name('admin.checkuser');
Route::get('admin/otp', [OtpController::class, 'index'])->name('admin.otp');
Route::post('admin/validotp', [OtpController::class, 'isValidOtp'])->name('admin.validotp');
Route::get('admin/changepassword', [OtpController::class, 'indexChangePassword'])->name('admin.changepassword');
Route::post('admin/updatepassword', [ForgotPasswordController::class, 'updatePassword'])->name('admin.updatepassword');

Route::post('/loginuser', [AuthController::class, 'loginUser'])->name('loginuser');
Route::get('/logout', [AuthController::class, 'logOut'])->name('logout');

/* Reset password - start */
Route::get('admin/reset-password', [AuthController::class, 'resetPasswordForm'])->name('admin.reset-password');
/* Reset password  - end */

/* Reset password - start */
Route::post('/admin/resetpassword', [AuthController::class, 'resetPassword'])->name('admin.resetpassword');
/* Reset password  - end */

// Route::group(['middleware' => ['auth', 'role:admin']], function () {
Route::group(['middleware' => ['auth']], function () {

    /* Dashboard - start */
    Route::get('/', [HomeController::class, 'dashboard'])->name('admin.dashboard');
    /* Dashboard  - end */
    Route::group(['prefix' => 'admin'], function () {


        Route::get('/organization', [OrganizationController::class, 'index'])->name('organization');
        Route::post('/organization', [OrganizationController::class, 'create'])->name('organization.create');
        Route::get('/organization/list', [OrganizationController::class, 'list'])->name('organization.list');
        Route::any('/organization/form', [OrganizationController::class, 'form'])->name('organization.form');
        Route::post('/organization/save', [OrganizationController::class, 'save'])->name('organization.save');
        Route::post('/delete', [OrganizationController::class, 'delete'])->name('organization.delete');
        Route::post('/view', [OrganizationController::class, 'view'])->name('organization.view');

        Route::group(['prefix' => 'role'], function () {
            Route::get('/', [RoleController::class, 'index'])->name('role');
            Route::post('/save', [RoleController::class, 'save'])->name('role.save');
            Route::post('/list', [RoleController::class, 'list'])->name('role.list');
            Route::post('/delete', [RoleController::class, 'delete'])->name('role.delete');
        });

        Route::group(['prefix' => 'permission'], function () {
            Route::get('/', [PermissionController::class, 'index'])->name('permission');
            Route::post('/save', [PermissionController::class, 'save'])->name('permission.save');
            Route::post('/list', [PermissionController::class, 'list'])->name('permission.list');
            Route::post('/delete', [PermissionController::class, 'delete'])->name('permission.delete');
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [UserController::class, 'index'])->name('user');
            Route::get('/list', [UserController::class, 'list'])->name('user.list');
            Route::any('/form', [UserController::class, 'form'])->name('user.form');
            Route::post('/save', [UserController::class, 'save'])->name('user.save');
            Route::post('/delete', [UserController::class, 'delete'])->name('user.delete');
            Route::post('/view', [UserController::class, 'view'])->name('user.view');
            Route::post('/status-update', [UserController::class, 'updateStatus'])->name('user.status');
        });

        Route::group(['prefix' => 'category'], function () {
            Route::get('/', [BackPanelCategoryController::class, 'index'])->name('category');
            Route::post('/tabs', [BackPanelCategoryController::class, 'tabs'])->name('category.tabs');
            Route::post('/list', [BackPanelCategoryController::class, 'list'])->name('category.list');
            Route::any('/form', [BackPanelCategoryController::class, 'form'])->name('category.form');
            Route::post('/save', [BackPanelCategoryController::class, 'save'])->name('category.save');
            Route::post('/delete', [BackPanelCategoryController::class, 'delete'])->name('category.delete');
        });

        Route::group(['prefix' => 'subcategory'], function () {
            Route::get('/', [SubCategoryController::class, 'index'])->name('subcategory');
            Route::post('/list', [SubCategoryController::class, 'list'])->name('subcategory.list');
            Route::any('/form', [SubCategoryController::class, 'form'])->name('subcategory.form');
            Route::post('/save', [SubCategoryController::class, 'save'])->name('subcategory.save');
            Route::post('/delete', [SubCategoryController::class, 'delete'])->name('subcategory.delete');
        });


        Route::group(['prefix' => 'brand'], function () {
            Route::get('/', [BrandController::class, 'index'])->name('brand');
            Route::post('/tabs', [BrandController::class, 'tabs'])->name('brand.tabs');
            Route::post('/list', [BrandController::class, 'list'])->name('brand.list');
            Route::any('/form', [BrandController::class, 'form'])->name('brand.form');
            Route::post('/save', [BrandController::class, 'save'])->name('brand.save');
            Route::post('/delete', [BrandController::class, 'delete'])->name('brand.delete');
        });

        Route::group(['prefix' => 'item'], function () {
            Route::get('/', [ItemController::class, 'index'])->name('item');
            Route::get('/list', [ItemController::class, 'list'])->name('item.list');
            Route::any('/form', [ItemController::class, 'form'])->name('item.form');
            Route::post('/save', [ItemController::class, 'save'])->name('item.save');
            Route::post('/delete', [ItemController::class, 'delete'])->name('item.delete');
            Route::post('/view', [ItemController::class, 'view'])->name('item.view');
        });


        Route::group(['prefix' => 'order'], function () {
            Route::get('/', [OrderController::class, 'index'])->name('order');
            Route::get('/list', [OrderController::class, 'list'])->name('order.list');
            Route::post('/view', [OrderController::class, 'view'])->name('order.view');
        });

        Route::group(['prefix' => 'inventory'], function () {
            Route::get('/', [InventoryController::class, 'index'])->name('inventory');
            Route::post('/save', [InventoryController::class, 'save'])->name('inventory.save');
            Route::get('/variations', [InventoryController::class, 'getVariations'])->name('inventory.variations');

            Route::post('/list', [InventoryController::class, 'list'])->name('inventory.list');
            Route::post('/view', [InventoryController::class, 'view'])->name('inventory.view');
            Route::any('/form', [InventoryController::class, 'form'])->name('inventory.form');
        });


        Route::group(['prefix' => 'vendor'], function () {
            Route::group(['prefix' => 'info'], function () {
                Route::get('/', [VendorController::class, 'index'])->name('vendor.info');
                Route::get('/list', [VendorController::class, 'list'])->name('vendor.info.list');
                Route::any('/form', [VendorController::class, 'form'])->name('vendor.info.form');
                Route::post('/save', [VendorController::class, 'save'])->name('vendor.info.save');
                Route::post('/delete', [VendorController::class, 'delete'])->name('vendor.info.delete');
                Route::post('/view', [VendorController::class, 'view'])->name('vendor.info.view');
            });
            Route::group(['prefix' => 'subcategory'], function () {
                Route::get('/', [SubCategoryController::class, 'index'])->name('subcategory');
                Route::post('/list', [SubCategoryController::class, 'list'])->name('subcategory.list');
                Route::any('/form', [SubCategoryController::class, 'form'])->name('subcategory.form');
                Route::post('/save', [SubCategoryController::class, 'save'])->name('subcategory.save');
                Route::post('/delete', [SubCategoryController::class, 'delete'])->name('subcategory.delete');
            });
        });

        Route::group(['prefix' => 'notification'], function () {
            Route::get('/', [NotificationController::class, 'index'])->name('notification');
            Route::post('/save', [NotificationController::class, 'save'])->name('notification.save');

            Route::get('/list', [NotificationController::class, 'list'])->name('notification.list');
            Route::post('/view', [NotificationController::class, 'view'])->name('notification.view');
            Route::any('/form', [NotificationController::class, 'form'])->name('notification.form');
            Route::post('/delete', [NotificationController::class, 'delete'])->name('notification.delete');
        });


        Route::group(['prefix' => 'price'], function () {
            Route::group(['prefix' => 'retailer'], function () {
                Route::get('/', [RetailerPriceController::class, 'index'])->name('retailer');
                Route::post('/save', [RetailerPriceController::class, 'save'])->name('retailer.save');
                Route::post('/list', [RetailerPriceController::class, 'list'])->name('retailer.list');
                Route::post('/delete', [RetailerPriceController::class, 'delete'])->name('retailer.delete');
            });
            Route::group(['prefix' => 'wholesaler'], function () {
                Route::get('/', [WholesalerPriceController::class, 'index'])->name('wholesaler');
                Route::post('/save', [WholesalerPriceController::class, 'save'])->name('wholesaler.save');
                Route::get('/list', [WholesalerPriceController::class, 'list'])->name('wholesaler.list');
                Route::post('/delete', [WholesalerPriceController::class, 'delete'])->name('wholesaler.delete');
                Route::any('/form', [WholesalerPriceController::class, 'form'])->name('wholesaler.form');
            });
        });

        /* Profile-start */
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [AuthController::class, 'profile'])->name('admin.profile');
            Route::post('/tab/get', [AuthController::class, 'getTabContent'])->name('admin.gettabcontent');
            Route::post('/image/update', [AuthController::class, 'uploadImage'])->name('admin.updateprofileimage');
            Route::post('/setting/update', [AuthController::class, 'updatePassword'])->name('admin.update');
            Route::post('/editprofile/updateprofile', [AuthController::class, 'updateProfileAll'])->name('admin.updateprofile');
        });
        /* Profile-end */

        /* Site settings - start */
        Route::group(['prefix' => 'sitesetting'], function () {
            Route::get('/', [SiteSettingController::class, 'siteSetting'])->name('admin.sitesetting');
            Route::post('/update', [SiteSettingController::class, 'updateSiteSetting'])->name('admin.sitesetting.update');
        });
        /** Site settings - end */

        /* About us - start */
        Route::group(['prefix' => 'aboutus'], function () {
            Route::get('/', [AboutUsController::class, 'aboutUs'])->name('admin.aboutus');
            Route::post('/update', [AboutUsController::class, 'updateAboutUs'])->name('admin.aboutus.update');
        });
        /* About us - end */
    });
});

Route::get('auth/{provider}/redirect',  [SocialAuthController::class, 'redirect'])->name('admin.sitesetting');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('admin.sitesetting');
