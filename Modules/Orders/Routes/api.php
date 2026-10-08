<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrderController;

Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
