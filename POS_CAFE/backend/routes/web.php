<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\LoyaltyController;
use App\Http\Controllers\Inventory\IngredientController;
use App\Http\Controllers\Inventory\RecipeController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Kds\KdsController;
use App\Http\Controllers\Management\CategoryController;
use App\Http\Controllers\Management\ExpenseController;
use App\Http\Controllers\Management\ProductController;
use App\Http\Controllers\Management\PromotionController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ShiftController;
use App\Http\Controllers\Report\DashboardController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Supplier\PurchaseOrderController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Role-routed smart dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/photos/search', [ProductController::class, 'searchPhotos'])->name('photos.search');

    // POS & Cashier Shifts (Admin, Manager, Cashier)
    Route::middleware(['role:admin,manager,cashier'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/calculate', [PosController::class, 'calculate'])->name('pos.calculate');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/orders/{order}/receipt-data', [PosController::class, 'receiptData'])->name('pos.receipt_data');
        Route::get('/pos/customers/search', [PosController::class, 'searchCustomers'])->name('pos.customers.search');
        Route::post('/pos/customers/quick-create', [PosController::class, 'quickCreateCustomer'])->name('pos.customers.quick_create');
        Route::post('/pos/orders/{order}/cancel', [PosController::class, 'cancel'])->name('pos.orders.cancel');

        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
        Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
        Route::post('/shifts/{shift}/cash-movement', [ShiftController::class, 'cashMovement'])->name('shifts.cash_movement');

        Route::resource('customers', CustomerController::class);
        Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
        Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem');
    });

    // Kitchen Display System (Admin, Manager, Barista)
    Route::middleware(['role:admin,manager,barista'])->group(function () {
        Route::get('/kds', [KdsController::class, 'index'])->name('kds.index');
        Route::get('/kds/poll', [KdsController::class, 'pollOrders'])->name('kds.poll');
        Route::post('/kds/orders/{order}/status', [KdsController::class, 'updateStatus'])->name('kds.orders.status');
    });

    // Operations & Management (Admin, Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/pos/orders/{order}/refund', [PosController::class, 'refund'])->name('pos.orders.refund');
        Route::post('/shifts/{shift}/review', [ShiftController::class, 'review'])->name('shifts.review');

        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/toggle', [ProductController::class, 'toggleAvailability'])->name('products.toggle');
        Route::resource('categories', CategoryController::class);

        Route::resource('ingredients', IngredientController::class);
        Route::resource('recipes', RecipeController::class);
        Route::get('/adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::post('/adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
        Route::get('/adjustments/ledger', [StockAdjustmentController::class, 'ledger'])->name('adjustments.ledger');

        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

        Route::post('/loyalty/rewards', [LoyaltyController::class, 'storeReward'])->name('loyalty.rewards.store');
        Route::resource('promotions', PromotionController::class);

        Route::resource('expenses', ExpenseController::class);
        Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');

        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
        Route::get('/reports/export-csv-download', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    });

    // System Administration & Settings (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('staff', StaffController::class);
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
