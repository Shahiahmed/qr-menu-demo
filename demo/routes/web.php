<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Guest menu. Single-tenant, so it lives at the site root — no /m/{slug} prefix.
Route::get('/', [MenuController::class, 'home'])->name('menu.home');

// Per-dish SEO pages (the Premium selling point).
Route::get('/d/{slug}', [MenuController::class, 'dish'])->name('menu.dish');

// Guest places an order from the table. Priced server-side (see controller).
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
