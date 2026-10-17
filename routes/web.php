<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

// Product implements getRouteKeyName() = 'slug', so this binds by slug.
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Cart (session based, keyed by product variant)
|--------------------------------------------------------------------------
*/

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->middleware('throttle:cart')->name('store');
    Route::patch('/{variant}', 'update')->middleware('throttle:cart')->name('update');
    Route::delete('/{variant}', 'destroy')->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Checkout (Cash on Delivery)
|--------------------------------------------------------------------------
*/

Route::controller(CheckoutController::class)->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->middleware('throttle:checkout')->name('store');
    // Order implements getRouteKeyName() = 'order_number'.
    Route::get('/thank-you/{order}', 'thankYou')->name('thankYou');
});

/*
|--------------------------------------------------------------------------
| Order tracking (public)
|--------------------------------------------------------------------------
*/

Route::get('/track', [TrackOrderController::class, 'form'])->name('track.form');
Route::post('/track', [TrackOrderController::class, 'show'])
    ->middleware('throttle:track')->name('track.show');

/*
|--------------------------------------------------------------------------
| Staff only: printable invoices
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/invoices/batch', [InvoiceController::class, 'batch'])->name('orders.invoice.batch');
    Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('orders.invoice');
});

/*
|--------------------------------------------------------------------------
| Search engines
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
*/

Route::controller(PageController::class)->name('pages.')->group(function () {
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'sendContact')->middleware('throttle:contact')->name('contact.send');
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/returns', 'returns')->name('returns');
});
