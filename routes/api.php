<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('productos', ProductController::class);
Route::apiResource('pedidos', OrderController::class);
