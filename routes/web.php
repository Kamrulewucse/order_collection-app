<?php

use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\AccountHeadController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionOrderController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SRController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReceiptPaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Models\Hotel;
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
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


        //User
        Route::resource('user', UserController::class)->middleware('permission:user');
        Route::get('user-datatable', [UserController::class, 'dataTable'])->name('user.datatable');


        //Supplier
        Route::resource('supplier', SupplierController::class)->middleware('permission:supplier');
        Route::get('supplier-datatable', [SupplierController::class, 'dataTable'])->name('supplier.datatable');

        //Unit
        Route::resource('unit', UnitController::class)->middleware('permission:product_unit');
        Route::get('unit-datatable', [UnitController::class, 'dataTable'])->name('unit.datatable');

        //Category
        Route::resource('category', CategoryController::class);
        Route::get('category-datatable', [CategoryController::class, 'dataTable'])->name('category.datatable');

        //Brand
        Route::resource('brand', BrandController::class)->middleware('permission:brand');
        Route::get('brand-datatable', [BrandController::class, 'dataTable'])->name('brand.datatable');

        //Product
        Route::resource('product', ProductController::class)->middleware('permission:product');
        Route::get('product-datatable', [ProductController::class, 'dataTable'])->name('product.datatable');

        //Purchase
        Route::resource('purchase', PurchaseOrderController::class)->middleware('permission:purchase');
        Route::get('purchase-datatable', [PurchaseOrderController::class, 'dataTable'])->name('purchase.datatable');
        Route::get('purchase-details/{purchase}', [PurchaseOrderController::class, 'details'])->name('purchase.details')->middleware('permission:purchase_list');
        Route::post('purchase/payment', [PurchaseOrderController::class, 'payment'])->name('purchase.supplier_payment')->middleware('permission:purchase_payment');

        //Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('permission:inventory');
        Route::get('inventory-datatable', [InventoryController::class, 'dataTable'])->name('inventory.datatable');
        Route::get('inventory-details', [InventoryController::class, 'details'])->name('inventory.details')->middleware('permission:inventory_log');
        Route::get('inventory-log-datatable', [InventoryController::class, 'inventoryLogDataTable'])->name('inventory_log.datatable');
        Route::post('inventory-utilize-product', [InventoryController::class, 'utilizeProduct'])->name('inventory.utilize_product')->middleware('permission:stock_utilized');

        Route::middleware('permission:distribution_settings')->group(function () {
            //DSR
            Route::resource('sr', SRController::class)->middleware('permission:dsr');
            Route::get('sr-datatable', [SRController::class, 'dataTable'])->name('sr.datatable');

            //Doctor
            Route::resource('doctor', DoctorController::class)->middleware('permission:dsr');
            Route::get('doctor-datatable', [DoctorController::class, 'dataTable'])->name('doctor.datatable');

            //Farm
            Route::resource('farm', FarmController::class);
            Route::get('farm-datatable', [FarmController::class, 'dataTable'])->name('farm.datatable');

            //Customer
            Route::resource('client', ClientController::class);
            Route::get('client-datatable', [ClientController::class, 'dataTable'])->name('client.datatable');
        });

        Route::middleware('permission:distribution')->group(function () {
            //Distribution Order
            Route::resource('distribution', DistributionOrderController::class);
            Route::get('distribution-datatable', [DistributionOrderController::class, 'dataTable'])->name('distribution.datatable');

            Route::get('customer-payments', [DistributionOrderController::class, 'customerPayments'])->name('customer-payments');
            Route::get('customer-payment-distribution-datatable', [DistributionOrderController::class, 'customerPaymentsDataTable'])->name('customer-payments.datatable');

            Route::get('distribution-receipt-details/{distributionOrder}', [DistributionOrderController::class, 'details'])->name('distribution.details');
            Route::get('distribution-final-details/{distributionOrder}', [DistributionOrderController::class, 'finalDetails'])->name('distribution.final_details');

            Route::get('distribution-invoice/{distributionOrder}', [DistributionOrderController::class, 'distributionInvoice'])->name('distribution.day_close');
            Route::post('distribution-invoice/{distributionOrder}', [DistributionOrderController::class, 'dayClosePost']);

            Route::post('distribution-hold-release-post/{distributionOrder}', [DistributionOrderController::class, 'holdReleasePost'])->name('distribution.hold_release_post');

            Route::get('distribution-customer-sale-entry/{distributionOrder}', [DistributionOrderController::class, 'customerSaleEntry'])->name('distribution.customer_sale_entry');
            Route::post('distribution-customer-sale-entry/{distributionOrder}', [DistributionOrderController::class, 'customerSaleEntryPost']);

            Route::get('distribution-customer-damage-product-entry/{distributionOrder}', [DistributionOrderController::class, 'customerDamageProductEntry'])->name('distribution.customer_damage_product_entry');
            Route::post('distribution-customer-damage-product-entry/{distributionOrder}', [DistributionOrderController::class, 'customerDamageProductEntryPost']);



            Route::get('distribution-customer-sale-details/{distributionOrder}', [DistributionOrderController::class, 'customerSaleDetails'])->name('distribution.customer_sale_details');
            Route::post('distribution/dsr-payment', [DistributionOrderController::class, 'payment'])->name('distribution.dsr_payment');
        });


        Route::resource('commission', CommissionController::class);
        Route::get('commission-datatable', [CommissionController::class, 'dataTable'])->name('commission.datatable');


        Route::middleware('permission:accounts')->group(function () {
            //Account Group
            Route::resource('account-group', AccountGroupController::class)->middleware('permission:account_group');
            Route::get('account-group-datatable', [AccountGroupController::class, 'dataTable'])->name('account-group.datatable');

            //Account Head
            Route::resource('account-head', AccountHeadController::class);
            Route::get('account-head-datatable', [AccountHeadController::class, 'dataTable'])->name('account-head.datatable');

            //Voucher
            Route::resource('voucher', ReceiptPaymentController::class);
            Route::get('voucher-datatable', [ReceiptPaymentController::class, 'dataTable'])->name('voucher.datatable');
            Route::get('voucher-details/{voucher}', [ReceiptPaymentController::class, 'details'])->name('voucher.details');


        });

        Route::middleware('permission:reports')->group(function () {
            //Report
            Route::post('export_html_content', [ReportController::class, 'exportHtmlContent'])->name('export.html_content');
            Route::get('report/sales-report', [ReportController::class, 'salesReport'])->name('report.sales_report')->middleware('permission:receipt_and_payment');
            Route::get('report/inventory-in-report', [ReportController::class, 'inventoryInReport'])->name('report.inventory_in')->middleware('permission:receipt_and_payment');
            Route::get('report/inventory-out-report', [ReportController::class, 'inventoryOutReport'])->name('report.inventory_out')->middleware('permission:receipt_and_payment');

            Route::get('report/sales-vs-payments', [ReportController::class, 'salesVsPayments'])->name('report.sales-vs-payments')->middleware('permission:receipt_and_payment');
            Route::get('report/payment-vs-product-received', [ReportController::class, 'paymentVsProductReceived'])->name('report.payment-vs-product-received')->middleware('permission:receipt_and_payment');
            Route::get('report/cash-and-stock', [ReportController::class, 'cashAndStock'])->name('report.cash-and-stock')->middleware('permission:receipt_and_payment');
            Route::get('report/sales-due', [ReportController::class, 'salesDue'])->name('report.sales_due')->middleware('permission:receipt_and_payment');

            Route::get('report/receipt-and-payment', [ReportController::class, 'receiptAndPayment'])->name('report.receipt_and_payment')->middleware('permission:receipt_and_payment');
            Route::get('report/ledger', [ReportController::class, 'ledger'])->name('report.ledger')->middleware('permission:ledger');
            Route::get('report/trial-balance', [ReportController::class, 'trialBalance'])->name('report.trial_balance')->middleware('permission:trial_balance');
            Route::get('report/income-statement', [ReportController::class, 'incomeStatement'])->name('report.income_statement')->middleware('permission:income_statement');
            Route::get('report/balance-sheet', [ReportController::class, 'balanceSheet'])->name('report.balance_sheet')->middleware('permission:balance_sheet');


        });

        //Profile
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('dark-mode-change', [ProfileController::class, 'darkModeChange'])->name('dark_mode_change');
        Route::post('profile-edit', [ProfileController::class, 'editPost'])->name('profile.edit_post');
        Route::get('password-change', [ProfileController::class, 'passwordEdit'])->name('profile.password_change');
        Route::post('password-change', [ProfileController::class, 'passwordUpdate']);
