<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\EnsureInstalled;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/
Route::middleware(EnsureInstalled::class)->group(function () {

Route::controller(StoreController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/shop', 'shop')->name('shop');
    Route::get('/product', 'product')->name('product');
    Route::get('/cart', 'cart')->name('cart');
    Route::get('/checkout', 'checkout')->name('checkout');
    Route::get('/wishlist', 'wishlist')->name('wishlist');
    Route::get('/about', 'about')->name('about');
    Route::get('/shipping', 'shipping')->name('shipping');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/{page}.html', 'legacy')->where('page', 'index|shop|product|cart|checkout|wishlist|about|shipping|contact');
});

Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');

}); // EnsureInstalled (storefront)

/*
|--------------------------------------------------------------------------
| One-time web installer (for cPanel without SSH)
|--------------------------------------------------------------------------
*/
Route::get('/install', [InstallController::class, 'show'])->name('install');
Route::post('/install', [InstallController::class, 'run'])->middleware('throttle:10,1')->name('install.run');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(EnsureInstalled::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'show'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Orders
        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/export', [Admin\OrderController::class, 'export'])->name('orders.export');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [Admin\OrderController::class, 'invoice'])->name('orders.invoice');
        Route::put('/orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
        Route::put('/orders/{order}/items', [Admin\OrderController::class, 'updateItems'])->name('orders.items');
        Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        // Catalogue
        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::patch('/products/{product}/toggle', [Admin\ProductController::class, 'toggle'])->name('products.toggle');
        Route::get('/categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

        // Customers & messages
        Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::put('/customers/{customer}', [Admin\CustomerController::class, 'update'])->name('customers.update');
        Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages/read-all', [Admin\MessageController::class, 'readAll'])->name('messages.readAll');
        Route::get('/messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::patch('/messages/{message}/unread', [Admin\MessageController::class, 'unread'])->name('messages.unread');
        Route::delete('/messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        // Account
        Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        // Owner / admin only
        Route::middleware('can:manage')->group(function () {
            Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings');
            Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
            Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        });
    });
});
