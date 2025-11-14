<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Categories CRUD powered by aliziodev/laravel-taxonomy
    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Products CRUD
    Route::resource('products', ProductController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/orders/{slug?}', [OrdersController::class, 'index'])->name('orders.index');
});

require __DIR__.'/settings.php';
