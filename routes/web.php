<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreSelectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Store selection routes (requires auth but not store selection)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('store/select', [StoreSelectionController::class, 'index'])->name('store.select');
    Route::post('store/select', [StoreSelectionController::class, 'select'])->name('store.select.submit');
    Route::post('store/switch', [StoreSelectionController::class, 'switch'])->name('store.switch');
});

Route::middleware(['auth', 'verified', 'store.selected'])->group(function () {
    // Redirect root to current store's menu
    Route::get('/', function () {
        $store = \App\Models\Store::find(session('current_store_id'));

        return redirect()->route('menu.index', ['store' => $store->slug]);
    });

    // Dashboard routes (requires view-dashboard permission)
    Route::middleware(['permission:view-dashboard'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
        Route::get('dashboard/export/excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');
        Route::get('dashboard/export/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
    });

    // Category routes (requires manage-categories permission)
    Route::middleware(['permission:manage-categories'])->group(function () {
        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    // Product routes (requires manage-products permission)
    Route::middleware(['permission:manage-products'])->group(function () {
        Route::resource('products', ProductController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('products/generate-codes', [ProductController::class, 'generateCodes'])->name('products.generate-codes');
    });

    Route::get('/store/{store:slug}/menu/{taxonomy:slug?}', [MenuController::class, 'index'])->name('menu.index');

    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'addToCart'])->name('add');
        Route::post('/add-barcode', [CartController::class, 'addByBarcode'])->name('add-barcode');
        Route::post('/update', [CartController::class, 'update'])->name('update');
        Route::post('/remove', [CartController::class, 'remove'])->name('remove');
        Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/validate-discount', [OrderController::class, 'validateDiscount'])->name('validate-discount');
    });

    // Student routes (requires manage-students permission for write operations)
    Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
    Route::get('students/by-student-id', [StudentController::class, 'getByStudentId'])->name('students.by-student-id');
    Route::get('students/scan/{token}', [StudentController::class, 'getByQrToken'])->name('students.scan');
    Route::middleware(['permission:manage-students'])->group(function () {
        Route::get('students/export/pdf', [StudentController::class, 'exportBulkPdf'])->name('students.export-bulk-pdf');
        Route::get('students/export/qr', [StudentController::class, 'exportBulkQrPdf'])->name('students.export-bulk-qr');
        Route::resource('students', StudentController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::prefix('students')->name('students.')->group(function () {
            Route::post('/{student}/deposit', [StudentController::class, 'deposit'])->name('deposit');
            Route::post('/{student}/withdraw', [StudentController::class, 'withdraw'])->name('withdraw');
            Route::get('/{student}/transactions', [StudentController::class, 'transactions'])->name('transactions');
            Route::get('/{student}/balance', [StudentController::class, 'getBalance'])->name('balance');
            Route::get('/{student}/qr-code', [StudentController::class, 'qrCode'])->name('qr-code');
            Route::get('/{student}/export-pdf', [StudentController::class, 'exportSinglePdf'])->name('export-single-pdf');
            Route::get('/{student}/export-qr', [StudentController::class, 'exportSingleQrPdf'])->name('export-single-qr');
        });
    });

    // Student Dashboard routes
    Route::prefix('student-dashboard')->name('student-dashboard.')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('index');
        Route::get('/search', [StudentDashboardController::class, 'searchStudent'])->name('search');
        Route::get('/{student}', [StudentDashboardController::class, 'show'])->name('show');
        Route::get('/{student}/orders', [StudentDashboardController::class, 'getOrders'])->name('orders');
        Route::get('/{student}/export/excel', [StudentDashboardController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{student}/export/pdf', [StudentDashboardController::class, 'exportPdf'])->name('export.pdf');
    });

    // User management routes (only for store-admin and head-office-admin)
    Route::middleware(['permission:manage-store-users'])->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
