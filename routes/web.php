<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
| Storefront
*/
Route::get('/', HomeController::class)->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');

Route::get('/basket', [CartController::class, 'index'])->name('cart.index');
Route::post('/basket', [CartController::class, 'store'])->name('cart.store');
Route::patch('/basket/{book}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/basket/{book}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/order/{order:order_number}/thanks', [CheckoutController::class, 'success'])->name('checkout.success');

/*
| Admin
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'store'])->middleware('throttle:5,1');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::patch('books/{book}/toggle', [Admin\BookController::class, 'toggle'])->name('books.toggle');
        Route::resource('books', Admin\BookController::class)->except('show');

        Route::patch('categories/{category}/toggle', [Admin\CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('categories', Admin\CategoryController::class)->except('show');

        Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    });
});
