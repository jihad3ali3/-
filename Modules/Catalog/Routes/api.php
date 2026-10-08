<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\ProductController;

Route::get('catalog/products', [ProductController::class, 'index'])->name('catalog.products.index');
Route::get('catalog/products/{product}', [ProductController::class, 'show'])->name('catalog.products.show');
