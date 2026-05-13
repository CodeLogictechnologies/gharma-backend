<?php

use App\Http\Controllers\BackPanel\AboutUsController;
use App\Http\Controllers\BackPanel\AuthController;
use App\Http\Controllers\BackPanel\BrandController;
use App\Http\Controllers\BackPanel\CategoryController as BackPanelCategoryController;
use App\Http\Controllers\BackPanel\DiscountController;
use App\Http\Controllers\BackPanel\ForgotPasswordController;
use App\Http\Controllers\BackPanel\HeatmapController;
use App\Http\Controllers\BackPanel\HomeController;
use App\Http\Controllers\BackPanel\InventoryController;
use App\Http\Controllers\BackPanel\InventoryReportController;
use App\Http\Controllers\BackPanel\OtpController;
use App\Http\Controllers\BackPanel\ItemController;
use App\Http\Controllers\BackPanel\NotificationController;
use App\Http\Controllers\BackPanel\OrderController;
use App\Http\Controllers\BackPanel\OrganizationController;
use App\Http\Controllers\BackPanel\PermissionController;
use App\Http\Controllers\BackPanel\RetailerPriceController;
use App\Http\Controllers\BackPanel\RoleController;
use App\Http\Controllers\BackPanel\SalesReportController;
use App\Http\Controllers\BackPanel\SiteSettingController;
use App\Http\Controllers\BackPanel\StoreController;
use App\Http\Controllers\BackPanel\SubCategoryController;
use App\Http\Controllers\BackPanel\UserController;
use App\Http\Controllers\DatabaseDumpController;
use App\Http\Controllers\BackPanel\VendorController;
use App\Http\Controllers\BackPanel\WholesalerPriceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SocialAuthController;
use App\Models\Payment;
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
    /* Dashboard  - end */
    Route::group(['prefix' => 'admin'], function () {

        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
        
        Route::get('/organization', [OrganizationController::class, 'index'])->name('organization');
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
            Route::post('/tab', [UserController::class, 'tabs'])->name('user.tab');
            Route::get('/list', [UserController::class, 'list'])->name('user.list');
            Route::get('/inactiveuserlist', [UserController::class, 'inActivelist'])->name('inactive.user.list');
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
            Route::post('/order-status-update', [OrderController::class, 'updateStatus'])->name('order.status.update');
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

        Route::group(['prefix' => 'discount'], function () {
            Route::get('/', [DiscountController::class, 'index'])->name('discount');
            Route::post('/save', [DiscountController::class, 'save'])->name('discount.save');
            Route::get('/list', [DiscountController::class, 'list'])->name('discount.list');
            Route::post('/delete', [DiscountController::class, 'delete'])->name('discount.delete');
            Route::any('/form', [DiscountController::class, 'form'])->name('discount.form');
            Route::post('/view', [DiscountController::class, 'view'])->name('discount.view');
            Route::get('/items/list', [DiscountController::class, 'lists'])->name('api.items.list');

            // Get variations for a specific item
            Route::get('/items/{id}/variations', [DiscountController::class, 'variations']);
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
        /* Profile-start */
        Route::group(['prefix' => 'payment'], function () {
            Route::get('/esewa', [AuthController::class, 'esewa'])->name('esewa');
        });


        /* Profile-end */


        Route::group(['prefix' => 'store'], function () {
            Route::get('', [StoreController::class, 'index'])->name('store');
            Route::get('/list', [StoreController::class, 'list'])->name('store.list');
            Route::any('/form', [StoreController::class, 'form'])->name('store.form');
            Route::post('/save', [StoreController::class, 'save'])->name('store.save');
            Route::post('/delete', [StoreController::class, 'delete'])->name('store.delete');
            Route::post('/view', [StoreController::class, 'view'])->name('store.view');
        });


        Route::group(['prefix' => 'refund'], function () {
            Route::get('', [RefundController::class, 'index'])->name('refund');
            Route::get('/list', [RefundController::class, 'list'])->name('refund.list');
            Route::post('/changeStatus', [RefundController::class, 'updateStatus'])->name('refund.update.status');
            Route::post('/view', [RefundController::class, 'view'])->name('refund.view');
        });

        Route::group(['prefix' => 'report'], function () {
            Route::group(['prefix' => 'sales'], function () {
                Route::get('report/sales',              [SalesReportController::class, 'index'])->name('report.sales');
                Route::get('report/sales/data',         [SalesReportController::class, 'data'])->name('report.sales.data');
                Route::get('report/sales/export/excel', [SalesReportController::class, 'exportExcel'])->name('report.sales.export.excel');
                Route::get('report/sales/export/pdf',   [SalesReportController::class, 'exportPdf'])->name('report.sales.export.pdf');
            });

            Route::group(['prefix' => 'inventory'], function () {
                Route::get('', [InventoryReportController::class, 'index'])->name('inventory.report');
                Route::get('/list', [InventoryReportController::class, 'data'])->name('report.inventory.data');

                Route::get('report/sales/export/excel', [InventoryReportController::class, 'exportExcel'])->name('report.inventory.export.excel');
                Route::get('report/sales/export/pdf',   [InventoryReportController::class, 'exportPdf'])->name('report.inventory.export.pdf');
            });
        });


        // routes/web.php
        Route::get('/heatmap', [HeatmapController::class, 'index'])
            ->name('admin.heatmap.index');

        // AJAX data endpoint for live filter updates
        Route::get('/heatmap/data', [HeatmapController::class, 'data'])
            ->name('admin.heatmap.data');
    });
});

Route::get('auth/{provider}/redirect',  [SocialAuthController::class, 'redirect'])->name('admin.sitesetting');
Route::get('auth/{provider}/call-back', [SocialAuthController::class, 'callback'])->name('admin.sitesetting');


Route::get('/success', [PaymentController::class, 'success']);
Route::get('/failure', [PaymentController::class, 'failure']);

Route::get('/khalti/success', [PaymentController::class, 'success']);
Route::get('/khalti/failure', [PaymentController::class, 'failure']);


Route::get('/payment', [PaymentController::class, 'index']);
Route::post('/payment/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');