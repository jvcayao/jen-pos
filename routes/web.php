<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Categories CRUD powered by aliziodev/laravel-taxonomy
    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Products CRUD
    Route::resource('products', ProductController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/menu/{taxonomy:slug?}', [MenuController::class, 'index'])->name('menu.index');

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/remove', [CartController::class, 'remove']);
    Route::post('/cart/update', [CartController::class, 'update']);
    Route::post('/cart/add', [ProductController::class, 'addToCart']);

    Route::get('cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

require __DIR__.'/settings.php';
