<?php

use App\Http\Controllers\Accounting\AccountHeadController;
use App\Http\Controllers\Accounting\ExpenseController;
use App\Http\Controllers\Accounting\LedgerController;
use App\Http\Controllers\Accounting\ReportController;
use App\Http\Controllers\Accounting\VoucherController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Kpi\ReportController as KpiReportController;
use App\Http\Controllers\Kpi\TargetController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\Purchases\PurchaseController;
use App\Http\Controllers\Returns\ReturnController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sr\AttendanceController;
use App\Http\Controllers\Sr\FaceController;
use App\Http\Controllers\Sr\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phase 1 Routes — Foundation
|--------------------------------------------------------------------------
| Later phases add their own route files (routes/sales.php,
| routes/inventory.php, routes/sr.php, ...) and are required at the
| bottom of this file — keeping this file from growing unbounded.
*/

Route::get('/', fn () => redirect()->route('login'));

Route::get('language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
        });
        Route::middleware('permission:users.create')->group(function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });
        Route::middleware('permission:users.edit')->group(function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        });
        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:roles.view')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        });
        Route::middleware('permission:roles.create')->group(function () {
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 8 — Email Notification System
    |----------------------------------------------------------------
    */
    Route::prefix('admin/notifications')->name('admin.notifications.')->middleware('permission:settings.manage')->group(function () {
        Route::get('/', [NotificationSettingController::class, 'index'])->name('index');
        Route::put('{notificationSetting}', [NotificationSettingController::class, 'update'])->name('update');
        Route::post('test', [NotificationSettingController::class, 'sendTest'])->name('test');
    });

    /*
    |----------------------------------------------------------------
    | Phase 2 — Master Data & Inventory
    |----------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('units', [UnitController::class, 'index'])->name('units.index');
            Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
        });
        Route::middleware('permission:inventory.manage')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['index', 'show']);
            Route::resource('units', UnitController::class)->except(['index', 'show']);
            Route::resource('warehouses', WarehouseController::class)->except(['index', 'show']);
            Route::resource('products', ProductController::class)->except(['index', 'show']);
        });

        Route::middleware('permission:stock.view')->group(function () {
            Route::get('stock', [StockController::class, 'index'])->name('stock.index');
        });
        Route::middleware('permission:stock.adjust')->group(function () {
            Route::get('stock/create', [StockController::class, 'create'])->name('stock.create');
            Route::post('stock', [StockController::class, 'store'])->name('stock.store');
        });
    });

    Route::prefix('partners')->name('partners.')->group(function () {
        Route::middleware('permission:partners.view')->group(function () {
            Route::get('/', [PartnerController::class, 'index'])->name('index');
        });
        Route::middleware('permission:partners.manage')->group(function () {
            Route::get('create', [PartnerController::class, 'create'])->name('create');
            Route::post('/', [PartnerController::class, 'store'])->name('store');
            Route::get('{partner}/edit', [PartnerController::class, 'edit'])->name('edit');
            Route::put('{partner}', [PartnerController::class, 'update'])->name('update');
            Route::delete('{partner}', [PartnerController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 3 — Primary & Secondary Sales
    |----------------------------------------------------------------
    | Fine-grained "which type" authorization (primary vs secondary)
    | happens inside SaleController::authorizeType() since it depends
    | on the ?type= query value, not just the route — this middleware
    | layer only gates "can this user touch Sales at all".
    */
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('{sale}', [SaleController::class, 'show'])->name('show');
        });
        Route::middleware('permission:sales.primary|sales.secondary')->group(function () {
            Route::get('create/new', [SaleController::class, 'create'])->name('create');
            Route::post('/', [SaleController::class, 'store'])->name('store');
            Route::post('{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 4 — SR GPS Live Tracking & Face Attendance
    |----------------------------------------------------------------
    */
    Route::prefix('sr')->name('sr.')->group(function () {
        Route::middleware('permission:sr.attendance')->group(function () {
            Route::get('face/enroll', [FaceController::class, 'create'])->name('face.create');
            Route::post('face/enroll', [FaceController::class, 'store'])->name('face.store');

            Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
            Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

            Route::post('tracking/ping', [TrackingController::class, 'ping'])->name('tracking.ping');
        });

        Route::middleware('permission:sr.tracking.view')->group(function () {
            Route::get('tracking/live-map', [TrackingController::class, 'liveMap'])->name('tracking.map');
            Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 5 — Accounting Integration
    |----------------------------------------------------------------
    */
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::middleware('permission:accounting.view')->group(function () {
            Route::get('/', [PurchaseController::class, 'index'])->name('index');
            Route::get('{purchase}', [PurchaseController::class, 'show'])->name('show');
        });
        Route::middleware('permission:accounting.manage')->group(function () {
            Route::get('create/new', [PurchaseController::class, 'create'])->name('create');
            Route::post('/', [PurchaseController::class, 'store'])->name('store');
            Route::post('{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
        });
    });

    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::middleware('permission:accounting.view')->group(function () {
            Route::get('account-heads', [AccountHeadController::class, 'index'])->name('account-heads.index');
            Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('ledger', [LedgerController::class, 'index'])->name('ledger.index');
            Route::get('ledger/{partner}', [LedgerController::class, 'show'])->name('ledger.show');
            Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        });
        Route::middleware('permission:accounting.manage')->group(function () {
            Route::resource('account-heads', AccountHeadController::class)->except(['index', 'show']);
            Route::resource('expenses', ExpenseController::class)->except(['index', 'show']);
            Route::get('vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
            Route::post('vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 6 — Return / Exchange Management
    |----------------------------------------------------------------
    */
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::middleware('permission:returns.view')->group(function () {
            Route::get('/', [ReturnController::class, 'index'])->name('index');
            Route::get('{return}', [ReturnController::class, 'show'])->name('show');
        });
        Route::middleware('permission:returns.manage')->group(function () {
            Route::get('create/new', [ReturnController::class, 'create'])->name('create');
            Route::post('/', [ReturnController::class, 'store'])->name('store');
            Route::post('{return}/cancel', [ReturnController::class, 'cancel'])->name('cancel');
        });
    });

    /*
    |----------------------------------------------------------------
    | Phase 7 — KPI, Commission & Reward
    |----------------------------------------------------------------
    */
    Route::prefix('kpi')->name('kpi.')->group(function () {
        Route::middleware('permission:kpi.view')->group(function () {
            Route::get('report', [KpiReportController::class, 'index'])->name('report');
        });
        Route::middleware('permission:kpi.manage')->group(function () {
            Route::get('targets', [TargetController::class, 'index'])->name('targets.index');
            Route::get('targets/create', [TargetController::class, 'create'])->name('targets.create');
            Route::post('targets', [TargetController::class, 'store'])->name('targets.store');
            Route::get('targets/{target}/edit', [TargetController::class, 'edit'])->name('targets.edit');
            Route::put('targets/{target}', [TargetController::class, 'update'])->name('targets.update');
            Route::delete('targets/{target}', [TargetController::class, 'destroy'])->name('targets.destroy');
        });
    });
});
