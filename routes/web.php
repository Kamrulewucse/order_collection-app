<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SRController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmVisitController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
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


Route::middleware('checkDatabaseConnection')->group(function (){
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::middleware(['auth','checkUserStatus'])->group(function () {
        Route::get('home', function () {
            return redirect()->route('check-in.create');
        });
        Route::get('module-dashboard', [DashboardController::class, 'moduleDashboard'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard_dashboard');


        //User
        Route::resource('user', UserController::class);
        Route::get('user-datatable', [UserController::class, 'dataTable'])->name('user.datatable');

        //Unit
        Route::resource('unit', UnitController::class);
        Route::get('unit-datatable', [UnitController::class, 'dataTable'])->name('unit.datatable');

        //Category
        Route::resource('category', CategoryController::class);
        Route::get('category-datatable', [CategoryController::class, 'dataTable'])->name('category.datatable');

        //Sub Category
        Route::resource('sub-category', SubCategoryController::class);
        Route::get('sub-category-datatable', [SubCategoryController::class, 'dataTable'])->name('subCategory.datatable');

        //Product
        Route::resource('product', ProductController::class);
        Route::get('product-datatable', [ProductController::class, 'dataTable'])->name('product.datatable');

        //Leave management
        Route::resource('leave-types', LeaveTypeController::class);
        Route::get('leave-types-datatable', [LeaveTypeController::class, 'dataTable'])->name('leave-types.datatable');

        Route::resource('leave', LeaveController::class);
        Route::get('leave-datatable', [LeaveController::class, 'dataTable'])->name('leave.datatable');
        Route::get('leave-approved/{leave}', [LeaveController::class, 'leaveApproved'])->name('leave.approved');

        //Leave management
        Route::resource('campaign', CampaignController::class);
        Route::get('campaign-datatable', [CampaignController::class, 'dataTable'])->name('campaign.datatable');

        //SR
        Route::resource('sr', SRController::class);
        Route::get('sr-datatable', [SRController::class, 'dataTable'])->name('sr.datatable');

        //Doctor
        Route::resource('doctor', DoctorController::class);
        Route::get('doctor-datatable', [DoctorController::class, 'dataTable'])->name('doctor.datatable');

        //Farm
        Route::resource('farm', FarmController::class);
        Route::get('farm-datatable', [FarmController::class, 'dataTable'])->name('farm.datatable');

        //Customer
        Route::resource('client', ClientController::class);
        Route::get('client-datatable', [ClientController::class, 'dataTable'])->name('client.datatable');

        //Distribution Order
        Route::resource('farm-visit', FarmVisitController::class);
        Route::get('sales-order-datatable', [SalesOrderController::class, 'dataTable'])->name('sales-order.datatable');

        //Distribution Order
        Route::resource('sales-order', SalesOrderController::class);
        Route::get('sales-order-datatable', [SalesOrderController::class, 'dataTable'])->name('sales-order.datatable');

        Route::get('client-payments', [SalesOrderController::class, 'customerPayments'])->name('client-payments');
        Route::get('customer-payment-sales-datatable', [SalesOrderController::class, 'customerPaymentsDataTable'])->name('client-payments.datatable');

        Route::get('sales-payment-details/{salePayment}', [SalesOrderController::class, 'salePaymentDetails'])->name('sales-order.details');
        Route::get('sales-order-edit/{saleOrder}', [SalesOrderController::class, 'salesInvoice'])->name('sales-order.day_close');
        Route::post('sales-order-edit/{saleOrder}', [SalesOrderController::class, 'finalSalePost']);

        Route::post('sales-order-in-transit-post/{saleOrder}', [SalesOrderController::class, 'inTransitPost'])->name('sales-order.in_transit_post');
        Route::post('distribution/sr-payment', [SalesOrderController::class, 'payment'])->name('saleOrder.sr_payment');


        //Report
        Route::post('export_html_content', [ReportController::class, 'exportHtmlContent'])->name('export.html_content');
        Route::get('report/sales-report', [ReportController::class, 'salesReport'])->name('report.sales_report');
        Route::get('sales-tracking-report', [ReportController::class, 'salesTrackingReport'])->name('sales_report_tracking');



        //Profile
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('dark-mode-change', [ProfileController::class, 'darkModeChange'])->name('dark_mode_change');
        Route::post('profile-edit', [ProfileController::class, 'editPost'])->name('profile.edit_post');
        Route::get('password-change', [ProfileController::class, 'passwordEdit'])->name('profile.password_change');
        Route::post('password-change', [ProfileController::class, 'passwordUpdate']);

        //CommonController
        Route::get('get_product_details', [CommonController::class, 'getProductDetails'])->name('get_product_details');
        Route::get('get_account_head', [CommonController::class, 'getAccountHead'])->name('get_account_head');
        Route::get('get_stock_info', [CommonController::class, 'getStockInfo'])->name('get_stock_info');
        Route::get('get_stock_info', [CommonController::class, 'getStockInfo'])->name('get_stock_info');
        Route::get('get_sales_orders', [CommonController::class, 'getSalesOrders'])->name('get_sales_orders');
        Route::get('get_sales_orders_client', [CommonController::class, 'getSalesOrdersCustomer'])->name('get_sales_orders_client');
        Route::get('get_sales_order_details', [CommonController::class, 'getSalesOrderDetails'])->name('get_sales_order_details');
        Route::get('get_distribution_product_info', [CommonController::class, 'getDistributionProductInfo'])->name('get_distribution_product_info');
        Route::get('get_collection_amount', [CommonController::class, 'getCollectionAmount'])->name('get_collection_amount');
        Route::get('/get-thanas/{districtId}', [CommonController::class, 'getThanasByDistrict'])->name('get.thanas');
        Route::get('get-subcategories/{category}',[CommonController::class, 'getSubcategories'])->name('get.subcategories');

    });

    require __DIR__ . '/auth.php';
});


Route::get('my-sql-db-connection',[SettingsController::class,'mySqlDbConnection'])
    ->name('my_sql_db_connection');
Route::post('my-sql-db-connection',[SettingsController::class,'mySqlDbConnectionUpdate']);

Route::get('/clear', function () {
    Artisan::call('cache:forget spatie.permission.cache');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});

