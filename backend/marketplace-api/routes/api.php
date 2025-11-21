<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductGatewayController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ReplicationController;
use App\Http\Controllers\GlobalProductsController;
use App\Http\Controllers\UserMarketplaceControllers;

Route::middleware('internal.auth')->group(function () {
    //Usuarios Marketplace
    Route::get('/usuarios', [UserMarketplaceControllers::class, 'informacionUsuarios']);
    Route::get('/usuarios/{id}', [UserMarketplaceControllers::class, 'informacionUsuario']);
    Route::post('/usuarios/login', [UserMarketplaceControllers::class, 'login']);
    Route::post('/usuarios', [UserMarketplaceControllers::class, 'guardarUsuario']);
    Route::put('/usuarios/{id}', [UserMarketplaceControllers::class, 'actualizarUsuario']);
    //Productos Gateway
    Route::get('/gateway/products', [GatewayController::class, 'handleProducts']);
    Route::get('/gateway/productos', [GatewayController::class, 'handleAllProducts']);
    Route::get('/gateway/recommended/products/{id}', [GatewayController::class, 'handleProductsRecomends']);
    //
    Route::get('/gateway/products/{id}', [GatewayController::class, 'show']);
    Route::post('/gateway/products/bulk', [GatewayController::class, 'bulk']);
    Route::post('/gateway/products', [GatewayController::class, 'createProduct']);
    //
    Route::get('/gateway/productos/usuario/{user_id}', [GatewayController::class, 'handleAllProductsUsuarios']);
    Route::delete('/gateway/products/{id}', [GatewayController::class, 'deleteProduct']);
    Route::put('/gateway/products/{id}', [GatewayController::class, 'updataProduct']);
    Route::put("/gateway/prod/store", [GatewayController::class, 'bulkUpdate']);
});

Route::get('/products', [ProductGatewayController::class, 'index']);
//
//
Route::post('/replicate', [ReplicationController::class, 'receive'])->middleware('internal.auth');
Route::get('/replicated/{category}', [ReplicationController::class, 'getByCategory'])->middleware('internal.auth');
Route::get('/replicated/{category}/{id}', [ReplicationController::class, 'getProduct'])->middleware('internal.auth');
//
Route::get('/global/products', [GlobalProductsController::class, 'index']);
Route::get('/global/products/{category}', [GlobalProductsController::class, 'byCategory']);
