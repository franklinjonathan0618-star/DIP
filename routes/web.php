<?php

use App\Http\Controllers\PhotoboothCheckoutController;
use App\Http\Controllers\PhotoboothOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('photobooth.capture');

Route::get('/susun-foto', function () {
    return view('photobooth.arrangement');
})->name('photobooth.arrangement');

Route::get('/kirim-foto', [PhotoboothCheckoutController::class, 'show'])
    ->name('photobooth.checkout');
Route::post('/kirim-foto', [PhotoboothCheckoutController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('photobooth.checkout.store');
Route::get('/pesanan/{order:public_id}', [PhotoboothOrderController::class, 'show'])
    ->name('photobooth.orders.show');
Route::post('/pesanan/{order:public_id}/kirim-ulang', [PhotoboothOrderController::class, 'resend'])
    ->middleware('throttle:3,1')
    ->name('photobooth.orders.resend');
