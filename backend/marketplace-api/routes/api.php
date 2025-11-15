<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductGatewayController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ReplicationController;
use App\Http\Controllers\GlobalProductsController;

Route::get('/products', [ProductGatewayController::class, 'index']);
//
Route::get('/gateway/products', [GatewayController::class, 'handleProducts']);
Route::get('/gateway/products/{id}', [GatewayController::class, 'show']);
Route::post('/gateway/products', [GatewayController::class, 'createProduct']);
Route::put('/gateway/products/{id}', [GatewayController::class, 'updataProduct']);
Route::delete('/gateway/products/{id}', [GatewayController::class, 'deleteProduct']);
//
Route::post('/replicate', [ReplicationController::class, 'receive'])->middleware('internal.auth');
Route::get('/replicated/{category}', [ReplicationController::class, 'getByCategory'])->middleware('internal.auth');
Route::get('/replicated/{category}/{id}', [ReplicationController::class, 'getProduct'])->middleware('internal.auth');
//
Route::get('/global/products', [GlobalProductsController::class, 'index']);
Route::get('/global/products/{category}', [GlobalProductsController::class, 'byCategory']);
