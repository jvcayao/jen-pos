<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::permanentRedirect('/', '/menu');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('products', ProductController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::post('products/generate-codes', [ProductController::class, 'generateCodes'])->name('products.generate-codes');

    Route::get('/menu/{taxonomy:slug?}', [MenuController::class, 'index'])->name('menu.index');

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

    Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
    Route::resource('students', StudentController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::prefix('students')->name('students.')->group(function () {
        Route::post('/{student}/deposit', [StudentController::class, 'deposit'])->name('deposit');
        Route::post('/{student}/withdraw', [StudentController::class, 'withdraw'])->name('withdraw');
        Route::get('/{student}/transactions', [StudentController::class, 'transactions'])->name('transactions');
        Route::get('/{student}/balance', [StudentController::class, 'getBalance'])->name('balance');
    });
});

require __DIR__.'/settings.php';