//        Route::get('global-settings', [SettingsController::class, 'globalSettings'])->name('global_settings');
//        Route::post('global-settings', [SettingsController::class, 'globalSettingsUpdate']);
        //CommonController
        Route::get('get_product_details', [CommonController::class, 'getProductDetails'])->name('get_product_details');
        Route::get('get_account_head', [CommonController::class, 'getAccountHead'])->name('get_account_head');
        Route::get('get_stock_info', [CommonController::class, 'getStockInfo'])->name('get_stock_info');
        Route::get('get_stock_info', [CommonController::class, 'getStockInfo'])->name('get_stock_info');
        Route::get('get_sales_orders', [CommonController::class, 'getSalesOrders'])->name('get_sales_orders');
        Route::get('get_sales_orders_customer', [CommonController::class, 'getSalesOrdersCustomer'])->name('get_sales_orders_customer');
        Route::get('get_sales_order_details', [CommonController::class, 'getSalesOrderDetails'])->name('get_sales_order_details');
        Route::get('get_distribution_product_info', [CommonController::class, 'getDistributionProductInfo'])->name('get_distribution_product_info');
        Route::get('get_collection_amount', [CommonController::class, 'getCollectionAmount'])->name('get_collection_amount');
        Route::get('/get-thanas/{districtId}', [CommonController::class, 'getThanasByDistrict'])->name('get.thanas');

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

